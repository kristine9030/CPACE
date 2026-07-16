<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AiTutorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiTutorController extends Controller
{
    /**
     * Chat endpoint for the floating AI Tutor widget. The client sends the
     * whole conversation each turn; the service handles the Gemini →
     * OpenRouter fallback.
     */
    public function chat(Request $request, AiTutorService $ai)
    {
        $data = $request->validate([
            'messages'           => ['required', 'array', 'min:1', 'max:30'],
            'messages.*.role'    => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:6000'],
        ]);

        try {
            $result = $ai->chat($data['messages']);

            return response()->json(['ok' => true] + $result);
        } catch (\Throwable $e) {
            Log::error('AI Tutor: all providers failed.', ['error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'The AI tutor is unavailable right now. Please try again in a moment.',
            ], 503);
        }
    }
}
