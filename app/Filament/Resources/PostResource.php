<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'مقالات';
    protected static ?string $modelLabel = 'مقاله';
    protected static ?string $pluralModelLabel = 'مقالات';
    protected static ?string $navigationGroup = 'مدیریت محتوا';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Tabs')->tabs([

                // ==================== تب اصلی ====================
                Forms\Components\Tabs\Tab::make('اطلاعات اصلی')
                    ->icon('heroicon-o-document')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(250)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, Forms\Set $set) =>
                                $set('slug', Str::slug($state))
                            )
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->label('اسلاگ')
                            ->required()
                            ->maxLength(280)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('خلاصه')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('محتوا')
                            ->required()
                            ->fileAttachmentsDirectory('posts/content')
                            ->columnSpanFull(),
                    ])->columns(2),

                // ==================== تب دسته و برچسب ====================
                Forms\Components\Tabs\Tab::make('دسته و برچسب')
                    ->icon('heroicon-o-folder')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('دسته‌بندی')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('tags')
                            ->label('برچسب‌ها')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('type')
                            ->label('نوع')
                            ->options([
                                'post' => 'مقاله',
                                'page' => 'صفحه',
                                'product' => 'محصول',
                            ])
                            ->default('post')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                'draft' => 'پیش‌نویس',
                                'pending' => 'در انتظار بررسی',
                                'published' => 'منتشر شده',
                                'archived' => 'آرشیو',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),

                // ==================== تب تصاویر ====================
                Forms\Components\Tabs\Tab::make('تصاویر')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('تصویر شاخص')
                            ->image()
                            ->directory('posts/featured')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('gallery')
                            ->label('گالری')
                            ->image()
                            ->multiple()
                            ->directory('posts/gallery')
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ==================== تب SEO ====================
                Forms\Components\Tabs\Tab::make('SEO')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('عنوان متا')
                            ->maxLength(200)
                            ->helperText('اگه خالی باشه، عنوان مقاله استفاده میشه'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('توضیحات متا')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('بین ۱۲۰ تا ۱۶۰ کاراکتر مناسب‌تره'),

                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('کلمات کلیدی')
                            ->maxLength(500)
                            ->helperText('با کاما جدا کن'),

                        Forms\Components\TextInput::make('canonical_url')
                            ->label('URL کانونیکال')
                            ->url()
                            ->maxLength(500),

                        Forms\Components\TextInput::make('og_title')
                            ->label('عنوان OG')
                            ->maxLength(200),

                        Forms\Components\Textarea::make('og_description')
                            ->label('توضیحات OG')
                            ->rows(2)
                            ->maxLength(500),

                        Forms\Components\FileUpload::make('og_image')
                            ->label('تصویر OG')
                            ->image()
                            ->directory('posts/og')
                            ->maxSize(5120),

                        Forms\Components\Select::make('twitter_card')
                            ->label('Twitter Card')
                            ->options([
                                'summary' => 'Summary',
                                'summary_large_image' => 'Summary Large Image',
                            ])
                            ->default('summary_large_image'),

                        Forms\Components\Toggle::make('is_indexable')
                            ->label('قابل ایندکس شدن')
                            ->default(true),

                        Forms\Components\Toggle::make('is_followable')
                            ->label('قابل دنبال کردن')
                            ->default(true),
                    ])->columns(2),

                // ==================== تب انتشار ====================
                Forms\Components\Tabs\Tab::make('انتشار')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('زمان انتشار')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('زمان‌بندی شده')
                            ->nullable(),
                    ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('تصویر')
                    ->getStateUsing(fn($record) => $record->featured_image_url)
                    ->height(50)
                    ->width(70),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('نویسنده')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('دسته')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'پیش‌نویس',
                        'pending' => 'در انتظار',
                        'published' => 'منتشر شده',
                        'archived' => 'آرشیو',
                    }),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('بازدید')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('انتشار')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخرین ویرایش')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => 'پیش‌نویس',
                        'pending' => 'در انتظار',
                        'published' => 'منتشر شده',
                        'archived' => 'آرشیو',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع')
                    ->options([
                        'post' => 'مقاله',
                        'page' => 'صفحه',
                        'product' => 'محصول',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
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