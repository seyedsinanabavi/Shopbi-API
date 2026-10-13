<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_url', 'new_url', 'status_code', 'hits', 'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'hits'        => 'integer',
        'status_code' => 'integer',
    ];

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUrl($query, string $url)
    {
        return $query->where('old_url', $url);
    }

    // ==================== Helpers ====================

    public function incrementHits(): void
    {
        $this->increment('hits');
    }
}