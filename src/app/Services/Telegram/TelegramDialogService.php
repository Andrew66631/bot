<?php

namespace App\Services\Telegram;

use Exception;
use Illuminate\Support\Facades\Log;

class TelegramDialogService extends TelegramConnectionService
{
    public function getDialogs(): array
    {
        try {
            $this->initializeMadeline();

            $self = $this->madeline->getSelf();
            if (!$self) {
                return ['success' => false, 'error' => 'Not authorized'];
            }

            $dialogs = $this->madeline->getFullDialogs();

            if (empty($dialogs)) {
                return ['success' => true, 'dialogs' => [], 'message' => 'No dialogs found'];
            }

            $formattedDialogs = [];

            foreach ($dialogs as $dialog) {
                try {
                    if (!isset($dialog['peer'])) {
                        continue;
                    }

                    $peer = $dialog['peer'];
                    $entity = $this->madeline->getInfo($peer);

                    $formattedDialogs[] = [
                        'peer' => $peer,
                        'entity' => $entity,
                        'unread_count' => $dialog['unread_count'] ?? 0,
                        'last_message_id' => $dialog['top_message'] ?? 0
                    ];

                } catch (Exception $e) {
                    Log::warning("Error processing dialog: " . $e->getMessage());
                    continue;
                }
            }

            return [
                'success' => true,
                'dialogs' => $formattedDialogs,
                'total_count' => count($dialogs),
                'processed_count' => count($formattedDialogs)
            ];

        } catch (Exception $e) {
            Log::error('getDialogs error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDialogByUsername(string $username): array
    {
        try {
            $this->initializeMadeline();

            if (!$this->isLoggedIn()) {
                return ['success' => false, 'error' => 'Not authorized', 'code' => 401];
            }

            $entity = $this->madeline->getInfo($username);

            $messages = $this->madeline->messages->getHistory([
                'peer' => $username,
                'limit' => 100
            ]);

            $formattedMessages = [];
            if (isset($messages['messages']) && is_array($messages['messages'])) {
                foreach ($messages['messages'] as $message) {
                    $formattedMessages[] = [
                        'id' => $message['id'] ?? null,
                        'date' => $message['date'] ?? null,
                        'text' => $message['message'] ?? '',
                        'outgoing' => $message['out'] ?? false
                    ];
                }
            }

            return [
                'success' => true,
                'dialog' => [
                    'type' => $entity['_'] ?? 'unknown',
                    'title' => $entity['title'] ?? $entity['first_name'] ?? $username,
                    'username' => $username
                ],
                'messages' => $formattedMessages,
                'total' => count($formattedMessages)
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'code' => 500];
        }
    }
}
