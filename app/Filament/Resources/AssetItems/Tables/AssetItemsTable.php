<?php

namespace App\Filament\Resources\AssetItems\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;

class AssetItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['activeLoan', 'asset']))

            // 🟢 Tetap pertahankan pengelompokan (grouping) biar terbagi per alat seperti di screenshot
            ->defaultGroup(
                Group::make('asset.nama_alat')
                    ->label('Nama Alat / Perangkat')
                    ->collapsible()
            )

            ->columns([
                TextColumn::make('asset.nama_alat')
                    ->label('Nama Alat')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('asset.kode_aset')
                    ->label('Kode Katalog')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nomor_seri_atau_qr')
                    ->label('Nomor Seri / QR')
                    ->description(fn ($record) => $record->asset?->spesifikasi) // Menampilkan tipe/spesifikasi persis di bawah nomor seri
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'dipinjam' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak' => 'warning',
                        'rusak_total' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                        'rusak_total' => 'Rusak Total',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('activeLoan.nama_siswa')
                    ->label('Peminjam Aktif')
                    ->default('-')
                    ->description(fn ($record) => $record->activeLoan?->kelas)
                    ->color(fn ($record) => $record->status === 'dipinjam' ? 'warning' : 'gray')
                    ->searchable(),

                TextColumn::make('lokasi')
                    ->label('Posisi Ruangan')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'gudang' => '📦 Gudang',
                        'ruang_kantor' => '🏢 Kantor',
                        'lab_tjkt' => '💻 Lab TJKT',
                        'lab_kkpi' => '🖥️ Lab KKPI',
                        'lab_fo' => '⚡ Lab FO',
                        default => $state,
                    })
                    ->sortable(),
            ])

            // 🟢 Dibuat default kosong tanpa filter dropdown bertumpuk agar UI minimalis & responsif
            ->filters([
                //
            ]);
    }
}
