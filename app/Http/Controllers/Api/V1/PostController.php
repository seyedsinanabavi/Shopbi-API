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
    ) {
        // ❌ middleware حذف شد — توی Route تعریف میشه
    }

    /**
     * لیست مقالات
     */
    public function index(Request $request)
    {
        $query = Post::query()
            ->with(['user', 'category', 'tags'])
            ->published();

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) =>
                $q->where('slug', $request->category)
            );
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn($q) =>
                $q->where('slug', $request->tag)
            );
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest'   => $query->oldest('published_at'),
            'popular'  => $query->orderByDesc('views_count'),
            'trending' => $query->orderByDesc('likes_count'),
            default    => $query->latest('published_at'),
        };

        $perPage = min((int) $request->input('per_page', 15), 50);
        $posts = $query->paginate($perPage);

        return $this->paginatedResponse($posts, PostResource::class);
    }

    /**
     * نمایش یک مقاله
     */
    public function show(Request $request, Post $post)
    {
        if (!$post->is_published) {
            $user = $request->user();
            if (!$user ||
                ($user->id !== $post->user_id &&
                 !$user->hasAnyRole(['admin', 'editor']))) {
                return $this->errorResponse('مقاله یافت نشد', 404);
            }
        }

        $cacheKey = "post_viewed:{$post->id}:" . $request->ip();
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
     * ساخت مقاله جدید
     */
    public function store(StorePostRequest $request)
    {
        try {
            $post = DB::transaction(function () use ($request) {
                $post = Post::create([
                    'user_id'          => $request->user()->id,
                    'category_id'      => $request->category_id,
                    'title'            => $request->title,
                    'content'          => $request->content,
                    'excerpt'          => $request->excerpt,
                    'type'             => $request->input('type', 'post'),
                    'status'           => $request->input('status', 'draft'),
                    'published_at'     => $request->published_at,
                    'scheduled_at'     => $request->scheduled_at,
                    'meta_title'       => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    'meta_keywords'    => $request->meta_keywords,
                    'canonical_url'    => $request->canonical_url,
                    'og_title'         => $request->og_title,
                    'og_description'   => $request->og_description,
                    'twitter_card'     => $request->input('twitter_card', 'summary_large_image'),
                    'is_indexable'     => $request->input('is_indexable', true),
                    'is_followable'    => $request->input('is_followable', true),
                    'schema_markup'    => $request->schema_markup,
                ]);

                if ($request->hasFile('featured_image')) {
                    $this->mediaService->uploadFeatured($post, $request->file('featured_image'));
                }

                if ($request->hasFile('gallery')) {
                    $this->mediaService->uploadGallery($post, $request->file('gallery'));
                }

                if ($request->hasFile('og_image')) {
                    $media = $post->addMediaFromRequest('og_image')
                        ->toMediaCollection('og_image');
                    $post->update(['og_image' => $media->getUrl()]);
                }

                if ($request->filled('tags')) {
                    $post->tags()->sync($request->tags);
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
        $this->authorize('update', $post);

        try {
            DB::transaction(function () use ($request, $post) {
                $post->update($request->only([
                    'title', 'content', 'excerpt', 'category_id', 'type', 'status',
                    'published_at', 'scheduled_at',
                    'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
                    'og_title', 'og_description', 'twitter_card',
                    'is_indexable', 'is_followable', 'schema_markup',
                ]));

                if ($request->boolean('remove_featured')) {
                    $this->mediaService->clearCollection($post, 'featured');
                } elseif ($request->hasFile('featured_image')) {
                    $this->mediaService->uploadFeatured($post, $request->file('featured_image'));
                }

                if ($request->filled('remove_gallery')) {
                    foreach ($request->remove_gallery as $mediaId) {
                        $media = $post->media()->find($mediaId);
                        $media?->delete();
                    }
                }

                if ($request->hasFile('gallery')) {
                    $this->mediaService->uploadGallery($post, $request->file('gallery'));
                }

                if ($request->hasFile('og_image')) {
                    $post->clearMediaCollection('og_image');
                    $media = $post->addMediaFromRequest('og_image')
                        ->toMediaCollection('og_image');
                    $post->update(['og_image' => $media->getUrl()]);
                }

                if ($request->has('tags')) {
                    $post->tags()->sync($request->tags ?? []);
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
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return $this->noContentResponse('مقاله با موفقیت حذف شد');
    }

    /**
     * مقالات من
     */
    public function myPosts(Request $request)
    {
        $query = Post::where('user_id', $request->user()->id)
            ->with(['category', 'tags'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate(min((int) $request->input('per_page', 15), 50));

        return $this->paginatedResponse($posts, PostResource::class);
    }
}