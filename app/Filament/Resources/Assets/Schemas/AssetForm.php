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
                ->label(__('assets.code_label'))
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder(__('assets.code_placeholder'))
                ->maxLength(255),

            TextInput::make('nama_alat')
                ->label(__('assets.name_label'))
                ->required()
                ->maxLength(255),

            TextInput::make('jenis')
                ->label(__('assets.type_label'))
                ->required()
                ->maxLength(255),

            TextInput::make('spesifikasi')
                ->label(__('assets.spec_label'))
                ->required()
                ->maxLength(255),

            Select::make('kegunaan')
                ->label(__('assets.usage_label'))
                ->options([
                    'praktik' => __('assets.usage_option_practice'),
                    'non_praktik' => __('assets.usage_option_non_practice'),
                    'Praktik Siswa' => __('assets.usage_option_student'),
                    'Praktik Guru' => __('assets.usage_option_teacher'),
                    'Ujian/CBT' => __('assets.usage_option_exam'),
                    'lainnya' => __('assets.usage_option_other'),
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
                ->label(__('assets.usage_custom_label'))
                ->placeholder(__('assets.usage_custom_placeholder'))
                ->maxLength(255)
                ->visible(fn (Get $get): bool => $get('kegunaan') === 'lainnya')
                ->dehydrated(false)
                ->live(),
        ]);
    }
}
