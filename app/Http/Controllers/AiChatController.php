<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiChatRequest;
use App\Services\AiChatService;

class AiChatController extends Controller
{
    public function chat(AiChatRequest $request, AiChatService $aiChatService)
    {
        $validated = $request->validated();

        try {
            $reply = $aiChatService->reply($validated['message'], $validated['history'] ?? []);

            return response()->json([
                'reply' => $reply,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Gemini request failed',
                'details' => $e->getMessage(),
            ], 502);
        }
    }
}
