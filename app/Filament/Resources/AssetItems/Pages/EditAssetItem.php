<?php

namespace App\Filament\Resources\AssetItems\Pages;

use App\Filament\Resources\AssetItems\AssetItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssetItem extends EditRecord
{
    protected static string $resource = AssetItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                    ->label(__('common.delete')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
