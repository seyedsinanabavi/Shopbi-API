<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ==================== Sanctum + Rate Limiting ====================
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
        ]);

        // ==================== Redirect + Security Headers ====================
        $middleware->web(append: [
            \App\Http\Middleware\RedirectMiddleware::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // ==================== Aliasها ====================
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ==================== JSON Response برای API ====================
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // ==================== 401 Unauthenticated ====================
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'ابتدا وارد حساب خود شوید',
                ], 401);
            }
        });

        // ==================== 403 Forbidden ====================
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما اجازه این کار را ندارید',
                ], 403);
            }
        });

        // ==================== 404 Not Found ====================
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'موردی یافت نشد',
                ], 404);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'مسیر یافت نشد',
                ], 404);
            }
        });

        // ==================== 422 Validation ====================
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطای اعتبارسنجی',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // ==================== 429 Too Many Requests ====================
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعداد درخواست‌ها بیش از حد مجاز. لطفاً کمی صبر کنید.',
                ], 429);
            }
        });

        // ==================== 500 Server Error ====================
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && !config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطای سرور. لطفاً بعداً تلاش کنید.',
                ], 500);
            }
        });
    })->create();