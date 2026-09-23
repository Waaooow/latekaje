<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $total = AssetItem::query()->count();

        $siapPakai = AssetItem::query()
            ->where('status', 'tersedia')
            ->where('kondisi', 'baik')
            ->count();

        $dipinjam = AssetItem::query()
            ->where('status', 'dipinjam')
            ->count();

        $karantina = AssetItem::query()
            ->whereIn('kondisi', ['rusak', 'rusak_total'])
            ->count();

        return [
            Stat::make(__('dashboard.total_units'), number_format($total))
                ->description(__('dashboard.total_units_desc'))
                ->icon('heroicon-o-archive-box'),

            Stat::make(__('dashboard.ready'), number_format($siapPakai))
                ->description(__('dashboard.ready_desc'))
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make(__('dashboard.borrowed'), number_format($dipinjam))
                ->description(__('dashboard.borrowed_desc'))
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make(__('dashboard.quarantine'), number_format($karantina))
                ->description(__('dashboard.quarantine_desc'))
                ->color('danger')
                ->icon('heroicon-o-wrench'),
        ];
    }
}
