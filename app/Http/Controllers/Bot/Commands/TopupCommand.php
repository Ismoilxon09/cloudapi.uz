<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\BotTopupRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Telegram\BotNotifier;
use App\Services\Telegram\TelegramService;

class TopupCommand
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

        $cardNumber = SystemSetting::get('card_number', '8600 1234 5678 9012');
        $cardHolder = SystemSetting::get('card_holder', 'ISMOILXON NURMATOV');
        $minTopup = SystemSetting::get('min_topup_amount', 10000);

        $text = "💳 <b>Hisobni to'ldirish</b>\n\n";
        $text .= "Quyidagi kartaga to'lov qiling:\n\n";
        $text .= "💳 Karta raqami:\n";
        $text .= "<code>{$cardNumber}</code>\n\n";
        $text .= "👤 Egasi: <b>{$cardHolder}</b>\n";
        $text .= "💰 Minimal: " . number_format((float)$minTopup, 0, '.', ' ') . " so'm\n\n";
        $text .= "📸 <b>To'lovdan keyin:</b>\n";
        $text .= "Chekning rasmini shu yerga yuboring. Admin tasdiqlagandan keyin balansingizga tushadi.\n\n";
        $text .= "⏱ Odatda 1-30 daqiqada tasdiqlanadi.";

        $this->tg->sendMessage($chatId, $text);
    }

    /**
     * Chek rasm yuborilganda
     */
    public function handlePhoto(array $message): void
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];

        $user = User::where('telegram_id', $telegramId)->first();
        if (!$user) return;

        // Eng katta rasmni olish
        $photos = $message['photo'];
        $largestPhoto = end($photos);
        $fileId = $largestPhoto['file_id'];

        // Caption'dan miqdor olish (ixtiyoriy)
        $caption = trim($message['caption'] ?? '');
        $amount = 0;
        if (preg_match('/\d+/', str_replace(' ', '', $caption), $m)) {
            $amount = (int)$m[0];
        }

        // Topup so'rovi yaratish
        $request = BotTopupRequest::create([
            'user_id' => $user->id,
            'amount_uzs' => $amount,
            'screenshot_file_id' => $fileId,
            'note' => $caption ?: null,
            'status' => 'pending',
        ]);

        $this->tg->sendMessage($chatId,
            "✅ <b>Chek qabul qilindi!</b>\n\n" .
            "📋 So'rov #{$request->id}\n" .
            ($amount > 0 ? "💰 Miqdor: " . number_format($amount, 0, '.', ' ') . " so'm\n" : "") .
            "⏱ Status: <b>Kutilmoqda</b>\n\n" .
            "Admin tasdiqlagandan keyin sizga xabar beriladi.");

        // Admin'ga xabar
        $this->notifyAdmin($request, $user, $fileId, $caption);
    }

    /**
     * Admin'ga yangi topup haqida xabar
     */
    protected function notifyAdmin(BotTopupRequest $req, User $user, string $fileId, string $caption): void
    {
        $adminId = env('TELEGRAM_ADMIN_CHAT_ID');
        if (!$adminId) return;

        $text = "🔔 <b>Yangi to'lov so'rovi!</b>\n\n";
        $text .= "👤 User: {$user->name}\n";
        $text .= "📱 Telefon: " . ($user->phone ?: 'N/A') . "\n";
        $text .= "🆔 ID: <code>{$user->id}</code>\n";
        $text .= "📋 So'rov: #{$req->id}\n";
        if ($caption) {
            $text .= "💬 Izoh: {$caption}\n";
        }
        $text .= "\n👇 Tasdiqlash uchun pastdan tanlang:";

        // Rasmni admin'ga yuborish (caption bilan)
        \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendPhoto", [
            'chat_id' => $adminId,
            'photo' => $fileId,
            'caption' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "✅ Tasdiqlash", 'callback_data' => "admin_topup_approve:{$req->id}"],
                        ['text' => "❌ Rad etish", 'callback_data' => "admin_topup_reject:{$req->id}"],
                    ],
                ],
            ]),
        ]);
    }

    /**
     * Admin tasdiqladi
     */
    public function adminApprove(array $callback, ?string $reqId): void
    {
        if (!$reqId) return;

        $adminId = (int)env('TELEGRAM_ADMIN_CHAT_ID');
        if ((int)$callback['from']['id'] !== $adminId) {
            $this->tg->sendMessage($callback['from']['id'], "❌ Sizda ruxsat yo'q.");
            return;
        }

        $req = BotTopupRequest::find($reqId);
        if (!$req || $req->status !== 'pending') return;

        $user = User::find($req->user_id);
        if (!$user || !$user->wallet) return;

        // Balansga qo'shish
        $user->wallet->increment('balance_uzs', $req->amount_uzs);
        $user->wallet->increment('total_deposited', $req->amount_uzs);

        $req->update([
            'status' => 'approved',
            'admin_id' => $adminId,
            'reviewed_at' => now(),
        ]);

        // User'ga xabar
        app(BotNotifier::class)->notifyTopupApproved($user->fresh(), $req->amount_uzs);

        // Admin xabarini yangilash
        $this->tg->editMessage(
            $callback['message']['chat']['id'],
            $callback['message']['message_id'],
            "✅ Tasdiqlandi #{$req->id}\nMiqdor: " . number_format($req->amount_uzs, 0, '.', ' ') . " so'm"
        );
    }

    /**
     * Admin rad etdi
     */
    public function adminReject(array $callback, ?string $reqId): void
    {
        if (!$reqId) return;

        $adminId = (int)env('TELEGRAM_ADMIN_CHAT_ID');
        if ((int)$callback['from']['id'] !== $adminId) return;

        $req = BotTopupRequest::find($reqId);
        if (!$req || $req->status !== 'pending') return;

        $req->update([
            'status' => 'rejected',
            'admin_id' => $adminId,
            'reviewed_at' => now(),
        ]);

        $user = User::find($req->user_id);
        if ($user) {
            app(BotNotifier::class)->notifyTopupRejected($user, $req->amount_uzs, 'Chek noto\'g\'ri yoki to\'lov topilmadi');
        }

        $this->tg->editMessage(
            $callback['message']['chat']['id'],
            $callback['message']['message_id'],
            "❌ Rad etildi #{$req->id}"
        );
    }

    public function handleAmount(array $callback, ?string $amount): void
    {
        // Future: state-based input bilan amount qabul qilish
    }
}