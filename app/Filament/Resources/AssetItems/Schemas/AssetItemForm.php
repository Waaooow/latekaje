<?php

namespace App\Filament\Resources\AssetItems\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Asset;
use App\Models\AssetItem;

// Namespace untuk tombol aksi di dalam inputan form
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Set;

class AssetItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 🟢 SEKARANG BERSIH: Validasi rules() dibuang agar input data gak gampang ke-block eror kuota
                Select::make('asset_id')
                    ->label('Pilih Tipe Alat (Katalog)')
                    ->relationship('asset', 'nama_alat')
                    ->searchable(['kode_aset', 'nama_alat'])
                    ->getOptionLabelFromRecordUsing(fn($record) => "[{$record->kode_aset}] {$record->nama_alat}")
                    ->required(),

                TextInput::make('nomor_seri_atau_qr')
                    ->label('Nomor Seri / Kode QR')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ketik SN pabrik atau klik tombol kanan untuk buat otomatis')
                    ->suffixAction(
                        \Filament\Actions\Action::make('generateUniqueCode')
                            ->icon('heroicon-m-arrow-path')
                            ->color('success')
                            ->tooltip('Generate Kode Unik Lab')
                            ->action(function ($set) {
                                // 1. Tentukan awalan kode unik (Hasil: LTKJ-2026-)
                                $prefix = 'LTKJ-' . date('Y') . '-'; 
                                
                                // 2. Cari data terakhir di DB yang kodenya mirip dengan awalan tahun ini
                                $lastItem = AssetItem::where('nomor_seri_atau_qr', 'LIKE', $prefix . '%')
                                    ->orderBy('nomor_seri_atau_qr', 'desc')
                                    ->first();
                                    
                                if ($lastItem) {
                                    // Mengambil 5 digit angka terakhir dari string kode terbesar di DB
                                    $lastNumber = (int) substr($lastItem->nomor_seri_atau_qr, -5);
                                    $nextNumber = $lastNumber + 1;
                                } else {
                                    // Jika tahun ini belum ada kode inventaris sama sekali, mulai dari 1
                                    $nextNumber = 1;
                                }
                                
                                // 3. Satukan kembali menjadi 5 digit berurut (contoh: LTKJ-2026-00001)
                                $newCode = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                                
                                // 4. Ketikkan otomatis hasilnya ke kolom inputan
                                $set('nomor_seri_atau_qr', $newCode);
                            })
                    ),

                Select::make('status')
                    ->label('Status Ketersediaan')
                    ->options([
                        'tersedia' => 'Tersedia (Siap Pakai)',
                        'dipinjam' => 'Sedang Dipinjam',
                    ])
                    ->required()
                    ->default('tersedia'),

                Select::make('kondisi')
                    ->label('Kondisi Fisik Alat')
                    ->options([
                        'baik' => '🟢 Baik (Bisa Digunakan)',
                        'rusak' => '🟡 Rusak Ringan (Butuh Perbaikan)',
                        'rusak_total' => '🔴 Rusak Total (Mati/Afkir)',
                    ])
                    ->required()
                    ->default('baik')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (in_array($state, ['rusak', 'rusak_total'])) {
                            $set('lokasi_select', 'gudang');
                            $set('lokasi', 'gudang');
                        }
                    }),

                Select::make('lokasi_select')
                    ->label('Lokasi Penempatan')
                    ->options([
                        'gudang' => 'Gudang (Penyimpanan/Rusak)',
                        'ruang_kantor' => 'Ruang Kantor',
                        'lab_tjkt' => 'Laboratorium TJKT',
                        'lab_kkpi' => 'Laboratorium KKPI',
                        'lab_fo' => 'Laboratorium Fiber Optic',
                        'lainnya' => 'Lainnya / Tulis Lokasi Kustom Baru...',
                    ])
                    ->required()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, $set, $record) {
                        if ($record) {
                            $standardLocations = ['gudang', 'ruang_kantor', 'lab_tjkt', 'lab_kkpi', 'lab_fo'];
                            if (in_array($record->lokasi, $standardLocations)) {
                                $set('lokasi_select', $record->lokasi);
                            } else {
                                $set('lokasi_select', 'lainnya');
                            }
                        } else {
                            $set('lokasi_select', 'gudang');
                        }
                    })
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state !== 'lainnya') {
                            $set('lokasi', $state);
                        } else {
                            $set('lokasi', '');
                        }
                    }),

                TextInput::make('lokasi')
                    ->label('Tulis Nama Ruangan / Lokasi Baru')
                    ->placeholder('Contoh: Ruang Kepala Sekolah, Lab Multimedia, Aula, dll.')
                    ->required()
                    ->visible(fn ($get) => $get('lokasi_select') === 'lainnya')
                    ->live(),
            ]);
    }
}