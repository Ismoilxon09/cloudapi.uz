<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'type', 'description', 'updated_by'];

    /**
     * Sozlama olish (cache bilan)
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;

            return match($setting->type) {
                'number' => (float)$setting->value,
                'boolean' => (bool)(int)$setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    /**
     * Sozlama saqlash
     */
    public static function set(string $key, $value, ?int $adminId = null): void
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return;

        if ($setting->type === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($setting->type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        $setting->update([
            'value' => (string)$value,
            'updated_by' => $adminId ?? auth()->id(),
        ]);

        Cache::forget("setting:{$key}");
    }

    /**
     * Hammasini olish
     */
    public static function all_settings(): array
    {
        $settings = self::all();
        $result = [];
        foreach ($settings as $s) {
            $result[$s->key] = self::get($s->key);
        }
        return $result;
    }
}