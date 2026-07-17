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

                // 🟢 BARU: Input untuk Jenis Perangkat
                TextInput::make('jenis')
                    ->label('Jenis Alat')
                    ->required()
                    ->placeholder('Contoh: Networking, Aksesoris, PC'),

                // 🟢 REVISI: Sekarang menggunakan TextInput biasa
                TextInput::make('spesifikasi')
                    ->label('Spesifikasi Alat')
                    ->required()
                    ->placeholder('Contoh: RB450G, RAM 256MB, 5 Port'),

                // 🟢 REVISI: Kolom kegunaan dengan value standar database (_)
                Select::make('kegunaan')
                    ->label('Kegunaan Alat')
                    ->options([
                        'praktik' => 'Alat Praktik (Bisa Dipinjam Siswa)',
                        'non_praktik' => 'Non-Praktik / Aset Jurusan',
                    ])
                    ->required(),

                TextInput::make('stok')
                    ->label('Jumlah Total Kuota Stok')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->placeholder('Masukkan total kuota alat'),
            ]);
    }
}
