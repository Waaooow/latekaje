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
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = "full";

    protected static ?string $heading = "Papan Visual Kontrol Kuota & Distribusi Alat";

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Asset::query()->withCount([
                    "assetItems",
                    "assetItems as ready_unit" => fn ($q) => $q->where("status", "tersedia")->where("kondisi", "baik"),
                    "assetItems as dipinjam_unit" => fn ($q) => $q->where("status", "dipinjam"),
                    "assetItems as rusak_unit" => fn ($q) => $q->whereIn("kondisi", ["rusak", "rusak_total"]),
                ])
            )
            ->contentGrid(["sm" => 1, "md" => 2, "lg" => 3])
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make("kode_aset")->badge()->color("info")->grow(false),
                        TextColumn::make("nama_alat")->weight("bold")->size("lg")->alignEnd(),
                    ]),
                    TextColumn::make("quota_progress")
                        ->state(fn ($record) => $record)
                        ->formatStateUsing(function ($record) {
                            $registered = $record->asset_items_count;
                            $maxStock = $record->stok ?? 0;
                            $pct = $maxStock > 0 ? min(100, ($registered / $maxStock) * 100) : 0;
                            $barColor = $pct >= 100 ? "bg-amber-500" : "bg-blue-600";
                            return new HtmlString(
                                "<div class=\"space-y-1 mt-3\"><div class=\"flex justify-between text-xs font-semibold text-gray-500\">".
                                "<span>Keterisian Kuota Katalog</span><span>{$registered} / {$maxStock} Unit Max</span></div>".
                                "<div class=\"w-full bg-gray-200 rounded-full h-3 overflow-hidden\">".
                                "<div class=\"{$barColor} h-3 rounded-full\" style=\"width: {$pct}%\"></div></div></div>"
                            );
                        }),
                    TextColumn::make("distribution_health")
                        ->state(fn ($record) => $record)
                        ->formatStateUsing(function ($record) {
                            $total = $record->asset_items_count;
                            if ($total == 0) {
                                return new HtmlString("<p class=\"text-xs text-gray-400 italic mt-4 text-center\">Belum ada unit fisik terinput</p>");
                            }
                            $ready = $record->ready_unit;
                            $dipinjam = $record->dipinjam_unit;
                            $rusak = $record->rusak_unit;
                            $r = round(($ready / $total) * 100);
                            $d = round(($dipinjam / $total) * 100);
                            $k = round(($rusak / $total) * 100);
                            return new HtmlString(
                                "<div class=\"mt-4 space-y-2 border-t pt-3\">".
                                "<p class=\"text-[10px] font-bold uppercase tracking-widest text-gray-400\">Sebaran Real-Time:</p>".
                                "<div class=\"flex justify-between text-xs\"><span class=\"text-emerald-600\">Siap Pakai</span><span class=\"font-bold\">{$ready} U ({$r}%)</span></div>".
                                "<div class=\"flex justify-between text-xs\"><span class=\"text-amber-600\">Dipinjam</span><span class=\"font-bold\">{$dipinjam} U ({$d}%)</span></div>".
                                "<div class=\"flex justify-between text-xs\"><span class=\"text-rose-600\">Rusak</span><span class=\"font-bold\">{$rusak} U ({$k}%)</span></div>".
                                "</div>"
                            );
                        }),
                ]),
            ])
            ->paginated([6, 9, 12])
            ->defaultPaginationPageOption(6);
    }
}
