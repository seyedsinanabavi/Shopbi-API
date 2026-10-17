<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Comment\StoreCommentRequest;
use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    /**
     * لیست کامنت‌های یک مقاله
     */
    public function index(Request $request, Post $post)
    {
        $comments = $post->comments()
            ->approved()
            ->roots()
            ->with(['user', 'children' => function ($q) {
                $q->approved()->with('user')->oldest();
            }])
            ->latest()
            ->paginate(15);

        return $this->paginatedResponse($comments, CommentResource::class);
    }

    /**
     * ثبت کامنت جدید
     */
    public function store(StoreCommentRequest $request, Post $post)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        // ✅ استفاده از validated()
        $data = $request->validated();

        $comment = Comment::create([
            'post_id'    => $post->getKey(),
            'user_id'    => $user->getKey(),
            'parent_id'  => $data['parent_id'] ?? null,
            'content'    => $data['content'],
            'status'     => 'pending',
            'ip_address' => $request->ip(),
        ]);

        $comment->load('user');

        return $this->successResponse(
            new CommentResource($comment),
            'کامنت شما ثبت شد و پس از تأیید نمایش داده می‌شود',
            201
        );
    }

    /**
     * حذف کامنت
     */
    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('ابتدا وارد حساب خود شوید', 401);
        }

        if ($comment->getAttribute('user_id') !== $user->getKey()
            && !$user->hasAnyRole(['admin', 'editor'])) {
            return $this->errorResponse('شما مجاز به حذف این کامنت نیستید', 403);
        }

        $comment->delete();

        return $this->noContentResponse('کامنت با موفقیت حذف شد');
    }
}