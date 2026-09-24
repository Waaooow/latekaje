<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Filament\Exports\LoanExporter;
use App\Services\LoanService;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assetItem.nomor_seri_atau_qr')
                    ->label(__('loans.col_qr'))
                    ->formatStateUsing(fn (?string $state): string => $state ?? __('loans.unit_deleted'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label(__('loans.col_nis'))
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('nama_siswa')
                    ->label(__('loans.col_borrower'))
                    ->description(fn ($record) => $record->kelas)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('return_pin')
                    ->label(__('loans.col_pin'))
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->visible(fn () => ! auth()->user()?->isSiswa() || filled(auth()->user()?->nis) || filled(auth()->user()?->student_id))
                    ->formatStateUsing(function (?string $state, $record): string {
                        if (! $state) {
                            return '-';
                        }

                        return auth()->user()?->ownsLoan($record) ?? false ? $state : '••••••';
                    }),

                TextColumn::make('tanggal_pinjam')
                    ->label(__('loans.col_borrowed_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('tanggal_kembali')
                    ->label(__('loans.col_returned_at'))
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('loans.col_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'aktif' ? __('loans.status_borrowed') : __('loans.status_returned'))
                    ->color(fn (string $state): string => $state === 'aktif' ? 'warning' : 'success'),

                TextColumn::make('returned_by')
                    ->label(__('loans.col_returned_by'))
                    ->description(fn ($record) => trim(($record->return_relation === 'wakil' ? __('loans.relation_proxy') : __('loans.relation_self')).($record->received_by ? __('loans.received_by_suffix', ['name' => $record->received_by]) : '')))
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('return_method')
                    ->label(__('loans.col_method'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'petugas' ? __('loans.method_staff') : ($state ? __('loans.method_self') : '-'))
                    ->color(fn (?string $state): string => $state === 'petugas' ? 'info' : 'gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('loans.filter_status'))
                    ->options([
                        'semua' => __('loans.filter_all'),
                        'aktif' => __('loans.filter_borrowed'),
                        'kembali' => __('loans.filter_returned'),
                    ])
                    ->default('aktif')
                    ->placeholder(__('loans.filter_all'))
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value) || $value === 'semua') {
                            return $query;
                        }

                        return $query->where('status', $value);
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(LoanExporter::class)
                    ->visible(fn (): bool => in_array(auth()->user()?->role, ['superadmin', 'toolman', 'anak_pkl'], true))
                    ->formats([ExportFormat::Xlsx, ExportFormat::Csv])
                    ->label(__('loans.export_recap'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
            ])
            ->recordActions([
                Action::make('kembalikan')
                    ->label(__('loans.action_return'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'aktif')
                    ->modalHeading(__('loans.return_modal_heading'))
                    ->modalDescription(new HtmlString('
                        <div x-data="latekajeScanner(\'reader-table-return\', \'qr-table-return-field\')" x-init="init()" style="margin-bottom: 0.75rem;">
                            <div id="reader-table-return" class="lk-reader"></div>
                            <p class="lk-status" x-text="status"></p>
                            <p class="lk-error" x-show="error" x-text="error"></p>
                            <div class="lk-row" x-show="cameras.length > 1" style="grid-template-columns: 1fr;">
                                <select x-model="cameraId" @change="restart()" class="lk-select">
                                    <template x-for="c in cameras" :key="c.id"><option :value="c.id" x-text="c.label || c.id"></option></template>
                                </select>
                            </div>
                            <div class="lk-row">
                                <button type="button" @click="restart()" class="lk-btn lk-btn-primary">'.__('loans.scan_retry').'</button>
                                <button type="button" @click="stop()" class="lk-btn">'.__('loans.camera_off').'</button>
                            </div>
                            <p class="lk-hint">'.__('loans.scan_hint_table').'</p>
                        </div>
                    '))
                    ->schema([
                        TextInput::make('nomor_seri_atau_qr')
                            ->label(__('loans.form_qr_label'))
                            ->required()
                            ->live()
                            ->extraAttributes(['id' => 'qr-table-return-field']),

                        TextInput::make('pin')
                            ->label(__('loans.form_pin_label'))
                            ->required()
                            ->length(6)
                            ->placeholder(__('loans.form_pin_placeholder')),

                        TextInput::make('returned_by')
                            ->label(__('loans.form_returned_by_label'))
                            ->required()
                            ->placeholder(__('loans.form_returned_by_placeholder'))
                            ->default(function () {
                                $user = auth()->user();

                                if ($user?->isSiswa() && ($user->nis || $user->student_id)) {
                                    return $user->student?->nama ?? $user->name;
                                }

                                return null;
                            }),

                        Select::make('return_relation')
                            ->label(__('loans.form_relation_label'))
                            ->options([
                                'sendiri' => __('loans.form_relation_self'),
                                'wakil' => __('loans.form_relation_proxy'),
                            ])
                            ->default('sendiri')
                            ->required(),

                        TextInput::make('received_by')
                            ->label(__('loans.form_received_by_label'))
                            ->placeholder(__('loans.form_received_by_placeholder')),

                        FileUpload::make('return_photo_path')
                            ->label(__('loans.form_photo_label'))
                            ->image()
                            ->disk('public')
                            ->directory('returns')
                            ->maxSize(2048),
                    ])
                    ->action(function ($record, array $data): void {
                        try {
                            LoanService::returnLoan($record, [
                                'qr' => (string) ($data['nomor_seri_atau_qr'] ?? ''),
                                'pin' => (string) ($data['pin'] ?? ''),
                                'returned_by' => (string) ($data['returned_by'] ?? ''),
                                'return_relation' => (string) ($data['return_relation'] ?? 'sendiri'),
                                'received_by' => (string) ($data['received_by'] ?? ''),
                                'return_photo_path' => $data['return_photo_path'] ?? null,
                            ]);
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title(__('loans.return_failed_title'))
                                ->body(collect($e->errors())->flatten()->implode(' '))
                                ->danger()
                                ->persistent()
                                ->send();

                            throw $e;
                        }

                        Notification::make()
                            ->title(__('loans.returned_title'))
                            ->body(__('loans.returned_body', ['name' => $record->nama_siswa]))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
