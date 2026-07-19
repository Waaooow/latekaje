<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_aset')
                    ->label('Kode Aset')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: TJKT-NET-001'),

                TextInput::make('nama_alat')
                    ->label('Nama Alat')
                    ->required()
                    ->placeholder('Contoh: ROUTER MIKROTIK'),

                TextInput::make('jenis')
                    ->label('Jenis Alat')
                    ->required()
                    ->placeholder('Contoh: Networking, Aksesoris, PC'),

                TextInput::make('spesifikasi')
                    ->label('Spesifikasi Alat')
                    ->required()
                    ->placeholder('Contoh: RB450G, RAM 256MB, 5 Port'),

                // 🟢 REVISI TOTAL: Menggunakan pola Hybrid Select agar membaca data lama & baru secara aman
                Select::make('kegunaan')
                    ->label('Kegunaan Alat')
                    ->options([
                        'praktik' => 'Alat Praktik (Bisa Dipinjam Siswa)',
                        'non_praktik' => 'Non-Praktik / Aset Jurusan',
                        'Praktik Siswa' => 'Praktik Siswa',
                        'Praktik Guru' => 'Praktik Guru',
                        'Ujian/CBT' => 'Ujian / CBT',
                        'lainnya' => 'Lainnya / Tulis Kustom Baru...',
                    ])
                    ->required()
                    ->live()
                    // Mengamankan data lama dari database saat form dimuat
                    ->afterStateHydrated(function ($state, $set, $record) {
                        if ($record) {
                            $standardOptions = ['praktik', 'non_praktik', 'Praktik Siswa', 'Praktik Guru', 'Ujian/CBT'];
                            // Jika data di DB berupa teks kustom lain (misal: "Penyambungan FO"), lempar ke input kustom
                            if (!in_array($record->kegunaan, $standardOptions)) {
                                $set('kegunaan', 'lainnya');
                                $set('kegunaan_kustom', $record->kegunaan);
                            }
                        }
                    })
                    // Cegat data sebelum disimpan ke database
                    ->dehydrateStateUsing(fn ($state, $get) => $state === 'lainnya' ? $get('kegunaan_kustom') : $state),

                // 🟢 INPUT BANTUAN: Muncul otomatis jika memilih opsi 'lainnya'
                TextInput::make('kegunaan_kustom')
                    ->label('Tulis Kegunaan Kustom Baru')
                    ->placeholder('Contoh: Penyambungan FO, Uji Kompetensi, dll.')
                    ->required(fn ($get) => $get('kegunaan') === 'lainnya')
                    ->visible(fn ($get) => $get('kegunaan') === 'lainnya')
                    ->dehydrated(false),

                TextInput::make('stok')
                    ->label('Jumlah Total Kuota Stok')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->placeholder('Masukkan total kuota alat'),
            ]);
    }
}