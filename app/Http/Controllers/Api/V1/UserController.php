<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\MediaService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * پروفایل کاربر لاگین شده
     * GET /api/v1/profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        $user->load(['roles', 'permissions', 'media']);

        return $this->successResponse(
            new UserResource($user),
            'اطلاعات پروفایل'
        );
    }

    /**
     * ویرایش پروفایل
     * PUT /api/v1/profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        // فقط فیلدهای مجاز
        $data = $request->safe()->only([
            'first_name',
            'last_name',
            'email',
            'gender',
            'birth_date',
            'national_code',
        ]);

        // تغییر رمز
        if ($request->filled('new_password')) {
            $currentPassword = $request->input('current_password');

            if (!Hash::check($currentPassword, $user->getAuthPassword())) {
                return $this->errorResponse('رمز عبور فعلی اشتباه است', 422);
            }

            $data['password'] = Hash::make($request->input('new_password'));
        }

        $user->update($data);

        // آپلود آواتار
        if ($request->hasFile('avatar')) {
            $this->mediaService->uploadAvatar($user, $request->file('avatar'));
        }

        return $this->successResponse(
            new UserResource($user->fresh()->load('roles')),
            'پروفایل با موفقیت ویرایش شد'
        );
    }

    /**
     * مقالات کاربر لاگین شده
     * GET /api/v1/profile/posts
     */
    public function myPosts(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        $posts = $user->posts()
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(15);

        return $this->paginatedResponse($posts, PostResource::class);
    }

    /**
     * نمایش پروفایل عمومی کاربر
     * GET /api/v1/users/{user}
     */
    public function show(User $user)
    {
        return $this->successResponse([
            'id'          => $user->getKey(),
            'full_name'   => $user->full_name,
            'avatar'      => $user->avatar_url,
            'bio'         => null,
            'posts_count' => $user->posts()->published()->count(),
            'joined_at'   => $user->created_at?->toIso8601String(),
        ], 'پروفایل عمومی کاربر');
    }
}