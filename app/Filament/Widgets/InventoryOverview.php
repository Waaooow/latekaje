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
            Stat::make('Total Unit', number_format($total))
                ->description('Seluruh unit terdaftar')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Siap Pakai', number_format($siapPakai))
                ->description('Tersedia + kondisi baik')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Dipinjam', number_format($dipinjam))
                ->description('Sedang dipinjam siswa')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Karantina', number_format($karantina))
                ->description('Rusak + rusak total')
                ->color('danger')
                ->icon('heroicon-o-wrench'),
        ];
    }
}
