<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'image'       => $this->image ? asset('storage/' . $this->image) : null,
            'icon'        => $this->icon ? asset('storage/' . $this->icon) : null,
            'banner'      => $this->banner ? asset('storage/' . $this->banner) : null,
            'color'       => $this->color,
            'order'       => $this->order,
            'is_active'   => $this->is_active,
            'show_in_menu'=> $this->show_in_menu,
            'parent'      => $this->whenLoaded('parent', fn() => $this->parent ? [
                'id'   => $this->parent->id,
                'name' => $this->parent->name,
                'slug' => $this->parent->slug,
            ] : null),
            'children'    => CategoryResource::collection($this->whenLoaded('children')),
            'posts_count' => $this->whenCounted('posts'),
            'seo' => [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords'    => $this->meta_keywords,
                'canonical_url'    => $this->canonical_url,
                'is_indexable'     => $this->is_indexable,
                'is_followable'    => $this->is_followable,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}