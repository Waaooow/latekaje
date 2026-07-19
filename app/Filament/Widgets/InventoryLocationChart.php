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
        // Tarik semua variasi lokasi yang ada di database secara realtime
        $data = AssetItem::select('lokasi', DB::raw('count(*) as total'))
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->groupBy('lokasi')
            ->pluck('total', 'lokasi')
            ->toArray();

        $totalAll = array_sum($data);

        $labels = [];
        $values = [];
        $colors = [];

        // Standar nama dan kode warna untuk lokasi bawaan sistem
        $standardMap = [
            'ruang_kantor' => ['label' => 'Ruang Kantor', 'color' => '#3b82f6'],
            'lab_tjkt'     => ['label' => 'Lab TJKT', 'color' => '#10b981'],
            'lab_kkpi'     => ['label' => 'Lab KKPI', 'color' => '#f59e0b'],
            'lab_fo'       => ['label' => 'Lab FO', 'color' => '#8b5cf6'],
            'gudang'       => ['label' => 'Gudang (Karantina)', 'color' => '#f43f5e'],
        ];

        // Palet warna otomatis untuk menampung lokasi-lokasi kustom baru
        $customColors = ['#ec4899', '#14b8a6', '#f97316', '#6366f1', '#a855f7', '#6b7280'];
        $customColorIdx = 0;

        foreach ($data as $lokasiKey => $count) {
            if ($count <= 0) continue;

            $pct = $totalAll > 0 ? round(($count / $totalAll) * 100) : 0;

            // Jika lokasi merupakan opsi standar
            if (array_key_exists($lokasiKey, $standardMap)) {
                $labels[] = "{$standardMap[$lokasiKey]['label']}: {$count} U ({$pct}%)";
                $values[] = $count;
                $colors[] = $standardMap[$lokasiKey]['color'];
            } else {
                // 🟢 AUTO-SYNC: Jika teks kustom, ubah format tulisan agar rapi dan beri warna dinamis
                $friendlyName = ucwords(str_replace('_', ' ', $lokasiKey));
                $labels[] = "🏫 {$friendlyName}: {$count} U ({$pct}%)";
                $values[] = $count;
                
                // Ambil warna dari palet cadangan secara bergiliran
                $colors[] = $customColors[$customColorIdx % count($customColors)];
                $customColorIdx++;
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