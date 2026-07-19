<?php

namespace App\Filament\Resources\AssetItems\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Asset;
use App\Models\AssetItem;

// 🟢 FIX: Suffix action sekarang resmi ikut melebur ke rumpun tunggal ini
use Filament\Actions\Action;
use Filament\Forms\Set;

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
                    ->required(),

                TextInput::make('nomor_seri_atau_qr')
                    ->label('Nomor Seri / Kode QR')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ketik SN pabrik atau klik tombol kanan untuk buat otomatis')
                    ->suffixAction(
                        Action::make('generateUniqueCode')
                            ->icon('heroicon-m-arrow-path')
                            ->color('success')
                            ->tooltip('Generate Kode Unik Lab')
                            ->action(function ($set) {
                                $prefix = 'LTKJ-' . date('Y') . '-'; 
                                
                                $lastItem = AssetItem::where('nomor_seri_atau_qr', 'LIKE', $prefix . '%')
                                    ->orderBy('nomor_seri_atau_qr', 'desc')
                                    ->first();
                                    
                                if ($lastItem) {
                                    $lastNumber = (int) substr($lastItem->nomor_seri_atau_qr, -5);
                                    $nextNumber = $lastNumber + 1;
                                } else {
                                    $nextNumber = 1;
                                }
                                
                                $newCode = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
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
                            $set('lokasi', 'gudang');
                        }
                    }),

                // Input utama untuk kolom 'lokasi' di database
                Select::make('lokasi')
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
                    ->afterStateHydrated(function ($state, $set, $record) {
                        if ($record) {
                            $standardLocations = ['gudang', 'ruang_kantor', 'lab_tjkt', 'lab_kkpi', 'lab_fo'];
                            if (!in_array($record->lokasi, $standardLocations)) {
                                $set('lokasi', 'lainnya');
                                $set('lokasi_kustom', $record->lokasi);
                            }
                        } else {
                            $set('lokasi', 'gudang');
                        }
                    })
                    ->dehydrateStateUsing(fn ($state, $get) => $state === 'lainnya' ? $get('lokasi_kustom') : $state),

                // Input kustom manual (hanya muncul saat select bernilai 'lainnya')
                TextInput::make('lokasi_kustom')
                    ->label('Tulis Nama Ruangan / Lokasi Baru')
                    ->placeholder('Contoh: Ruang Kepala Sekolah, Lab Multimedia, Aula, dll.')
                    ->required(fn ($get) => $get('lokasi') === 'lainnya')
                    ->visible(fn ($get) => $get('lokasi') === 'lainnya')
                    ->dehydrated(false),
            ]);
    }
}