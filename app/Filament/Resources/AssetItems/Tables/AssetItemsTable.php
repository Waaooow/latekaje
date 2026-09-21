<?php

namespace App\Filament\Resources\AssetItems\Tables;

use App\Filament\Exports\AssetItemExporter;
use App\Filament\Imports\AssetItemImporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AssetItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['activeLoan', 'asset', 'location']))
            ->defaultGroup(Group::make('asset.nama_alat')->label('Aset')->collapsible())
            ->columns([
                TextColumn::make('asset.nama_alat')
                    ->label('Nama Alat')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('asset.kode_aset')
                    ->label('Kode Aset')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nomor_seri_atau_qr')
                    ->label('Nomor Seri / QR')
                    ->searchable()
                    ->description(fn ($record): ?string => $record->asset?->spesifikasi),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'dipinjam' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('kondisi')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak' => 'warning',
                        'rusak_total' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'rusak' => 'Rusak',
                        'rusak_total' => 'Rusak Total',
                        default => (string) $state,
                    }),

                TextColumn::make('activeLoan.nama_siswa')
                    ->label('Dipinjam Oleh')
                    ->placeholder('—')
                    ->description(fn ($record): ?string => $record->activeLoan?->kelas),

                TextColumn::make('location.label')
                    ->label('Lokasi')
                    ->badge()
                    ->color('info'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(AssetItemImporter::class),
                ExportAction::make()
                    ->exporter(AssetItemExporter::class),
                Action::make('printAllQr')
                    ->label('Cetak Semua QR')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (): string => route('print.qr', ['ids' => 'all']))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('printQr')
                    ->label('Cetak QR')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn ($record): string => route('print.qr', ['ids' => $record->getKey()]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('printBulkQr')
                        ->label('Cetak QR Terpilih')
                        ->icon('heroicon-o-qr-code')
                        ->action(fn (Collection $records) => redirect()->away(
                            route('print.qr', ['ids' => $records->pluck('id')->implode(',')])
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('pindahRuanganMassal')
                        ->label('Pindah Ruangan')
                        ->icon('heroicon-o-map-pin')
                        ->form([
                            Select::make('location_id')
                                ->label('Lokasi Tujuan')
                                ->relationship('location', 'label')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['location_id' => $data['location_id']]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
