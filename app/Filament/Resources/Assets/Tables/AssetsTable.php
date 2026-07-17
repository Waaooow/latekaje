<?php

namespace App\Filament\Resources\Assets\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                // Menggunakan hitungan total & kondisi secara realtime (Anti-kepentok)
                \App\Models\Asset::query()->withCount([
                    'assetItems', 
                    'assetItems as unit_baik' => fn ($query) => $query->where('kondisi', 'baik'),
                    'assetItems as unit_rusak' => fn ($query) => $query->where('kondisi', 'rusak'),
                    'assetItems as unit_rusak_total' => fn ($query) => $query->where('kondisi', 'rusak_total'),
                ])
            )
            ->columns([
                TextColumn::make('kode_aset')
                    ->label('Kode Aset')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_alat')
                    ->label('Nama Alat')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('jenis')
                    ->label('Jenis Alat')
                    ->badge()
                    ->color('info'),

                TextColumn::make('spesifikasi')
                    ->label('Spesifikasi')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('kegunaan')
                    ->label('Kegunaan')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'praktik' => '🛠️ Praktik',
                        'non_praktik' => '📋 Non-Praktik',
                        default => $state,
                    }),

                TextColumn::make('status_kondisi')
                    ->label('Kondisi Unit')
                    ->html()
                    ->state(function ($record) {
                        $baik = $record->unit_baik ?? 0;
                        $rusak = $record->unit_rusak ?? 0;
                        $rusakTotal = $record->unit_rusak_total ?? 0;

                        return "
                            <div class='flex flex-wrap gap-1'>
                                <span class='px-2 py-0.5 text-xs font-semibold rounded-md bg-green-500/10 text-green-500 border border-green-500/20'>Baik: {$baik}</span>
                                <span class='px-2 py-0.5 text-xs font-semibold rounded-md bg-amber-500/10 text-amber-500 border border-amber-500/20'>Rusak: {$rusak}</span>
                                <span class='px-2 py-0.5 text-xs font-semibold rounded-md bg-danger-500/10 text-danger-500 border border-danger-500/20'>Rusak Total: {$rusakTotal}</span>
                            </div>
                        ";
                    }),

                // Menampilkan 'asset_items_count' hasil kalkulasi dinamis database
                TextColumn::make('asset_items_count')
                    ->label('Jumlah Total')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),
            ])
            
            // JALUR ABSOLUT: Mengunci Edit & Delete untuk tabel induk katalog
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])

            // 🟢 FIX FINAL: Memanggil langsung DeleteBulkAction dari rumpun utama Filament\Actions
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }
}