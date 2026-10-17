<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * ارسال کد OTP
     */
    public function sendOtp(string $phone, string $code): bool
    {
        if (app()->environment('local') && config('services.sms.driver') === 'log') {
            Log::channel('single')->info("📱 OTP for {$phone}: {$code}");
            return true;
        }

        return match (config('services.sms.driver')) {
            'kavenegar' => $this->sendKavenegar($phone, $code),
            'smsir'     => $this->sendSmsIr($phone, $code),
            default     => false,
        };
    }

    /**
     * پیامک متنی عادی
     */
    public function sendText(string $phone, string $message): bool
    {
        if (app()->environment('local')) {
            Log::info("📱 SMS to {$phone}: {$message}");
            return true;
        }

        return match (config('services.sms.driver')) {
            'kavenegar' => $this->sendKavenegarText($phone, $message),
            'smsir'     => $this->sendSmsIrText($phone, $message),
            default     => false,
        };
    }

    // ==================== Kavenegar ====================

    protected function sendKavenegar(string $phone, string $code): bool
    {
        try {
            $response = Http::timeout(10)->get(
                "https://api.kavenegar.com/v1/" . config('services.sms.kavenegar.api_key') . "/verify/lookup.json",
                [
                    'receptor' => $phone,
                    'token'    => $code,
                    'template' => config('services.sms.kavenegar.template', 'verify'),
                ]
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Kavenegar OTP error: " . $e->getMessage());
            return false;
        }
    }

    protected function sendKavenegarText(string $phone, string $message): bool
    {
        try {
            $response = Http::timeout(10)->post(
                "https://api.kavenegar.com/v1/" . config('services.sms.kavenegar.api_key') . "/sms/send.json",
                [
                    'receptor' => $phone,
                    'message'  => $message,
                    'sender'   => config('services.sms.kavenegar.sender'),
                ]
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Kavenegar SMS error: " . $e->getMessage());
            return false;
        }
    }

    // ==================== SMS.ir ====================

    protected function sendSmsIr(string $phone, string $code): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key'    => config('services.sms.smsir.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.sms.ir/v1/send/verify', [
                    'mobile'     => $phone,
                    'templateId' => config('services.sms.smsir.template_id'),
                    'parameters' => [
                        ['name' => 'CODE', 'value' => $code],
                    ],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("SMS.ir OTP error: " . $e->getMessage());
            return false;
        }
    }

    protected function sendSmsIrText(string $phone, string $message): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key'    => config('services.sms.smsir.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.sms.ir/v1/send/bulk', [
                    'lineNumber'  => config('services.sms.smsir.line_number'),
                    'messageText' => $message,
                    'mobiles'     => [$phone],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("SMS.ir text error: " . $e->getMessage());
            return false;
        }
    }
}