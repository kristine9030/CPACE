<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI fallback for the question-import flow: used when the free rule-based
 * parser (QuestionImportParser) can't confidently find questions in a file's
 * extracted text, and for images (which can only be read by a vision model).
 *
 * Provider strategy mirrors AiQuestionAssistantService (Gemini first, since
 * it also does vision; OpenRouter as a text-only fallback for documents).
 */
class AiQuestionImportService
{
    private const GEMINI_COOLDOWN_KEY = 'ai_question_import.gemini_down';
    private const GEMINI_COOLDOWN_MINUTES = 5;
    private const TIMEOUT_SECONDS = 90;
    // Keep each AI request well under the model's context window and out of
    // "wall of text" territory that makes it skip/merge questions.
    private const MAX_CHARS_PER_CHUNK = 9000;

    /**
     * Structure a photo/scan (e.g. a computation problem) into questions via
     * vision. Gemini only — OpenRouter's free-tier models here are text-only.
     *
     * @return array<int, array>
     */
    public function extractFromImage(string $path, string $mimeType): array
    {
        if (! config('services.gemini.key')) {
            throw new \RuntimeException('Image import needs the AI vision service, which is not configured right now.');
        }

        $base64 = base64_encode(file_get_contents($path));
        $reply = $this->askGeminiVision($this->systemPrompt(), $base64, $mimeType);

        return $this->parseItems($reply);
    }

    /**
     * Structure free-form extracted text into questions. Splits long text
     * into chunks so one huge PDF doesn't blow the model's context or get
     * silently truncated.
     *
     * @return array<int, array>
     */
    public function extractFromText(string $text): array
    {
        $chunks = $this->chunk(trim($text));
        $items = [];

        foreach ($chunks as $chunk) {
            $reply = $this->generate($this->systemPrompt(), [
                ['role' => 'user', 'content' => "Text extracted from a faculty's file:\n\n{$chunk}"],
            ]);
            array_push($items, ...$this->parseItems($reply));
        }

        return $items;
    }

