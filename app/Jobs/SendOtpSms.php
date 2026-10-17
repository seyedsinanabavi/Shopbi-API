<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOtpSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $phone,
        public string $code
    ) {}

    public function handle(SmsService $smsService): void
    {
        $success = $smsService->sendOtp($this->phone, $this->code);

        if (!$success) {
            Log::warning("SMS failed for {$this->phone}");
            $this->release(60);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SMS job failed for {$this->phone}: " . $exception->getMessage());
    }
}