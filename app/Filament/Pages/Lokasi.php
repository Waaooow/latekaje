<?php

namespace App\Filament\Pages;

use App\Models\AssetItem;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class Lokasi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Data per Lokasi';

    protected static ?string $slug = 'data-per-lokasi';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.lokasi';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetItem::query()->with(['location', 'asset'])
            )
            ->columns([
                TextColumn::make('location.label')
                    ->label('Lokasi')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('asset.nama_alat')
                    ->label('Alat')
                    ->description(fn ($record): ?string => trim(($record->asset?->kode_aset ?? '').' · '.($record->asset?->spesifikasi ?? ''), ' ·'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nomor_seri_atau_qr')
                    ->label('Kode QR')
                    ->badge()
                    ->color('warning')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'tersedia' ? 'success' : 'warning'),
            ])
            ->defaultSort('location.label', 'asc')
            ->paginated(false);
    }
}
