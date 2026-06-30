<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use App\Models\PromoCodeUse;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    /**
     * Promokod kiritish va bonusni hisobga olish
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:3', 'max:50'],
        ], [
            'code.required' => 'Promokod kiriting',
            'code.min' => 'Promokod juda qisqa',
            'code.max' => 'Promokod juda uzun',
        ]);

        $user = $request->user();
        $codeInput = strtoupper(trim($request->input('code')));

        // Promokod topish
        $promo = PromoCode::whereRaw('UPPER(code) = ?', [$codeInput])->first();

        if (!$promo) {
            return $this->error('Bunday promokod mavjud emas');
        }

        // Tekshirish
        $check = $promo->canBeUsedBy($user);
        if (!$check['ok']) {
            return $this->error($check['reason']);
        }

        // Transaction'da bajaramiz (xavfsiz)
        try {
            DB::transaction(function () use ($user, $promo, $request) {
                // Wallet topish yoki yaratish
                $wallet = $user->wallet ?? $user->wallet()->create([
                    'balance_uzs' => 0,
                    'bonus_balance_uzs' => 0,
                    'total_deposited' => 0,
                    'total_spent' => 0,
                ]);

                // Bonus qo'shish (bonus hamyonga)
                $wallet->bonus_balance_uzs += $promo->bonus_amount;
                $wallet->save();

                // Transaction tarixi
                if (class_exists(Transaction::class)) {
                    $wallet->transactions()->create([
                        'user_id' => $user->id,
                        'type' => 'bonus',
                        'amount' => $promo->bonus_amount,
                        'balance_after' => $wallet->bonus_balance_uzs,
                        'description' => "Promokod: {$promo->code}",
                        'metadata' => json_encode([
                            'promo_code_id' => $promo->id,
                            'promo_code' => $promo->code,
                        ]),
                    ]);
                }

                // Ishlatish tarixi
                PromoCodeUse::create([
                    'promo_code_id' => $promo->id,
                    'user_id' => $user->id,
                    'bonus_given' => $promo->bonus_amount,
                    'ip_address' => $request->ip(),
                    'device_hash' => $request->header('X-Device-Hash'),
                ]);

                // Promokod ishlatish soni
                $promo->increment('uses_count');
            });

            Log::info('Promokod ishlatildi', [
                'user_id' => $user->id,
                'promo_code' => $promo->code,
                'bonus' => $promo->bonus_amount,
            ]);

            return response()->json([
                'ok' => true,
                'message' => "✅ Tabriklaymiz! +{$promo->bonus_amount} GP bonus oldingiz",
                'bonus_amount' => $promo->bonus_amount,
                'new_balance' => $user->fresh()->wallet->bonus_balance_uzs ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Promokod ishlatish xatosi', [
                'user_id' => $user->id,
                'promo_code' => $promo->code,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Xato yuz berdi. Qaytadan urinib ko\'ring');
        }
    }

    /**
     * User ishlatgan promokodlar ro'yxati
     */
    public function myUses(Request $request)
    {
        $uses = PromoCodeUse::where('user_id', $request->user()->id)
            ->with('promoCode')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'ok' => true,
            'uses' => $uses->map(fn($use) => [
                'code' => $use->promoCode->code ?? 'O\'chirilgan',
                'bonus' => $use->bonus_given,
                'used_at' => $use->created_at->diffForHumans(),
            ]),
        ]);
    }

    private function error(string $message)
    {
        return response()->json([
            'ok' => false,
            'message' => $message,
        ], 422);
    }
}