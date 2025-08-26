<?php

namespace App\Console\Commands;

use App\Handlers\TelegramEventHandler;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramBotCommand extends Command
{
    protected $signature = 'telegram:bot';
    protected $description = 'Запуск';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle(): int
    {
        if (!$this->telegramService->isLoggedIn()) {
            $this->error('Не выполнена авторизация в Telegram.');
            return 1;
        }

        $sessionInfo = $this->telegramService->getSessionInfo();
        if ($sessionInfo['success'] && $sessionInfo['logged_in']) {
            $user = $sessionInfo['user'];
            $username = $user['username'] ?? $user['first_name'] ?? 'Unknown';
            $this->info("Вы вошли как: {$username}");
        }

        $this->info('Бот запущен. Нажмите Ctrl+C, чтобы остановить..');

        try {
            $madeline = $this->telegramService->getClientWithHandler(TelegramEventHandler::class);
            $madeline->start();

        } catch (\Exception $e) {
            $this->error('Ошибка запуска бота: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
