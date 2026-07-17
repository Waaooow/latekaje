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
                // 🟢 REVISI SAKTI: Jalur query disesuaikan dengan struktur kolom database yang benar
                Asset::query()->withCount([
                    'assetItems', // Untuk kolom 'Total Unit' secara realtime
                    
                    // 🟢 Tersedia (Ready) = Status tersedia DAN Kondisi harus Baik
                    'assetItems as ready_unit' => fn ($query) => $query->where('status', 'tersedia')->where('kondisi', 'baik'),
                    
                    // 🟢 Dipinjam Siswa = Status dipinjam
                    'assetItems as dipinjam_unit' => fn ($query) => $query->where('status', 'dipinjam'),
                    
                    // 🟢 Rusak (Di Gudang) = Kondisi rusak ATAU rusak_total (diambil dari kolom kondisi)
                    'assetItems as rusak_unit' => fn ($query) => $query->whereIn('kondisi', ['rusak', 'rusak_total']),
                ])
            )
            ->columns([
                TextColumn::make('kode_aset')
                    ->label('Kode Katalog')
                    ->sortable(),

                TextColumn::make('nama_alat')
                    ->label('Nama Perangkat / Alat')
                    ->searchable(),

                // 🟢 REVISI: Mengubah 'stok' menjadi 'asset_items_count' agar sinkron dengan data riil
                TextColumn::make('asset_items_count')
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