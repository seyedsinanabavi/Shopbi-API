<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description',
        'image', 'icon', 'banner', 'color',
        'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
        'og_title', 'og_description', 'og_image',
        'schema_markup', 'is_indexable', 'is_followable',
        'order', 'is_active', 'show_in_menu',
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'is_indexable'  => 'boolean',
        'is_followable' => 'boolean',
        'is_active'     => 'boolean',
        'show_in_menu'  => 'boolean',
        'order'         => 'integer',
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

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    // ==================== Media ====================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('banner')->singleFile();
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    // ==================== Helpers ====================

    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->name;
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function getFullPathAttribute(): string
    {
        $paths = [$this->slug];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($paths, $parent->slug);
            $parent = $parent->parent;
        }

        return implode('/', $paths);
    }
}