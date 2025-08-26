<?php

namespace App\Http\Controllers;

use app\Services\TelegramService;
use Illuminate\Http\JsonResponse;

class DialogController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function index(): JsonResponse
    {
        if (!$this->telegramService->isLoggedIn()) {
            return response()->json(['error' => 'Нет авторизации'], 401);
        }

        $result = $this->telegramService->getDialogs();

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['dialogs' => $result['dialogs']]);
    }
}
