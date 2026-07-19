<?php

namespace App\Filament\Widgets;

use App\Models\AssetItem;
use Filament\Widgets\ChartWidget;

class InventoryConditionChart extends ChartWidget
{
    protected ?string $heading = '📊 Analisis Kondisi Fisik Alat (Global)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $baik = AssetItem::where('kondisi', 'baik')->count();
        $rusakRingan = AssetItem::where('kondisi', 'rusak')->count();
        $rusakTotal = AssetItem::where('kondisi', 'rusak_total')->count();
        
        // Total keseluruhan unit fisik untuk menghitung persentase pecahan
        $total = $baik + $rusakRingan + $rusakTotal;

        // Hitung persentase masing-masing (amankan dari pembagian dengan angka 0)
        $baikPct = $total > 0 ? round(($baik / $total) * 100) : 0;
        $rusakRinganPct = $total > 0 ? round(($rusakRingan / $total) * 100) : 0;
        $rusakTotalPct = $total > 0 ? round(($rusakTotal / $total) * 100) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Unit',
                    'data' => [$baik, $rusakRingan, $rusakTotal],
                    'backgroundColor' => ['#22c55e', '#eab308', '#ef4444'],
                ],
            ],
            // 🟢 SEKARANG LANGSUNG TERTULIS ANGKA & PERSENTASENYA
            'labels' => [
                "🟢 Baik: {$baik} Unit ({$baikPct}%)",
                "🟡 Rusak Ringan: {$rusakRingan} Unit ({$rusakRinganPct}%)",
                "🔴 Rusak Total: {$rusakTotal} Unit ({$rusakTotalPct}%)"
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}