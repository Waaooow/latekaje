<?php

namespace App\Filament\Resources\AssetItems\Schemas;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\Location;
use App\Services\AssetItemCode;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AssetItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('asset_id')
                ->label('Aset')
                ->relationship('asset', 'nama_alat')
                ->searchable(['kode_aset', 'nama_alat'])
                ->getOptionLabelFromRecordUsing(fn (Asset $record): string => "[{$record->kode_aset}] {$record->nama_alat}")
                ->preload()
                ->required(),

            TextInput::make('nomor_seri_atau_qr')
                ->label('Nomor Seri / QR')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->suffixAction(
                    Action::make('generate')
                        ->icon('heroicon-m-arrow-path')
                        ->action(function (Set $set): void {
                            $set('nomor_seri_atau_qr', AssetItemCode::next());
                        })
                ),

            TextInput::make('jumlah')
                ->label('Jumlah Unit Dibuat')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->maxValue(500)
                ->required()
                ->helperText('Isi > 1 untuk membuat banyak unit sekaligus. Kode sisanya digenerate otomatis.')
                ->hiddenOn('edit'),

            Textarea::make('sn_manual')
                ->label('SN Manual (opsional)')
                ->rows(3)
                ->placeholder("Satu SN per baris, misal:\nSN-PC-001\nSN-PC-002")
                ->helperText('Kosongkan bila unit tidak punya SN — sistem generate kode otomatis.')
                ->hiddenOn('edit'),

            Select::make('status')
                ->options([
                    'tersedia' => 'Tersedia',
                    'dipinjam' => 'Dipinjam',
                ])
                ->default('tersedia')
                ->required(),

            Select::make('kondisi')
                ->options([
                    'baik' => 'Baik',
                    'rusak' => 'Rusak',
                    'rusak_total' => 'Rusak Total',
                ])
                ->default('baik')
                ->required()
                ->live()
                ->afterStateUpdated(function (mixed $state, Set $set): void {
                    if (in_array($state, ['rusak', 'rusak_total'], true)) {
                        $gudangId = Location::where('key', 'gudang')->value('id');

                        if ($gudangId) {
                            $set('location_id', $gudangId);
                        }
                    }
                }),

            Select::make('location_id')
                ->label('Lokasi Penempatan')
                ->relationship('location', 'label')
                ->required()
                ->preload()
                ->searchable()
                ->afterStateHydrated(function (Select $component, mixed $state, Set $set, mixed $record): void {
                    if (! $record && blank($state)) {
                        $gudangId = Location::where('key', 'gudang')->value('id');

                        if ($gudangId) {
                            $set('location_id', $gudangId);
                            $component->state($gudangId);
                        }
                    }
                }),
        ]);
    }
}
