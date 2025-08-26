<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramStatusCommand extends Command
{
    protected $signature = 'telegram:status';
    protected $description = 'Проверка статуса бота';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle(): int
    {
        $this->info('Проверка статуса бота...');

        $isLoggedIn = $this->telegramService->isLoggedIn();
        $this->info('Войти: ' . ($isLoggedIn ? 'YES' : 'NO'));

        if ($isLoggedIn) {
            $sessionInfo = $this->telegramService->getSessionInfo();
            if ($sessionInfo['success']) {
                $user = $sessionInfo['user'];
                $username = $user['username'] ?? $user['first_name'] ?? 'Неизвестный';
                $this->info("Пользователь: {$username}");
                $this->info("Файл сессии: {$sessionInfo['session_file']}");
                $this->info("Размер сессии: " . number_format($sessionInfo['session_size'] / 1024, 2) . ' KB');
            }
        }

        $apiId = env('TELEGRAM_API_ID');
        $apiHash = env('TELEGRAM_API_HASH');

        $this->info('API ID: ' . ($apiId ? 'SET' : 'MISSING'));
        $this->info('API Hash: ' . ($apiHash ? 'SET' : 'MISSING'));

        return 0;
    }
}
