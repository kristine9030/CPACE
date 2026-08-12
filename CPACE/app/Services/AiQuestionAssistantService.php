<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI writing assistant for faculty building the test bank: drafts a full
 * question (choices + explanation) from a subject/topic, and rewords an
 * existing question into a new variant.
 *
 * Provider strategy mirrors AiTutorService: Gemini is tried first, falling
 * back to OpenRouter (and a short Gemini cooldown) on failure. Kept as a
 * separate class from AiTutorService because the two have different prompts,
 * response shapes, and parsing/validation needs.
 */
class AiQuestionAssistantService
{
    private const GEMINI_COOLDOWN_KEY = 'ai_question_assistant.gemini_down';
    private const GEMINI_COOLDOWN_MINUTES = 5;
    private const TIMEOUT_SECONDS = 45;

    /**
     * Draft a full question grounded in the given subject/topic and the
     * faculty's existing questions for that topic (so the AI doesn't repeat
     * what's already in the bank).
     *
     * @param  array<int, string>  $existingQuestions
     * @return array{question_text: string, explanation: string, choices?: array, tf_answer?: bool}
     */
    public function draftQuestion(
        string $subjectName,
        string $topicName,
        string $difficulty,
        string $questionType,
        array $existingQuestions,
        ?string $seedIdea
    ): array {
        $prompt = "Subject: {$subjectName}\nTopic: {$topicName}\nDifficulty: {$difficulty}\nQuestion type: "
            . ($questionType === 'true_false' ? 'True/False' : 'Multiple choice (4 options, exactly one correct)')
            . "\n";

        if ($seedIdea) {
            $prompt .= "\nThe faculty member wants the question to be about: {$seedIdea}\n";
        }

        if (! empty($existingQuestions)) {
            $prompt .= "\nQuestions already in the test bank for this topic (write something that tests a DIFFERENT angle or sub-concept, do not duplicate these):\n";
            foreach (array_slice($existingQuestions, 0, 15) as $q) {
                $prompt .= '- ' . mb_substr($q, 0, 200) . "\n";
            }
        }

        $prompt .= "\nRespond with ONLY the JSON object described in the system instructions.";

        $reply = $this->generate($this->draftSystemPrompt($questionType), [
            ['role' => 'user', 'content' => $prompt],
        ]);

        return $this->parseDraft($reply, $questionType);
    }

    /**
     * Reword an existing question, keeping its meaning and correct answer
     * identical, for use as a student-facing variant.
     */
    public function rewriteVariant(string $subjectName, string $topicName, string $originalQuestion): string
    {
        $prompt = "Subject: {$subjectName}\nTopic: {$topicName}\n\nOriginal question:\n{$originalQuestion}\n\n"
            . 'Respond with ONLY the reworded question text, nothing else — no quotes, no labels, no explanation.';

        $reply = $this->generate($this->variantSystemPrompt(), [
            ['role' => 'user', 'content' => $prompt],
        ]);

        $reply = trim($reply, " \t\n\r\0\x0B\"'");

        if ($reply === '') {
            throw new \RuntimeException('AI variant reply was empty.');
        }

        return $reply;
    }

    /**
     * Run one completion through the provider chain (Gemini → OpenRouter).
     */
    private function generate(string $system, array $messages): string
    {
        if (config('services.gemini.key') && ! Cache::has(self::GEMINI_COOLDOWN_KEY)) {
            try {
                $reply = $this->askGemini($system, $messages);

                if ($reply !== null && $reply !== '') {
                    return $reply;
                }
            } catch (\Throwable $e) {
                Log::warning('AI Question Assistant: Gemini failed, falling back to OpenRouter.', [
                    'error' => $e->getMessage(),
                ]);
            }

            Cache::put(self::GEMINI_COOLDOWN_KEY, true, now()->addMinutes(self::GEMINI_COOLDOWN_MINUTES));
        }

        return $this->askOpenRouter($system, $messages);
    }

