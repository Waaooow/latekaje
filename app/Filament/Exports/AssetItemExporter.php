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
                ->label(__('assets.name_label')),
            ExportColumn::make('asset.kode_aset')
                ->label(__('assets.code_label')),
            ExportColumn::make('nomor_seri_atau_qr')
                ->label(__('units.serial_label')),
            ExportColumn::make('kondisi')
                ->label(__('units.condition_label')),
            ExportColumn::make('location.label')
                ->label(__('units.location_label')),
            ExportColumn::make('status')
                ->label(__('common.status')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('units.export_completed', ['success' => number_format($export->successful_rows)]);

        if ($failed = $export->getFailedRowsCount()) {
            $body .= __('units.export_failed_suffix', ['failed' => number_format($failed)]);
        }

        return $body . '.';
    }
}
