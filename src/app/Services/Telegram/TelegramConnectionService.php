<?php

namespace App\Services\Telegram;

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use Exception;
use Illuminate\Support\Facades\Log;

class TelegramConnectionService
{
    protected ?API $madeline = null;
    protected string $sessionPath;

    public function __construct()
    {
        $this->sessionPath = storage_path('app/telegram_session.madeline');
    }

    protected function initializeMadeline(): void
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
}
