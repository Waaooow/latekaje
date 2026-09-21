<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Services\LoanService;
use Filament\Actions\Action;
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
                    ->label('Kode QR')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_siswa')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date()
                    ->sortable(),

                TextColumn::make('tanggal_kembali')
                    ->label('Tgl Kembali')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'aktif' ? 'Dipinjam' : 'Kembali')
                    ->color(fn (string $state): string => $state === 'aktif' ? 'warning' : 'success'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'semua' => 'Semua',
                        'aktif' => 'Dipinjam',
                        'kembali' => 'Kembali',
                    ])
                    ->default('aktif')
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value) || $value === 'semua') {
                            return $query;
                        }

                        return $query->where('status', $value);
                    }),
            ])
            ->recordActions([
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'aktif')
                    ->modalHeading('Kembalikan Alat (Scan QR)')
                    ->modalDescription(new HtmlString('
                        <div x-data="latekajeScanner(\'reader-table-return\', \'qr-table-return-field\')" x-init="init()" class="mb-3">
                            <div id="reader-table-return" style="min-height:240px" class="w-full overflow-hidden rounded-lg border border-dashed border-gray-300 bg-black"></div>
                            <p class="mt-2 text-sm text-gray-500" x-text="status"></p>
                            <p class="mt-1 text-sm text-red-600" x-show="error" x-text="error"></p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <select x-show="cameras.length > 1" x-model="cameraId" @change="restart()" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                                    <template x-for="c in cameras" :key="c.id"><option :value="c.id" x-text="c.label || c.id"></option></template>
                                </select>
                                <button type="button" @click="restart()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">Scan Ulang</button>
                                <button type="button" @click="stop()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">Matikan Kamera</button>
                            </div>
                            <p class="mt-2 text-xs text-gray-400">Hasil scan otomatis mengisi kolom Kode QR di bawah. Bisa juga diketik manual.</p>
                        </div>
                    '))
                    ->schema([
                        TextInput::make('nomor_seri_atau_qr')
                            ->label('Scan / Ketik Kode QR Alat')
                            ->required()
                            ->extraAttributes(['id' => 'qr-table-return-field']),

                        TextInput::make('nama_siswa_input')
                            ->label('Nama Penerima (Toolman)')
                            ->required(),

                        Select::make('kelas_input')
                            ->label('Kelas Penerima')
                            ->required()
                            ->options([
                                'X TJKT 1' => 'X TJKT 1',
                                'X TJKT 2' => 'X TJKT 2',
                                'XI TJKT 1' => 'XI TJKT 1',
                                'XI TJKT 2' => 'XI TJKT 2',
                                'XII TJKT 1' => 'XII TJKT 1',
                                'XII TJKT 2' => 'XII TJKT 2',
                                'GURU / STAF' => 'GURU / STAF',
                            ]),
                    ])
                    ->action(function ($record, array $data): void {
                        try {
                            LoanService::returnLoan(
                                $record,
                                (string) $data['nomor_seri_atau_qr'],
                                (string) $data['nama_siswa_input'],
                                (string) $data['kelas_input'],
                            );
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Gagal mengembalikan')
                                ->body(collect($e->errors())->flatten()->implode(' '))
                                ->danger()
                                ->persistent()
                                ->send();

                            throw $e;
                        }

                        Notification::make()
                            ->title('Alat dikembalikan')
                            ->body('Peminjaman '.$record->nama_siswa.' telah ditutup.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
