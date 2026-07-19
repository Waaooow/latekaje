<?php

namespace App\Filament\Resources\UserResource\Pages;

// 🟢 REVISI SAKTI: Import langsung ke file kelasnya, bukan nama foldernya
use App\Filament\Resources\UserResource\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}