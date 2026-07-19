<?php

namespace App\Filament\Resources\Assets\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

// 🟢 FIX IMPORT: Menggunakan import resmi unifikasi Filament baru, bersih dari backslash inline
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // 🟢 FIX 1: Menggunakan modifyQueryUsing agar tidak merusak fitur search & filter bawaan Filament Resource
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'assetItems', 
                'assetItems as unit_baik' => fn ($query) => $query->where('kondisi', 'baik'),
                'assetItems as unit_rusak' => fn ($query) => $query->where('kondisi', 'rusak'),
                'assetItems as unit_rusak_total' => fn ($query) => $query->where('kondisi', 'rusak_total'),
            ]))
            
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

                // 🟢 FIX 2: Dibuat menjadi badge dan ditambahkan mapping emoji agar sinkron dengan Form Baru
                TextColumn::make('kegunaan')
                    ->label('Kegunaan')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'praktik' => '🛠️ Praktik',
                        'non_praktik' => '💼 Non-Praktik',
                        'Praktik Siswa' => '👨‍🎓 Praktik Siswa',
                        'Praktik Guru' => '👨‍🏫 Praktik Guru',
                        'Ujian/CBT' => '📝 Ujian / CBT',
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

                TextColumn::make('asset_items_count')
                    ->label('Jumlah Total')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),
            ])
            
            // 🟢 FIX 3: Rapi dan aman dari eror compiler
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}