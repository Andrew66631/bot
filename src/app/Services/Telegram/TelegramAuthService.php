<?php

namespace App\Services\Telegram;

use Exception;
use Illuminate\Support\Facades\Log;

class TelegramAuthService extends TelegramConnectionService
{
    public function startLogin(string $phone): array
    {
        try {
            $this->initializeMadeline();
            $sentCode = $this->madeline->phoneLogin($phone);

            return [
                'success' => true,
                'timeout' => $sentCode['timeout'] ?? 60,
                'phone_code_hash' => $sentCode['phone_code_hash'],
                'next_step' => 'enter_code'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'details' => 'Проверьте номер телефона и апи данные'
            ];
        }
    }

    public function completeLogin(string $code, string $phoneCodeHash = ''): array
    {
        try {
            $this->initializeMadeline();
            $authorization = $this->madeline->completePhoneLogin($code);

            if ($authorization['_'] === 'auth.authorization') {
                return [
                    'success' => true,
                    'user' => $authorization['user'],
                    'message' => 'Совершен вход'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Неизвестная ошибка: ' . $authorization['_']
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function logout(): bool
    {
        try {
            if ($this->isLoggedIn()) {
                $this->madeline->logout();
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error('Не удалось выйти: ' . $e->getMessage());
            return false;
        }
    }
}
