<?php

namespace App\Http\Requests\Api\V1\Tag;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'editor']);
    }

    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'string', 'max:100'],
            'description'      => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}