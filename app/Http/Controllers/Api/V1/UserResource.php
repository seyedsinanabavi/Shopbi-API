<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'phone'        => $this->phone,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'full_name'    => $this->full_name,
            'email'        => $this->email,
            'avatar'       => $this->avatar ?? $this->getFirstMediaUrl('avatar') ?: null,
            'gender'       => $this->gender,
            'birth_date'   => $this->birth_date?->toDateString(),
            'role'         => $this->role,
            'status'       => $this->status,
            'roles'        => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'permissions'  => $this->whenLoaded('permissions', fn() => $this->getAllPermissions()->pluck('name')),
            'phone_verified' => !is_null($this->phone_verified_at),
            'last_login_at'  => $this->last_login_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}