<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Tutor backend used by the floating chat widget.
 *
 * Provider strategy: Gemini is always tried first. If Gemini errors out
 * (bad key, rate limit, outage), the request falls back to OpenRouter and
 * Gemini is put on a short cooldown so we stop hammering it. Once the
 * cooldown expires the next request tries Gemini again automatically.
 */
class AiTutorService
{
    private const GEMINI_COOLDOWN_KEY = 'ai_tutor.gemini_down';
    private const GEMINI_COOLDOWN_MINUTES = 5;
    private const TIMEOUT_SECONDS = 45;

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{reply: string, provider: string}
     */
    public function chat(array $messages): array
    {
        return $this->generate($this->systemPrompt(), $messages);
    }

    /**
     * Personalised performance insights for the student Performance page.
     * Takes a compact summary of the student's real quiz stats and returns
     * ready-to-render insight cards.
     *
     * @return array{insights: array, provider: string}
     */
    public function performanceInsights(array $summary): array
    {
        $prompt = "Here is the student's current performance data (all numbers are real, from the database):\n\n"
            . json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            . "\n\nGenerate the insight cards now. Respond with ONLY the JSON array.";

        $result = $this->generate($this->insightsSystemPrompt(), [
            ['role' => 'user', 'content' => $prompt],
        ]);

        return [
            'insights' => $this->parseInsights($result['reply']),
            'provider' => $result['provider'],
        ];
    }

    /**
     * Run one completion through the provider chain (Gemini → OpenRouter).
     *
     * @return array{reply: string, provider: string}
     */
    private function generate(string $system, array $messages): array
    {
        if (config('services.gemini.key') && ! Cache::has(self::GEMINI_COOLDOWN_KEY)) {
            try {
                $reply = $this->askGemini($system, $messages);

                if ($reply !== null && $reply !== '') {
                    return ['reply' => $reply, 'provider' => 'gemini'];
                }
            } catch (\Throwable $e) {
                Log::warning('AI Tutor: Gemini failed, falling back to OpenRouter.', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Gemini misbehaved — rest it for a few minutes, then retry it.
            Cache::put(self::GEMINI_COOLDOWN_KEY, true, now()->addMinutes(self::GEMINI_COOLDOWN_MINUTES));
        }

        $reply = $this->askOpenRouter($system, $messages);

        return ['reply' => $reply, 'provider' => 'openrouter'];
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
                // Generous cap: Gemini also spends "thinking" tokens from this
                // budget, so a low value cuts answers off mid-sentence.
                'generationConfig'   => ['temperature' => 0.4, 'maxOutputTokens' => 4096],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 300));
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $text  = collect($parts)->pluck('text')->filter()->implode("\n");

        return $text !== '' ? $text : null;
    }

    /**
     * Try each configured OpenRouter model in order until one answers.
     * The free models run on different upstream providers, so when one is
     * rate-limited the next usually is not.
     */
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
                Log::warning("AI Tutor: OpenRouter model {$model} failed, trying next.", [
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
                'model'      => $model,
                'messages'   => array_merge(
                    [['role' => 'system', 'content' => $system]],
                    $messages
                ),
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
     * Parse the model's insight JSON into safe, render-ready cards.
     * Whitelists tones/icons and truncates text so a misbehaving reply can
     * never break the UI.
     */
    private function parseInsights(string $reply): array
    {
        // Models sometimes wrap JSON in ```json fences or add prose — cut
        // everything outside the outermost [ ... ].
        $start = strpos($reply, '[');
        $end   = strrpos($reply, ']');
        if ($start === false || $end === false || $end <= $start) {
            throw new \RuntimeException('AI insights reply contained no JSON array.');
        }

        $items = json_decode(substr($reply, $start, $end - $start + 1), true);
        if (! is_array($items)) {
            throw new \RuntimeException('AI insights reply was not valid JSON.');
        }

        $tones = ['red', 'amber', 'blue', 'green'];
        $icons = [
            'fa-chart-line', 'fa-book-open', 'fa-clock', 'fa-brain', 'fa-fire',
            'fa-bullseye', 'fa-trophy', 'fa-lightbulb', 'fa-triangle-exclamation',
            'fa-calendar-check', 'fa-arrow-trend-up', 'fa-arrow-trend-down',
        ];

        $insights = [];
        foreach ($items as $item) {
            if (! is_array($item) || empty($item['title']) || empty($item['desc'])) {
                continue;
            }
            $insights[] = [
                'tone'  => in_array($item['tone'] ?? '', $tones, true) ? $item['tone'] : 'blue',
                'icon'  => in_array($item['icon'] ?? '', $icons, true) ? $item['icon'] : 'fa-lightbulb',
                'title' => mb_substr(trim((string) $item['title']), 0, 60),
                'desc'  => mb_substr(trim((string) $item['desc']), 0, 200),
            ];
            if (count($insights) === 5) {
                break;
            }
        }

        if (empty($insights)) {
            throw new \RuntimeException('AI insights reply contained no usable items.');
        }

        return $insights;
    }

    private function insightsSystemPrompt(): string
    {
        return <<<'PROMPT'
You are the CPACE AI study coach inside a CPA board exam reviewer app for Philippine accountancy students. You will receive one student's real performance data as JSON: overall accuracy and week-over-week change, questions attempted, weak and strong topics (with accuracy and attempt counts), accuracy per CPA subject, study streak, average time per question, and quiz-mode usage.

Respond with ONLY a JSON array of 3 to 5 insight cards, no prose, no markdown fences. Each card is an object:
{"tone": "red|amber|blue|green", "icon": "<one of: fa-chart-line, fa-book-open, fa-clock, fa-brain, fa-fire, fa-bullseye, fa-trophy, fa-lightbulb, fa-triangle-exclamation, fa-calendar-check, fa-arrow-trend-up, fa-arrow-trend-down>", "title": "<max 45 chars>", "desc": "<max 140 chars, one or two sentences>"}

Tone meaning: red = urgent weakness, amber = needs attention, blue = neutral tip, green = strength/praise.

Guidelines:
- Speak directly to the student ("you"). Be specific: name actual topics, subjects, and numbers from the data instead of generic advice.
- Prioritise the most impactful findings: the weakest topics, a falling or rising accuracy trend, subjects below their passing threshold, poor pacing, or an impressive streak worth praising.
- Give an actionable next step in each desc (what to practice, which mode to use, how to schedule review).
- Include at least one positive card when the data honestly supports it.
- If the student has very little data, say so and encourage them to take more quizzes so you can analyse their performance.
PROMPT;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are the CPACE AI Tutor, a study assistant inside a CPA board exam reviewer app for Philippine accountancy students. The CPA subjects are FAR (Financial Accounting and Reporting), AFAR (Advanced Financial Accounting and Reporting), AUD (Auditing), TAX (Taxation), RFBT (Regulatory Framework for Business Transactions), and MS (Management Services).

When a student highlights a term or passage from their review notes and asks about it, explain the concept clearly and give the rationale behind it — why it works that way, common exam traps, and a short example when helpful. Reference the relevant Philippine standards or laws (PFRS/PAS, PSA, NIRC as amended, etc.) when applicable.

Keep answers concise and exam-focused: a short direct explanation first, then supporting detail. Keep answers under about 250 words — the chat window is small — unless the student explicitly asks you to elaborate or continue. Use simple formatting only: short paragraphs, dashes for lists, **bold** for key terms. Avoid tables and nested lists. Stay on CPA review topics; if asked something unrelated, gently steer back to studying. Do not invent citations you are unsure of.
PROMPT;
    }
}
