<?php

namespace App\Filament\Exports;

use App\Models\AssetItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetItemExporter extends Exporter
{
    protected static ?string $model = AssetItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('asset.nama_alat')
                ->label('Nama Alat'),
            ExportColumn::make('asset.kode_aset')
                ->label('Kode Aset'),
            ExportColumn::make('nomor_seri_atau_qr')
                ->label('Nomor Seri / QR'),
            ExportColumn::make('kondisi')
                ->label('Kondisi'),
            ExportColumn::make('location.label')
                ->label('Lokasi'),
            ExportColumn::make('status')
                ->label('Status'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data unit selesai: ' . number_format($export->successful_rows) . ' baris berhasil';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ', ' . number_format($failed) . ' baris gagal';
        }

        return $body . '.';
    }
}
