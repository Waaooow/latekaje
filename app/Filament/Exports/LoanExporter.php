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
                ->label('Alat'),

            ExportColumn::make('assetItem.nomor_seri_atau_qr')
                ->label('Kode QR'),

            ExportColumn::make('nama_siswa')
                ->label('Peminjam'),

            ExportColumn::make('kelas')
                ->label('Kelas'),

            ExportColumn::make('tanggal_pinjam')
                ->label('Tgl Pinjam')
                ->formatStateUsing($dt),

            ExportColumn::make('tanggal_kembali')
                ->label('Tgl Kembali')
                ->formatStateUsing($dt),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (?string $state): string => $state === 'aktif' ? 'Dipinjam' : 'Kembali'),

            ExportColumn::make('lama_hari')
                ->label('Lama'),

            ExportColumn::make('return_pin')
                ->label('PIN'),

            ExportColumn::make('returned_by')
                ->label('Dikembalikan Oleh'),

            ExportColumn::make('return_relation')
                ->label('Status Pengembali'),

            ExportColumn::make('return_method')
                ->label('Metode'),

            ExportColumn::make('received_by')
                ->label('Diterima Petugas'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export rekap selesai: '.number_format($export->successful_rows).' baris.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' baris gagal.';
        }

        return $body;
    }
}
