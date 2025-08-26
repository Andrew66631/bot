<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TelegramService;

class TelegramAuthMiddleware
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json(['error' => 'Нет токена'], 401);
        }

        if ($token !== 'test-telegram-token') {
            return response()->json(['error' => 'Токен не верный'], 401);
        }


        return $next($request);
    }
}
