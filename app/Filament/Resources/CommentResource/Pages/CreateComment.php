<?php

namespace App\Filament\Resources\CommentResource\Pages;

use App\Filament\Resources\CommentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateComment extends CreateRecord
{
    protected static string $resource = CommentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // اگه user_id خالی بود، کاربر فعلی
        if (empty($data['user_id'])) {
            $data['user_id'] = Auth::id();
        }

        // IP خودکار
        if (empty($data['ip_address'])) {
            $data['ip_address'] = request()->ip();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}