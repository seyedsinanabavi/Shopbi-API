<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    /**
     * قبل از ساخت
     */
    public function creating(Post $post): void
    {
        // اسلاگ اگه خالی بود
        if (empty($post->slug)) {
            $post->slug = $this->generateUniqueSlug($post->title);
        }

        // زمان خواندن
        $post->reading_time = $this->calculateReadingTime($post->content);

        // اگه published_at خالی بود و status published
        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }

        // توییت کارت پیش‌فرض
        if (empty($post->twitter_card)) {
            $post->twitter_card = 'summary_large_image';
        }

        // متا پیش‌فرض
        if (empty($post->meta_title)) {
            $post->meta_title = $post->title;
        }

        if (empty($post->meta_description)) {
            $post->meta_description = Str::limit(
                strip_tags($post->excerpt ?? $post->content),
                160
            );
        }
    }

    /**
     * قبل از آپدیت
     */
    public function updating(Post $post): void
    {
        // آپدیت زمان خواندن اگه محتوا عوض شده
        if ($post->isDirty('content')) {
            $post->reading_time = $this->calculateReadingTime($post->content);
        }

        // متا دیسکریپشن رو آپدیت کن اگه محتوا عوض شد و متا دستی ست نشده
        if ($post->isDirty(['content', 'excerpt']) && empty($post->meta_description)) {
            $post->meta_description = Str::limit(
                strip_tags($post->excerpt ?? $post->content),
                160
            );
        }

        // اگه status به published تغییر کرد و published_at خالی بود
        if ($post->isDirty('status') && $post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }
    }

    /**
     * بعد از ساخت
     */
    public function created(Post $post): void
    {
        // آپدیت تعداد پست‌های دسته
        if ($post->category) {
            $this->updateCategoryCount($post->category);
        }
    }

    /**
     * بعد از آپدیت
     */
    public function updated(Post $post): void
    {
        if ($post->wasChanged('category_id')) {
            // آپدیت دسته قدیمی
            if ($post->getOriginal('category_id')) {
                $oldCategory = \App\Models\Category::find($post->getOriginal('category_id'));
                if ($oldCategory) {
                    $this->updateCategoryCount($oldCategory);
                }
            }
            // آپدیت دسته جدید
            if ($post->category) {
                $this->updateCategoryCount($post->category);
            }
        }
    }

    /**
     * قبل از حذف
     */
    public function deleted(Post $post): void
    {
        // پاک کردن تصاویر
        $post->clearMediaCollection('featured');
        $post->clearMediaCollection('gallery');
    }

    // ==================== Helpers ====================

    protected function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        
        // اگه فارسی بود، از Str::slug با زبان فارسی استفاده کن
        if (empty($slug)) {
            $slug = $this->transliterate($title);
        }

        $originalSlug = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function transliterate(string $text): string
    {
        // تبدیل ساده فارسی به انگلیسی
        $persian = ['آ','ا','ب','پ','ت','ث','ج','چ','ح','خ','د','ذ','ر','ز','ژ','س','ش','ص','ض','ط','ظ','ع','غ','ف','ق','ک','گ','ل','م','ن','و','ه','ی'];
        $english = ['a','a','b','p','t','s','j','ch','h','kh','d','z','r','z','zh','s','sh','s','z','t','z','a','gh','f','gh','k','g','l','m','n','v','h','y'];
        
        $text = str_replace($persian, $english, $text);
        $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
        $text = preg_replace('/\s+/', '-', $text);
        $text = strtolower(trim($text, '-'));

        return $text ?: 'post-' . time();
    }

    protected function calculateReadingTime(string $content): int
    {
        // حذف تگ‌های HTML
        $text = strip_tags($content);
        
        // شمارش کلمات (فارسی + انگلیسی)
        $wordCount = $this->countWords($text);
        
        // ۲۰۰ کلمه در دقیقه
        return max(1, (int) ceil($wordCount / 200));
    }

    protected function countWords(string $text): int
    {
        // تقسیم بر اساس فاصله
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        return count($words);
    }

    protected function updateCategoryCount(\App\Models\Category $category): void
    {
        // می‌تونی فیلد posts_count اضافه کنی
        // فعلاً نیازی نیست
    }
}