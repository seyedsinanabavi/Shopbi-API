<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * پاسخ موفق
     */
    protected function successResponse(
        $data = null,
        string $message = 'عملیات با موفقیت انجام شد',
        int $code = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * پاسخ خطا
     */
    protected function errorResponse(
        string $message = 'خطایی رخ داد',
        int $code = 400,
        $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * پاسخ صفحه‌بندی‌شده
     */
    protected function paginatedResponse(
        $paginator,
        $resource = null,
        string $message = 'لیست با موفقیت دریافت شد'
    ): JsonResponse {
        $items = $resource
            ? $resource::collection($paginator->items())
            : $paginator->items();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
                'last'  => $paginator->url($paginator->lastPage()),
            ],
        ]);
    }

    /**
     * پاسخ بدون محتوا
     */
    protected function noContentResponse(string $message = 'عملیات با موفقیت انجام شد'): JsonResponse
    {
        return $this->successResponse(null, $message, 200);
    }
}