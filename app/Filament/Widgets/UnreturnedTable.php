<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Services\RecapService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UnreturnedTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return __('dashboard.unreturned_heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Loan::query()
                    ->with(['assetItem.asset'])
                    ->where('status', 'aktif')
                    ->orderBy('tanggal_pinjam')
            )
            ->headerActions([
                Action::make('kirimRekap')
                    ->label(__('recap.send_now'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (): bool => in_array(auth()->user()?->role, ['superadmin', 'toolman', 'anak_pkl'], true))
                    ->action(function (): void {
                        $result = RecapService::sendNow();

                        Notification::make()
                            ->title(__('recap.sent_title', ['total' => $result['total']]))
                            ->body(implode(' | ', array_map(
                                fn ($k, $v) => "[$k] $v",
                                array_keys($result['channels']),
                                $result['channels']
                            )))
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('assetItem.nomor_seri_atau_qr')
                    ->label(__('dashboard.col_qr'))
                    ->formatStateUsing(fn (?string $state): string => $state ?? __('dashboard.unit_deleted'))
                    ->searchable(),

                TextColumn::make('assetItem.asset.nama_alat')
                    ->label(__('dashboard.col_tool'))
                    ->searchable(),

                TextColumn::make('nama_siswa')
                    ->label(__('dashboard.col_borrower'))
                    ->description(fn ($record) => $record->kelas)
                    ->searchable(),

                TextColumn::make('lama_pinjam')
                    ->label(__('dashboard.col_duration'))
                    ->badge()
                    ->color(fn ($record) => $record->tanggal_pinjam->diffInDays(now()) >= 7 ? 'danger' : ($record->tanggal_pinjam->diffInDays(now()) >= 3 ? 'warning' : 'gray'))
                    ->state(fn ($record) => ($d = $record->tanggal_pinjam->diffInDays(now())) === 0 ? __('dashboard.duration_today') : __('dashboard.duration_days', ['count' => $d])),

                TextColumn::make('return_pin')
                    ->label(__('dashboard.col_pin'))
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
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
