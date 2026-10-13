<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'type', 'group', 'description', 'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // ==================== Scopes ====================

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    // ==================== Static Helpers ====================

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) return $default;

            return static::castValue($setting->value, $setting->type);
        });
    }

    public static function set(string $key, $value, string $type = 'string', bool $public = false): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value'     => is_array($value) ? json_encode($value) : $value,
                'type'      => $type,
                'is_public' => $public,
            ]
        );

        Cache::forget("setting.{$key}");
    }

    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }

    // ==================== Events ====================

    protected static function booted(): void
    {
        static::saved(fn($s) => Cache::forget("setting.{$s->key}"));
        static::deleted(fn($s) => Cache::forget("setting.{$s->key}"));
    }
}