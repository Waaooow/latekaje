<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverview extends BaseWidget
{
    // 🟢 Urutan 1: Berada di baris paling atas
    protected static ?int $sort = 1;

    // 🟢 Rentangkan penuh ke samping agar menjadi fondasi utama
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Unit Barang', AssetItem::count())
                ->description('Semua unit fisik terdaftar')
                ->descriptionIcon('heroicon-m-squares-plus')
                ->color('info'),

            Stat::make('Unit Siap Pakai (Ready)', AssetItem::where('status', 'tersedia')->where('kondisi', 'baik')->count())
                ->description('Kondisi baik & tersedia')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Sedang Dipinjam', AssetItem::where('status', 'dipinjam')->count())
                ->description('Unit di tangan siswa/guru')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Karantina (Rusak)', AssetItem::whereIn('kondisi', ['rusak', 'rusak_total'])->count())
                ->description('Perlu perbaikan / afkir')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}