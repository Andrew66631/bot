<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramTestCommand extends Command
{
    protected $signature = 'telegram:test';
    protected $description = 'Отправка тестового сообщения';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle(): int
    {
        $this->info('Отправка тестового сообщения...');

        if (!$this->telegramService->isLoggedIn()) {
            $this->error('Не авторизован в тг.');
            return 1;
        }

        $sessionInfo = $this->telegramService->getSessionInfo();
        if (!$sessionInfo['success']) {
            $this->error('Невозможно получить информацию о сеансе.');
            return 1;
        }

        $user = $sessionInfo['user'];
        $userId = $user['id'] ?? null;

        if (!$userId) {
            $this->error('Невозможно получить идентификатор пользователя.');
            $this->info('Инфо: ' . json_encode($user, JSON_PRETTY_PRINT));
            return 1;
        }

        $result = $this->telegramService->sendMessage($userId, 'Привет Андрей!');

        if ($result['success']) {
            $this->info('Тестовое сообщение отправлено');
            $this->info("Message ID: " . ($result['message_id'] ?? 'N/A'));
            $this->info("Full result: " . json_encode($result['result'] ?? [], JSON_PRETTY_PRINT));
        } else {
            $this->error('Не удалось отправить сообщение: ' . $result['error']);
        }

        return 0;
    }
}
