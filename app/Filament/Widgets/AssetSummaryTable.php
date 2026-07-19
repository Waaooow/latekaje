<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;

class AssetSummaryTable extends BaseWidget
{
    // 🟢 Urutan ke-4 (Berada di paling bawah setelah 2 grafik pie)
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '📊 Papan Visual Kontrol Kuota & Distribusi Alat';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Asset::query()->withCount([
                    'assetItems', 
                    'assetItems as ready_unit' => fn ($query) => $query->where('status', 'tersedia')->where('kondisi', 'baik'),
                    'assetItems as dipinjam_unit' => fn ($query) => $query->where('status', 'dipinjam'),
                    'assetItems as rusak_unit' => fn ($query) => $query->whereIn('kondisi', ['rusak', 'rusak_total']),
                ])
            )
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
            ])
            ->columns([
                Stack::make([
                    // 1. HEADER KARTU
                    Split::make([
                        TextColumn::make('kode_aset')
                            ->badge()
                            ->color('info')
                            ->grow(false),
                        
                        TextColumn::make('nama_alat')
                            ->weight('bold')
                            ->size('lg')
                            ->alignEnd(),
                    ]),

                    // 2. VISUAL PROGRESS BAR KUOTA
                    TextColumn::make('quota_progress')
                        ->state(fn ($record) => $record)
                        ->formatStateUsing(function ($record) {
                            $registered = $record->asset_items_count;
                            $maxStock = $record->stok ?? 0;
                            $pct = $maxStock > 0 ? min(100, ($registered / $maxStock) * 100) : 0;
                            $barColor = $pct >= 100 ? 'bg-amber-500' : 'bg-blue-600';
                            
                            return new HtmlString("
                                <div class='space-y-1 mt-3'>
                                    <div class='flex justify-between text-xs font-semibold text-gray-500 dark:text-gray-400'>
                                        <span>📦 Keterisian Kuota Katalog</span>
                                        <span class='text-gray-800 dark:text-gray-200'>{$registered} / {$maxStock} Unit Max</span>
                                    </div>
                                    <div class='w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden shadow-inner'>
                                        <div class='{$barColor} h-3 rounded-full transition-all duration-500' style='width: {$pct}%'></div>
                                    </div>
                                </div>
                            ");
                        }),

                    // 3. VISUAL MICRO INDICATORS SEBARAN ALAT
                    TextColumn::make('distribution_health')
                        ->state(fn ($record) => $record)
                        ->formatStateUsing(function ($record) {
                            $total = $record->asset_items_count;
                            
                            if ($total == 0) {
                                return new HtmlString("<p class='text-xs text-gray-400 italic mt-4 text-center border-t border-dashed border-gray-200 pt-3'>Belum ada unit fisik terinput</p>");
                            }
                            
                            $ready = $record->ready_unit;
                            $dipinjam = $record->dipinjam_unit;
                            $rusak = $record->rusak_unit;
                            
                            $readyPct = ($ready / $total) * 100;
                            $dipinjamPct = ($dipinjam / $total) * 100;
                            $rusakPct = ($rusak / $total) * 100;
                            
                            return new HtmlString("
                                <div class='mt-4 space-y-2.5 border-t border-gray-100 dark:border-gray-800 pt-3.5'>
                                    <p class='text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500'>Sebaran Distribusi Real-Time:</p>
                                    
                                    <div class='space-y-0.5'>
                                        <div class='flex justify-between text-xs'>
                                            <span class='text-emerald-600 dark:text-emerald-400 font-medium'>🟢 Siap Pakai</span>
                                            <span class='font-bold text-gray-700 dark:text-gray-300'>{$ready} <span class='text-[10px] font-normal text-gray-400'>U</span></span>
                                        </div>
                                        <div class='w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5'>
                                            <div class='bg-emerald-500 h-1.5 rounded-full' style='width: {$readyPct}%'></div>
                                        </div>
                                    </div>

                                    <div class='space-y-0.5'>
                                        <div class='flex justify-between text-xs'>
                                            <span class='text-amber-600 dark:text-amber-400 font-medium'>🟡 Dipinjam</span>
                                            <span class='font-bold text-gray-700 dark:text-gray-300'>{$dipinjam} <span class='text-[10px] font-normal text-gray-400'>U</span></span>
                                        </div>
                                        <div class='w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5'>
                                            <div class='bg-amber-500 h-1.5 rounded-full' style='width: {$dipinjamPct}%'></div>
                                        </div>
                                    </div>

                                    <div class='space-y-0.5'>
                                        <div class='flex justify-between text-xs'>
                                            <span class='text-rose-600 dark:text-rose-400 font-medium'>🔴 Rusak (Gudang)</span>
                                            <span class='font-bold text-gray-700 dark:text-gray-300'>{$rusak} <span class='text-[10px] font-normal text-gray-400'>U</span></span>
                                        </div>
                                        <div class='w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5'>
                                            <div class='bg-rose-500 h-1.5 rounded-full' style='width: {$rusakPct}%'></div>
                                        </div>
                                    </div>
                                </div>
                            ");
                        }),
                ])
                ->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 p-5 hover:shadow-md transition-all duration-200',
                ]),
            ])
            ->paginated([6, 9, 12])
            ->defaultPaginationPageOption(6);
    }
}