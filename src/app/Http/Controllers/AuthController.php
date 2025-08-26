<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function auth(AuthRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->telegramService->isLoggedIn()) {
            return response()->json(['message' => 'Вы авторизованы']);
        }

        $result = $this->telegramService->startLogin($data['phone']);

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json([
            'message' => 'Code sent',
            'phone_code_hash' => $result['phone_code_hash'],
            'timeout' => $result['timeout'] ?? 60
        ]);
    }

    public function confirmAuth(AuthRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->telegramService->completeLogin(
            $data['code'],
            $data['phone_code_hash'] ?? ''
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json([
            'message' => 'Совершен вход',
            'user' => $result['user'] ?? null
        ]);
    }

    public function checkAuth(): JsonResponse
    {
        $isLoggedIn = $this->telegramService->isLoggedIn();
        $sessionInfo = $this->telegramService->getSessionInfo();

        return response()->json([
            'logged_in' => $isLoggedIn,
            'session_info' => $sessionInfo
        ]);
    }

    public function logout(): JsonResponse
    {
        $result = $this->telegramService->logout();

        if ($result) {
            return response()->json(['message' => 'Совершен выход']);
        }

        return response()->json(['error' => 'Выход не удался'], 400);
    }

    public function cleanup(): JsonResponse
    {
        $result = $this->telegramService->cleanupSession();

        if ($result) {
            return response()->json(['message' => 'Сессия очищена']);
        }

        return response()->json(['error' => 'Не удалось очистить сессию'], 400);
    }
}
