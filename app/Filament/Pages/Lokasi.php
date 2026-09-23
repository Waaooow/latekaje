<?php

namespace App\Filament\Pages;

use App\Models\AssetItem;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class Lokasi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    public static function getNavigationLabel(): string
    {
        return __('common.nav_location_data');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('common.nav_location_data');
    }

    protected static ?string $slug = 'data-per-lokasi';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.lokasi';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetItem::query()->with(['location', 'asset'])
            )
            ->columns([
                TextColumn::make('location.label')
                    ->label(__('units.location_label'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('asset.nama_alat')
                    ->label(__('units.name_label'))
                    ->description(fn ($record): ?string => trim(($record->asset?->kode_aset ?? '').' · '.($record->asset?->spesifikasi ?? ''), ' ·'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nomor_seri_atau_qr')
                    ->label(__('loans.col_qr'))
                    ->badge()
                    ->color('warning')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('kondisi')
                    ->label(__('units.condition_label'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('status')
                    ->label(__('common.status'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'tersedia' ? 'success' : 'warning'),
            ])
            ->defaultSort('location.label', 'asc')
            ->paginated(false);
    }
}
