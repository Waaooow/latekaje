<?php

namespace App\Filament\Resources\AssetItems\Tables;

use App\Filament\Exports\AssetItemExporter;
use App\Filament\Imports\AssetItemImporter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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
            ->defaultGroup(Group::make('asset.nama_alat')->label(__('units.group_asset'))->collapsible())
            ->columns([
                TextColumn::make('asset.nama_alat')
                    ->label(__('units.name_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('asset.kode_aset')
                    ->label(__('units.code_label'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nomor_seri_atau_qr')
                    ->label(__('units.serial_label'))
                    ->searchable()
                    ->description(fn ($record): ?string => $record->asset?->spesifikasi),

                TextColumn::make('status')
                    ->label(__('units.status_label'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'dipinjam' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('kondisi')
                    ->label(__('units.condition_label'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak' => 'warning',
                        'rusak_total' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'baik' => __('units.condition_good'),
                        'rusak' => __('units.condition_damaged'),
                        'rusak_total' => __('units.condition_total_loss'),
                        default => (string) $state,
                    }),

                TextColumn::make('activeLoan.borrower_name')
                    ->label(__('units.borrowed_by_label'))
                    ->placeholder('—')
                    ->description(fn ($record): ?string => $record->activeLoan?->group),

                TextColumn::make('location.label')
                    ->label(__('units.location_label'))
                    ->badge()
                    ->color('info'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label(__('common.import'))
                    ->importer(AssetItemImporter::class),
                Action::make('downloadTemplateCsv')
                    ->label(__('common.template_csv'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        $headers = [
                            'nama_alat', 'kode_aset', 'jenis', 'spesifikasi', 'kegunaan',
                            'nomor_seri_atau_qr', 'kondisi', 'lokasi', 'status', 'jumlah',
                        ];

                        $rows = [
                            // Satuan dengan SN manual.
                            ['Mini PC', 'PC-MINI-001', 'Komputer', 'Intel N100, RAM 8GB', 'praktik', 'SN-PC-001', 'baik', 'lab_tjkt', 'tersedia', ''],
                            // Massal: jumlah tanpa SN → kode digenerate otomatis.
                            ['Kabel HDMI', 'KBL-HDMI-001', 'Aksesoris', '2 meter', 'praktik', '', 'baik', 'gudang', 'tersedia', '10'],
                        ];

                        return response()->streamDownload(function () use ($headers, $rows) {
                            // Titik-koma agar langsung terparsing di Excel Indonesia.
                            $out = fopen('php://output', 'w');
                            fwrite($out, "\xEF\xBB\xBF");
                            fputcsv($out, $headers, ';');

                            foreach ($rows as $row) {
                                fputcsv($out, $row, ';');
                            }

                            fclose($out);
                        }, 'template-import-unit.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
                    }),
                ExportAction::make()
                    ->label(__('units.download_excel'))
                    ->exporter(AssetItemExporter::class),
                Action::make('printAllQr')
                    ->label(__('units.print_all_qr'))
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (): string => route('print.qr', ['ids' => 'all']))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('common.edit')),
                    DeleteAction::make()
                        ->label(__('common.delete')),
                    Action::make('printQr')
                        ->label(__('units.print_qr'))
                        ->icon('heroicon-o-qr-code')
                        ->url(fn ($record): string => route('print.qr', ['ids' => $record->getKey()]))
                        ->openUrlInNewTab(),
                ])
                    ->label(__('units.actions_label'))
                    ->icon('heroicon-o-ellipsis-horizontal'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->label(__('common.delete')),
                    BulkAction::make('printBulkQr')
                        ->label(__('units.print_selected_qr'))
                        ->icon('heroicon-o-qr-code')
                        ->action(fn (Collection $records) => redirect()->away(
                            route('print.qr', ['ids' => $records->pluck('id')->implode(',')])
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('pindahRuanganMassal')
                        ->label(__('units.move_room'))
                        ->icon('heroicon-o-map-pin')
                        ->form([
                            Select::make('location_id')
                                ->label(__('units.target_location_label'))
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
