<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetSummaryTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '📊 Monitoring Real-Time Stok & Kondisi Alat';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Asset::query()->withCount([
                    'assetItems as ready_unit' => fn ($query) => $query->where('status', 'tersedia'),
                    'assetItems as dipinjam_unit' => fn ($query) => $query->where('status', 'dipinjam'),
                    'assetItems as rusak_unit' => fn ($query) => $query->where('status', 'rusak'),
                ])
            )
            ->columns([
                TextColumn::make('kode_aset')
                    ->label('Kode Katalog')
                    ->sortable(),

                TextColumn::make('nama_alat')
                    ->label('Nama Perangkat / Alat')
                    ->searchable(),

                TextColumn::make('stok')
                    ->label('Total Unit')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('ready_unit')
                    ->label('Tersedia (Ready)')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('dipinjam_unit')
                    ->label('Dipinjam Siswa')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),

                TextColumn::make('rusak_unit')
                    ->label('Rusak (Di Gudang)')
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
            ]);
    }
}
