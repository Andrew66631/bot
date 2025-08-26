<?php

namespace App\Services\Telegram;

use Exception;

class TelegramMessageService extends TelegramConnectionService
{
    public function getMessages(mixed $peer, int $limit = 50): array
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

    public function sendMessage(mixed $peer, string $message): array
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

    public function getMessage(mixed $peer, int $messageId): array
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