    /** @return array<int, string> */
    private function chunk(string $text): array
    {
        if (mb_strlen($text) <= self::MAX_CHARS_PER_CHUNK) {
            return [$text];
        }

        // Chunk on paragraph boundaries so a question is never cut in half.
        $paragraphs = preg_split('/\n{2,}/', $text) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            if ($current !== '' && mb_strlen($current) + mb_strlen($paragraph) > self::MAX_CHARS_PER_CHUNK) {
                $chunks[] = $current;
                $current = '';
            }
            $current .= ($current === '' ? '' : "\n\n") . $paragraph;
        }
        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks ?: [$text];
    }

    private function generate(string $system, array $messages): string
    {
        if (config('services.gemini.key') && ! Cache::has(self::GEMINI_COOLDOWN_KEY)) {
            try {
                $reply = $this->askGemini($system, $messages);
                if ($reply !== null && $reply !== '') {
                    return $reply;
                }
            } catch (\Throwable $e) {
                Log::warning('AI Question Import: Gemini failed, falling back to OpenRouter.', ['error' => $e->getMessage()]);
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
                'generationConfig'   => ['temperature' => 0.2, 'maxOutputTokens' => 8192],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 300));
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $text = collect($parts)->pluck('text')->filter()->implode("\n");

        return $text !== '' ? $text : null;
    }

    private function askGeminiVision(string $system, string $base64Image, string $mimeType): string
    {
        $model = config('services.gemini.model');

        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [
                        ['text' => 'Read every question in this image (including any computation/numeric problems) and structure them per the system instructions.'],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]],
                    ],
                ]],
                'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 8192],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini vision HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 300));
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $text = collect($parts)->pluck('text')->filter()->implode("\n");

        if ($text === '') {
            throw new \RuntimeException('Gemini vision returned an empty reply.');
        }

        return $text;
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
                Log::warning("AI Question Import: OpenRouter model {$model} failed, trying next.", ['error' => $lastError]);
            }
        }

        throw new \RuntimeException('All OpenRouter models failed. Last error: ' . $lastError);
    }

    private function askOpenRouterModel(string $model, string $system, array $messages): string
    {
        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->withToken(config('services.openrouter.key'))
            ->withHeaders(['HTTP-Referer' => config('app.url'), 'X-Title' => 'CPACE CPA Reviewer'])
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'       => $model,
                'messages'    => array_merge([['role' => 'system', 'content' => $system]], $messages),
                'temperature' => 0.2,
                'max_tokens'  => 8192,
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
     * Parse the model's reply into whitelisted item rows. Skips (rather than
     * throws on) any individual malformed question so one bad entry doesn't
     * sink the whole batch — the faculty reviews everything anyway.
     *
     * @return array<int, array>
     */
    private function parseItems(string $reply): array
    {
        $start = strpos($reply, '[');
        $end = strrpos($reply, ']');
        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $data = json_decode(substr($reply, $start, $end - $start + 1), true);
        if (! is_array($data)) {
            return [];
        }

        $items = [];
        foreach ($data as $raw) {
            $item = $this->normalizeItem($raw);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private function normalizeItem($raw): ?array
    {
        if (! is_array($raw) || empty($raw['question_text'])) {
            return null;
        }

        $type = ($raw['question_type'] ?? 'mcq') === 'true_false' ? 'true_false' : 'mcq';
        $difficulty = in_array($raw['difficulty'] ?? null, ['easy', 'moderate', 'difficult'], true)
            ? $raw['difficulty'] : 'moderate';

        if ($type === 'true_false') {
            if (! is_bool($raw['tf_answer'] ?? null)) {
                return null;
            }
            $choices = [
                ['label' => 'A', 'text' => 'True', 'is_correct' => $raw['tf_answer'] === true],
                ['label' => 'B', 'text' => 'False', 'is_correct' => $raw['tf_answer'] === false],
            ];

            return [
                'question_text' => trim((string) $raw['question_text']),
                'question_type' => 'true_false',
                'choices'       => $choices,
                'explanation'   => trim((string) ($raw['explanation'] ?? '')) ?: null,
                'difficulty'    => $difficulty,
                'confidence'    => 75,
            ];
        }

        $rawChoices = $raw['choices'] ?? null;
        if (! is_array($rawChoices) || count($rawChoices) < 2) {
            return null;
        }

        $labels = ['A', 'B', 'C', 'D'];
        $choices = [];
        $correctCount = 0;
        foreach (array_values($rawChoices) as $i => $c) {
            if (! is_array($c) || ! isset($c['text']) || trim((string) $c['text']) === '' || ! isset($labels[$i])) {
                continue;
            }
            $isCorrect = (bool) ($c['is_correct'] ?? false);
            $correctCount += $isCorrect ? 1 : 0;
            $choices[] = ['label' => $labels[$i], 'text' => trim((string) $c['text']), 'is_correct' => $isCorrect];
        }

        if (count($choices) < 2) {
            return null;
        }

        return [
            'question_text' => trim((string) $raw['question_text']),
            'question_type' => 'mcq',
            'choices'       => $choices,
            'explanation'   => trim((string) ($raw['explanation'] ?? '')) ?: null,
            'difficulty'    => $difficulty,
            'confidence'    => $correctCount === 1 ? 75 : 35,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are extracting exam questions from a faculty member's uploaded file (or photo) for a CPA review platform's Test Bank. The source may be messy: inconsistent numbering, OCR noise, tables, or handwritten/typed computation problems.

Find EVERY question present and respond with ONLY a JSON array, no prose, no markdown fences, in exactly this shape:
[
  {
    "question_text": "<the full question stem, including any given numbers/data for a computation problem>",
    "question_type": "mcq" | "true_false",
    "choices": [{"text": "<choice text>", "is_correct": true|false}, ...],   // omit for true_false
    "tf_answer": true|false,   // only for true_false
    "explanation": "<brief rationale for the correct answer, or a short solution/computation if none was given in the source>",
    "difficulty": "easy" | "moderate" | "difficult"
  }
]

Rules:
- If the source already states the correct answer, use it exactly — do not second-guess it.
- If the source does NOT state a correct answer (e.g. a bare computation problem), work it out yourself and mark the correct choice/tf_answer, and say so briefly in the explanation (e.g. "Computed: ...").
- Keep multiple-choice questions to the choices actually given (2-4). Do not invent extra choices.
- Preserve numbers, currency, dates, and formulas in the question text exactly as given.
- If a block of text is clearly not a question (headers, page numbers, instructions), skip it.
- If nothing in the source is a gradable question, return an empty array [].
PROMPT;
    }
}
