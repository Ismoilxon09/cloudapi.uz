<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'wallet_id', 'type', 'status',
        'amount_uzs', 'balance_after',
        'payment_method', 'reference', 'meta', 'description',
    ];

    protected function casts(): array {
        return [
            'meta'          => 'array',
            'amount_uzs'    => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo {
        return $this->belongsTo(Wallet::class);
    }
}