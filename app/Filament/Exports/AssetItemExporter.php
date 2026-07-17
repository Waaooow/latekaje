<?php

namespace App\Filament\Exports;

use App\Models\AssetItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetItemExporter extends Exporter
{
    // 🟢 Disesuaikan dengan PHP 8.5 agar tidak error inheritance
    protected static ?string $model = AssetItem::class;

    public static function getColumns(): array
    {
        return [
            // 🟢 Mengambil data dari relasi model induk (Asset)
            ExportColumn::make('asset.nama_alat')
                ->label('Nama Perangkat / Alat'),
                
            ExportColumn::make('asset.kode_aset')
                ->label('Kode Katalog'),

            // 🟢 Kolom asli dari tabel asset_items
            ExportColumn::make('nomor_seri_atau_qr')
                ->label('Nomor Seri / QR'),

            ExportColumn::make('kondisi')
                ->label('Kondisi'),

            ExportColumn::make('lokasi')
                ->label('Lokasi'),

            ExportColumn::make('status')
                ->label('Status Keberadaan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Proses export data unit telah selesai dan ' . number_format($export->successful_rows) . ' baris data berhasil diproses.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal di-export.';
        }

        return $body;
    }
}