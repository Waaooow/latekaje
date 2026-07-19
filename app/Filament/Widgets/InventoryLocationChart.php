<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InventoryLocationChart extends ChartWidget
{
    protected ?string $heading = '🏫 Sebaran Distribusi Unit per Ruangan';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $data = AssetItem::select('lokasi', DB::raw('count(*) as total'))
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->groupBy('lokasi')
            ->pluck('total', 'lokasi')
            ->toArray();

        // Hitung total seluruh ruangan untuk porsi persentase
        $totalAll = array_sum($data);

        $labels = [];
        $values = [];
        $colors = [];

        $ruanganMap = [
            'ruang_kantor' => ['label' => 'Ruang Kantor', 'color' => '#3b82f6'],
            'lab_tjkt'     => ['label' => 'Lab TJKT', 'color' => '#10b981'],
            'lab_kkpi'     => ['label' => 'Lab KKPI', 'color' => '#f59e0b'],
            'lab_fo'       => ['label' => 'Lab FO', 'color' => '#8b5cf6'],
            'gudang'       => ['label' => 'Gudang (Karantina)', 'color' => '#f43f5e'],
            'lainnya'      => ['label' => 'Lainnya', 'color' => '#6b7280'],
        ];

        foreach ($ruanganMap as $key => $info) {
            if (isset($data[$key]) && $data[$key] > 0) {
                $count = $data[$key];
                $pct = $totalAll > 0 ? round(($count / $totalAll) * 100) : 0;
                
                // 🟢 MENYUNTIKKAN DATA INSTAN KE LABEL LEGENDA
                $labels[] = "{$info['label']}: {$count} U ({$pct}%)";
                $values[] = $count;
                $colors[] = $info['color'];
            }
        }

        if (empty($values)) {
            $labels = ['Belum Ada Data'];
            $values = [0];
            $colors = ['#e5e7eb'];
        }

        return [
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}