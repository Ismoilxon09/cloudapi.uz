<?php

namespace App\Services\Telegram;

use App\Models\BotNotification;
use App\Models\User;

class BotNotifier
{
    protected TelegramService $tg;

    public function __construct(TelegramService $tg)
    {
        $this->tg = $tg;
    }

    /**
     * Foydalanuvchiga xabar yuborish
     */
    public function notify(User $user, string $type, string $message, array $data = []): bool
    {
        if (!$user->telegram_id) {
            return false;
        }

        // Bot orqali yuborish
        $result = $this->tg->sendMessage($user->telegram_id, $message);
        $sent = $result && ($result['ok'] ?? false);

        // Tarixga yozish — web notifications uchun ham
        BotNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'message' => $message,
            'data' => !empty($data) ? $data : null,
            'sent_at' => $sent ? now() : null,
            'action_url' => $data['action_url'] ?? null,
        ]);

        return $sent;
    }

    /**
     * Topup tasdiqlandi
     */
    public function notifyTopupApproved(User $user, float $amount): bool
    {
        $message = "💰 <b>Hisobingiz to'ldirildi!</b>\n\n";
        $message .= "Miqdor: <b>+" . number_format($amount, 0, '.', ' ') . " so'm</b>\n";
        $message .= "Yangi balans: <b>" . number_format($user->wallet->balance_uzs, 0, '.', ' ') . " so'm</b>\n\n";
        $message .= "🎯 Endi API ishlatishingiz mumkin!";

        return $this->notify($user, 'topup_approved', $message, ['amount' => $amount]);
    }

    /**
     * Topup rad etildi
     */
    public function notifyTopupRejected(User $user, float $amount, string $reason = ''): bool
    {
        $message = "❌ <b>To'lov tasdiqlanmadi</b>\n\n";
        $message .= "Miqdor: " . number_format($amount, 0, '.', ' ') . " so'm\n";
        if ($reason) {
            $message .= "Sabab: {$reason}\n";
        }
        $message .= "\n📞 Savol bo'lsa: @coder_nurmatov";

        return $this->notify($user, 'topup_rejected', $message, ['amount' => $amount, 'reason' => $reason]);
    }

    /**
     * Vazifa bajarilganda
     */
    public function notifyTaskCompleted(User $user, string $taskName, int $reward): bool
    {
        $message = "✅ <b>Vazifa bajarildi!</b>\n\n";
        $message .= "📌 {$taskName}\n";
        $message .= "🎁 Mukofot: <b>+{$reward} GP</b>\n\n";
        $message .= "💎 Jami bonus: <b>" . number_format($user->wallet->bonus_balance_uzs, 0, '.', ' ') . " GP</b>";

        return $this->notify($user, 'task_completed', $message, ['task' => $taskName, 'reward' => $reward]);
    }

    /**
     * Referral keldi
     */
    public function notifyReferralJoined(User $referrer, string $newUserName, int $reward): bool
    {
        $message = "🎉 <b>Yangi do'st keldi!</b>\n\n";
        $message .= "👤 {$newUserName} sizning taklif linkingiz orqali ro'yxatdan o'tdi.\n";
        $message .= "🎁 Mukofot: <b>+{$reward} GP</b>\n\n";
        $message .= "💎 Bonus balans: <b>" . number_format($referrer->wallet->bonus_balance_uzs, 0, '.', ' ') . " GP</b>";

        return $this->notify($referrer, 'referral_joined', $message, ['referred' => $newUserName, 'reward' => $reward]);
    }

    /**
     * Daily bonus
     */
    public function notifyDailyBonus(User $user, int $reward, int $streak): bool
    {
        $message = "🎁 <b>Kunlik bonus!</b>\n\n";
        $message .= "Mukofot: <b>+{$reward} GP</b>\n";
        $message .= "🔥 Streak: <b>{$streak} kun</b>\n\n";
        $message .= "💎 Bonus: <b>" . number_format($user->wallet->bonus_balance_uzs, 0, '.', ' ') . " GP</b>";

        return $this->notify($user, 'daily_bonus', $message, ['reward' => $reward, 'streak' => $streak]);
    }

    /**
     * Balans kam
     */
    public function notifyLowBalance(User $user): bool
    {
        $total = $user->wallet->balance_uzs + $user->wallet->bonus_balance_uzs;
        $message = "⚠️ <b>Balansingiz tugayapti</b>\n\n";
        $message .= "💰 Asosiy: " . number_format($user->wallet->balance_uzs, 0, '.', ' ') . " so'm\n";
        $message .= "🎁 Bonus: " . number_format($user->wallet->bonus_balance_uzs, 0, '.', ' ') . " GP\n";
        $message .= "─────────────\n";
        $message .= "Jami: <b>" . number_format($total, 0, '.', ' ') . " so'm</b>\n\n";
        $message .= "💳 To'ldirish uchun /topup buyrug'ini ishlating";

        return $this->notify($user, 'low_balance', $message, ['balance' => $total]);
    }

    /**
     * Ticket javob keldi
     */
    public function notifyTicketReply(User $user, string $title, string $reply): bool
    {
        $message = "📩 <b>Ticketingizga javob keldi</b>\n\n";
        $message .= "📌 <i>{$title}</i>\n\n";
        $message .= "💬 {$reply}\n\n";
        $message .= "Yana savolingiz bo'lsa, yangi ticket oching.";

        return $this->notify($user, 'ticket_reply', $message, ['title' => $title]);
    }

    /**
     * Login kod
     */
    public function sendLoginCode(int $telegramId, string $code): bool
    {
        $message = "🔐 <b>CloudAPI login kodi</b>\n\n";
        $message .= "<code>{$code}</code>\n\n";
        $message .= "⏱ 5 daqiqa amal qiladi\n";
        $message .= "⚠️ Bu kodni <b>hech kim bilan</b> baham ko'rmang!";

        $result = $this->tg->sendMessage($telegramId, $message);
        return $result && ($result['ok'] ?? false);
    }
}