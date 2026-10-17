<?php

namespace App\Http\Requests\Api\V1\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'editor']);
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:150'],
            'parent_id'        => ['nullable', 'exists:categories,id'],
            'description'      => ['nullable', 'string'],
            'image'            => ['nullable', 'image', 'max:2048'],
            'icon'             => ['nullable', 'image', 'max:512'],
            'banner'           => ['nullable', 'image', 'max:5120'],
            'color'            => ['nullable', 'string', 'max:20'],
            'meta_title'       => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:500'],
            'canonical_url'    => ['nullable', 'url', 'max:500'],
            'is_indexable'     => ['nullable', 'boolean'],
            'is_followable'    => ['nullable', 'boolean'],
            'order'            => ['nullable', 'integer'],
            'is_active'        => ['nullable', 'boolean'],
            'show_in_menu'     => ['nullable', 'boolean'],
        ];
    }
}