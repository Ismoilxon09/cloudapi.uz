<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCodeUse extends Model
{
    protected $table = 'promo_code_uses';
    public $timestamps = false;

    protected $fillable = [
        'promo_code_id',
        'user_id',
        'bonus_given',
        'ip_address',
        'device_hash',
        'created_at',
    ];

    protected $casts = [
        'bonus_given' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->created_at) $model->created_at = now();
        });
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}