    private function askGemini(string $system, array $messages): ?string
    {
        $model = config('services.gemini.model');

        $contents = array_map(fn ($m) => [
            'role'  => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ], $messages);

        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents'           => $contents,
                'generationConfig'   => ['temperature' => 0.5, 'maxOutputTokens' => 2048],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 300));
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $text  = collect($parts)->pluck('text')->filter()->implode("\n");

        return $text !== '' ? $text : null;
    }

    private function askOpenRouter(string $system, array $messages): string
    {
        $lastError = 'no OpenRouter models configured';

        foreach (config('services.openrouter.models', []) as $model) {
            $model = trim($model);
            if ($model === '') {
                continue;
            }

            try {
                return $this->askOpenRouterModel($model, $system, $messages);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("AI Question Assistant: OpenRouter model {$model} failed, trying next.", [
                    'error' => $lastError,
                ]);
            }
        }

        throw new \RuntimeException('All OpenRouter models failed. Last error: ' . $lastError);
    }

    private function askOpenRouterModel(string $model, string $system, array $messages): string
    {
        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->withToken(config('services.openrouter.key'))
            ->withHeaders([
                'HTTP-Referer' => config('app.url'),
                'X-Title'      => 'CPACE CPA Reviewer',
            ])
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'       => $model,
                'messages'    => array_merge([['role' => 'system', 'content' => $system]], $messages),
                'temperature' => 0.5,
                'max_tokens'  => 2048,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenRouter HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 300));
        }

        $text = trim((string) $response->json('choices.0.message.content'));

        if ($text === '') {
            throw new \RuntimeException('OpenRouter returned an empty reply.');
        }

        return $text;
    }

    /**
     * Parse the draft JSON into a safe, whitelisted shape. Throws if the
     * model didn't return something usable — caller falls back gracefully.
     */
    private function parseDraft(string $reply, string $questionType): array
    {
        $start = strpos($reply, '{');
        $end   = strrpos($reply, '}');
        if ($start === false || $end === false || $end <= $start) {
            throw new \RuntimeException('AI draft reply contained no JSON object.');
        }

        $data = json_decode(substr($reply, $start, $end - $start + 1), true);
        if (! is_array($data) || empty($data['question_text'])) {
            throw new \RuntimeException('AI draft reply was not valid JSON.');
        }

        $result = [
            'question_text' => trim((string) $data['question_text']),
            'explanation'   => trim((string) ($data['explanation'] ?? '')),
        ];

        if ($questionType === 'true_false') {
            if (! is_bool($data['tf_answer'] ?? null)) {
                throw new \RuntimeException('AI draft reply missing a boolean tf_answer.');
            }
            $result['tf_answer'] = $data['tf_answer'];

            return $result;
        }

        $choices = $data['choices'] ?? null;
        if (! is_array($choices) || count($choices) !== 4) {
            throw new \RuntimeException('AI draft reply did not contain exactly 4 choices.');
        }

        $labels = ['a', 'b', 'c', 'd'];
        $parsed = [];
        $correctCount = 0;
        foreach ($labels as $i => $label) {
            $c = $choices[$i] ?? null;
            if (! is_array($c) || ! isset($c['text']) || trim((string) $c['text']) === '') {
                throw new \RuntimeException("AI draft reply choice {$label} is missing text.");
            }
            $isCorrect = (bool) ($c['is_correct'] ?? false);
            $correctCount += $isCorrect ? 1 : 0;
            $parsed[$label] = ['text' => trim((string) $c['text']), 'is_correct' => $isCorrect];
        }

        if ($correctCount !== 1) {
            throw new \RuntimeException('AI draft reply must mark exactly one choice correct.');
        }

        $result['choices'] = $parsed;

        return $result;
    }

    private function draftSystemPrompt(string $questionType): string
    {
        $shape = $questionType === 'true_false'
            ? '{"question_text": "<statement to evaluate>", "tf_answer": true|false, "explanation": "<why it is true/false>"}'
            : '{"question_text": "<question stem>", "choices": [{"text": "<choice A>", "is_correct": true|false}, {"text": "<choice B>", "is_correct": true|false}, {"text": "<choice C>", "is_correct": true|false}, {"text": "<choice D>", "is_correct": true|false}], "explanation": "<why the correct choice is correct>"}';

        return <<<PROMPT
You are a CPA board exam item writer for CPACE, a review platform for Philippine accountancy students preparing for the CPALE. You write exam-quality test bank questions grounded in Philippine standards (PFRS/PAS, PSA, NIRC as amended, the Corporation Code, and other applicable Philippine laws) and CPALE syllabus conventions.

Given a subject, topic, difficulty, and question type, write ONE original, exam-style question for that exact topic. Respond with ONLY a single JSON object, no prose, no markdown fences, in exactly this shape:
{$shape}

Guidelines:
- Match the requested difficulty: easy = a single direct concept; moderate = requires applying a rule to a short scenario; difficult = multi-step computation or a scenario with a distractor trap.
- For multiple choice, all 4 choices must be plausible and roughly similar in length; wrong choices should reflect common student errors, not be obviously wrong.
- Exactly one choice must have is_correct = true.
- The explanation must justify the correct answer with a brief rationale a student can learn from — cite the specific standard/law/section when relevant.
- Do not duplicate any question listed as already existing in the bank — cover a different angle or sub-concept of the topic.
- Keep the question self-contained: don't refer to "the passage above" or any external material.
PROMPT;
    }

    private function variantSystemPrompt(): string
    {
        return <<<'PROMPT'
You are helping a CPA review faculty member write an alternative wording (a "variant") of an existing test bank question, so students can't just memorise one phrasing.

Rules — these are strict:
- The meaning, the facts, and the correct answer MUST stay exactly the same. Do not change any number, name, or condition.
- Keep technical/accounting/legal terms (e.g. FIFO, VAT, NRV, PFRS references) exactly as written — only reword the surrounding framing language.
- If the question contains "NOT" or any other negation, it must remain in the reworded version, with the same logical meaning.
- Do not turn a multiple-choice stem into a different question type, and don't add or remove information.
- Output only the reworded question text — no choices, no explanation, no quotes, no preamble.
PROMPT;
    }
}
