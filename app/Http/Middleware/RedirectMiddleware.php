<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMiddleware
{
    /**
     * ریدایرکت خودکار بر اساس جدول redirects
     */
    public function handle(Request $request, Closure $next): Response
    {
        // فقط برای GET
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // مسیرهای ادمین و API رو رد کن
        if ($request->is('admin/*') || $request->is('api/*') || $request->is('livewire/*')) {
            return $next($request);
        }

        // جستجوی ریدایرکت
        $url = '/' . ltrim($request->getPathInfo(), '/');
        $redirect = Redirect::active()->byUrl($url)->first();

        if ($redirect) {
            $redirect->incrementHits();

            return redirect($redirect->new_url, $redirect->status_code);
        }

        return $next($request);
    }
}