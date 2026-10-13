<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'کامنت‌ها';
    protected static ?string $modelLabel = 'کامنت';
    protected static ?string $pluralModelLabel = 'کامنت‌ها';
    protected static ?string $navigationGroup = 'مدیریت محتوا';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('post_id')
                ->label('مقاله')
                ->relationship('post', 'title')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('user_id')
                ->label('کاربر')
                ->relationship('user', 'phone')
                ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                ->searchable(['first_name', 'last_name', 'phone'])
                ->preload()
                ->default(fn() => Auth::id())
                ->required(),

            Forms\Components\Select::make('parent_id')
                ->label('پاسخ به')
                ->relationship('parent', 'id')
                ->getOptionLabelFromRecordUsing(fn($record) => 
                    "کامنت #{$record->id} — " . \Str::limit($record->content, 30)
                )
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Textarea::make('content')
                ->label('متن')
                ->required()
                ->rows(5)
                ->columnSpanFull(),

            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([
                    'pending' => 'در انتظار',
                    'approved' => 'تأیید شده',
                    'rejected' => 'رد شده',
                ])
                ->default('pending')
                ->required(),

            Forms\Components\TextInput::make('ip_address')
                ->label('IP')
                ->maxLength(45)
                ->disabled()
                ->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('کاربر')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('post.title')
                    ->label('مقاله')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),

                Tables\Columns\TextColumn::make('content')
                    ->label('متن')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'تأیید شده',
                        'rejected' => 'رد شده',
                        'pending' => 'در انتظار',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('زمان')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار',
                        'approved' => 'تأیید شده',
                        'rejected' => 'رد شده',
                    ]),

                Tables\Filters\SelectFilter::make('post')
                    ->label('مقاله')
                    ->relationship('post', 'title')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('user')
                    ->label('کاربر')
                    ->relationship('user', 'phone'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('تأیید')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'approved')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);
                        \Filament\Notifications\Notification::make()
                            ->title('کامنت تأیید شد')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('رد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'rejected')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        \Filament\Notifications\Notification::make()
                            ->title('کامنت رد شد')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve_all')
                    ->label('تأیید همه')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($records) {
                        $records->each->update(['status' => 'approved']);
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}