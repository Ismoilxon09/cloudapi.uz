<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'description',
        'bonus_amount',
        'max_uses',
        'uses_count',
        'max_per_user',
        'valid_from',
        'valid_until',
        'min_account_age_hours',
        'require_telegram',
        'require_email_verified',
        'require_phone_verified',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'bonus_amount' => 'integer',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'max_per_user' => 'integer',
        'min_account_age_hours' => 'integer',
        'require_telegram' => 'boolean',
        'require_email_verified' => 'boolean',
        'require_phone_verified' => 'boolean',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Promokod ishlatishlar tarixi
     */
    public function uses(): HasMany
    {
        return $this->hasMany(PromoCodeUse::class);
    }

    /**
     * Promokod hozir ishlatiladimi?
     */
    public function isUsable(): bool
    {
        // Active emas
        if (!$this->is_active) return false;

        // Hali boshlanmagan
        if ($this->valid_from && $this->valid_from->isFuture()) return false;

        // Tugagan
        if ($this->valid_until && $this->valid_until->isPast()) return false;

        // Limit tugagan
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) return false;

        return true;
    }

    /**
     * User shu kodni ishlata oladimi?
     * @return array ['ok' => bool, 'reason' => string|null]
     */
    public function canBeUsedBy(User $user): array
    {
        // Avval umumiy tekshirish
        if (!$this->isUsable()) {
            $reason = match (true) {
                !$this->is_active => 'Promokod faol emas',
                $this->valid_until && $this->valid_until->isPast() => 'Promokod muddati tugagan',
                $this->max_uses !== null && $this->uses_count >= $this->max_uses => 'Promokod limiti tugagan',
                default => 'Promokod ishlatib bo\'lmaydi',
            };
            return ['ok' => false, 'reason' => $reason];
        }

        // User shu kodni allaqachon ishlatganmi?
        $userUsesCount = $this->uses()->where('user_id', $user->id)->count();
        if ($userUsesCount >= $this->max_per_user) {
            return ['ok' => false, 'reason' => 'Siz bu promokodni allaqachon ishlatgansiz'];
        }

        // Telegram majburiy
        if ($this->require_telegram && !$user->telegram_id) {
            return ['ok' => false, 'reason' => 'Bu promokod uchun Telegram ulash majburiy'];
        }

        // Email tasdiqlash majburiy
        if ($this->require_email_verified && !$user->email_verified_at) {
            return ['ok' => false, 'reason' => 'Bu promokod uchun email tasdiqlash majburiy'];
        }

        // Telefon tasdiqlash majburiy
        if ($this->require_phone_verified && !$user->phone_verified_at) {
            return ['ok' => false, 'reason' => 'Bu promokod uchun telefon tasdiqlash majburiy'];
        }

        // Account yoshi
        if ($this->min_account_age_hours > 0) {
            $accountAgeHours = $user->created_at->diffInHours(now());
            if ($accountAgeHours < $this->min_account_age_hours) {
                $needHours = $this->min_account_age_hours - $accountAgeHours;
                return [
                    'ok' => false,
                    'reason' => "Akkauntingiz hali yangi. Yana {$needHours} soat kuting"
                ];
            }
        }

        return ['ok' => true, 'reason' => null];
    }

    /**
     * Qolgan ishlatish soni
     */
    public function getRemainingUses(): ?int
    {
        if ($this->max_uses === null) return null; // unlimited
        return max(0, $this->max_uses - $this->uses_count);
    }
}