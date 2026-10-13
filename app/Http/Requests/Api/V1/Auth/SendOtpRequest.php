<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/',
            ],
            'type' => 'nullable|in:login,register',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل الزامی است',
            'phone.regex'    => 'شماره موبایل معتبر نیست (مثال: 09123456789)',
            'type.in'        => 'نوع درخواست نامعتبر است',
        ];
    }

    protected function prepareForValidation(): void
    {
        // تبدیل اعداد فارسی به انگلیسی
        if ($this->phone) {
            $this->merge([
                'phone' => $this->convertToEnglish($this->phone),
            ]);
        }
    }

    protected function convertToEnglish(string $value): string
    {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];

        $value = str_replace($persian, $english, $value);
        $value = str_replace($arabic, $english, $value);

        return $value;
    }
}