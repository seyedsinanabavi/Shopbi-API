<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'code'  => ['required', 'string', 'digits:5'],
            'type'  => 'nullable|in:login,register',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل الزامی است',
            'phone.regex'    => 'شماره موبایل معتبر نیست',
            'code.required'  => 'کد تأیید الزامی است',
            'code.digits'    => 'کد تأیید باید ۵ رقم باشد',
        ];
    }

    protected function prepareForValidation(): void
    {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];

        $this->merge([
            'phone' => $this->phone ? str_replace($persian, $english, $this->phone) : null,
            'code'  => $this->code ? str_replace($persian, $english, $this->code) : null,
        ]);
    }
}   