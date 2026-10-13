<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * لیست — همه می‌تونن ببینن
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * مشاهده — همه می‌تونن (اگه published باشه)
     */
    public function view(?User $user, Post $post): bool
    {
        if ($post->status === 'published' && $post->published_at <= now()) {
            return true;
        }

        if (!$user) return false;
        return $user->id === $post->user_id || $user->isAdmin();
    }

    /**
     * ساخت — نیاز به مجوز
     */
    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    /**
     * ویرایش — نویسنده خودش یا ادمین
     */
    public function update(User $user, Post $post): bool
    {
        // ادمین همیشه می‌تونه
        if ($user->hasAnyRole(['admin', 'editor'])) {
            return true;
        }

        // نویسنده فقط اگه پست خودش باشه و منتشر نشده
        if ($user->id === $post->user_id) {
            return $post->status !== 'published' || $user->can('posts.publish');
        }

        return false;
    }

    /**
     * حذف — ادمین یا نویسنده
     */
    public function delete(User $user, Post $post): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('editor')) {
            return true;
        }

        return $user->id === $post->user_id && $user->can('posts.delete');
    }

    /**
     * انتشار — فقط با مجوز
     */
    public function publish(User $user, Post $post): bool
    {
        return $user->can('posts.publish');
    }
}