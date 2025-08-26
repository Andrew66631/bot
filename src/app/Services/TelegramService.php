<?php

namespace App\Services;

use App\Services\Telegram\TelegramAuthService;
use App\Services\Telegram\TelegramConnectionService;
use App\Services\Telegram\TelegramDialogService;
use App\Services\Telegram\TelegramMessageService;

class TelegramService
{
    private TelegramConnectionService $connectionService;
    private TelegramAuthService $authService;
    private TelegramDialogService $dialogService;
    private TelegramMessageService $messageService;

    public function __construct(
        TelegramConnectionService $connectionService,
        TelegramAuthService $authService,
        TelegramDialogService $dialogService,
        TelegramMessageService $messageService
    ) {
        $this->connectionService = $connectionService;
        $this->authService = $authService;
        $this->dialogService = $dialogService;
        $this->messageService = $messageService;
    }

    // Connection methods
    public function getClient(): \danog\MadelineProto\API
    {
        return $this->connectionService->getClient();
    }

    public function isLoggedIn(): bool
    {
        return $this->connectionService->isLoggedIn();
    }

    public function getSessionInfo(): array
    {
        return $this->connectionService->getSessionInfo();
    }

    public function cleanupSession(): bool
    {
        return $this->connectionService->cleanupSession();
    }

    // Auth methods
    public function startLogin(string $phone): array
    {
        return $this->authService->startLogin($phone);
    }

    public function completeLogin(string $code, string $phoneCodeHash = ''): array
    {
        return $this->authService->completeLogin($code, $phoneCodeHash);
    }

    public function logout(): bool
    {
        return $this->authService->logout();
    }

    // Dialog methods
    public function getDialogs(): array
    {
        return $this->dialogService->getDialogs();
    }

    public function getDialogByUsername(string $username): array
    {
        return $this->dialogService->getDialogByUsername($username);
    }

    // Message methods
    public function getMessages(mixed $peer, int $limit = 50): array
    {
        return $this->messageService->getMessages($peer, $limit);
    }

    public function sendMessage(mixed $peer, string $message): array
    {
        return $this->messageService->sendMessage($peer, $message);
    }

    public function getMessage(mixed $peer, int $messageId): array
    {
        return $this->messageService->getMessage($peer, $messageId);
    }
}
