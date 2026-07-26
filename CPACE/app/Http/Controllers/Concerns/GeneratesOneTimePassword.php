<?php

namespace App\Http\Controllers\Concerns;

trait GeneratesOneTimePassword
{
    /**
     * Human-readable one-time password, e.g. "Kf7p-Rq3m".
     */
    private function generateOneTimePassword(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $pick = fn () => $chars[random_int(0, strlen($chars) - 1)];
        $block = fn () => implode('', array_map($pick, range(1, 4)));

        return $block() . '-' . $block();
    }
}
