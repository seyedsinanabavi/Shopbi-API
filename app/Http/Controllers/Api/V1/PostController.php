<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Post\StorePostRequest;
use App\Http\Requests\Api\V1\Post\UpdatePostRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Services\MediaService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * لیست مقالات
     */
    public function index(Request $request)
    {
        $query = Post::query()
            ->with(['user', 'category', 'tags'])
            ->published();

        // فیلتر دسته
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) =>
                $q->where('slug', $request->input('category'))
            );
        }

        // فیلتر برچسب
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn($q) =>
                $q->where('slug', $request->input('tag'))
            );
        }

        // فیلتر نوع
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // جستجو
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%");
            });
        }

        // مرتب‌سازی
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest'   => $query->oldest('published_at'),
            'popular'  => $query->orderByDesc('views_count'),
            'trending' => $query->orderByDesc('likes_count'),
            default    => $query->latest('published_at'),
        };

        // صفحه‌بندی
        $perPage = min((int) $request->input('per_page', 15), 50);
        $posts = $query->paginate($perPage);

        return $this->paginatedResponse($posts, PostResource::class);
    }

    /**
     * نمایش مقاله
     */
    public function show(Request $request, Post $post)
    {
        // اگه منتشر نشده، فقط صاحب/ادمین ببینه
        if (!$post->is_published) {
            $user = $request->user();
            if (!$user ||
                ($user->getKey() !== $post->getAttribute('user_id') &&
                 !$user->hasAnyRole(['admin', 'editor']))) {
                return $this->errorResponse('مقاله یافت نشد', 404);
            }
        }

        // افزایش بازدید (کش شده)
        $cacheKey = "post_viewed:{$post->getKey()}:" . $request->ip();
        if (!cache()->has($cacheKey)) {
            $post->incrementViews();
            cache()->put($cacheKey, true, now()->addHours(6));
        }

        $post->load(['user', 'category', 'tags', 'approvedComments.user']);

        return $this->successResponse(
            new PostResource($post),
            'جزئیات مقاله'
        );
    }

    /**
     * ساخت مقاله
     */
    public function store(StorePostRequest $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        try {
            $post = DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                $post = Post::create([
                    'user_id'          => $user->getKey(),
                    'category_id'      => $data['category_id'] ?? null,
                    'title'            => $data['title'],
                    'content'          => $data['content'],
                    'excerpt'          => $data['excerpt'] ?? null,
                    'type'             => $data['type'] ?? 'post',
                    'status'           => $data['status'] ?? 'draft',
                    'published_at'     => $data['published_at'] ?? null,
                    'scheduled_at'     => $data['scheduled_at'] ?? null,
                    'meta_title'       => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'meta_keywords'    => $data['meta_keywords'] ?? null,
                    'canonical_url'    => $data['canonical_url'] ?? null,
                    'og_title'         => $data['og_title'] ?? null,
                    'og_description'   => $data['og_description'] ?? null,
                    'twitter_card'     => $data['twitter_card'] ?? 'summary_large_image',
                    'is_indexable'     => $data['is_indexable'] ?? true,
                    'is_followable'    => $data['is_followable'] ?? true,
                    'schema_markup'    => $data['schema_markup'] ?? null,
                ]);

                // آپلود تصویر شاخص
                if ($request->hasFile('featured_image')) {
                    $this->mediaService->uploadFeatured($post, $request->file('featured_image'));
                }

                // آپلود گالری
                if ($request->hasFile('gallery')) {
                    $this->mediaService->uploadGallery($post, $request->file('gallery'));
                }

                // آپلود OG
                if ($request->hasFile('og_image')) {
                    $media = $post->addMediaFromRequest('og_image')
                        ->toMediaCollection('og_image');
                    $post->update(['og_image' => $media->getUrl()]);
                }

                // برچسب‌ها
                if (!empty($data['tags'])) {
                    $post->tags()->sync($data['tags']);
                }

                return $post;
            });

            $post->load(['user', 'category', 'tags']);

            return $this->successResponse(
                new PostResource($post),
                'مقاله با موفقیت ساخته شد',
                201
            );
        } catch (\Exception $e) {
            Log::error('Post store error: ' . $e->getMessage());
            return $this->errorResponse('خطا در ساخت مقاله', 500);
        }
    }

    /**
     * ویرایش مقاله
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        // چک مجوز
        if ($user->getKey() !== $post->getAttribute('user_id')
            && !$user->hasAnyRole(['admin', 'editor'])) {
            return $this->errorResponse('شما مجاز به ویرایش این مقاله نیستید', 403);
        }

        try {
            DB::transaction(function () use ($request, $post) {
                $data = $request->validated();

                $post->update(array_filter([
                    'title'            => $data['title'] ?? null,
                    'content'          => $data['content'] ?? null,
                    'excerpt'          => $data['excerpt'] ?? null,
                    'category_id'      => $data['category_id'] ?? null,
                    'type'             => $data['type'] ?? null,
                    'status'           => $data['status'] ?? null,
                    'published_at'     => $data['published_at'] ?? null,
                    'scheduled_at'     => $data['scheduled_at'] ?? null,
                    'meta_title'       => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'meta_keywords'    => $data['meta_keywords'] ?? null,
                    'canonical_url'    => $data['canonical_url'] ?? null,
                    'og_title'         => $data['og_title'] ?? null,
                    'og_description'   => $data['og_description'] ?? null,
                    'twitter_card'     => $data['twitter_card'] ?? null,
                    'is_indexable'     => $data['is_indexable'] ?? null,
                    'is_followable'    => $data['is_followable'] ?? null,
                    'schema_markup'    => $data['schema_markup'] ?? null,
                ], fn($v) => !is_null($v)));

                // تصویر
                if ($request->boolean('remove_featured')) {
                    $this->mediaService->clearCollection($post, 'featured');
                } elseif ($request->hasFile('featured_image')) {
                    $this->mediaService->uploadFeatured($post, $request->file('featured_image'));
                }

                // گالری
                if ($request->filled('remove_gallery')) {
                    foreach ($request->input('remove_gallery') as $mediaId) {
                        $media = $post->media()->find($mediaId);
                        $media?->delete();
                    }
                }

                if ($request->hasFile('gallery')) {
                    $this->mediaService->uploadGallery($post, $request->file('gallery'));
                }

                // برچسب‌ها
                if (isset($data['tags'])) {
                    $post->tags()->sync($data['tags'] ?? []);
                }
            });

            $post->refresh()->load(['user', 'category', 'tags']);

            return $this->successResponse(
                new PostResource($post),
                'مقاله با موفقیت ویرایش شد'
            );
        } catch (\Exception $e) {
            Log::error('Post update error: ' . $e->getMessage());
            return $this->errorResponse('خطا در ویرایش مقاله', 500);
        }
    }

    /**
     * حذف مقاله
     */
    public function destroy(Request $request, Post $post)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        if ($user->getKey() !== $post->getAttribute('user_id')
            && !$user->hasAnyRole(['admin', 'editor'])) {
            return $this->errorResponse('شما مجاز به حذف این مقاله نیستید', 403);
        }

        $post->delete();

        return $this->noContentResponse('مقاله با موفقیت حذف شد');
    }

    /**
     * مقالات من
     */
    public function myPosts(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        $query = Post::where('user_id', $user->getKey())
            ->with(['category', 'tags'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $posts = $query->paginate(min((int) $request->input('per_page', 15), 50));

        return $this->paginatedResponse($posts, PostResource::class);
    }
}