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

    protected static ?string $heading = 'Belum Kembali (semua pinjaman aktif)';

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
                    ->label('Kirim Rekap Sekarang')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (): bool => in_array(auth()->user()?->role, ['superadmin', 'toolman', 'anak_pkl'], true))
                    ->action(function (): void {
                        $result = RecapService::sendNow();

                        Notification::make()
                            ->title('Rekap dikirim ('.$result['total'].' unit)')
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
                    ->label('Kode QR')
                    ->formatStateUsing(fn (?string $state): string => $state ?? '(unit dihapus)')
                    ->searchable(),

                TextColumn::make('assetItem.asset.nama_alat')
                    ->label('Alat')
                    ->searchable(),

                TextColumn::make('nama_siswa')
                    ->label('Peminjam')
                    ->description(fn ($record) => $record->kelas)
                    ->searchable(),

                TextColumn::make('lama_pinjam')
                    ->label('Lama')
                    ->badge()
                    ->color(fn ($record) => $record->tanggal_pinjam->diffInDays(now()) >= 7 ? 'danger' : ($record->tanggal_pinjam->diffInDays(now()) >= 3 ? 'warning' : 'gray'))
                    ->state(fn ($record) => ($d = $record->tanggal_pinjam->diffInDays(now())) === 0 ? 'hari ini' : $d.' hari'),

                TextColumn::make('return_pin')
                    ->label('PIN')
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->visible(fn () => ! auth()->user()?->isSiswa()),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
