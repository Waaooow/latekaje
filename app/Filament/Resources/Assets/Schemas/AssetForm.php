<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('kode_aset')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('TJKT-NET-001')
                ->maxLength(255),

            TextInput::make('nama_alat')
                ->required()
                ->maxLength(255),

            TextInput::make('jenis')
                ->required()
                ->maxLength(255),

            TextInput::make('spesifikasi')
                ->required()
                ->maxLength(255),

            Select::make('kegunaan')
                ->options([
                    'praktik' => 'Praktik',
                    'non_praktik' => 'Non Praktik',
                    'Praktik Siswa' => 'Praktik Siswa',
                    'Praktik Guru' => 'Praktik Guru',
                    'Ujian/CBT' => 'Ujian/CBT',
                    'lainnya' => 'Lainnya',
                ])
                ->required()
                ->live()
                ->afterStateHydrated(function (Select $component, mixed $state, Set $set): void {
                    $standard = [
                        'praktik',
                        'non_praktik',
                        'Praktik Siswa',
                        'Praktik Guru',
                        'Ujian/CBT',
                        'lainnya',
                    ];

                    if (filled($state) && ! in_array($state, $standard, true)) {
                        $set('kegunaan_kustom', $state);
                        $component->state('lainnya');
                    }
                })
                ->dehydrateStateUsing(function (mixed $state, Get $get): mixed {
                    if ($state === 'lainnya') {
                        return $get('kegunaan_kustom') ?: $state;
                    }

                    return $state;
                }),

            TextInput::make('kegunaan_kustom')
                ->label('Kegunaan Lainnya')
                ->placeholder('Tulis kegunaan kustom…')
                ->maxLength(255)
                ->visible(fn (Get $get): bool => $get('kegunaan') === 'lainnya')
                ->dehydrated(false)
                ->live(),
        ]);
    }
}
