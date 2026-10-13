<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Tag extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name', 'slug', 'description',
        'meta_title', 'meta_description', 'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    // ==================== Slug ====================

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ==================== روابط ====================

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function publishedPosts()
    {
        return $this->belongsToMany(Post::class)
                    ->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    // ==================== Helpers ====================

    public function updateUsageCount(): void
    {
        $this->update(['usage_count' => $this->posts()->count()]);
    }
}