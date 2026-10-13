<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'      => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'unique:users,phone'],
            'code'       => ['required', 'string', 'digits:5'],
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name'  => ['required', 'string', 'min:2', 'max:50'],
            'email'      => ['nullable', 'email', 'unique:users,email'],
            'password'   => ['nullable', 'string', Password::min(8)],
            'gender'     => ['nullable', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique'      => 'این شماره قبلاً ثبت‌نام شده است',
            'email.unique'      => 'این ایمیل قبلاً استفاده شده است',
            'password.min'      => 'رمز عبور باید حداقل ۸ کاراکتر باشد',
        ];
    }
}