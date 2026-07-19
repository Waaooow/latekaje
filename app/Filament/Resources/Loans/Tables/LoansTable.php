<?php

namespace App\Filament\Resources\Loans\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action; 
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as FacadesSchema;

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
            ->actions([
                Action::make('kembalikan')
                    ->label('Kembalikan Alat')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'aktif')
                    
                    ->modalHeading('Verifikasi Pengembalian Alat')
                    ->modalDescription('Silakan dekatkan kode QR barang ke kamera di bawah, lalu isi data verifikasi.')
                    ->modalSubmitActionLabel('Verifikasi & Proses Kembali')
                    
                    ->form([
                        Forms\Components\Placeholder::make('scanner_camera_live')
                            ->label('Kamera Scanner Aktif')
                            ->content(new HtmlString('
                                <!-- 🟢 INJEKSI CSS: Paksa elemen video agar tetap lurus (tidak mirror) -->
                                <style>
                                    #reader-table-embedded video {
                                        transform: scaleX(1) !important;
                                        -webkit-transform: scaleX(1) !important;
                                    }
                                </style>

                                <div x-data="{
                                    html5QrCode: null,
                                    scanned: false,
                                    scannedCode: \'\',
                                    initScanner() {
                                        if (typeof Html5Qrcode === \'undefined\') {
                                            let script = document.createElement(\'script\');
                                            script.src = \'https://unpkg.com/html5-qrcode\';
                                            script.onload = () => this.startCamera();
                                            document.head.appendChild(script);
                                        } else {
                                            this.startCamera();
                                        }
                                    },
                                    startCamera() {
                                        this.scanned = false;
                                        if (this.html5QrCode) { this.html5QrCode.clear(); }
                                        this.html5QrCode = new Html5Qrcode(\'reader-table-embedded\');
                                        this.html5QrCode.start(
                                            { facingMode: \'environment\' },
                                            { fps: 15, qrbox: { width: 180, height: 180 } },
                                            (decodedText) => {
                                                let inputField = document.getElementById(\'qr-table-return-field\');
                                                if (inputField) {
                                                    inputField.value = decodedText;
                                                    inputField.dispatchEvent(new Event(\'input\'));
                                                }
                                                this.stopCamera();
                                                this.scanned = true;
                                                this.scannedCode = decodedText;
                                            },
                                            (errorMessage) => {}
                                        ).catch(err => console.error(err));
                                    },
                                    stopCamera() {
                                        if (this.html5QrCode) {
                                            this.html5QrCode.stop().catch(err => console.error(err));
                                        }
                                    }
                                }"
                                x-init="initScanner()"
                                x-on:destroy="stopCamera()"
                                class="flex flex-col items-center justify-center p-3 text-center bg-gray-50 dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 mb-2">
                                    
                                    <div x-show="!scanned" class="w-full flex flex-col items-center">
                                        <div id="reader-table-embedded" class="w-full max-w-xs overflow-hidden rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-900" style="aspect-ratio: 1/1;"></div>
                                        <p class="mt-2 text-xs text-gray-400 animate-pulse">Dekatkan stiker QR Code alat ke arah kamera lab.</p>
                                    </div>

                                    <div x-show="scanned" class="p-4 bg-emerald-500/10 text-emerald-500 rounded-lg w-full flex flex-col items-center justify-center gap-1" style="display: none;">
                                        <span class="text-2xl">✅</span>
                                        <p class="text-xs font-semibold">Scan Berhasil Divalidasi!</p>
                                        <p class="text-xs font-mono bg-white dark:bg-gray-900 px-2 py-0.5 rounded shadow-sm border border-gray-200 dark:border-gray-800 mt-1" x-text="scannedCode"></p>
                                        <button type="button" @click="startCamera()" class="mt-2 text-xs text-primary-500 underline hover:text-primary-600">Scan Ulang Barang</button>
                                    </div>
                                </div>
                            ')),

                        Forms\Components\TextInput::make('nomor_seri_atau_qr')
                            ->label('Kode QR / Nomor Seri Alat')
                            ->id('qr-table-return-field')
                            ->required()
                            ->placeholder('Otomatis terisi via scan di atas atau ketik manual...'),

                        Forms\Components\TextInput::make('nama_siswa_input')
                            ->label('Nama Lengkap Peminjam (Verifikasi)')
                            ->required()
                            ->placeholder('Ketik nama Anda sesuai saat meminjam...'),

                        Forms\Components\Select::make('kelas_input')
                            ->label('Kelas / Jabatan (Verifikasi)')
                            ->options([
                                'X TJKT 1' => 'X TJKT 1',
                                'X TJKT 2' => 'X TJKT 2',
                                'XI TJKT 1' => 'XI TJKT 1',
                                'XI TJKT 2' => 'XI TJKT 2',
                                'XII TJKT 1' => 'XII TJKT 1',
                                'XII TJKT 2' => 'XII TJKT 2',
                                'GURU / STAF' => '💼 Guru / Staf Instruktur',
                            ])
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih kelas Anda...'),
                    ])
                    
                    ->action(function ($record, array $data) {
                        $actualQr = $record->assetItem?->nomor_seri_atau_qr;

                        if (!$actualQr || $actualQr !== $data['nomor_seri_atau_qr']) {
                            Notification::make()
                                ->title('Verifikasi Gagal ❌')
                                ->body('Kode QR tidak cocok! Alat fisik yang Anda bawa bukan unit yang terdaftar di log pinjaman ini.')
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        $inputName  = strtolower(trim($data['nama_siswa_input']));
                        $dbName     = strtolower(trim($record->nama_siswa));
                        $inputClass = strtolower(trim($data['kelas_input']));
                        $dbClass    = strtolower(trim($record->kelas));

                        if ($inputName !== $dbName || $inputClass !== $dbClass) {
                            Notification::make()
                                ->title('Verifikasi Gagal ❌')
                                ->body('Nama atau Kelas tidak cocok dengan data peminjam asli dari unit ini!')
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        $record->update([
                            'status' => 'kembali',
                            'tanggal_kembali' => now(),
                        ]);

                        $record->assetItem?->update([
                            'status' => 'tersedia',
                        ]);

                        Notification::make()
                            ->title('Pengembalian Berhasil! 🎉')
                            ->body("Terima kasih **{$record->nama_siswa}**, unit alat telah aman dikembalikan ke dalam lab.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}