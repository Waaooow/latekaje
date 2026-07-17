<?php

namespace App\Filament\Resources\AssetItems\Pages;

use App\Filament\Resources\AssetItems\AssetItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetItems extends ListRecords
{
    protected static string $resource = AssetItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
