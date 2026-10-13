<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_id', 'user_id', 'parent_id',
        'content', 'status', 'ip_address',
    ];

    // ==================== روابط ====================

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // ==================== Scopes ====================

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // ==================== Helpers ====================

    public function approve(): bool
    {
        return $this->update(['status' => 'approved']);
    }

    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }
}