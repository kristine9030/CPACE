<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Turns a student's own review note into a short practice quiz, so they can
 * check whether what they wrote down actually stuck.
 *
 * Provider strategy mirrors AiQuestionAssistantService: Gemini first, falling
 * back to OpenRouter (with a short Gemini cooldown) on failure. Kept separate
 * from that class because the grounding rules are different — these questions
 * must come from the note itself, not from the CPALE syllabus at large.
 */
class AiNoteQuizService
{
    private const GEMINI_COOLDOWN_KEY = 'ai_note_quiz.gemini_down';
    private const GEMINI_COOLDOWN_MINUTES = 5;
    private const TIMEOUT_SECONDS = 60;

    /** Note text longer than this is truncated before it goes to the model. */
    private const MAX_NOTE_CHARS = 6000;

    public const MIN_QUESTIONS = 3;
    public const MAX_QUESTIONS = 10;

    /**
     * Build a multiple-choice quiz from one note.
     *
     * @return array<int, array{question_text: string, choices: array<int, array{text: string, is_correct: bool}>, explanation: string}>
     */
    public function generate(
        string $title,
        string $content,
        ?string $subjectName,
        ?string $topicName,
        int $count
    ): array {
        $count = max(self::MIN_QUESTIONS, min(self::MAX_QUESTIONS, $count));

        $prompt = "Note title: {$title}\n";

        if ($subjectName) {
            $prompt .= "Subject: {$subjectName}\n";
        }
        if ($topicName) {
            $prompt .= "Topic: {$topicName}\n";
        }

        $prompt .= "Number of questions: {$count}\n\n"
            . "--- BEGIN NOTE ---\n"
            . mb_substr($content, 0, self::MAX_NOTE_CHARS) . "\n"
            . "--- END NOTE ---\n\n"
            . 'Respond with ONLY the JSON object described in the system instructions.';

        $reply = $this->generateCompletion($this->systemPrompt(), [
            ['role' => 'user', 'content' => $prompt],
        ]);

        return $this->parseQuiz($reply, $count);
    }

    /**
     * Run one completion through the provider chain (Gemini → OpenRouter).
     */
    private function generateCompletion(string $system, array $messages): string
    {
        if (config('services.gemini.key') && ! Cache::has(self::GEMINI_COOLDOWN_KEY)) {
            try {
                $reply = $this->askGemini($system, $messages);

                if ($reply !== null && $reply !== '') {
                    return $reply;
                }
            } catch (\Throwable $e) {
                Log::warning('AI Note Quiz: Gemini failed, falling back to OpenRouter.', [
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
                'generationConfig'   => ['temperature' => 0.4, 'maxOutputTokens' => 4096],
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
                Log::warning("AI Note Quiz: OpenRouter model {$model} failed, trying next.", [
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
                'temperature' => 0.4,
                'max_tokens'  => 4096,
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
     * Parse the reply into a validated, whitelisted shape. Individual bad
     * questions are dropped rather than failing the whole quiz; we only throw
     * if nothing usable came back at all.
     *
     * @return array<int, array{question_text: string, choices: array, explanation: string}>
     */
    private function parseQuiz(string $reply, int $requested): array
    {
        $start = strpos($reply, '{');
        $end   = strrpos($reply, '}');
        if ($start === false || $end === false || $end <= $start) {
            throw new \RuntimeException('AI quiz reply contained no JSON object.');
        }

        $data = json_decode(substr($reply, $start, $end - $start + 1), true);
        if (! is_array($data) || ! is_array($data['questions'] ?? null)) {
            throw new \RuntimeException('AI quiz reply was not valid JSON.');
        }

        $questions = [];

        foreach ($data['questions'] as $raw) {
            $parsed = $this->parseQuestion($raw);

            if ($parsed !== null) {
                $questions[] = $parsed;
            }

            if (count($questions) === $requested) {
                break;
            }
        }

        if (count($questions) < self::MIN_QUESTIONS) {
            throw new \RuntimeException('AI quiz reply had fewer than ' . self::MIN_QUESTIONS . ' usable questions.');
        }

        return $questions;
    }

    /**
     * @return array{question_text: string, choices: array, explanation: string}|null
     */
    private function parseQuestion(mixed $raw): ?array
    {
        if (! is_array($raw) || trim((string) ($raw['question_text'] ?? '')) === '') {
            return null;
        }

        $choices = $raw['choices'] ?? null;
        if (! is_array($choices) || count($choices) !== 4) {
            return null;
        }

        $parsed = [];
        $correct = 0;

        foreach (array_values($choices) as $choice) {
            if (! is_array($choice) || trim((string) ($choice['text'] ?? '')) === '') {
                return null;
            }

            $isCorrect = (bool) ($choice['is_correct'] ?? false);
            $correct += $isCorrect ? 1 : 0;

            $parsed[] = [
                'text'       => trim((string) $choice['text']),
                'is_correct' => $isCorrect,
            ];
        }

        if ($correct !== 1) {
            return null;
        }

        return [
            'question_text' => trim((string) $raw['question_text']),
            'choices'       => $parsed,
            'explanation'   => trim((string) ($raw['explanation'] ?? '')),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are a CPA board exam item writer for CPACE, a review platform for Philippine accountancy students preparing for the CPALE. A student has written a study note and wants to test whether they actually retained it.

You will be given the student's note. Write multiple-choice questions that check understanding of THAT NOTE.

Respond with ONLY a single JSON object, no prose, no markdown fences, in exactly this shape:
{"questions": [{"question_text": "<question stem>", "choices": [{"text": "<choice A>", "is_correct": true|false}, {"text": "<choice B>", "is_correct": true|false}, {"text": "<choice C>", "is_correct": true|false}, {"text": "<choice D>", "is_correct": true|false}], "explanation": "<why the correct choice is correct, pointing back to what the note says>"}]}

Grounding rules — these are strict:
- Every question must be answerable from the note alone. Do not test facts the note never mentions.
- If the note is thin on content, write fewer questions rather than inventing material. Quality over hitting the requested count.
- Do not quote the note back verbatim as the stem; test whether the student understood it, not whether they can pattern-match a sentence.
- If the note contains an error relative to Philippine standards (PFRS/PAS, PSA, NIRC as amended, the Corporation Code), still write the question against the correct standard, and use the explanation to flag the discrepancy plainly.

Question quality:
- Exactly one choice must have is_correct = true.
- All 4 choices must be plausible and roughly similar in length; wrong choices should reflect common student errors, not be obviously wrong.
- Vary what you test: definitions, applying a rule to a short scenario, computations, and comparisons between concepts the note covers.
- Keep each question self-contained: never refer to "the note", "the passage above", or any external material in the stem.
PROMPT;
    }
}
