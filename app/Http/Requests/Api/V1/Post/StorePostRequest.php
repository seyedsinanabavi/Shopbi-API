<?php

namespace App\Http\Requests\Api\V1\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('posts.create');
    }

    public function rules(): array
    {
        return [
            // اطلاعات اصلی
            'title'          => ['required', 'string', 'min:3', 'max:250'],
            'content'        => ['required', 'string', 'min:10'],
            'excerpt'        => ['nullable', 'string', 'max:500'],
            'category_id'    => ['nullable', 'exists:categories,id'],
            'type'           => ['nullable', 'in:post,page,product'],
            'status'         => ['nullable', 'in:draft,pending,published,archived'],
            'published_at'   => ['nullable', 'date'],
            'scheduled_at'   => ['nullable', 'date', 'after:now'],

            // برچسب‌ها
            'tags'           => ['nullable', 'array'],
            'tags.*'         => ['integer', 'exists:tags,id'],

            // تصاویر
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'gallery'        => ['nullable', 'array', 'max:10'],
            'gallery.*'      => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],

            // SEO
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

    public function messages(): array
    {
        return [
            'title.required'      => 'عنوان مقاله الزامی است',
            'title.min'           => 'عنوان باید حداقل ۳ کاراکتر باشد',
            'content.required'    => 'محتوای مقاله الزامی است',
            'content.min'         => 'محتوا باید حداقل ۱۰ کاراکتر باشد',
            'category_id.exists'  => 'دسته‌بندی انتخاب شده معتبر نیست',
            'featured_image.image'=> 'فایل تصویر شاخص باید تصویر باشد',
            'featured_image.max'  => 'حجم تصویر شاخص نباید بیشتر از ۵ مگابایت باشد',
            'gallery.max'         => 'حداکثر ۱۰ تصویر می‌توانید آپلود کنید',
            'scheduled_at.after'  => 'زمان‌بندی باید بعد از زمان فعلی باشد',
        ];
    }
}