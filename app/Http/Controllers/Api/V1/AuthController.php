<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\SendOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * ارسال کد OTP
     * POST /api/v1/auth/send-otp
     */
    public function sendOtp(SendOtpRequest $request)
    {
        $phone = $request->input('phone');
        $type  = $request->input('type', 'login'); // login | register

        // چک وجود کاربر برای register
        $userExists = User::where('phone', $phone)->exists();

        if ($type === 'register' && $userExists) {
            return $this->errorResponse('این شماره قبلاً ثبت‌نام کرده است. وارد شوید.', 422);
        }

        if ($type === 'login' && !$userExists) {
            return $this->errorResponse('این شماره ثبت‌نام نشده است. ابتدا ثبت‌نام کنید.', 404);
        }

        // ارسال OTP
        $result = $this->otpService->send($phone, $type);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 429, $result);
        }

        return $this->successResponse($result, $result['message']);
    }

    /**
     * تأیید کد OTP و لاگین/ثبت‌نام
     * POST /api/v1/auth/verify-otp
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $phone = $request->input('phone');
        $code  = $request->input('code');
        $type  = $request->input('type', 'login');

        // تأیید کد
        $result = $this->otpService->verify($phone, $code, $type);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422, $result);
        }

        // پیدا یا ساخت کاربر
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            // ثبت‌نام سریع با OTP
            $user = DB::transaction(function () use ($phone, $request) {
                $user = User::create([
                    'phone'              => $phone,
                    'role'               => 'user',
                    'status'             => 'active',
                    'phone_verified_at'  => now(),
                    'last_login_ip'      => $request->ip(),
                    'last_login_at'      => now(),
                ]);

                $user->assignRole('user');
                return $user;
            });
        } else {
            // چک بلاک نبودن
            if ($user->status === 'banned') {
                return $this->errorResponse('حساب کاربری شما مسدود است', 403);
            }

            if (!$user->phone_verified_at) {
                $user->markPhoneAsVerified();
            }

            $user->update([
                'last_login_ip' => $request->ip(),
                'last_login_at' => now(),
            ]);
        }

        // پاک کردن توکن‌های قبلی
        $user->tokens()->delete();

        // ساخت توکن جدید
        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addDays(30)
        )->plainTextToken;

        return $this->successResponse([
            'user'       => new UserResource($user->load('roles', 'permissions')),
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60, // 30 روز به ثانیه
        ], 'با موفقیت وارد شدید');
    }

    /**
     * ثبت‌نام کامل (اختیاری - با اطلاعات بیشتر)
     * POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request)
    {
        $phone = $request->input('phone');
        $code  = $request->input('code');

        // تأیید OTP
        $result = $this->otpService->verify($phone, $code, 'register');
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        if (User::where('phone', $phone)->exists()) {
            return $this->errorResponse('این شماره قبلاً ثبت‌نام کرده است', 422);
        }

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'phone'             => $request->phone,
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'email'             => $request->email,
                'password'          => $request->password ? Hash::make($request->password) : null,
                'gender'            => $request->gender,
                'birth_date'        => $request->birth_date,
                'role'              => 'user',
                'status'            => 'active',
                'phone_verified_at' => now(),
                'last_login_ip'     => $request->ip(),
                'last_login_at'     => now(),
            ]);

            $user->assignRole('user');
            return $user;
        });

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return $this->successResponse([
            'user'       => new UserResource($user->load('roles')),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 'ثبت‌نام با موفقیت انجام شد', 201);
    }

    /**
     * خروج از حساب
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->noContentResponse('با موفقیت خارج شدید');
    }

    /**
     * خروج از همه دستگاه‌ها
     * POST /api/v1/auth/logout-all
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->noContentResponse('از همه دستگاه‌ها خارج شدید');
    }

    /**
     * اطلاعات کاربر لاگین شده
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('roles', 'permissions', 'media');

        return $this->successResponse(
            new UserResource($user),
            'اطلاعات کاربر'
        );
    }

    /**
     * رفرش توکن
     * POST /api/v1/auth/refresh
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return $this->successResponse([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
        ], 'توکن جدید صادر شد');
    }
}