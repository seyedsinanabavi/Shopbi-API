<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // اگه نقش انتخاب نشده، پیش‌فرض user
        if (empty($data['role'])) {
            $data['role'] = 'user';
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        // اگه هیچ نقشی انتخاب نشده، نقش user رو بده
        if ($this->record->roles()->count() === 0) {
            $this->record->assignRole('user');
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}