<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance_uzs',
        'bonus_balance_uzs',
        'total_deposited',
        'total_spent',
        'total_bonus_earned',
        'total_bonus_spent',
    ];

    protected $casts = [
        'balance_uzs' => 'decimal:2',
        'bonus_balance_uzs' => 'decimal:2',
        'total_deposited' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'total_bonus_earned' => 'decimal:2',
        'total_bonus_spent' => 'decimal:2',
    ];

    /**
     * Total balance (asosiy + bonus)
     */
    public function getTotalBalanceAttribute(): float
    {
        return (float)$this->balance_uzs + (float)$this->bonus_balance_uzs;
    }

    protected function casts(): array
    {
        return [
            'balance_uzs'     => 'decimal:2',
            'total_deposited' => 'decimal:2',
            'total_spent'     => 'decimal:2',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }

    public function deposit(float $amount, string $method = 'manual', ?string $ref = null, ?string $desc = null): Transaction
    {
        $this->increment('balance_uzs', $amount);
        $this->increment('total_deposited', $amount);
        return $this->transactions()->create([
            'user_id'        => $this->user_id,
            'type'           => 'deposit',
            'status'         => 'completed',
            'amount_uzs'     => $amount,
            'balance_after'  => $this->fresh()->balance_uzs,
            'payment_method' => $method,
            'reference'      => $ref,
            'description'    => $desc ?? 'Hamyon to\'ldirish',
        ]);
    }

    public function withdraw(float $amount, string $type = 'usage', ?string $desc = null): ?Transaction
    {
        $totalAvailable = (float)$this->balance_uzs + (float)$this->bonus_balance_uzs;
        if ($totalAvailable < $amount) return null;

        $fromBonus = 0.0;
        $fromMain = 0.0;

        // AVVAL bonus hamyondan yechish
        if ($this->bonus_balance_uzs > 0) {
            $fromBonus = min((float)$this->bonus_balance_uzs, $amount);
            $this->decrement('bonus_balance_uzs', $fromBonus);
            $this->increment('total_bonus_spent', $fromBonus);
        }

        $remaining = $amount - $fromBonus;

        // KEYIN asosiy hamyondan
        if ($remaining > 0) {
            $fromMain = $remaining;
            $this->decrement('balance_uzs', $fromMain);
            $this->increment('total_spent', $fromMain);
        }

        // Bitta tranzaksiya (umumiy)
        return $this->transactions()->create([
            'user_id'       => $this->user_id,
            'type'          => $type,
            'status'        => 'completed',
            'amount_uzs'    => -$amount,
            'balance_after' => $this->fresh()->balance_uzs,
            'description'   => $desc . ($fromBonus > 0 ? " (Bonus: {$fromBonus}, Asosiy: {$fromMain})" : ''),
        ]);
    }

    /**
     * Bonus hamyonga GP qo'shish
     */
    public function addBonus(float $amount, string $reason = ''): void
    {
        $this->increment('bonus_balance_uzs', $amount);
        $this->increment('total_bonus_earned', $amount);
    }
}