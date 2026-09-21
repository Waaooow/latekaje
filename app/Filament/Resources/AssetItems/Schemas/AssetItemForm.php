<?php

namespace App\Filament\Resources\AssetItems\Schemas;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\Location;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
                            $prefix = 'LTKJ-' . now()->format('Y') . '-';

                            $max = AssetItem::where('nomor_seri_atau_qr', 'like', $prefix . '%')
                                ->orderBy('nomor_seri_atau_qr', 'desc')
                                ->value('nomor_seri_atau_qr');

                            $next = 1;

                            if (is_string($max) && str_starts_with($max, $prefix)) {
                                $next = ((int) substr($max, strlen($prefix))) + 1;
                            }

                            $set('nomor_seri_atau_qr', $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT));
                        })
                ),

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
