<?php

namespace App\Http\Controllers;

use App\Services\SupportChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function chat(Request $request, SupportChatService $chat): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $history = $request->session()->get('support_chat_history', []);
        $reply = $chat->reply($data['message'], $history);

        $history[] = ['role' => 'user', 'content' => $data['message']];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        $request->session()->put('support_chat_history', array_slice($history, -20));

        return response()->json([
            'reply' => $reply,
            'ai' => $chat->isAiEnabled(),
            'provider' => $chat->providerLabel(),
        ]);
    }
}
