<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ProxyKey extends Model
{
    use SoftDeletes;

    /**
     * Plain text kalit — faqat yaratish vaqtida set qilinadi
     * DB'ga yozilmaydi (transient)
     */
    public ?string $plainKey = null;

    protected $fillable = [
        'user_id',
        'name',
        'key_prefix',
        'key_hash',
        'key_encrypted',
        'status',
        'rate_limit_per_minute',
        'allowed_models',
        'total_requests',
        'total_tokens',
        'expires_at',
        'last_used_at',
    ];

    /**
     * Faqat key'ni yaratganda full_key keladi (bir martagina)
     * Boshqa joyda hech qachon ko'rinmaydi
     */
    protected $hidden = [
        'key_hash',
        'key_encrypted',
    ];

    protected $casts = [
        'allowed_models' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * full_key olish (backward compatibility uchun)
     */
    public function getFullKeyAttribute(): ?string
    {
        return $this->plainKey;
    }

    /**
     * Yangi xavfsiz API kalit yaratish
     * - cap-{32 random chars} format
     * - SHA-256 hash DB ga saqlanadi (verification uchun)
     * - Encrypted versiya DB ga saqlanadi (admin uchun ko'rish imkoniyati, lekin shifrlangan)
     * - Plain text bir martagina qaytariladi
     */
    public static function generate(int $userId, string $name, float $initialBalance = 0): self
    {
        // 32 belgilik kriptografik kuchli random
        $randomPart = bin2hex(random_bytes(16)); // 32 hex chars = 128 bit entropy
        $fullKey = 'cap-' . $randomPart;

        $key = self::create([
            'user_id' => $userId,
            'name' => strip_tags($name),
            'key_prefix' => substr($fullKey, 0, 12) . '...', // cap-abc12345...
            'key_hash' => hash('sha256', $fullKey),
            'key_encrypted' => Crypt::encryptString($fullKey),
            'status' => 'active',
            'rate_limit_per_minute' => 60,
            'total_requests' => 0,
            'total_tokens' => 0,
        ]);

        // Plain text key'ni faqat shu yerda saqlaymiz (transient, DB'ga yozilmaydi)
        $key->plainKey = $fullKey;

        return $key;
    }

    /**
     * Hash bilan key topish (verification uchun)
     */
    public static function findByKey(string $key): ?self
    {
        $hash = hash('sha256', $key);
        return self::where('key_hash', $hash)->first();
    }

    /**
     * Encrypted key'ni decrypt qilish (faqat admin uchun)
     */
    public function decryptKey(): ?string
    {
        try {
            return Crypt::decryptString($this->key_encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(ProxyUsage::class, 'proxy_key_id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}