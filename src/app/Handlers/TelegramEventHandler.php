<?php

namespace App\Handlers;

use danog\MadelineProto\EventHandler;
use Illuminate\Support\Facades\Log;

class TelegramEventHandler extends EventHandler
{

    const ADMIN = "GoncharovAndre";

    /**
     * @return string[]
     */
    public function getReportPeers()
    {
        return [self::ADMIN];
    }

    /**
     * @param array $update
     * @return void
     */
    public function onUpdateNewMessage(array $update): void
    {
        if ($update['message']['_'] === 'messageEmpty') {
            return;
        }

        $this->onMessage($update['message']);
    }

    /**
     * @param array $message
     * @return void
     */
    public function onMessage(array $message): void
    {
        try {
            if (isset($message['out']) && $message['out']) {
                return;
            }

            $peer = $message['peer_id'];
            $messageId = $message['id'];
            $senderId = $message['from_id'] ?? null;
            $text = $message['message'] ?? '';

            Log::info('Received message', [
                'peer' => $peer,
                'message_id' => $messageId,
                'sender_id' => $senderId,
                'text' => $text
            ]);

            if (strpos($text, '/start') === 0) {
                $this->messages->sendMessage([
                    'peer' => $peer,
                    'message' => "Hello! I'm a Telegram bot. How can I help you?",
                    'reply_to_msg_id' => $messageId
                ]);
                Log::info('Sent response to /start command');
            }

            elseif (strpos($text, '/help') === 0) {
                $this->messages->sendMessage([
                    'peer' => $peer,
                    'message' => "Available commands:\n/start - Start the bot\n/help - Show this help",
                    'reply_to_msg_id' => $messageId
                ]);
            }

            else {
                $this->messages->sendMessage([
                    'peer' => $peer,
                    'message' => "I received your message: \"$text\"",
                    'reply_to_msg_id' => $messageId
                ]);
                Log::info('Echo response sent');
            }

        } catch (\Exception $e) {
            Log::error('Сообщение об ошибке обработки: ' . $e->getMessage());
        }
    }

    /**
     * @param array $update
     * @return void
     */
    public function onAny(array $update): void
    {
        Log::debug('Получено обновление', ['update_type' => $update['_'] ?? 'unknown']);
    }

    /**
     * @return void
     */
    public function onStart(): void
    {
        Log::info('TelegramEventHandler успешно запущен');
    }

    /**
     * @param array $update
     * @return void
     */
    public function onUpdateSelf(array $update): void
    {
        Log::info('Бот инициализирован');
    }
}
