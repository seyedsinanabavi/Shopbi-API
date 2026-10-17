<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Category\StoreCategoryRequest;
use App\Http\Requests\Api\V1\Category\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * لیست دسته‌بندی‌ها
     * GET /api/v1/categories
     */
    public function index(Request $request)
    {
        $query = Category::query()
            ->withCount('posts')
            ->with(['parent', 'children']);

        // اگه فقط دسته‌های ریشه (برای منو)
        if ($request->boolean('roots_only')) {
            $query->whereNull('parent_id');
        }

        // فیلتر بر اساس والد
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // فقط فعال‌ها
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        // فیلتر بر اساس نمایش در منو
        if ($request->boolean('in_menu')) {
            $query->where('show_in_menu', true);
        }

        $categories = $query->ordered()->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'لیست دسته‌بندی‌ها'
        );
    }

    /**
     * نمایش یک دسته
     * GET /api/v1/categories/{slug}
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'posts' => function ($q) {
            $q->published()->latest('published_at')->limit(10);
        }]);

        return $this->successResponse(
            new CategoryResource($category),
            'جزئیات دسته‌بندی'
        );
    }

    /**
     * ساخت دسته (ادمین)
     * POST /api/v1/categories
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return $this->successResponse(
            new CategoryResource($category),
            'دسته‌بندی با موفقیت ساخته شد',
            201
        );
    }

    /**
     * ویرایش دسته (ادمین)
     * PUT /api/v1/categories/{category}
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return $this->successResponse(
            new CategoryResource($category->fresh()),
            'دسته‌بندی با موفقیت ویرایش شد'
        );
    }

    /**
     * حذف دسته (ادمین)
     * DELETE /api/v1/categories/{category}
     */
    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return $this->errorResponse(
                'این دسته‌بندی دارای مقاله است و قابل حذف نیست',
                422
            );
        }

        $category->delete();

        return $this->noContentResponse('دسته‌بندی با موفقیت حذف شد');
    }
}