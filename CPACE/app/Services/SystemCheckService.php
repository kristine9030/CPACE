<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Live, in-process health checks for the Super Admin "System Checks" page.
 * Every check is read-only / non-destructive: the AI provider checks hit a
 * free metadata endpoint (list models) rather than a billed generation call,
 * and the email check opens an SMTP handshake without sending a message.
 */
class SystemCheckService
{
    /** @return array<int, array{key:string,label:string,status:string,message:string,latency_ms:?int}> */
    public function runAll(): array
    {
        return [
            $this->checkDatabase(),
            $this->checkSessionSigning(),
            $this->checkEmail(),
            $this->checkGemini(),
            $this->checkOpenRouter(),
            $this->checkAnthropic(),
        ];
    }

    public function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();
            DB::select('select 1');

            return $this->result('database', 'Database', 'ok', 'Connected and responding.', $start);
        } catch (\Throwable $e) {
            return $this->result('database', 'Database', 'fail', $this->safeMessage($e), $start);
        }
    }

    public function checkSessionSigning(): array
    {
        $start = microtime(true);

        try {
            $token = Str::random(24);
            $encrypted = Crypt::encryptString($token);
            $roundTrip = Crypt::decryptString($encrypted);

            if ($roundTrip !== $token) {
                return $this->result('session', 'Session Signing', 'fail', 'Encrypt/decrypt round-trip mismatch.', $start);
            }

            return $this->result('session', 'Session Signing', 'ok', 'APP_KEY round-trip verified.', $start);
        } catch (\Throwable $e) {
            return $this->result('session', 'Session Signing', 'fail', $this->safeMessage($e), $start);
        }
    }

    public function checkEmail(): array
    {
        $start = microtime(true);
        $mailer = config('mail.default');

        if ($mailer === 'log' || $mailer === 'array') {
            return $this->result('email', 'Email Delivery', 'warn', "Mailer is \"{$mailer}\" — nothing actually leaves the server in this environment.", $start);
        }

        try {
            $transport = Mail::mailer()->getSymfonyTransport();

            if (method_exists($transport, 'start')) {
                $transport->start();
                if (method_exists($transport, 'stop')) {
                    $transport->stop();
                }
            }

            return $this->result('email', 'Email Delivery', 'ok', "SMTP handshake to " . config('mail.mailers.smtp.host', 'mail host') . " succeeded. No message was sent.", $start);
        } catch (\Throwable $e) {
            return $this->result('email', 'Email Delivery', 'fail', $this->safeMessage($e), $start);
        }
    }

    public function checkGemini(): array
    {
        $start = microtime(true);
        $key = config('services.gemini.key');

        if (blank($key)) {
            return $this->result('gemini', 'Gemini', 'warn', 'No API key configured.', $start);
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['x-goog-api-key' => $key])
                ->get('https://generativelanguage.googleapis.com/v1beta/models');

            return $response->successful()
                ? $this->result('gemini', 'Gemini', 'ok', 'API key accepted, models endpoint reachable.', $start)
                : $this->result('gemini', 'Gemini', 'fail', "HTTP {$response->status()} from Gemini.", $start);
        } catch (\Throwable $e) {
            return $this->result('gemini', 'Gemini', 'fail', $this->safeMessage($e), $start);
        }
    }

    public function checkOpenRouter(): array
    {
        $start = microtime(true);
        $key = config('services.openrouter.key');

        if (blank($key)) {
            return $this->result('openrouter', 'OpenRouter', 'warn', 'No API key configured.', $start);
        }

        try {
            $response = Http::timeout(8)->withToken($key)->get('https://openrouter.ai/api/v1/models');

            return $response->successful()
                ? $this->result('openrouter', 'OpenRouter', 'ok', 'API key accepted, models endpoint reachable.', $start)
                : $this->result('openrouter', 'OpenRouter', 'fail', "HTTP {$response->status()} from OpenRouter.", $start);
        } catch (\Throwable $e) {
            return $this->result('openrouter', 'OpenRouter', 'fail', $this->safeMessage($e), $start);
        }
    }

    public function checkAnthropic(): array
    {
        $start = microtime(true);
        $key = config('services.anthropic.key');

        if (blank($key)) {
            return $this->result('anthropic', 'Claude (Anthropic)', 'warn', 'No API key configured.', $start);
        }

        try {
            $response = Http::timeout(8)->withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
            ])->get('https://api.anthropic.com/v1/models');

            return $response->successful()
                ? $this->result('anthropic', 'Claude (Anthropic)', 'ok', 'API key accepted, models endpoint reachable.', $start)
                : $this->result('anthropic', 'Claude (Anthropic)', 'fail', "HTTP {$response->status()} from Anthropic.", $start);
        } catch (\Throwable $e) {
            return $this->result('anthropic', 'Claude (Anthropic)', 'fail', $this->safeMessage($e), $start);
        }
    }

    private function result(string $key, string $label, string $status, string $message, float $start): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'message' => $message,
            'latency_ms' => (int) round((microtime(true) - $start) * 1000),
        ];
    }

    /** Never echo a raw exception message back to the browser — it can carry
     *  connection strings, hostnames, or other config details. */
    private function safeMessage(\Throwable $e): string
    {
        return 'Check failed: ' . Str::limit(class_basename($e), 60);
    }
}
