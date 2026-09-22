<?php

namespace App\Filament\Exports;

use App\Models\Loan;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Carbon;

class LoanExporter extends Exporter
{
    protected static ?string $model = Loan::class;

    public static function getColumns(): array
    {
        $dt = fn ($state) => $state
            ? Carbon::parse($state)->format('d M Y H:i')
            : '-';

        return [
            ExportColumn::make('nama_alat')
                ->label(__('loans.exp_tool')),

            ExportColumn::make('assetItem.nomor_seri_atau_qr')
                ->label(__('loans.exp_qr')),

            ExportColumn::make('nis')
                ->label(__('loans.exp_nis')),

            ExportColumn::make('nama_siswa')
                ->label(__('loans.exp_borrower')),

            ExportColumn::make('kelas')
                ->label(__('loans.exp_class')),

            ExportColumn::make('tanggal_pinjam')
                ->label(__('loans.exp_borrowed_at'))
                ->formatStateUsing($dt),

            ExportColumn::make('tanggal_kembali')
                ->label(__('loans.exp_returned_at'))
                ->formatStateUsing($dt),

            ExportColumn::make('status')
                ->label(__('loans.exp_status'))
                ->formatStateUsing(fn (?string $state): string => $state === 'aktif' ? __('loans.exp_borrowed') : __('loans.exp_returned')),

            ExportColumn::make('lama_hari')
                ->label(__('loans.exp_duration')),

            ExportColumn::make('return_pin')
                ->label(__('loans.exp_pin')),

            ExportColumn::make('returned_by')
                ->label(__('loans.exp_returned_by')),

            ExportColumn::make('return_relation')
                ->label(__('loans.exp_returner_status')),

            ExportColumn::make('return_method')
                ->label(__('loans.exp_method')),

            ExportColumn::make('received_by')
                ->label(__('loans.exp_received_by')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('loans.export_done', ['count' => number_format($export->successful_rows)]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.__('loans.export_failed', ['count' => number_format($failedRowsCount)]);
        }

        return $body;
    }
}
