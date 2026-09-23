<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use App\Models\Location;
use Filament\Widgets\ChartWidget;

class InventoryLocationChart extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): ?string
    {
        return __("dashboard.location_heading");
    }

    protected function getType(): string
    {
        return "doughnut";
    }

    protected function getData(): array
    {
        $standardColors = [
            "gudang" => "#f43f5e",
            "ruang_kantor" => "#3b82f6",
            "lab_tjkt" => "#10b981",
            "lab_kkpi" => "#f59e0b",
            "lab_fo" => "#8b5cf6",
        ];

        $fallbackPalette = ["#ec4899", "#14b8a6", "#6366f1", "#a855f7", "#6b7280", "#f97316"];

        $grouped = AssetItem::query()
            ->selectRaw("location_id, COUNT(*) as aggregate")
            ->groupBy("location_id")
            ->pluck("aggregate", "location_id");

        $locations = Location::query()
            ->whereIn("id", $grouped->keys()->all())
            ->get(["id", "key", "label"])
            ->keyBy("id");

        $chartLabels = [];
        $chartData = [];
        $chartColors = [];
        $i = 0;

        foreach ($grouped as $locationId => $count) {
            $loc = $locations->get($locationId);
            $label = $loc?->label ?? "Tanpa lokasi";
            $key = $loc?->key ?? "";
            $chartLabels[] = "{$label} ({$count})";
            $chartData[] = (int) $count;
            $chartColors[] = $standardColors[$key] ?? $fallbackPalette[$i % count($fallbackPalette)];
            $i++;
        }

        if ($chartData === []) {
            $chartLabels = ["Belum ada data"];
            $chartData = [0];
            $chartColors = ["#e5e7eb"];
        }

        return [
            "labels" => $chartLabels,
            "datasets" => [
                [
                    "data" => $chartData,
                    "backgroundColor" => $chartColors,
                ],
            ],
        ];
    }
}
