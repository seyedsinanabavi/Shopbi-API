<?php

namespace App\Providers;

use App\Models\Post;
use App\Observers\PostObserver;
use App\Policies\PostPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ==================== Observer ====================
        Post::observe(PostObserver::class);

        // ==================== Policy ====================
        Gate::policy(Post::class, PostPolicy::class);

        // ==================== سوپر ادمین ====================
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        // ==================== Rate Limiting ====================
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->getKey() ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->input('phone') ?: $request->ip());
        });
    }
}