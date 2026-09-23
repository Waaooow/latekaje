<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Models\Location;
use App\Services\AssetItemService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                    ->label(__('assets.code_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_alat')
                    ->label(__('assets.name_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis')
                    ->label(__('assets.type_label'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('spesifikasi')
                    ->label(__('assets.spec_label'))
                    ->limit(30)
                    ->tooltip(fn ($record): ?string => $record->spesifikasi),

                TextColumn::make('kegunaan')
                    ->label(__('assets.usage_label'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'praktik' => '🛠️ '.(__('assets.usage_option_practice')),
                        'non_praktik' => '📋 '.(__('assets.usage_option_non_practice')),
                        'Praktik Siswa' => '🎓 '.(__('assets.usage_option_student')),
                        'Praktik Guru' => '🧑‍🏫 '.(__('assets.usage_option_teacher')),
                        'Ujian/CBT' => '💻 '.(__('assets.usage_option_exam')),
                        'lainnya' => '✨ '.(__('assets.usage_option_other')),
                        default => (string) $state,
                    }),

                TextColumn::make('status_kondisi')
                    ->label(__('assets.condition_status_label'))
                    ->html()
                    ->getStateUsing(function ($record): string {
                        $baik = (int) ($record->unit_baik ?? 0);
                        $rusak = (int) ($record->unit_rusak ?? 0);
                        $rusakTotal = (int) ($record->unit_rusak_total ?? 0);

                        return '<span class="fi-badge">'.__('assets.condition_good').": {$baik}</span> "
                            .'<span class="fi-badge">'.__('assets.condition_damaged').": {$rusak}</span> "
                            .'<span class="fi-badge">'.__('assets.condition_total_loss').": {$rusakTotal}</span>";
                    }),

                TextColumn::make('asset_items_count')
                    ->label(__('assets.unit_count_label'))
                    ->counts('assetItems')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                Action::make('tambahUnit')
                    ->label(__('assets.add_unit'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('info')
                    ->modalHeading(fn ($record) => __('assets.add_unit_heading', ['name' => $record->nama_alat]))
                    ->form([
                        TextInput::make('jumlah')
                            ->label(__('assets.qty_label'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(500)
                            ->required(),

                        Select::make('location_id')
                            ->label(__('assets.placement_label'))
                            ->options(fn () => Location::orderBy('label')->pluck('label', 'id'))
                            ->default(fn () => Location::where('key', 'gudang')->value('id'))
                            ->required(),

                        Select::make('kondisi')
                            ->label(__('assets.initial_condition_label'))
                            ->options([
                                'baik' => __('assets.condition_good'),
                                'rusak' => __('assets.condition_damaged'),
                                'rusak_total' => __('assets.condition_total_loss'),
                            ])
                            ->default('baik')
                            ->required(),

                        Textarea::make('sn_manual')
                            ->label(__('assets.sn_manual_label'))
                            ->rows(3)
                            ->placeholder(__('assets.sn_manual_placeholder'))
                            ->helperText(__('assets.sn_manual_helper')),
                    ])
                    ->action(function ($record, array $data): void {
                        $serials = collect(preg_split('/\r\n|\r|\n/', (string) ($data['sn_manual'] ?? '')))
                            ->map(fn ($s) => trim((string) $s))
                            ->filter()
                            ->values()
                            ->all();

                        $qty = max((int) $data['jumlah'], count($serials), 1);

                        $units = AssetItemService::bulkCreate(
                            $record,
                            $qty,
                            (int) $data['location_id'],
                            (string) $data['kondisi'],
                            'tersedia',
                            $serials,
                        );

                        Notification::make()
                            ->title(__('assets.units_added_title', ['count' => $units->count(), 'name' => $record->nama_alat]))
                            ->body(__('assets.units_added_body', ['first' => $units->first()->nomor_seri_atau_qr, 'last' => $units->last()->nomor_seri_atau_qr]))
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label(__('common.edit')),
                DeleteAction::make()
                    ->label(__('common.delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->label(__('common.delete')),
                ]),
            ]);
    }
}
