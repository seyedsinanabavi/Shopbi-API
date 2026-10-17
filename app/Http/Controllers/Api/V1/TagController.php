<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tag\StoreTagRequest;
use App\Http\Requests\Api\V1\Tag\UpdateTagRequest;
use App\Http\Resources\Api\V1\TagResource;
use App\Models\Tag;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use ApiResponse;

    /**
     * لیست برچسب‌ها
     * GET /api/v1/tags
     */
    public function index(Request $request)
    {
        $query = Tag::query()->withCount('posts');

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        $sort = $request->input('sort', 'popular');
        match ($sort) {
            'newest'  => $query->latest(),
            'name'    => $query->orderBy('name'),
            default   => $query->orderByDesc('usage_count'),
        };

        $perPage = min((int) $request->input('per_page', 30), 100);
        $tags = $query->paginate($perPage);

        return $this->paginatedResponse($tags, TagResource::class);
    }

    /**
     * نمایش یک برچسب
     * GET /api/v1/tags/{slug}
     */
    public function show(Tag $tag)
    {
        $tag->load(['posts' => function ($q) {
            $q->published()->latest('published_at')->limit(10);
        }]);

        return $this->successResponse(
            new TagResource($tag),
            'جزئیات برچسب'
        );
    }

    /**
     * ساخت برچسب (ادمین/ادیتور)
     * POST /api/v1/tags
     */
    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());

        return $this->successResponse(
            new TagResource($tag),
            'برچسب با موفقیت ساخته شد',
            201
        );
    }

    /**
     * ویرایش برچسب
     * PUT /api/v1/tags/{tag}
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return $this->successResponse(
            new TagResource($tag->fresh()),
            'برچسب با موفقیت ویرایش شد'
        );
    }

    /**
     * حذف برچسب
     * DELETE /api/v1/tags/{tag}
     */
    public function destroy(Tag $tag)
    {
        $tag->posts()->detach();
        $tag->delete();

        return $this->noContentResponse('برچسب با موفقیت حذف شد');
    }
}