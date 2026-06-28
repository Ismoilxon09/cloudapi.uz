<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\DailyCheckin;
use App\Models\User;
use App\Services\Telegram\BotNotifier;
use App\Services\Telegram\TelegramService;

class DailyCommand
{
    public function __construct(protected TelegramService $tg) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];

        $user = User::where('telegram_id', $telegramId)->first();
        if (!$user) {
            $this->tg->sendMessage($chatId, "❌ /start ni bosing.");
            return;
        }

        // Bugun allaqachon olganmi?
        $today = today()->toDateString();
        $todayCheckin = DailyCheckin::where('user_id', $user->id)
            ->where('checkin_date', $today)
            ->first();

        if ($todayCheckin) {
            $tomorrow = today()->addDay();
            $hoursLeft = now()->diffInHours($tomorrow);
            $this->tg->sendMessage($chatId,
                "⏱ <b>Bugun bonus oldingiz!</b>\n\n" .
                "Ertaga qaytib keling — yana bonus oling.\n\n" .
                "⏰ {$hoursLeft} soatdan keyin");
            return;
        }

        // Streak hisoblash (kecha kirgan bo'lsa +1, aks holda 1 dan boshlanadi)
        $yesterday = today()->subDay()->toDateString();
        $yesterdayCheckin = DailyCheckin::where('user_id', $user->id)
            ->where('checkin_date', $yesterday)
            ->first();

        $streak = $yesterdayCheckin ? ($yesterdayCheckin->streak + 1) : 1;

        // Mukofot — streak ga qarab
        $reward = $this->calculateReward($streak);

        // Saqlash
        DailyCheckin::create([
            'user_id' => $user->id,
            'checkin_date' => $today,
            'reward_gp' => $reward,
            'streak' => $streak,
        ]);

        if ($user->wallet) {
            $user->wallet->increment('bonus_balance_uzs', $reward);
            $user->wallet->increment('total_bonus_earned', $reward);
        }

        // Notification
        app(BotNotifier::class)->notifyDailyBonus($user->fresh(), $reward, $streak);
    }

    /**
     * Streak'ga qarab mukofot
     */
    protected function calculateReward(int $streak): int
    {
        if ($streak >= 30) return 50;
        if ($streak >= 14) return 30;
        if ($streak >= 7) return 20;
        if ($streak >= 3) return 10;
        return 5;
    }

    public function handleClaim(array $callback): void
    {
        $this->handle([
            'chat' => $callback['message']['chat'],
            'from' => $callback['from'],
        ]);
    }
}