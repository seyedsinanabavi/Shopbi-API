<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'excerpt'       => $this->excerpt,
            'content'       => $this->when(
                $request->routeIs('*.show') || $request->has('full'),
                $this->content
            ),
            'type'          => $this->type,
            'status'        => $this->status,
            
            // تصاویر
            'featured_image' => $this->getFirstMediaUrl('featured') ?: $this->featured_image,
            'gallery'        => $this->getMedia('gallery')->map(fn($m) => [
                'id'  => $m->id,
                'url' => $m->getUrl(),
                'thumb' => $m->getUrl('thumb'),
            ]),
            
            // روابط
            'author' => $this->whenLoaded('user', fn() => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
                'avatar'    => $this->user->avatar,
            ]),
            
            'category' => $this->whenLoaded('category', fn() => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->map(fn($tag) => [
                'id'   => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])),
            
            // آمار
            'views_count'    => $this->views_count,
            'likes_count'    => $this->likes_count,
            'comments_count' => $this->comments_count,
            'reading_time'   => $this->reading_time,
            
            // SEO
            'seo' => [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords'    => $this->meta_keywords,
                'canonical_url'    => $this->canonical_url,
                'og_title'         => $this->og_title,
                'og_description'   => $this->og_description,
                'og_image'         => $this->og_image,
                'twitter_card'     => $this->twitter_card,
                'schema_markup'    => $this->schema_markup,
                'is_indexable'     => $this->is_indexable,
                'is_followable'    => $this->is_followable,
            ],
            
            // زمان‌ها
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
            'url'          => $this->url,
        ];
    }
}