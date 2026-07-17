<?php

namespace App\Filament\Resources\AssetItems\Pages;

use App\Filament\Resources\AssetItems\AssetItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetItem extends CreateRecord
{
    protected static string $resource = AssetItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
