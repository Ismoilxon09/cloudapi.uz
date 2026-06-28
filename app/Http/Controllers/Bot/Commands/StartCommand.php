<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\User;
use App\Models\Wallet;
use App\Models\SystemSetting;
use App\Models\Referral;
use App\Services\Telegram\BotNotifier;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StartCommand
{
    public function __construct(protected TelegramService $tg) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $tgUser = $message['from'];
        $telegramId = $tgUser['id'];
        $tgUsername = $tgUser['username'] ?? null;
        $firstName = $tgUser['first_name'] ?? 'User';
        $lastName = $tgUser['last_name'] ?? '';
        $fullName = trim("{$firstName} {$lastName}");

        // /start parametri (referral kod yoki linking token)
        $text = trim($message['text'] ?? '');
        $startParam = '';
        if (strpos($text, ' ') !== false) {
            $startParam = trim(substr($text, strpos($text, ' ') + 1));
        }

        // User topish yoki yaratish
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            // Yangi user — yaratamiz
            $user = $this->createUser($telegramId, $tgUsername, $fullName, $startParam);
        } else {
            // Eski user — username yangilash
            if ($tgUsername && $user->telegram_username !== $tgUsername) {
                $user->update(['telegram_username' => $tgUsername]);
            }
        }

        // Telefon raqam yo'q bo'lsa — so'rash
        if (!$user->phone) {
            $this->requestPhone($chatId, $firstName);
            return;
        }

        // Asosiy menyu
        $this->showMainMenu($chatId, $user);
    }

    /**
     * Yangi user yaratish
     */
    protected function createUser(int $telegramId, ?string $username, string $fullName, string $startParam): User
    {
        return DB::transaction(function () use ($telegramId, $username, $fullName, $startParam) {
            // Referral kod ajratish
            $referrerId = null;
            if (str_starts_with($startParam, 'ref_')) {
                $code = substr($startParam, 4);
                $referrer = User::where('referral_code', $code)->first();
                if ($referrer) {
                    $referrerId = $referrer->id;
                }
            }

            // User yaratish
            $user = User::create([
                'name' => strip_tags($fullName),
                'email' => null,
                'password' => null,
                'email_verified_at' => now(),
                'telegram_id' => $telegramId,
                'telegram_username' => $username,
                'telegram_linked_at' => now(),
                'role' => 'user',
                'status' => 'active',
                'language' => 'uz',
                'referral_code' => $this->generateReferralCode(),
            ]);

            // Wallet (asosiy + bonus)
            $signupBonus = (float)SystemSetting::get('signup_bonus_uzs', 0);
            Wallet::create([
                'user_id' => $user->id,
                'balance_uzs' => 0,
                'bonus_balance_uzs' => $signupBonus,
                'total_deposited' => 0,
                'total_spent' => 0,
                'total_bonus_earned' => $signupBonus,
                'total_bonus_spent' => 0,
            ]);

            // Referral bonus
            if ($referrerId) {
                $refBonus = (int)SystemSetting::get('referral_bonus_gp', 10);
                Referral::create([
                    'referrer_id' => $referrerId,
                    'referred_id' => $user->id,
                    'bonus_gp' => $refBonus,
                    'bonus_paid' => 1,
                ]);

                // Referrer'ga GP berish
                $referrer = User::find($referrerId);
                if ($referrer && $referrer->wallet) {
                    $referrer->wallet->increment('bonus_balance_uzs', $refBonus);
                    $referrer->wallet->increment('total_bonus_earned', $refBonus);

                    // Bot orqali xabar
                    app(BotNotifier::class)->notifyReferralJoined($referrer->fresh(), $fullName, $refBonus);
                }

                // Yangi user'ga ham bonus
                $user->wallet->increment('bonus_balance_uzs', $refBonus);
                $user->wallet->increment('total_bonus_earned', $refBonus);
            }

            return $user->fresh();
        });
    }

    /**
     * Telefon raqam so'rash
     */
    protected function requestPhone(int $chatId, string $firstName): void
    {
        $message = "👋 Salom, <b>{$firstName}</b>!\n\n";
        $message .= "🚀 <b>CloudAPI</b> botiga xush kelibsiz!\n\n";
        $message .= "📱 Hisobingizni faollashtirish uchun telefon raqamingizni yuboring.\n\n";
        $message .= "Pastdagi tugmani bosishingiz kifoya — Telegram avtomatik yuboradi.";

        $this->tg->sendContactRequest($chatId, $message);
    }

    /**
     * Asosiy menyu
     */
    protected function showMainMenu(int $chatId, User $user): void
    {
        $balance = number_format($user->wallet->balance_uzs, 0, '.', ' ');
        $bonus = number_format($user->wallet->bonus_balance_uzs, 0, '.', ' ');

        $message = "👋 Salom, <b>{$user->name}</b>!\n\n";
        $message .= "🌐 <b>CloudAPI</b> — sizning AI darvozangiz\n\n";
        $message .= "💰 Asosiy hamyon: <b>{$balance} so'm</b>\n";
        $message .= "🎁 Bonus hamyon: <b>{$bonus} GP</b>\n\n";
        $message .= "📌 <b>Asosiy ish web platformada:</b>\n";
        $message .= "https://cloudapi.uz\n\n";
        $message .= "Bot orqali balansingizni to'ldirib, vazifalardan GP topishingiz mumkin.";

        $keyboard = [
            [
                ['text' => '💰 Balans'],
                ['text' => '✅ Vazifalar'],
            ],
            [
                ['text' => '🎁 Kunlik bonus'],
                ['text' => '👥 Referral'],
            ],
            [
                ['text' => '💳 To\'ldirish'],
                ['text' => '❓ Yordam'],
            ],
        ];

        $this->tg->sendReplyKeyboard($chatId, $message, $keyboard);
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());
        return $code;
    }
}