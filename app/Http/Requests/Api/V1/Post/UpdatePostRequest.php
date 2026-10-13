<?php

namespace App\Http\Requests\Api\V1\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('posts.edit');
    }

    public function rules(): array
    {
        return [
            'title'          => ['sometimes', 'string', 'min:3', 'max:250'],
            'content'        => ['sometimes', 'string', 'min:10'],
            'excerpt'        => ['nullable', 'string', 'max:500'],
            'category_id'    => ['nullable', 'exists:categories,id'],
            'type'           => ['nullable', 'in:post,page,product'],
            'status'         => ['nullable', 'in:draft,pending,published,archived'],
            'published_at'   => ['nullable', 'date'],
            'scheduled_at'   => ['nullable', 'date'],

            'tags'           => ['nullable', 'array'],
            'tags.*'         => ['integer', 'exists:tags,id'],

            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_featured'=> ['nullable', 'boolean'],
            'gallery'        => ['nullable', 'array', 'max:10'],
            'gallery.*'      => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['integer'],

            'meta_title'       => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:500'],
            'canonical_url'    => ['nullable', 'url', 'max:500'],
            'og_title'         => ['nullable', 'string', 'max:200'],
            'og_description'   => ['nullable', 'string', 'max:500'],
            'og_image'         => ['nullable', 'image', 'max:5120'],
            'twitter_card'     => ['nullable', 'in:summary,summary_large_image,app,player'],
            'is_indexable'     => ['nullable', 'boolean'],
            'is_followable'    => ['nullable', 'boolean'],
            'schema_markup'    => ['nullable', 'array'],
        ];
    }
}