<?php

namespace App\Filament\Imports;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\Location;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AssetItemImporter extends Importer
{
    protected static ?string $model = AssetItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama_alat')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('kode_aset')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('jenis')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('spesifikasi')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('kegunaan')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('nomor_seri_atau_qr')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('kondisi')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('lokasi')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('status')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(fn () => null),
        ];
    }

    public function resolveRecord(): ?AssetItem
    {
        $namaAlat = trim((string) ($this->data['nama_alat'] ?? ''));

        if ($namaAlat === '') {
            return null;
        }

        $asset = Asset::firstOrCreate(
            ['nama_alat' => $namaAlat],
            [
                'kode_aset' => trim((string) ($this->data['kode_aset'] ?? '')) ?: ('IMP-' . strtoupper(substr(md5($namaAlat), 0, 8))),
                'jenis' => trim((string) ($this->data['jenis'] ?? '')) ?: 'Umum',
                'spesifikasi' => trim((string) ($this->data['spesifikasi'] ?? '')) ?: '-',
                'kegunaan' => trim((string) ($this->data['kegunaan'] ?? '')) ?: 'praktik',
                'stok' => 1,
            ]
        );

        $rawKey = trim((string) ($this->data['lokasi'] ?? ''));
        $key = $rawKey !== '' ? $rawKey : 'gudang';

        $location = Location::firstOrCreate(
            ['key' => $key],
            ['label' => ucwords(str_replace(['_', '-'], ' ', $key))]
        );

        $serial = trim((string) ($this->data['nomor_seri_atau_qr'] ?? ''));

        if ($serial === '') {
            return null;
        }

        $kondisi = strtolower(trim((string) ($this->data['kondisi'] ?? 'baik')));
        if (! in_array($kondisi, ['baik', 'rusak', 'rusak_total'], true)) {
            $kondisi = 'baik';
        }

        $status = strtolower(trim((string) ($this->data['status'] ?? 'tersedia')));
        if (! in_array($status, ['tersedia', 'dipinjam'], true)) {
            $status = 'tersedia';
        }

        $item = AssetItem::firstOrNew([
            'nomor_seri_atau_qr' => $serial,
        ]);

        $item->fill([
            'asset_id' => $asset->getKey(),
            'kondisi' => $kondisi,
            'location_id' => $location->getKey(),
            'status' => $status,
        ]);

        return $item;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor data unit selesai: ' . number_format($import->successful_rows) . ' baris berhasil';

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ', ' . number_format($failed) . ' baris gagal';
        }

        return $body . '.';
    }
}
