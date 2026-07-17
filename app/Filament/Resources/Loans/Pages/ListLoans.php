<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction; // 🟢 IMPORT: Core Action bawaan Filament v5

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Pinjam Alat')
                ->icon('heroicon-o-plus'),
        ];
    }
}
