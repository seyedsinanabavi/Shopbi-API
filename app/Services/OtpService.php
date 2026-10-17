<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    /**
     * تولید و ارسال کد OTP
     */
    public function send(string $phone, string $type = 'login'): array
    {
        // 1. چک Rate Limit (فاصله بین ارسال‌ها)
        $recentCode = OtpCode::forPhone($phone)
            ->ofType($type)
            ->where('created_at', '>', now()->subSeconds(config('services.otp.resend_delay', 60)))
            ->latest()
            ->first();

        if ($recentCode) {
            $remaining = config('services.otp.resend_delay', 60) - now()->diffInSeconds($recentCode->created_at);
            return [
                'success' => false,
                'message' => "لطفاً {$remaining} ثانیه دیگر دوباره تلاش کنید",
                'wait'    => $remaining,
            ];
        }

        // 2. غیرفعال کردن کدهای قبلی
        OtpCode::forPhone($phone)
            ->ofType($type)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        // 3. تولید کد جدید
        $code = $this->generateCode();
        $expiresAt = now()->addMinutes(config('services.otp.expire_minutes', 2));

        // 4. ذخیره در دیتابیس
        $otp = OtpCode::create([
            'phone'        => $phone,
            'code'         => $code,
            'type'         => $type,
            'max_attempts' => config('services.otp.max_attempts', 5),
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'expires_at'   => $expiresAt,
        ]);

        // 5. ارسال پیامک (با Queue)
        try {
            \App\Jobs\SendOtpSms::dispatch($phone, $code);
            $sent = true;
        } catch (\Exception $e) {
            // اگه Job نبود، مستقیم بفرست
            $sent = $this->smsService->sendOtp($phone, $code);
        }

        if (!$sent) {
            $otp->delete();
            return [
                'success' => false,
                'message' => 'خطا در ارسال پیامک، لطفاً بعداً تلاش کنید',
            ];
        }

        return [
            'success'    => true,
            'message'    => 'کد تأیید ارسال شد',
            'expires_in' => config('services.otp.expire_minutes', 2) * 60,
        ];
    }

    /**
     * تأیید کد OTP
     */
    public function verify(string $phone, string $code, string $type = 'login'): array
    {
        // 1. پیدا کردن آخرین کد
        $otp = OtpCode::forPhone($phone)
            ->ofType($type)
            ->latest()
            ->first();

        if (!$otp) {
            return ['success' => false, 'message' => 'کدی برای این شماره یافت نشد'];
        }

        // 2. چک اعتبار
        if (!$otp->isValid()) {
            if ($otp->isExpired()) {
                return ['success' => false, 'message' => 'کد منقضی شده است'];
            }
            return ['success' => false, 'message' => 'کد نامعتبر است'];
        }

        // 3. چک کد
        if ($otp->code !== $code) {
            $otp->incrementAttempts();
            $remaining = $otp->max_attempts - $otp->attempts;
            return [
                'success'   => false,
                'message'   => "کد اشتباه است. {$remaining} تلاش باقی مانده",
                'remaining' => $remaining,
            ];
        }

        // 4. موفق - علامت‌گذاری
        $otp->markAsVerified();

        return ['success' => true, 'message' => 'کد تأیید شد'];
    }

    /**
     * تولید کد تصادفی
     */
    protected function generateCode(): string
    {
        $length = config('services.otp.length', 5);
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}