<?php

namespace App\Services;

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use Exception;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private ?API $madeline = null;
    private string $sessionPath;

    public function __construct()
    {
        $this->sessionPath = storage_path('app/telegram_session.madeline');
    }

    private function initializeMadeline(?string $eventHandler = null): void
    {
        if ($this->madeline === null) {
            try {
                $settings = new Settings();

                $apiId = env('TELEGRAM_API_ID');
                $apiHash = env('TELEGRAM_API_HASH');

                $settings->getAppInfo()->setApiId((int)$apiId);
                $settings->getAppInfo()->setApiHash($apiHash);

                $this->madeline = new API($this->sessionPath, $settings);


            } catch (Exception $e) {
                Log::error('MadelineProto Ошибка инициализации: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    public function getClient(): API
    {
        $this->initializeMadeline();
        return $this->madeline;
    }

    public function getClientWithHandler(string $eventHandler): API
    {
        $this->initializeMadeline($eventHandler);
        return $this->madeline;
    }

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

    public function getDialogs(): array
    {
        try {
            $this->initializeMadeline();
            $dialogs = $this->madeline->getFullDialogs();
            return ['success' => true, 'dialogs' => $dialogs];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getMessages(int $peer, int $limit = 50): array
    {
        try {
            $this->initializeMadeline();
            $messages = $this->madeline->messages->getHistory(
                peer: $peer,
                limit: $limit
            );
            return ['success' => true, 'messages' => $messages];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendMessage(int $peer, string $message): array
    {
        try {
            $this->initializeMadeline();
            $result = $this->madeline->messages->sendMessage(
                peer: $peer,
                message: $message
            );

            $messageId = $result['id'] ?? $result['updates'][0]['id'] ?? null;

            return [
                'success' => true,
                'message_id' => $messageId,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function isLoggedIn(): bool
    {
        try {
            $this->initializeMadeline();
            $self = $this->madeline->getSelf();
            return $self !== null;
        } catch (\Exception $e) {
            return false;
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

    public function getSessionInfo(): array
    {
        try {
            $this->initializeMadeline();
            $self = $this->madeline->getSelf();

            return [
                'success' => true,
                'logged_in' => $self !== null,
                'user' => $self,
                'session_file' => file_exists($this->sessionPath) ? $this->sessionPath : null,
                'session_size' => file_exists($this->sessionPath) ? filesize($this->sessionPath) : 0
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function cleanupSession(): bool
    {
        try {
            if (file_exists($this->sessionPath)) {
                unlink($this->sessionPath);
            }
            $this->madeline = null;
            return true;
        } catch (\Exception $e) {
            Log::error('Не получилось очистить сессию: ' . $e->getMessage());
            return false;
        }
    }

    public function getMessage(int $peer, int $messageId): array
    {
        try {
            $this->initializeMadeline();

            $messages = $this->madeline->messages->getMessages(
                id: [
                    [
                        '_' => 'inputMessageID',
                        'id' => (int)$messageId
                    ]
                ]
            );

            if (empty($messages['messages'])) {
                return [
                    'success' => false,
                    'error' => 'Сообщение отсутствует'
                ];
            }

            $message = $messages['messages'][0];

            return [
                'success' => true,
                'message' => $message
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
