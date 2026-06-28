<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotNotification extends Model
{
    protected $table = 'bot_notifications';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type', 'icon', 'title', 'message',
        'data', 'action_url', 'sent_at', 'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (!$m->created_at) $m->created_at = now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * O'qilgan deb belgilash
     */
    public function markAsRead(): bool
    {
        if ($this->read_at) return true;
        return $this->update(['read_at' => now()]);
    }

    /**
     * Type'ga qarab icon olish
     */
    public function getDisplayIcon(): string
    {
        if ($this->icon) return $this->icon;

        return match ($this->type) {
            'topup_approved' => 'check_circle',
            'topup_rejected' => 'cancel',
            'task_completed' => 'task_alt',
            'referral_joined' => 'group_add',
            'daily_bonus' => 'card_giftcard',
            'low_balance' => 'warning',
            'ticket_reply' => 'mark_email_read',
            'broadcast' => 'campaign',
            'welcome' => 'celebration',
            default => 'notifications',
        };
    }

    /**
     * Type'ga qarab rang olish
     */
    public function getColorClass(): string
    {
        return match ($this->type) {
            'topup_approved', 'task_completed', 'daily_bonus', 'referral_joined' => 'notif-success',
            'topup_rejected', 'low_balance' => 'notif-warning',
            'ticket_reply' => 'notif-info',
            'broadcast' => 'notif-primary',
            default => 'notif-default',
        };
    }

    /**
     * Type'ga qarab title qaytarish
     */
    public function getDisplayTitle(): string
    {
        if ($this->title) return $this->title;

        return match ($this->type) {
            'topup_approved' => "💰 To'lov tasdiqlandi",
            'topup_rejected' => "❌ To'lov rad etildi",
            'task_completed' => "✅ Vazifa bajarildi",
            'referral_joined' => "🎉 Yangi do'st qo'shildi",
            'daily_bonus' => "🎁 Kunlik bonus",
            'low_balance' => "⚠️ Balans tugayapti",
            'ticket_reply' => "📩 Ticket javobi",
            'broadcast' => "📢 Yangilik",
            'welcome' => "👋 Xush kelibsiz",
            default => "🔔 Bildirishnoma",
        };
    }

    /**
     * Vaqt — "5 daqiqa oldin" formatida
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }
}