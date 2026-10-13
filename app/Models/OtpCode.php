<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code',
        'type',
        'attempts',
        'max_attempts',
        'ip_address',
        'user_agent',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
    ];

    // ==================== Scopes ====================

    public function scopeValid($query)
    {
        return $query->whereNull('verified_at')
                     ->where('expires_at', '>', now())
                     ->whereColumn('attempts', '<', 'max_attempts');
    }

    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ==================== متدها ====================

    public function isValid(): bool
    {
        return is_null($this->verified_at)
            && $this->expires_at->isFuture()
            && $this->attempts < $this->max_attempts;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function markAsVerified(): bool
    {
        return $this->update(['verified_at' => now()]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}