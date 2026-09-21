<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class LoanActivityChart extends ChartWidget
{
    protected ?string $heading = 'Aktivitas Peminjaman (6 Bulan Terakhir)';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $key = $d->format('Y-m');
            $months[$key] = 0;
            $labels[] = $d->locale('id')->translatedFormat('M Y');
        }

        $rows = Loan::query()
            ->selectRaw("DATE_FORMAT(tanggal_pinjam, '%Y-%m') AS ym, COUNT(*) AS c")
            ->where('tanggal_pinjam', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')
            ->pluck('c', 'ym')
            ->all();

        foreach ($months as $key => $v) {
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Peminjaman',
                    'data' => $values,
                    'backgroundColor' => '#f59e0b',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
