<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use App\Models\Location;
use Filament\Widgets\ChartWidget;

class InventoryLocationChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Unit per Lokasi';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $grouped = AssetItem::query()
            ->selectRaw('location_id, COUNT(*) as aggregate')
            ->groupBy('location_id')
            ->pluck('aggregate', 'location_id');

        $labels = Location::query()
            ->whereIn('id', $grouped->keys()->all())
            ->pluck('label', 'id');

        $chartLabels = [];
        $chartData = [];

        foreach ($grouped as $locationId => $count) {
            $chartLabels[] = ($labels[$locationId] ?? 'Tanpa lokasi')." ({$count})";
            $chartData[] = (int) $count;
        }

        if ($chartData === []) {
            $chartLabels = ['Belum ada data'];
            $chartData = [0];
        }

        return [
            'labels' => $chartLabels,
            'datasets' => [
                [
                    'data' => $chartData,
                ],
            ],
        ];
    }
}
