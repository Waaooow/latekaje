<?php

namespace App\Filament\Resources\AssetItems\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;

// Namespace untuk Actions di atas Header Tabel (Filament v5)
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;
use App\Filament\Imports\AssetItemImporter;
use App\Filament\Exports\AssetItemExporter;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class AssetItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['activeLoan', 'asset']))

            // Tetap pertahankan pengelompokan (grouping) biar terbagi per alat
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
                    ->description(fn($record) => $record->asset?->spesifikasi)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'let tersedia' => 'success',
                        'tersedia' => 'success',
                        'dipinjam' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak' => 'warning',
                        'rusak_total' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                        'rusak_total' => 'Rusak Total',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('activeLoan.nama_siswa')
                    ->label('Peminjam Aktif')
                    ->default('-')
                    ->description(fn($record) => $record->activeLoan?->kelas)
                    ->color(fn($record) => $record->status === 'dipinjam' ? 'warning' : 'gray')
                    ->searchable(),

                TextColumn::make('lokasi')
                    ->label('Posisi Ruangan')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'gudang' => '📦 Gudang',
                        'ruang_kantor' => '🏢 Kantor',
                        'lab_tjkt' => '💻 Lab TJKT',
                        'lab_kkpi' => '🖥️ Lab KKPI',
                        'lab_fo' => '⚡ Lab FO',
                        default => $state,
                    })
                    ->sortable(),
            ])

            ->filters([
                // Dibuat kosong sesuai request awal agar UI responsif
            ])

            ->headerActions([
                ImportAction::make()
                    ->importer(AssetItemImporter::class)
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning'),

                ExportAction::make()
                    ->exporter(AssetItemExporter::class)
                    ->label('Unduh Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),

                // 🟢 TAMBAHKAN INI: Tombol Cetak Massal Global di Atas Tabel
                \Filament\Actions\Action::make('printAllQr')
                    ->label('Cetak Semua QR')
                    ->icon('heroicon-o-printer')
                    ->color('info') // Warna biru biar beda dari yang lain
                    ->url(route('print.qr', ['ids' => 'all']))
                    ->openUrlInNewTab(),
            ])

            // JALUR ABSOLUT: Mengunci Edit & Delete agar aman dari konflik v5
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),

                // 🟢 TAMBAHKAN INI: Tombol Cetak QR Satuan di Baris Tabel
                \Filament\Actions\Action::make('printQr')
                    ->label('Cetak QR')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn($record): string => route('print.qr', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
            ])

            // 🟢 FIX FINAL: Memanggil langsung DeleteBulkAction dari rumpun utama Filament\Actions
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),

                // 🟢 TAMBAHKAN INI: Fitur Cetak QR Massal lewat Ceklis
                \Filament\Actions\BulkAction::make('printBulkQr')
                    ->label('Cetak QR Terpilih')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (Collection $records) {
                        // Gabungkan semua ID item yang diceklis menjadi string (misal: "1,3,5")
                        $ids = $records->pluck('id')->implode(',');

                        // Redirect aman ke tab cetak baru
                        return redirect()->away(route('print.qr', ['ids' => $ids]));
                    }),
            ]);
    }
}
