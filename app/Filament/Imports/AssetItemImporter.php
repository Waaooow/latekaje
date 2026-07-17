<?php

namespace App\Filament\Imports;

use App\Models\Asset;
use App\Models\AssetItem;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AssetItemImporter extends Importer
{
    protected static ?string $model = AssetItem::class;

    public static function getColumns(): array
    {
        return [
            // 🟢 FIX 100%: Menggunakan fillRecordUsing(fn () => null) agar Filament tidak menyentuh model anak
            ImportColumn::make('nama_alat')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null),
                
            ImportColumn::make('kode_aset')
                ->fillRecordUsing(fn () => null),
                
            ImportColumn::make('jenis')
                ->fillRecordUsing(fn () => null),
                
            ImportColumn::make('spesifikasi')
                ->fillRecordUsing(fn () => null),
                
            ImportColumn::make('kegunaan')
                ->fillRecordUsing(fn () => null),

            // Kolom asli milik tabel asset_items (Bypass juga agar tidak menimpa logic fallback kita)
            ImportColumn::make('nomor_seri_atau_qr')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null),
                
            ImportColumn::make('kondisi')
                ->fillRecordUsing(fn () => null),
                
            ImportColumn::make('lokasi')
                ->fillRecordUsing(fn () => null),
                
            ImportColumn::make('status')
                ->fillRecordUsing(fn () => null),
        ];
    }

    public function resolveRecord(): ?AssetItem
    {
        // 1. Ambil atau buat data Katalog Aset (Induk)
        $asset = Asset::firstOrCreate(
            ['nama_alat' => $this->data['nama_alat']],
            [
                'kode_aset' => ($this->data['kode_aset'] ?? '') ?: 'AST-' . strtoupper(uniqid()),
                'jenis' => ($this->data['jenis'] ?? '') ?: 'perangkat',
                'spesifikasi' => ($this->data['spesifikasi'] ?? '') ?: '-',
                'kegunaan' => ($this->data['kegunaan'] ?? '') ?: 'praktik',
            ]
        );

        // 2. Cari apakah nomor seri ini sudah pernah ada untuk menghindari duplikat
        $assetItem = AssetItem::firstOrNew([
            'nomor_seri_atau_qr' => $this->data['nomor_seri_atau_qr'],
        ]);

        // 3. Amankan pengisian data unit fisik dengan fallback nilai default secara absolut
        $assetItem->fill([
            'asset_id' => $asset->id,
            'nomor_seri_atau_qr' => $this->data['nomor_seri_atau_qr'],
            'kondisi' => ($this->data['kondisi'] ?? '') ?: 'baik',
            'lokasi' => ($this->data['lokasi'] ?? '') ?: 'gudang',
            'status' => ($this->data['status'] ?? '') ?: 'tersedia',
        ]);

        return $assetItem;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Proses import data unit telah selesai dan ' . number_format($import->successful_rows) . ' baris berhasil dimasukkan.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal di-import.';
        }

        return $body;
    }
}