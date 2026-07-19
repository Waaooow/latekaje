<?php

namespace App\Filament\Resources\Loans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\AssetItem;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('asset_item_id')
                    ->label('Pilih Unit Barang / Scan QR')
                    ->searchable()
                    ->required()
                    ->getSearchResultsUsing(function (string $search): array {
                        return AssetItem::query()
                            ->where('status', 'tersedia')
                            ->where(function ($query) use ($search) {
                                $query->where('nomor_seri_atau_qr', 'like', "%{$search}%")
                                    ->orWhereHas('asset', function ($q) use ($search) {
                                        $q->where('nama_alat', 'like', "%{$search}%");
                                    });
                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn($record) => [
                                $record->id => self::formatLabel($record)
                            ])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $record = AssetItem::find($value);
                        return $record ? self::formatLabel($record) : null;
                    }),

                TextInput::make('nama_siswa')
                    ->label('Nama Lengkap Siswa')
                    ->required()
                    ->placeholder('Masukkan nama lengkap kamu...'),
                // 🟢 KELAS: Dibiarkan bisa diketik bebas agar siswa bisa menginput kelasnya saat ini
                TextInput::make('kelas')
                    ->label('Kelas')
                    ->required()
                    ->placeholder('Contoh: XII TJKT 2'),
            ]);
    }

    /**
     * Helper khusus (Static) untuk menyamakan format tampilan teks di dropdown
     */
    private static function formatLabel(AssetItem $record): string
    {
        $namaAlat = $record->asset?->nama_alat ?? 'Alat Tidak Dikenal';

        $lokasi = match ($record->lokasi) {
            'gudang' => '📦 Gudang',
            'ruang_kantor' => '🏢 Kantor',
            'lab_tjkt' => '💻 Lab TJKT',
            'lab_kkpi' => '🖥️ Lab KKPI',
            'lab_fo' => '⚡ Lab FO',
            default => '📍 Lainnya',
        };

        return "[{$record->nomor_seri_atau_qr}] {$namaAlat} ({$lokasi})";
    }
}
