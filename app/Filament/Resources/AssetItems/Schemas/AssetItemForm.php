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
                ->label(__('units.asset_label'))
                ->relationship('asset', 'nama_alat')
                ->searchable(['kode_aset', 'nama_alat'])
                ->getOptionLabelFromRecordUsing(fn (Asset $record): string => "[{$record->kode_aset}] {$record->nama_alat}")
                ->preload()
                ->required(),

            TextInput::make('nomor_seri_atau_qr')
                ->label(__('units.serial_label'))
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->suffixAction(
                    Action::make('generate')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (Set $set): void {
                            $set('nomor_seri_atau_qr', AssetItemCode::next());
                        })
                ),

            TextInput::make('jumlah')
                ->label(__('units.qty_created_label'))
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->maxValue(500)
                ->required()
                ->helperText(__('units.qty_created_helper'))
                ->hiddenOn('edit'),

            Textarea::make('sn_manual')
                ->label(__('units.sn_manual_label'))
                ->rows(3)
                ->placeholder(__('units.sn_manual_placeholder'))
                ->helperText(__('units.sn_manual_helper'))
                ->hiddenOn('edit'),

            Select::make('status')
                ->options([
                    'tersedia' => __('units.status_available'),
                    'dipinjam' => __('units.status_borrowed'),
                ])
                ->default('tersedia')
                ->required(),

            Select::make('kondisi')
                ->options([
                    'baik' => __('units.condition_good'),
                    'rusak' => __('units.condition_damaged'),
                    'rusak_total' => __('units.condition_total_loss'),
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
                ->label(__('units.placement_label'))
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
