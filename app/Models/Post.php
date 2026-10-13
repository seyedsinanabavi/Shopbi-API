<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasSlug, InteractsWithMedia;

    // ==================== Fillable ====================

    protected $fillable = [
        // اصلی
        'user_id', 'category_id',
        'title', 'slug', 'excerpt', 'content',
        'featured_image', 'type', 'status',

        // SEO
        'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
        'og_title', 'og_description', 'og_image', 'twitter_card',
        'schema_markup', 'is_indexable', 'is_followable',

        // آمار
        'views_count', 'likes_count', 'comments_count', 'reading_time',

        // زمان‌بندی
        'published_at', 'scheduled_at',
    ];

    // ==================== Casts ====================

    protected $casts = [
        'schema_markup'  => 'array',
        'is_indexable'   => 'boolean',
        'is_followable'  => 'boolean',
        'published_at'   => 'datetime',
        'scheduled_at'   => 'datetime',
        'views_count'    => 'integer',
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
        'reading_time'   => 'integer',
    ];

    // ==================== Slug ====================

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(200);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ==================== روابط ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('status', 'approved');
    }

    // ==================== Media ====================

    public function registerMediaCollections(): void
    {
        // تصویر شاخص — یک فایل
        $this->addMediaCollection('featured')
            ->singleFile()
            ->useFallbackUrl('/images/default-post.jpg')
            ->useFallbackPath(public_path('images/default-post.jpg'));

        // گالری — چند فایل
        $this->addMediaCollection('gallery');

        // تصویر OG — یک فایل
        $this->addMediaCollection('og_image')
            ->singleFile();
    }

    /**
     * تبدیل‌های خودکار تصاویر
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->nonQueued();
    }

    // ==================== Scopes ====================

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIndexable($query)
    {
        return $query->where('is_indexable', true);
    }

    public function scopeLatestPublished($query)
    {
        return $query->published()->latest('published_at');
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==================== Accessors ====================

    /**
     * عنوان متا — اگه خالی بود، عنوان مقاله
     */
    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    /**
     * توضیحات متا — اگه خالی بود، خلاصه یا محتوا
     */
    public function getMetaDescriptionAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        $text = strip_tags($this->excerpt ?? $this->content);
        return Str::limit($text, 160);
    }

    /**
     * URL مقاله
     */
    public function getUrlAttribute(): string
    {
        // page ها URL متفاوتی دارن
        if ($this->type === 'page') {
            return url("/{$this->slug}");
        }

        if ($this->type === 'product') {
            return url("/products/{$this->slug}");
        }

        // پیش‌فرض: post
        return url("/blog/{$this->slug}");
    }

    /**
     * وضعیت انتشار
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at->isPast();
    }

    /**
     * تصویر شاخص کامل
     */
    public function getFeaturedImageUrlAttribute(): string
    {
        $media = $this->getFirstMedia('featured');
        return $media ? $media->getUrl() : ($this->featured_image ?? '/images/default-post.jpg');
    }

    /**
     * تامبنیل
     */
    public function getFeaturedImageThumbAttribute(): string
    {
        $media = $this->getFirstMedia('featured');
        return $media ? $media->getUrl('thumb') : $this->featured_image_url;
    }

    /**
     * زمان خواندن به فارسی
     */
    public function getReadingTimeTextAttribute(): string
    {
        return $this->reading_time . ' دقیقه مطالعه';
    }

    // ==================== Helpers ====================

    /**
     * افزایش بازدید
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * محاسبه زمان خواندن — پشتیبانی از فارسی
     */
    public function calculateReadingTime(): int
    {
        $text = strip_tags($this->content);

        // تقسیم بر اساس فاصله و شمارش کلمات
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        // میانگین ۲۰۰ کلمه در دقیقه
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * آیا کاربر می‌تونه این مقاله رو ببینه؟
     */
    public function isVisibleTo(?User $user): bool
    {
        // اگه منتشر شده — همه می‌تونن
        if ($this->is_published) {
            return true;
        }

        // اگه لاگین نکرده
        if (!$user) {
            return false;
        }

        // نویسنده خودش
        if ($user->id === $this->user_id) {
            return true;
        }

        // ادمین یا ادیتور
        return $user->hasAnyRole(['admin', 'editor']);
    }
}