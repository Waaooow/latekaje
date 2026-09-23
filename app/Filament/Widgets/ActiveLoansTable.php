<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveLoansTable extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return __('dashboard.active_heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Loan::query()
                    ->with(['assetItem.asset'])
                    ->where('status', 'aktif')
                    ->latest('tanggal_pinjam')
            )
            ->columns([
                TextColumn::make('assetItem.nomor_seri_atau_qr')
                    ->label(__('dashboard.col_qr'))
                    ->formatStateUsing(fn (?string $state): string => $state ?? '(unit dihapus)')
                    ->searchable(),

                TextColumn::make('nama_siswa')
                    ->label(__('dashboard.col_borrower'))
                    ->description(fn ($record) => $record->kelas)
                    ->searchable(),

                TextColumn::make('tanggal_pinjam')
                    ->label(__('dashboard.col_borrowed_since'))
                    ->since()
                    ->sortable(),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
