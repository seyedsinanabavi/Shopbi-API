<?php

namespace App\Http\Requests\Api\V1\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content'   => ['required', 'string', 'min:2', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'متن کامنت الزامی است',
            'content.min'      => 'کامنت باید حداقل ۲ کاراکتر باشد',
            'content.max'      => 'کامنت نباید بیشتر از ۲۰۰۰ کاراکتر باشد',
            'parent_id.exists' => 'کامنت والد یافت نشد',
        ];
    }
}