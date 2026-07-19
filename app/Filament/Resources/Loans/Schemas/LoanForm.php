<?php

namespace App\Filament\Resources\Loans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden; // 🟢 TAMBAHKAN INI
use Illuminate\Support\HtmlString;
use App\Models\AssetItem; // 🟢 TAMBAHKAN INI

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 🟢 1. HIDDEN FIELD: Menampung foreign key asli untuk disimpan ke tabel database 'loans'
                Hidden::make('asset_item_id')
                    ->required(),

                // 2. INPUT KODE BARANG (Hanya jembatan UI, tidak disimpan langsung ke DB)
                TextInput::make('nomor_seri_atau_qr')
                    ->label('Scan / Ketik Kode QR Alat')
                    ->required()
                    ->placeholder('Arahkan kamera ke stiker QR atau ketik manual...')
                    ->live() // Wajib live agar perubahan terbaca secara realtime
                    ->dehydrated(false) // 🟢 PENTING: Mencegah Filament memasukkan string ini ke tabel loans
                    
                    // 🟢 Validasi kustom biar gak crash kalau kode salah atau alat lagi dipinjam
                    ->rules([
                        fn () => function (string $attribute, $value, \Closure $fail) {
                            $item = AssetItem::where('nomor_seri_atau_qr', $value)->first();
                            if (!$item) {
                                $fail('❌ Kode QR / Nomor Seri tidak terdaftar di sistem LATEKAJE!');
                                return;
                            }
                            if ($item->status === 'dipinjam') {
                                $fail('⚠️ Gagal! Alat ini statusnya sedang dipinjam oleh siswa lain.');
                            }
                        }
                    ])
                    
                    // 🟢 Sinkronisasi: Cari ID barang di DB berdasarkan string teks yang di-scan/diketik
                    ->afterStateUpdated(function ($state, $set) {
                        $item = AssetItem::where('nomor_seri_atau_qr', $state)->first();
                        if ($item) {
                            $set('asset_item_id', $item->id); // Isi hidden field otomatis
                        } else {
                            $set('asset_item_id', null);
                        }
                    })

                    // 🟢 Mengisi kembali kolom teks saat halaman EDIT data lama dibuka
                    ->afterStateHydrated(function ($set, $record) {
                        if ($record && $record->asset_item_id) {
                            $item = AssetItem::find($record->asset_item_id);
                            if ($item) {
                                $set('nomor_seri_atau_qr', $item->nomor_seri_atau_qr);
                            }
                        }
                    })
                    
                    // 🟢 TOMBOL SCANNER WEBCAM
                    ->suffixAction(
                        \Filament\Actions\Action::make('scanWebcamQr')
                            ->icon('heroicon-o-camera')
                            ->color('warning')
                            ->tooltip('Buka Kamera QR Scanner')
                            
                            ->modalHeading('Arahkan Stiker QR ke Kamera Lab')
                            ->modalWidth('md')
                            ->modalSubmitAction(false) 
                            
                            ->modalContent(new HtmlString('
                                <div x-data="{
                                    html5QrCode: null,
                                    
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
                                        this.html5QrCode = new Html5Qrcode(\'reader-loan-scanner\');
                                        this.html5QrCode.start(
                                            { facingMode: \'environment\' }, 
                                            {
                                                fps: 15,
                                                qrbox: { width: 220, height: 220 }
                                            },
                                            (decodedText) => {
                                                // Masukkan hasil scan ke field text UI
                                                $wire.set(\'data.nomor_seri_atau_qr\', decodedText);
                                                
                                                // Trigger event update secara manual agar afterStateUpdated() langsung berjalan
                                                $dispatch(\'input\'); 
                                                
                                                this.stopCamera();
                                                
                                                let closeBtn = document.querySelector(\'.fi-modal-close-btn\');
                                                if (closeBtn) closeBtn.click();
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
                                class="flex flex-col items-center justify-center p-2 text-center">
                                    
                                    <div id="reader-loan-scanner" class="w-full max-w-xs overflow-hidden rounded-xl border-2 border-dashed border-gray-400 bg-gray-900" style="aspect-ratio: 1/1;"></div>
                                    
                                    <p class="mt-3 text-xs text-gray-500 animate-pulse">
                                        Posisikan stiker QR Code tepat di dalam kotak kamera.
                                    </p>
                                </div>
                            '))
                    ),

                // 3. INPUT NAMA SISWA
                TextInput::make('nama_siswa')
                    ->label('Nama Lengkap Siswa')
                    ->required()
                    ->placeholder('Ketik nama peminjam...')
                    ->maxLength(255),

                // 4. DROPDOWN KELAS
                Select::make('kelas')
                    ->label('Kelas / Jabatan')
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
                    ->placeholder('Pilih kelas siswa...'),
            ]);
    }
}