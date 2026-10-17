<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'first_name'       => ['nullable', 'string', 'max:100'],
            'last_name'        => ['nullable', 'string', 'max:100'],
            'email'            => ['nullable', 'email', 'max:255', "unique:users,email,{$userId}"],
            'gender'           => ['nullable', 'in:male,female,other'],
            'birth_date'       => ['nullable', 'date', 'before:today'],
            'national_code'    => ['nullable', 'string', 'digits:10'],
            'avatar'           => ['nullable', 'image', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password'     => ['nullable', 'string', Password::min(8), 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'              => 'این ایمیل قبلاً استفاده شده است',
            'gender.in'                 => 'جنسیت نامعتبر است',
            'birth_date.before'         => 'تاریخ تولد باید قبل از امروز باشد',
            'national_code.digits'      => 'کد ملی باید ۱۰ رقم باشد',
            'current_password.required_with' => 'برای تغییر رمز، رمز فعلی الزامی است',
            'new_password.min'          => 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد',
            'new_password.confirmed'    => 'تکرار رمز عبور مطابقت ندارد',
        ];
    }
}