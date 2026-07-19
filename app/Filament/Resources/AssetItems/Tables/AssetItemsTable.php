<?php

namespace App\Filament\Resources\AssetItems\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;

// 🟢 KUNCI DI VERSI BARU: Semua jenis Action melebur jadi satu di rumpun ini
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;

use App\Filament\Imports\AssetItemImporter;
use App\Filament\Exports\AssetItemExporter;
use App\Models\AssetItem;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Select;

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
                // Kosong agar UI responsif
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

                Action::make('printAllQr')
                    ->label('Cetak Semua QR')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(route('print.qr', ['ids' => 'all']))
                    ->openUrlInNewTab(),
            ])

            ->actions([
                EditAction::make(),
                DeleteAction::make(),

                Action::make('printQr')
                    ->label('Cetak QR')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn($record): string => route('print.qr', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
            ])

            ->bulkActions([
                DeleteBulkAction::make(),

                BulkAction::make('printBulkQr')
                    ->label('Cetak QR Terpilih')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $ids = $records->pluck('id')->implode(',');
                        return redirect()->away(route('print.qr', ['ids' => $ids]));
                    }),

                BulkAction::make('pindahRuanganMassal')
                    ->label('Pindahkan Ruangan')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->form([
                        Select::make('lokasi_baru')
                            ->label('Pilih Ruangan Tujuan')
                            ->options([
                                'gudang' => 'Gudang',
                                'ruang_kantor' => 'Kantor',
                                'lab_tjkt' => 'Lab TJKT',
                                'lab_kkpi' => 'Lab KKPI',
                                'lab_fo' => 'Lab FO',
                            ])
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $records->each(function (AssetItem $record) use ($data) {
                            $record->update([
                                'lokasi' => $data['lokasi_baru'],
                            ]);
                        });
                    }),
            ]);
    }
}