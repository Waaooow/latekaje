<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use Filament\Widgets\ChartWidget;

class InventoryConditionChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Kondisi Inventaris';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $baik = AssetItem::query()->where('kondisi', 'baik')->count();
        $rusak = AssetItem::query()->where('kondisi', 'rusak')->count();
        $rusakTotal = AssetItem::query()->where('kondisi', 'rusak_total')->count();
        $total = max(1, $baik + $rusak + $rusakTotal);

        $pct = fn (int $v): string => number_format($v / $total * 100, 1).'%';

        return [
            'labels' => [
                "Baik ({$baik}, {$pct($baik)})",
                "Rusak ({$rusak}, {$pct($rusak)})",
                "Rusak Total ({$rusakTotal}, {$pct($rusakTotal)})",
            ],
            'datasets' => [
                [
                    'data' => [$baik, $rusak, $rusakTotal],
                    'backgroundColor' => ['#22c55e', '#f59e0b', '#ef4444'],
                ],
            ],
        ];
    }
}
