<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * MASS ASSIGNMENT himoyasi — faqat shu maydonlar to'ldirilishi mumkin
     * role, status, balance hech qachon user input'dan kelmasligi kerak
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_verified_at',
        'country',
        'language',
        'role',
        'status',
        'referral_code',
        'referred_by',
        'telegram_id',
        'telegram_chat_id',
        'telegram_username',
        'telegram_linked_at',
        'email_verified_at',
        'google_id',
        'github_id',
        'avatar',
        'oauth_provider',
    ];

    /**
     * Hech qachon JSON da qaytarilmasligi kerak
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Avtomatik hash
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // === Relations ===
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function proxyKeys(): HasMany
    {
        return $this->hasMany(ProxyKey::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(ProxyUsage::class);
    }

    // === Helpers ===
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }
}