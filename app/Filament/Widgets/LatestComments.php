<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestComments extends BaseWidget
{
    protected static ?string $heading = 'آخرین کامنت‌ها';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Comment::query()
                    ->with(['user', 'post'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('کاربر')
                    ->searchable()
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('post.title')
                    ->label('مقاله')
                    ->limit(30)
                    ->searchable()
                    ->icon('heroicon-m-document-text'),

                Tables\Columns\TextColumn::make('content')
                    ->label('متن')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'تأیید شده',
                        'rejected' => 'رد شده',
                        'pending' => 'در انتظار',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('زمان')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc');
    }
}