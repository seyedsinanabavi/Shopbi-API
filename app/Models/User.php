<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasRoles, InteractsWithMedia;

    protected $fillable = [
        'phone',
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        // 'role',  ← حذف شد
        'status',
        'national_code',
        'birth_date',
        'gender',
        'phone_verified_at',
        'email_verified_at',
        'last_login_ip',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'birth_date'        => 'date',
        'password'          => 'hashed',
    ];

    // ==================== روابط ====================

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class, 'phone', 'phone');
    }

    // ==================== Media ====================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useFallbackUrl('/images/default-avatar.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued();
    }

    // ==================== Filament ====================

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['admin', 'editor', 'author'])
            && $this->status === 'active';
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    // ==================== Accessors ====================

    public function getFullNameAttribute(): string
    {
        $name = trim("{$this->first_name} {$this->last_name}");
        return $name ?: $this->phone;
    }

    public function getAvatarUrlAttribute(): string
    {
        $media = $this->getFirstMedia('avatar');
        return $media ? $media->getUrl() : ($this->avatar ?? '/images/default-avatar.png');
    }

    public function getAvatarThumbAttribute(): string
    {
        $media = $this->getFirstMedia('avatar');
        return $media ? $media->getUrl('thumb') : $this->avatar_url;
    }

    // ==================== متدها ====================

    /**
     * آیا ادمین/ادیتور است؟ (بر اساس Spatie)
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'editor']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_ip' => $ip,
            'last_login_at' => now(),
        ]);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAdmins($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'editor']);
        });
    }

    public function scopeBanned($query)
    {
        return $query->where('status', 'banned');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }
}