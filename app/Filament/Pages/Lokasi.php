<?php

namespace App\Filament\Pages;

use App\Models\AssetItem;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Data per Lokasi';
    protected static ?string $slug = 'data-per-lokasi';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.lokasi';

    public function getTableRecordKey(Model|array $record): string
    {
        return is_array($record) 
            ? (string) ($record['nomor_seri_atau_qr'] ?? '') 
            : (string) $record->nomor_seri_atau_qr;
    }

    public function table(Table $table): Table
    {
        $query = AssetItem::query();
        
        // Kunci ke kolom unik agar terhindar dari bentrokan sorting bawaan Filament v5
        $query->getModel()->setKeyName('nomor_seri_atau_qr');

        return $table
            ->query(
                $query
                    ->join('assets', 'asset_items.asset_id', '=', 'assets.id')
                    ->whereNotNull('asset_items.lokasi')
                    ->where('asset_items.lokasi', '!=', '')
                    ->select([
                        'asset_items.lokasi',
                        'asset_items.asset_id',
                        'asset_items.nomor_seri_atau_qr',
                        'asset_items.kondisi',
                        'asset_items.status',
                        'assets.nama_alat',
                        'assets.kode_aset',
                    ])
                    ->groupBy([
                        'asset_items.lokasi',
                        'asset_items.asset_id',
                        'asset_items.nomor_seri_atau_qr',
                        'asset_items.kondisi',
                        'asset_items.status',
                        'assets.nama_alat',
                        'assets.kode_aset',
                    ])
            )
            // 🟢 DEFAULT SORTING: Pertama kali dibuka, data otomatis urut berdasarkan ruangan terdekat (A-Z)
            ->defaultSort('lokasi', 'asc')
            ->columns([
                TextColumn::make('lokasi')
                    ->label('Posisi Ruangan')
                    ->searchable()
                    // 🟢 Aktifkan sortir kolom ruangan
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace('_', ' ', $state))),
                
                TextColumn::make('nama_alat')
                    ->label('Nama Perangkat / Alat')
                    ->searchable()
                    // 🟢 Aktifkan sortir kolom nama alat
                    ->sortable()
                    ->description(fn ($record) => "Katalog: {$record->kode_aset}"),

                TextColumn::make('nomor_seri_atau_qr')
                    ->label('Kode QR / ID Perangkat')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    // 🟢 Aktifkan sortir kolom nomor seri/QR
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode QR berhasil disalin!')
                    ->copyMessageDuration(1500),
                
                TextColumn::make('kondisi')
                    ->label('Kondisi Fisik')
                    ->badge()
                    // 🟢 Aktifkan sortir kolom kondisi fisik
                    ->sortable()
                    ->state(fn ($record) => match ($record->kondisi) {
                        'baik' => 'Baik',
                        'rusak' => 'Rusak Ringan',
                        'rusak_total' => 'Rusak Total',
                        default => $record->kondisi,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak Ringan' => 'warning',
                        'Rusak Total' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status Ketersediaan')
                    ->badge()
                    // 🟢 Aktifkan sortir kolom status ketersediaan
                    ->sortable()
                    ->state(fn ($record) => match ($record->status) {
                        'tersedia' => 'Tersedia',
                        'dipinjam' => 'Sedang Dipinjam',
                        default => $record->status,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Sedang Dipinjam' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}