<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'usage_count' => $this->usage_count,
            'posts_count' => $this->whenCounted('posts'),
            'seo' => [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}