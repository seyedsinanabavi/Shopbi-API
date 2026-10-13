<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'تنظیمات سایت';
    protected static ?string $title = 'تنظیمات سایت';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // عمومی
            'site_name'        => Setting::get('site_name', 'سایت من'),
            'site_description' => Setting::get('site_description', ''),
            'contact_email'    => Setting::get('contact_email', ''),
            'contact_phone'    => Setting::get('contact_phone', ''),
            'address'          => Setting::get('address', ''),

            // SEO
            'meta_title'       => Setting::get('meta_title', ''),
            'meta_description' => Setting::get('meta_description', ''),
            'meta_keywords'    => Setting::get('meta_keywords', ''),
            'google_analytics' => Setting::get('google_analytics', ''),

            // اجتماعی
            'social_instagram' => Setting::get('social_instagram', ''),
            'social_telegram'  => Setting::get('social_telegram', ''),
            'social_twitter'   => Setting::get('social_twitter', ''),
            'social_linkedin'  => Setting::get('social_linkedin', ''),
            'social_youtube'   => Setting::get('social_youtube', ''),

            // OTP
            'otp_expire_minutes' => Setting::get('otp_expire_minutes', 2),
            'otp_length'         => Setting::get('otp_length', 5),
            'otp_max_attempts'   => Setting::get('otp_max_attempts', 5),
            'otp_resend_delay'   => Setting::get('otp_resend_delay', 60),

            // سیستم
            'maintenance_mode'    => Setting::get('maintenance_mode', false),
            'maintenance_message' => Setting::get('maintenance_message', ''),
            'allow_registration'  => Setting::get('allow_registration', true),
            'allow_comments'      => Setting::get('allow_comments', true),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')->tabs([

                    // ==================== عمومی ====================
                    Forms\Components\Tabs\Tab::make('عمومی')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\TextInput::make('site_name')
                                ->label('نام سایت')
                                ->required(),

                            Forms\Components\Textarea::make('site_description')
                                ->label('توضیحات سایت')
                                ->rows(2),

                            Forms\Components\TextInput::make('contact_email')
                                ->label('ایمیل تماس')
                                ->email(),

                            Forms\Components\TextInput::make('contact_phone')
                                ->label('تلفن تماس')
                                ->tel(),

                            Forms\Components\Textarea::make('address')
                                ->label('آدرس')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])->columns(2),

                    // ==================== SEO ====================
                    Forms\Components\Tabs\Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('عنوان متا پیش‌فرض')
                                ->maxLength(200),

                            Forms\Components\Textarea::make('meta_description')
                                ->label('توضیحات متا پیش‌فرض')
                                ->rows(2)
                                ->maxLength(500),

                            Forms\Components\TextInput::make('meta_keywords')
                                ->label('کلمات کلیدی'),

                            Forms\Components\Textarea::make('google_analytics')
                                ->label('کد Google Analytics')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])->columns(2),

                    // ==================== شبکه‌های اجتماعی ====================
                    Forms\Components\Tabs\Tab::make('شبکه‌های اجتماعی')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            Forms\Components\TextInput::make('social_instagram')
                                ->label('اینستاگرام')
                                ->url(),

                            Forms\Components\TextInput::make('social_telegram')
                                ->label('تلگرام')
                                ->url(),

                            Forms\Components\TextInput::make('social_twitter')
                                ->label('توییتر')
                                ->url(),

                            Forms\Components\TextInput::make('social_linkedin')
                                ->label('لینکدین')
                                ->url(),

                            Forms\Components\TextInput::make('social_youtube')
                                ->label('یوتیوب')
                                ->url(),
                        ])->columns(2),

                    // ==================== OTP ====================
                    Forms\Components\Tabs\Tab::make('OTP')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Forms\Components\TextInput::make('otp_expire_minutes')
                                ->label('زمان انقضا (دقیقه)')
                                ->numeric()
                                ->default(2),

                            Forms\Components\TextInput::make('otp_length')
                                ->label('طول کد')
                                ->numeric()
                                ->default(5),

                            Forms\Components\TextInput::make('otp_max_attempts')
                                ->label('حداکثر تلاش')
                                ->numeric()
                                ->default(5),

                            Forms\Components\TextInput::make('otp_resend_delay')
                                ->label('فاصله ارسال مجدد (ثانیه)')
                                ->numeric()
                                ->default(60),
                        ])->columns(2),

                    // ==================== سیستم ====================
                    Forms\Components\Tabs\Tab::make('سیستم')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Forms\Components\Toggle::make('maintenance_mode')
                                ->label('حالت تعمیر')
                                ->helperText('اگه فعال باشه، سایت بسته میشه'),

                            Forms\Components\Textarea::make('maintenance_message')
                                ->label('پیام حالت تعمیر')
                                ->rows(2),

                            Forms\Components\Toggle::make('allow_registration')
                                ->label('اجازه ثبت‌نام'),

                            Forms\Components\Toggle::make('allow_comments')
                                ->label('اجازه کامنت'),
                        ])->columns(2),
                ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('تنظیمات ذخیره شد')
            ->success()
            ->send();
    }
}