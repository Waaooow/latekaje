<?php

namespace App\Filament\Resources\Assets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'assetItems',
                'assetItems as unit_baik' => fn (Builder $q) => $q->where('kondisi', 'baik'),
                'assetItems as unit_rusak' => fn (Builder $q) => $q->where('kondisi', 'rusak'),
                'assetItems as unit_rusak_total' => fn (Builder $q) => $q->where('kondisi', 'rusak_total'),
            ]))
            ->columns([
                TextColumn::make('kode_aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_alat')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis')
                    ->badge()
                    ->color('info'),

                TextColumn::make('spesifikasi')
                    ->limit(30)
                    ->tooltip(fn ($record): ?string => $record->spesifikasi),

                TextColumn::make('kegunaan')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'praktik' => '🛠️ Praktik',
                        'non_praktik' => '📋 Non Praktik',
                        'Praktik Siswa' => '🎓 Praktik Siswa',
                        'Praktik Guru' => '🧑‍🏫 Praktik Guru',
                        'Ujian/CBT' => '💻 Ujian/CBT',
                        'lainnya' => '✨ Lainnya',
                        default => (string) $state,
                    }),

                TextColumn::make('status_kondisi')
                    ->label('Status Kondisi')
                    ->html()
                    ->getStateUsing(function ($record): string {
                        $baik = (int) ($record->unit_baik ?? 0);
                        $rusak = (int) ($record->unit_rusak ?? 0);
                        $rusakTotal = (int) ($record->unit_rusak_total ?? 0);

                        return "<span class=\"fi-badge\">Baik: {$baik}</span> "
                            . "<span class=\"fi-badge\">Rusak: {$rusak}</span> "
                            . "<span class=\"fi-badge\">Rusak Total: {$rusakTotal}</span>";
                    }),

                TextColumn::make('asset_items_count')
                    ->label('Jumlah Unit')
                    ->counts('assetItems')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
