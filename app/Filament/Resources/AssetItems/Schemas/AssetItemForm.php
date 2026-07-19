<?php

namespace App\Filament\Resources\AssetItems\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Asset;
use App\Models\AssetItem;

class AssetItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('asset_id')
                    ->label('Pilih Tipe Alat (Katalog)')
                    ->relationship('asset', 'nama_alat')
                    ->searchable(['kode_aset', 'nama_alat'])
                    ->getOptionLabelFromRecordUsing(fn($record) => "[{$record->kode_aset}] {$record->nama_alat}")
                    ->required()
                    ->rules(fn(Select $component): array => [
                        function (string $attribute, $value, \Closure $fail) use ($component) {
                            $recordId = null;
                            $path = request()->getPathInfo(); 
                            if (preg_match('/\/(\d+)\/edit/', $path, $matches)) {
                                $recordId = $matches[1];
                            }
                            if (!$recordId && isset($component->getLivewire()->record)) {
                                $recordId = $component->getLivewire()->record->id;
                            }

                            if ($recordId) {
                                $originalItem = AssetItem::find($recordId);
                                if ($originalItem && $originalItem->asset_id == $value) {
                                    return; 
                                }
                            }

                            $asset = Asset::find($value);
                            if (!$asset) return;

                            $query = AssetItem::where('asset_id', $value);
                            if ($recordId) {
                                $query->where('id', '!=', $recordId);
                            }

                            $jumlahTerdaftar = $query->count();

                            if ($jumlahTerdaftar >= $asset->stok) {
                                $fail("❌ Gagal Simpan! Jumlah unit fisik untuk '{$asset->nama_alat}' sudah mencapai batas maksimal kuota ({$asset->stok} unit). Silakan naikkan jumlah stok terlebih dahulu di menu 'Total Aset' jika ingin menambah unit baru.");
                            }
                        }
                    ]),

                TextInput::make('nomor_seri_atau_qr')
                    ->label('Nomor Seri / Kode QR')
                    ->required()
                    ->unique(ignoreRecord: true),

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
                        // Jika alat rusak, otomatis arahkan dropdown ke gudang & isi teks lokasi dengan gudang
                        if (in_array($state, ['rusak', 'rusak_total'])) {
                            $set('lokasi_select', 'gudang');
                            $set('lokasi', 'gudang');
                        }
                    }),

                // 🟢 DROPDOWN UTAMA (Hanya untuk kontrol UI, tidak disimpan ke DB)
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
                    ->dehydrated(false) // Mencegah field bayangan ini masuk ke database
                    ->afterStateHydrated(function ($state, $set, $record) {
                        // Sinkronisasi saat buka halaman EDIT data lama
                        if ($record) {
                            $standardLocations = ['gudang', 'ruang_kantor', 'lab_tjkt', 'lab_kkpi', 'lab_fo'];
                            if (in_array($record->lokasi, $standardLocations)) {
                                $set('lokasi_select', $record->lokasi);
                            } else {
                                $set('lokasi_select', 'lainnya');
                            }
                        } else {
                            $set('lokasi_select', 'gudang'); // Default saat tambah baru
                        }
                    })
                    ->afterStateUpdated(function ($state, $set) {
                        // Sinkronisasi saat user mengubah pilihan dropdown
                        if ($state !== 'lainnya') {
                            $set('lokasi', $state);
                        } else {
                            $set('lokasi', ''); // Kosongkan biar user bisa mengetik manual
                        }
                    }),

                // 🟢 INPUT TEKS KUSTOM (Ini yang bertugas menyimpan string asli ke database)
                TextInput::make('lokasi')
                    ->label('Tulis Nama Ruangan / Lokasi Baru')
                    ->placeholder('Contoh: Ruang Kepala Sekolah, Lab Multimedia, Aula, dll.')
                    ->required()
                    // Hanya menampakkan diri jika opsi dropdown bernilai 'lainnya'
                    ->visible(fn ($get) => $get('lokasi_select') === 'lainnya')
                    ->live(),
            ]);
    }
}