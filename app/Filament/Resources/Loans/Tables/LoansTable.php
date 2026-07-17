<?php

namespace App\Filament\Resources\Loans\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assetItem.nomor_seri_atau_qr')
                    ->label('Kode QR Unit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_siswa')
                    ->label('Nama Peminjam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->sortable(),

                TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('tanggal_kembali')
                    ->label('Tgl Kembali')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'warning',
                        'kembali' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktif' => '🔄 Dipinjam',
                        'kembali' => '✅ Kembali',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Log')
                    ->options([
                        'aktif' => '🔄 Sedang Dipinjam',
                        'kembali' => '✅ Sudah Kembali',
                        'semua' => '📋 Log (Semua Data)',
                    ])
                    ->default('aktif')
                    ->selectablePlaceholder(false)
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'aktif') {
                            $query->where('status', 'aktif');
                        } elseif ($data['value'] === 'kembali') {
                            $query->where('status', 'kembali');
                        }
                    }),
            ])
            // 🟢 LAYOUT DIAPUS: UI otomatis balik modal dropdown popover yang tersembunyi
            ->actions([
                Action::make('kembalikan')
                    ->label('Kembalikan Alat')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'aktif')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengembalian')
                    ->modalDescription('Apakah alat ini sudah benar-benar dikembalikan?')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'kembali',
                            'tanggal_kembali' => now(),
                        ]);

                        $record->assetItem?->update([
                            'status' => 'tersedia',
                        ]);
                    }),
            ]);
    }
}
