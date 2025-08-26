<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramLoginCommand extends Command
{
    protected $signature = 'telegram:login';
    protected $description = 'Команда для логина';

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle(): int
    {
        if ($this->option('clean')) {
            $this->telegramService->cleanupSession();
            $this->info('Session cleaned up');
        }

        if ($this->telegramService->isLoggedIn()) {
            $sessionInfo = $this->telegramService->getSessionInfo();
            if ($sessionInfo['success'] && $sessionInfo['logged_in']) {
                $user = $sessionInfo['user'];
                $username = $user['username'] ?? $user['first_name'] ?? 'Unknown';
                $this->info("✓ Вы вошли как: {$username}");

                if (!$this->confirm('Хотите войти, используя другую учетную запись?')) {
                    return 0;
                }

                $this->telegramService->logout();
                $this->telegramService->cleanupSession();
                sleep(2);
            }
        }



        $phone = $this->ask('Введите номер телефона', 'вида +X XXX XXX XX XX');

        if (!preg_match('/^\+[0-9]{10,15}$/', $phone)) {
            $this->error('Номер не корректный используйте формат +X XXX XXX XX XX');
            return 1;
        }

        $this->info('Отправка проверочного кода ' . $phone . '...');
        $authResult = $this->telegramService->startLogin($phone);

        if (!$authResult['success']) {
            $this->error('Введите проверочный код: ' . $authResult['error']);

            if (strpos($authResult['error'], 'FLOOD_WAIT') !== false) {
                $this->info('Ждите и повторите позже');
            }

            return 1;
        }

        $this->info('✓ Код верный!');
        $this->info('Please check your Telegram app for the code');

        $code = $this->ask('Проверьте код в приложении Telegram.');

        if (!preg_match('/^[0-9]{5}$/', $code)) {
            $this->error('Неверный формат кода. Введите 5 цифр.');
            return 1;
        }

        $this->info('Верификация...');
        $confirmResult = $this->telegramService->completeLogin($code);

        if (!$confirmResult['success']) {
            $this->error('Ошибка проверки кода: ' . $confirmResult['error']);

            if ($this->confirm('Попробуйте еще раз с новым кодом?')) {
                $code = $this->ask('Введите новый код:');
                $confirmResult = $this->telegramService->completeLogin($code);

                if (!$confirmResult['success']) {
                    $this->error('Вторая попытка не удалась: ' . $confirmResult['error']);
                    return 1;
                }
            } else {
                return 1;
            }
        }

        $this->info('✓ ' . ($confirmResult['message'] ?? 'Вход совершен!'));

        if (isset($confirmResult['user']['username'])) {
            $this->info('Вы вошли в систему как: @' . $confirmResult['user']['username']);
        } else if (isset($confirmResult['user']['first_name'])) {
            $this->info('Вы вошли в систему как: ' . $confirmResult['user']['first_name']);
        }

        $sessionInfo = $this->telegramService->getSessionInfo();
        if ($sessionInfo['success']) {
            $this->info('Файл сессии: ' . $sessionInfo['session_file']);
            $this->info('Размер файла сессии: ' . number_format($sessionInfo['session_size'] / 1024, 2) . ' KB');
        }
        return 0;
    }
}
