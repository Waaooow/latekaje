<?php

namespace App\Filament\Resources\Loans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\AssetItem; // KUNCI: Panggil model AssetItem langsung

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
                    // 1. Ambil alih sistem pencarian agar mendukung pencarian lintas tabel (Relasi)
                    ->getSearchResultsUsing(function (string $search): array {
                        return AssetItem::query()
                            ->where('status', 'tersedia') // Hanya barang ready
                            ->where(function ($query) use ($search) {
                                // Cari berdasarkan Nomor Seri/QR lokal...
                                $query->where('nomor_seri_atau_qr', 'like', "%{$search}%")
                                    // ...ATAU cari menembus tabel 'assets' berdasarkan 'nama_alat'
                                    ->orWhereHas('asset', function ($q) use ($search) {
                                        $q->where('nama_alat', 'like', "%{$search}%");
                                    });
                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($record) => [
                                $record->id => self::formatLabel($record)
                            ])
                            ->toArray();
                    })
                    // 2. Mengambil label yang sesuai saat data dimuat (misal saat edit halaman)
                    ->getOptionLabelUsing(function ($value): ?string {
                        $record = AssetItem::find($value);
                        return $record ? self::formatLabel($record) : null;
                    }),

                TextInput::make('nama_siswa')
                    ->label('Nama Lengkap Siswa')
                    ->required()
                    ->placeholder('Masukkan nama siswa peminjam...'),

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
