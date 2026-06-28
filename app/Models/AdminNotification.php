<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'type', 'priority', 'title', 'message', 'data',
        'target_url', 'read_at', 'read_by',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Tez yaratish + Telegram ga yuborish
     */
    public static function notify(
        string $type,
        string $title,
        ?string $message = null,
        array $data = [],
        string $priority = 'normal',
        ?string $targetUrl = null
    ): self {
        $notification = self::create([
            'type' => $type,
            'priority' => $priority,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'target_url' => $targetUrl,
            'created_at' => now(),
        ]);

        // Telegram ga yuborish (urgent/high bo'lsa)
        if (in_array($priority, ['high', 'urgent'])) {
            self::sendToTelegram($notification);
        }

        return $notification;
    }

    /**
     * Admin Telegram chat ga yuborish
     */
    protected static function sendToTelegram(self $notification): void
    {
        $token = SystemSetting::get('telegram_bot_token');
        $chatId = SystemSetting::get('telegram_admin_chat_id');

        if (!$token || !$chatId) return;

        $emoji = match($notification->priority) {
            'urgent' => '🚨',
            'high' => '⚠️',
            'normal' => '🔔',
            default => '📝',
        };

        $text = "{$emoji} *{$notification->title}*\n\n" . ($notification->message ?? '');
        if ($notification->target_url) {
            $text .= "\n\n" . config('app.url') . $notification->target_url;
        }

        try {
            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            \Log::error('Telegram notify failed: ' . $e->getMessage());
        }
    }

    public function markRead(?int $adminId = null): void
    {
        $this->update([
            'read_at' => now(),
            'read_by' => $adminId ?? auth()->id(),
        ]);
    }

    public function scopeUnread($q) { return $q->whereNull('read_at'); }
    public function scopeUrgent($q) { return $q->where('priority', 'urgent'); }
}