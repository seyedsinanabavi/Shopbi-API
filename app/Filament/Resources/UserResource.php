<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'کاربران';
    protected static ?string $modelLabel = 'کاربر';
    protected static ?string $pluralModelLabel = 'کاربران';
    protected static ?string $navigationGroup = 'مدیریت کاربران';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('اطلاعات اصلی')
                ->schema([
                    Forms\Components\TextInput::make('phone')
                        ->label('شماره موبایل')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->tel()
                        ->maxLength(15)
                        ->regex('/^09[0-9]{9}$/')
                        ->validationMessages([
                            'regex' => 'شماره موبایل باید با ۰۹ شروع بشه و ۱۱ رقم باشه',
                        ]),

                    Forms\Components\TextInput::make('first_name')
                        ->label('نام')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('last_name')
                        ->label('نام خانوادگی')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('email')
                        ->label('ایمیل')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->label('رمز عبور')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn($state) =>
                            filled($state) ? Hash::make($state) : null
                        )
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $operation) => $operation === 'create')
                        ->minLength(8)
                        ->maxLength(255)
                        ->helperText('حداقل ۸ کاراکتر'),

                    // ==================== نقش‌های Spatie ====================
                    Forms\Components\Select::make('roles')
                        ->label('نقش‌ها (دسترسی‌ها)')
                        ->relationship('roles', 'name')
                        ->getOptionLabelFromRecordUsing(fn($record) => match ($record->name) {
                            'admin' => 'ادمین (دسترسی کامل)',
                            'editor' => 'ادیتور (مدیریت محتوا)',
                            'author' => 'نویسنده (فقط مقالات خودش)',
                            'user' => 'کاربر عادی',
                            default => $record->name,
                        })
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('نقش‌های Spatie تعیین‌کننده دسترسی‌های کاربر هستن')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('اطلاعات تکمیلی')
                ->schema([
                    Forms\Components\FileUpload::make('avatar')
                        ->label('آواتار')
                        ->image()
                        ->directory('avatars')
                        ->imageEditor()
                        ->avatar()
                        ->maxSize(2048),

                    Forms\Components\TextInput::make('national_code')
                        ->label('کد ملی')
                        ->maxLength(10)
                        ->regex('/^[0-9]{10}$/')
                        ->validationMessages([
                            'regex' => 'کد ملی باید ۱۰ رقم باشه',
                        ]),

                    Forms\Components\DatePicker::make('birth_date')
                        ->label('تاریخ تولد')
                        ->displayFormat('Y/m/d')
                        ->maxDate(now()),

                    Forms\Components\Select::make('gender')
                        ->label('جنسیت')
                        ->options([
                            'male' => 'مرد',
                            'female' => 'زن',
                            'other' => 'سایر',
                        ])
                        ->placeholder('انتخاب کنید')
                        ->nullable(),
                ])->columns(2),

            Forms\Components\Section::make('وضعیت و دسترسی')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('وضعیت حساب')
                        ->options([
                            'active' => 'فعال',
                            'inactive' => 'غیرفعال',
                            'banned' => 'مسدود',
                        ])
                        ->default('active')
                        ->required(),

                    Forms\Components\Toggle::make('phone_verified_at')
                        ->label('تأیید شماره موبایل')
                        ->dehydrateStateUsing(fn($state) => $state ? now() : null)
                        ->dehydrated(fn($state) => true)
                        ->formatStateUsing(fn($state) => !is_null($state))
                        ->helperText('اگه فعال باشه، شماره تأیید شده'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('آواتار')
                    ->getStateUsing(fn($record) => $record->avatar_url)
                    ->circular(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('نام')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('موبایل')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('کپی شد!'),

                Tables\Columns\TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                // ==================== نقش‌های Spatie ====================
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('نقش')
                    ->badge()
                    ->separator(',')
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'editor' => 'warning',
                        'author' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'admin' => 'ادمین',
                        'editor' => 'ادیتور',
                        'author' => 'نویسنده',
                        'user' => 'کاربر',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'banned' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'فعال',
                        'inactive' => 'غیرفعال',
                        'banned' => 'مسدود',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('phone_verified_at')
                    ->label('تأیید')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('عضویت')
                    ->dateTime('Y/m/d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('نقش')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'active' => 'فعال',
                        'inactive' => 'غیرفعال',
                        'banned' => 'مسدود',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('ban')
                    ->label('مسدود')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'banned')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['status' => 'banned'])),

                Tables\Actions\Action::make('activate')
                    ->label('فعال‌سازی')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'active')
                    ->action(fn($record) => $record->update(['status' => 'active'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}