<?php

namespace App\Services\Agent;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Har agent uchun alohida Telegram bot klienti (token per-agent).
 * CloudAPI'ning o'z boti uchun App\Services\Telegram\TelegramService ishlatiladi;
 * bu esa foydalanuvchi keltirgan @BotFather tokeni bilan ishlaydi.
 */
class AgentTelegram
{
    protected string $baseUrl;

    public function __construct(protected string $token)
    {
        $this->baseUrl = "https://api.telegram.org/bot{$token}";
    }

    /** Bot ma'lumotlari — tokenni tekshirish va username olish uchun. */
    public function getMe(): ?array
    {
        return $this->call('getMe');
    }

    /** Xabar yuborish (uzun matnlar bo'laklarga bo'linadi). Standart: oddiy matn. */
    public function sendMessage(int|string $chatId, string $text, array $options = []): ?array
    {
        $last = null;
        foreach ($this->splitText($text) as $part) {
            $last = $this->call('sendMessage', array_merge([
                'chat_id' => $chatId,
                'text'    => $part,
                'disable_web_page_preview' => true,
            ], $options));
        }
        return $last;
    }

    /** "yozmoqda..." holati. */
    public function sendChatAction(int|string $chatId, string $action = 'typing'): ?array
    {
        return $this->call('sendChatAction', ['chat_id' => $chatId, 'action' => $action]);
    }

    public function setWebhook(string $url, string $secretToken): ?array
    {
        return $this->call('setWebhook', [
            'url'             => $url,
            'secret_token'    => $secretToken,
            'allowed_updates' => json_encode(['message']),
            'drop_pending_updates' => true,
        ]);
    }

    public function deleteWebhook(): ?array
    {
        return $this->call('deleteWebhook', ['drop_pending_updates' => true]);
    }

    /** Webhook holati — diagnostika (URL, pending, oxirgi xato). */
    public function getWebhookInfo(): ?array
    {
        return $this->call('getWebhookInfo');
    }

    /** Telegram 4096 belgili chegara — xavfsiz bo'laklarga bo'lish. */
    protected function splitText(string $text, int $limit = 4000): array
    {
        $text = trim($text);
        if ($text === '') return [''];
        if (mb_strlen($text) <= $limit) return [$text];

        $parts = [];
        while (mb_strlen($text) > $limit) {
            $slice = mb_substr($text, 0, $limit);
            $cut = mb_strrpos($slice, "\n");
            if ($cut === false || $cut < $limit * 0.5) $cut = $limit;
            $parts[] = trim(mb_substr($text, 0, $cut));
            $text = trim(mb_substr($text, $cut));
        }
        if ($text !== '') $parts[] = $text;
        return $parts;
    }

    protected function call(string $method, array $params = []): ?array
    {
        try {
            $response = Http::timeout(20)->post("{$this->baseUrl}/{$method}", $params);
            $data = $response->json();
            if (!($data['ok'] ?? false)) {
                Log::warning("AgentTelegram API error: {$method}", ['response' => $data]);
            }
            return $data;
        } catch (\Throwable $e) {
            Log::error("AgentTelegram exception: {$method}", ['message' => $e->getMessage()]);
            return null;
        }
    }
}
