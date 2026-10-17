<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'content'    => $this->content,
            'status'     => $this->status,
            'parent_id'  => $this->parent_id,
            'user'       => $this->whenLoaded('user', fn() => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
                'avatar'    => $this->user->avatar_url,
            ]),
            'children'   => CommentResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}