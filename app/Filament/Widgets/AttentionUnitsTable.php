<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AttentionUnitsTable extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 1;

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return __('dashboard.attention_heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetItem::query()
                    ->with(['asset', 'location'])
                    ->whereIn('kondisi', ['rusak', 'rusak_total'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('nomor_seri_atau_qr')
                    ->label(__('dashboard.col_qr'))
                    ->searchable(),

                TextColumn::make('asset.nama_alat')
                    ->label(__('dashboard.col_tool'))
                    ->searchable(),

                TextColumn::make('kondisi')
                    ->label(__('dashboard.col_condition'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'rusak' ? __('dashboard.cond_damaged') : __('dashboard.cond_total_loss'))
                    ->color(fn (string $state): string => $state === 'rusak' ? 'warning' : 'danger'),

                TextColumn::make('location.label')
                    ->label(__('dashboard.col_location'))
                    ->badge()
                    ->color('gray'),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
