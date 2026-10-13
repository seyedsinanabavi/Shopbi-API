<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $usersLastWeek = User::where('created_at', '>=', now()->subDays(7))->count();
        $postsLastWeek = Post::where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('کاربران', User::count())
                ->description("+{$usersLastWeek} در هفته اخیر")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart(
                    User::query()
                        ->where('created_at', '>=', now()->subDays(7))
                        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->pluck('count')
                        ->toArray() ?: [0, 0, 0, 0, 0, 0, 0]
                ),

            Stat::make('مقالات', Post::count())
                ->description(
                    Post::where('status', 'published')->count() . ' منتشر شده • +' . $postsLastWeek . ' این هفته'
                )
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->chart(
                    Post::query()
                        ->where('created_at', '>=', now()->subDays(7))
                        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->pluck('count')
                        ->toArray() ?: [0, 0, 0, 0, 0, 0, 0]
                ),

            Stat::make('بازدید کل', number_format(Post::sum('views_count')))
                ->description('مجموع بازدید مقالات')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),

            Stat::make('کامنت‌ها', Comment::count())
                ->description(
                    Comment::where('status', 'pending')->count() . ' در انتظار تأیید'
                )
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('danger'),
        ];
    }
}