<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token') ?? env('TELEGRAM_BOT_TOKEN', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Xabar yuborish
     */
    public function sendMessage(int|string $chatId, string $text, array $options = []): ?array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ], $options);

        return $this->call('sendMessage', $params);
    }

    /**
     * Inline keyboard bilan xabar
     */
    public function sendMessageWithKeyboard(int|string $chatId, string $text, array $keyboard): ?array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    /**
     * Contact share tugmasi bilan
     */
    public function sendContactRequest(int|string $chatId, string $text, string $buttonText = "📱 Telefon raqamni yuborish"): ?array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => $buttonText, 'request_contact' => true]]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    /**
     * Reply keyboard tozalash
     */
    public function removeKeyboard(int|string $chatId, string $text): ?array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);
    }

    /**
     * Reply keyboard yuborish
     */
    public function sendReplyKeyboard(int|string $chatId, string $text, array $buttons): ?array
    {
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode([
                'keyboard' => $buttons,
                'resize_keyboard' => true,
                'is_persistent' => true,
            ]),
        ]);
    }

    /**
     * Xabarni o'zgartirish
     */
    public function editMessage(int|string $chatId, int $messageId, string $text, array $options = []): ?array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->call('editMessageText', $params);
    }

    /**
     * Callback query'ga javob
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): ?array
    {
        return $this->call('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
    }

    /**
     * Kanalga obuna tekshirish
     */
    public function getChatMember(string $chatId, int $userId): ?array
    {
        return $this->call('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * User kanalga obunami?
     */
    public function isUserSubscribed(string $channelId, int $userId): bool
    {
        $result = $this->getChatMember($channelId, $userId);
        if (!$result || !isset($result['result']['status'])) {
            return false;
        }
        $status = $result['result']['status'];
        return in_array($status, ['member', 'administrator', 'creator']);
    }

    /**
     * Webhook o'rnatish
     */
    public function setWebhook(string $url, array $options = []): ?array
    {
        return $this->call('setWebhook', array_merge([
            'url' => $url,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ], $options));
    }

    /**
     * Webhook o'chirish
     */
    public function deleteWebhook(): ?array
    {
        return $this->call('deleteWebhook', ['drop_pending_updates' => true]);
    }

    /**
     * Webhook holatini olish
     */
    public function getWebhookInfo(): ?array
    {
        return $this->call('getWebhookInfo');
    }

    /**
     * Bot ma'lumotlari
     */
    public function getMe(): ?array
    {
        return $this->call('getMe');
    }

    /**
     * Asosiy API chaqiruvi
     */
    protected function call(string $method, array $params = []): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('Telegram bot token not configured');
            return null;
        }

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/{$method}", $params);
            $data = $response->json();

            if (!($data['ok'] ?? false)) {
                Log::warning("Telegram API error: {$method}", [
                    'response' => $data,
                    'params' => $this->sanitizeForLog($params),
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("Telegram API exception: {$method}", [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function sanitizeForLog(array $params): array
    {
        $clean = $params;
        if (isset($clean['text']) && strlen($clean['text']) > 200) {
            $clean['text'] = substr($clean['text'], 0, 200) . '...';
        }
        return $clean;
    }
}