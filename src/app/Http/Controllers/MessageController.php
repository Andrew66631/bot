<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function index(string $dialogId): JsonResponse
    {
        $result = $this->telegramService->getMessages($dialogId);

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['messages' => $result['messages']]);
    }

    public function store(StoreMessageRequest $request, string $dialogId): JsonResponse
    {
        $data = $request->validated();

        $result = $this->telegramService->sendMessage(
            $dialogId,
            $data['text']
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json([
            'message' => 'Message sent successfully',
            'message_id' => $result['message_id']
        ]);
    }

    public function show(string $dialogId, string $messageId): JsonResponse
    {
        $result = $this->telegramService->getMessage($dialogId, $messageId);

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['message' => $result['message']]);
    }
}
