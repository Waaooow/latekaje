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
                    ->label('Kode QR')
                    ->formatStateUsing(fn (?string $state): string => $state ?? '(unit dihapus)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_siswa')
                    ->label('Peminjam')
                    ->description(fn ($record) => $record->kelas)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('return_pin')
                    ->label('PIN')
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->visible(fn () => ! auth()->user()?->isSiswa()),

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
                    ->formatStateUsing(fn (string $state): string => $state === 'aktif' ? 'Dipinjam' : 'Kembali')
                    ->color(fn (string $state): string => $state === 'aktif' ? 'warning' : 'success'),

                TextColumn::make('returned_by')
                    ->label('Dikembalikan Oleh')
                    ->description(fn ($record) => trim(($record->return_relation === 'wakil' ? 'Di Wakilkan' : 'Sendiri').($record->received_by ? ' · Diterima: '.$record->received_by : '')))
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('return_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'petugas' ? 'Petugas' : ($state ? 'Mandiri' : '-'))
                    ->color(fn (?string $state): string => $state === 'petugas' ? 'info' : 'gray')
                    ->toggleable(),
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
            ->headerActions([
                ExportAction::make()
                    ->exporter(LoanExporter::class)
                    ->formats([ExportFormat::Xlsx, ExportFormat::Csv])
                    ->label('Export Rekap')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
            ])
            ->recordActions([
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'aktif')
                    ->modalHeading('Kembalikan Alat')
                    ->modalDescription(new HtmlString('
                        <div x-data="latekajeScanner(\'reader-table-return\', \'qr-table-return-field\')" x-init="init()" class="mb-3">
                            <div id="reader-table-return" style="min-height:240px;aspect-ratio:4/3;" class="w-full overflow-hidden rounded-lg border border-dashed border-gray-300"></div>
                            <p class="mt-2 text-sm text-gray-500" x-text="status"></p>
                            <p class="mt-1 text-sm text-red-600" x-show="error" x-text="error"></p>
                            <div class="mt-2" x-show="cameras.length > 1">
                                <select x-model="cameraId" @change="restart()" class="w-full rounded-lg border border-gray-300 px-2 py-2 text-sm">
                                    <template x-for="c in cameras" :key="c.id"><option :value="c.id" x-text="c.label || c.id"></option></template>
                                </select>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <button type="button" @click="restart()" class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white">Scan Ulang</button>
                                <button type="button" @click="stop()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">Matikan Kamera</button>
                            </div>
                            <p class="mt-2 text-xs text-gray-400">Hasil scan mengisi kolom Kode QR di bawah — modal tetap terbuka, isi form lalu tekan Konfirmasi.</p>
                        </div>
                    '))
                    ->schema([
                        TextInput::make('nomor_seri_atau_qr')
                            ->label('Scan / Ketik Kode QR Alat')
                            ->required()
                            ->live()
                            ->extraAttributes(['id' => 'qr-table-return-field']),

                        TextInput::make('pin')
                            ->label('PIN Pengembalian (6 digit)')
                            ->required()
                            ->length(6)
                            ->placeholder('Diberikan saat meminjam'),

                        TextInput::make('returned_by')
                            ->label('Nama Pengembali (yang bawa alat)')
                            ->required()
                            ->placeholder('cth: Budi Santoso'),

                        Select::make('return_relation')
                            ->label('Status Pengembali')
                            ->options([
                                'sendiri' => 'Peminjam sendiri',
                                'wakil' => 'Di Wakilkan teman',
                            ])
                            ->default('sendiri')
                            ->required(),

                        TextInput::make('received_by')
                            ->label('Diterima Oleh Petugas (opsional)')
                            ->placeholder('Kosongkan bila mandiri / tanpa petugas'),

                        FileUpload::make('return_photo_path')
                            ->label('Foto Bukti (opsional)')
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
