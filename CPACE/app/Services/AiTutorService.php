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
        $system = $this->systemPrompt();

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

    private function askOpenRouter(string $system, array $messages): string
    {
        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->withToken(config('services.openrouter.key'))
            ->withHeaders([
                'HTTP-Referer' => config('app.url'),
                'X-Title'      => 'CPACE CPA Reviewer',
            ])
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'      => config('services.openrouter.model'),
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

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are the CPACE AI Tutor, a study assistant inside a CPA board exam reviewer app for Philippine accountancy students. The CPA subjects are FAR (Financial Accounting and Reporting), AFAR (Advanced Financial Accounting and Reporting), AUD (Auditing), TAX (Taxation), RFBT (Regulatory Framework for Business Transactions), and MS (Management Services).

When a student highlights a term or passage from their review notes and asks about it, explain the concept clearly and give the rationale behind it — why it works that way, common exam traps, and a short example when helpful. Reference the relevant Philippine standards or laws (PFRS/PAS, PSA, NIRC as amended, etc.) when applicable.

Keep answers concise and exam-focused: a short direct explanation first, then supporting detail. Keep answers under about 250 words — the chat window is small — unless the student explicitly asks you to elaborate or continue. Use simple formatting only: short paragraphs, dashes for lists, **bold** for key terms. Avoid tables and nested lists. Stay on CPA review topics; if asked something unrelated, gently steer back to studying. Do not invent citations you are unsure of.
PROMPT;
    }
}
