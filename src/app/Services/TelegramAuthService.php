<?php

namespace App\Services;

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use Exception;
use Illuminate\Support\Facades\Log;

class TelegramAuthService
{
    private ?API $madeline = null;
    private string $sessionPath;

    public function __construct()
    {
        $this->sessionPath = storage_path('app/telegram_session.madeline');
    }

    public function initialize(): void
    {
        if ($this->madeline === null) {
            try {
                $settings = new Settings();

                $settings->getConnection()->setTimeout(30);
                $settings->getConnection()->setRetry(false);

                $this->madeline = new API($this->sessionPath, $settings);

            } catch (Exception $e) {
                Log::error('Инициализация не удалась: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    public function startPhoneLogin(string $phone): array
    {
        try {
            $this->initialize();

            $result = $this->madeline->auth->sendCode(
                phone_number: $phone,
                settings: [
                    '_' => 'codeSettings',
                    'allow_flashcall' => false,
                    'current_number' => true,
                    'allow_app_hash' => true
                ]
            );

            return [
                'success' => true,
                'phone_code_hash' => $result['phone_code_hash'],
                'timeout' => $result['timeout'] ?? 120,
                'type' => $result['type']['_'] ?? 'sms'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function completePhoneLogin(string $code, string $phoneCodeHash): array
    {
        try {
            $this->initialize();

            $result = $this->madeline->auth->signIn([
                'phone_code' => $code,
                'phone_code_hash' => $phoneCodeHash,
                'phone_number' => ''
            ]);

            return [
                'success' => true,
                'user' => $result
            ];

        } catch (Exception $e) {
            try {
                $result = $this->madeline->auth->signUp([
                    'phone_code' => $code,
                    'phone_code_hash' => $phoneCodeHash,
                    'first_name' => 'User',
                    'last_name' => 'Bot'
                ]);

                return [
                    'success' => true,
                    'user' => $result,
                    'new_user' => true
                ];

            } catch (Exception $signUpError) {
                return [
                    'success' => false,
                    'error' => 'Вход: ' . $e->getMessage() . ' | Регистрация: ' . $signUpError->getMessage()
                ];
            }
        }
    }

    public function isLoggedIn(): bool
    {
        try {
            $this->initialize();
            $self = $this->madeline->getSelf();
            return $self !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSessionInfo(): array
    {
        try {
            $this->initialize();
            $self = $this->madeline->getSelf();

            return [
                'success' => true,
                'logged_in' => $self !== null,
                'user' => $self,
                'session_file' => $this->sessionPath,
                'file_exists' => file_exists($this->sessionPath)
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function cleanup(): bool
    {
        try {
            if (file_exists($this->sessionPath)) {
                unlink($this->sessionPath);
            }
            if (file_exists($this->sessionPath . '.lock')) {
                unlink($this->sessionPath . '.lock');
            }
            $this->madeline = null;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getClient(): ?API
    {
        $this->initialize();
        return $this->madeline;
    }
}
