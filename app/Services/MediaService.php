<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    /**
     * آپلود تصویر شاخص
     */
    public function uploadFeatured(HasMedia $model, UploadedFile $file): ?Media
    {
        $this->validateImage($file);

        return $model
            ->addMedia($file)
            ->usingFileName($this->generateFileName($file))
            ->toMediaCollection('featured');
    }

    /**
     * آپلود بنر دسته
     */
    public function uploadBanner(HasMedia $model, UploadedFile $file): ?Media
    {
        $this->validateImage($file);

        return $model
            ->addMedia($file)
            ->usingFileName($this->generateFileName($file))
            ->toMediaCollection('banner');
    }

    /**
     * آپلود گالری (چندتایی)
     */
    public function uploadGallery(HasMedia $model, array $files): array
    {
        $mediaItems = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            
            $this->validateImage($file);
            
            $mediaItems[] = $model
                ->addMedia($file)
                ->usingFileName($this->generateFileName($file))
                ->toMediaCollection('gallery');
        }

        return $mediaItems;
    }

    /**
     * آپلود آواتار کاربر
     */
    public function uploadAvatar(HasMedia $model, UploadedFile $file): ?Media
    {
        $this->validateImage($file);

        return $model
            ->addMedia($file)
            ->usingFileName($this->generateFileName($file))
            ->toMediaCollection('avatar');
    }

    /**
     * حذف مدیا
     */
    public function delete(Media $media): bool
    {
        return $media->delete();
    }

    /**
     * حذف همه مدیای یه collection
     */
    public function clearCollection(HasMedia $model, string $collection): void
    {
        $model->clearMediaCollection($collection);
    }

    // ==================== Helpers ====================

    protected function validateImage(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('فرمت تصویر پشتیبانی نمی‌شود');
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('حجم تصویر نباید بیشتر از ۵ مگابایت باشد');
        }
    }

    protected function generateFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::random(20) . '_' . time() . '.' . $extension;
    }
}