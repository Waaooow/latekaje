<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\AssetItem;
use App\Models\SchoolClass;
use App\Models\Student;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class LoanForm
{
    private static function lockedToSelf(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSiswa() && ($user->student_id || $user->nis));
    }

    private static function selfStudent(): ?\App\Models\Student
    {
        return auth()->user()?->student;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('asset_item_id')
                ->required(),

            Section::make('Langkah 1 — Scan Alat')
                ->description('Arahkan stiker QR ke kamera, atau ketik kodenya manual bila kamera tidak tersedia.')
                ->schema([
                    Placeholder::make('qr_scanner')
                        ->hiddenLabel()
                        ->content(new HtmlString('
                            <div x-data="latekajeScanner(\'reader-loan-inline\', \'loan-qr-field\')" x-init="init()">
                                <div id="reader-loan-inline" class="lk-reader"></div>
                                <p class="lk-status" x-text="status"></p>
                                <p class="lk-error" x-show="error" x-text="error"></p>
                                <div class="lk-row" x-show="cameras.length > 1" style="grid-template-columns: 1fr;">
                                    <select x-model="cameraId" @change="restart()" class="lk-select">
                                        <template x-for="c in cameras" :key="c.id"><option :value="c.id" x-text="c.label || c.id"></option></template>
                                    </select>
                                </div>
                                <div class="lk-row">
                                    <button type="button" @click="restart()" class="lk-btn lk-btn-primary">Scan Ulang</button>
                                    <button type="button" @click="stop()" class="lk-btn">Matikan Kamera</button>
                                </div>
                            </div>
                        ')),

                    TextInput::make('nomor_seri_atau_qr')
                        ->label('Kode Alat')
                        ->placeholder('Hasil scan muncul di sini, atau ketik manual cth: LTKJ-0001')
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->rules([
                            fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                if (blank($value)) {
                                    return;
                                }

                                $item = AssetItem::query()
                                    ->where('nomor_seri_atau_qr', $value)
                                    ->first();

                                if (! $item) {
                                    $fail('Kode alat tidak terdaftar.');

                                    return;
                                }

                                if ($item->status === 'dipinjam' || $item->activeLoan()->exists()) {
                                    $fail('Alat sedang dipinjam.');

                                    return;
                                }

                                if ($item->kondisi !== 'baik') {
                                    $fail('Alat kondisi rusak, tidak layak pinjam.');
                                }
                            },
                        ])
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            if (blank($state)) {
                                $set('asset_item_id', null);

                                return;
                            }

                            $item = AssetItem::query()
                                ->where('nomor_seri_atau_qr', $state)
                                ->first();

                            $set('asset_item_id', $item?->getKey());
                        })
                        ->afterStateHydrated(function (Set $set, $state, $record): void {
                            if ($record?->assetItem) {
                                $set('nomor_seri_atau_qr', $record->assetItem->nomor_seri_atau_qr);
                            }
                        })
                        ->extraAttributes(['id' => 'loan-qr-field']),
                ]),

            Section::make('Langkah 2 — Siapa yang Meminjam')
                ->description('Tulis nama asli sesuai absen. Akun ini dipakai bersama, jadi jangan asal isi.')
                ->schema([
                    Placeholder::make('borrower_memory')
                        ->hiddenLabel()
                        ->content(new HtmlString('
                            <div x-data="{ show: false, label: \'\' }" x-init="
                                const KEY = \'latekaje_last_borrower\';
                                const readFields = () => {
                                    const rn = document.getElementById(\'loan-nama-field\');
                                    const rk = document.getElementById(\'loan-kelas-field\');
                                    const n = rn ? (rn.tagName === \'INPUT\' ? rn : rn.querySelector(\'input\')) : null;
                                    const k = rk ? rk.querySelector(\'select\') : null;
                                    return { n, k };
                                };
                                const refresh = () => {
                                    try {
                                        const raw = localStorage.getItem(KEY);
                                        if (raw) {
                                            const d = JSON.parse(raw);
                                            if (d.nama) { show = true; label = d.nama + (d.kelas ? \' — \' + d.kelas : \'\'); return; }
                                        }
                                    } catch (e) {}
                                    show = false;
                                };
                                refresh();
                                if (!window.latekajeBorrowerSaver) {
                                    window.latekajeBorrowerSaver = true;
                                    const save = () => {
                                        try {
                                            const f = readFields();
                                            const nama = (f.n && f.n.value || \'\').trim();
                                            const kelas = (f.k && f.k.value || \'\').trim();
                                            if (nama && kelas) localStorage.setItem(KEY, JSON.stringify({ nama, kelas }));
                                        } catch (e) {}
                                    };
                                    document.addEventListener(\'input\', save);
                                    document.addEventListener(\'change\', save);
                                }
                            ">
                                <button x-show="show" type="button" class="rounded-lg border border-amber-400 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700" @click="
                                    try {
                                        const d = JSON.parse(localStorage.getItem(\'latekaje_last_borrower\'));
                                        const root = document.getElementById(\'loan-nama-field\');
                                        const input = root ? (root.tagName === \'INPUT\' ? root : root.querySelector(\'input\')) : null;
                                        if (input && d.nama) {
                                            input.focus();
                                            input.value = d.nama;
                                            input.dispatchEvent(new Event(\'input\', { bubbles: true }));
                                            input.dispatchEvent(new Event(\'change\', { bubbles: true }));
                                        }
                                        const kroot = document.getElementById(\'loan-kelas-field\');
                                        const sel = kroot ? kroot.querySelector(\'select\') : null;
                                        if (sel && d.kelas) {
                                            sel.value = d.kelas;
                                            sel.dispatchEvent(new Event(\'change\', { bubbles: true }));
                                        }
                                    } catch (e) {}
                                ">Pakai lagi: <span x-text="label"></span></button>
                            </div>
                        ')),

                    Select::make('student_id')
                        ->label('Pilih Siswa (ketik nama / NIS)')
                        ->disabled(fn (): bool => self::lockedToSelf())
                        ->default(fn () => auth()->user()?->student_id)
                        ->searchable()
                        ->options(fn () => Student::where('aktif', true)->orderBy('nama')->get()->mapWithKeys(fn ($st) => [$st->id => $st->label()]))
                        ->afterStateUpdated(function (Set $set, $state): void {
                            $st = $state ? Student::find($state) : null;
                            $set('nis', $st?->nis);
                            if ($st) {
                                $set('nama_siswa', $st->nama);
                                $set('kelas', $st->kelas);
                            }
                        })
                        ->createOptionForm([
                            TextInput::make('nis')
                                ->label('NIS')
                                ->maxLength(64),
                            TextInput::make('nama')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(255),
                            Select::make('kelas')
                                ->label('Kelas')
                                ->required()
                                ->searchable()
                                ->options(fn () => SchoolClass::orderBy('label')->pluck('label', 'label')),
                        ])
                        ->createOptionUsing(fn (array $data): int => Student::create([
                            'nis' => blank($data['nis'] ?? null) ? null : trim((string) $data['nis']),
                            'nama' => trim((string) $data['nama']),
                            'kelas' => $data['kelas'],
                            'aktif' => true,
                        ])->getKey())
                        ->helperText('Siswa belum terdaftar? Ketik lalu pilih “Buat baru”. Bisa juga isi manual di bawah.'),

                    Hidden::make('nis'),

                    TextInput::make('nama_siswa')
                        ->label('Nama Lengkap')
                        ->disabled(fn (): bool => self::lockedToSelf())
                        ->default(fn () => self::selfStudent()?->nama ?? auth()->user()?->name)
                        ->placeholder('Terisi otomatis dari siswa terpilih')
                        ->required()
                        ->maxLength(255)
                        ->extraAttributes(['id' => 'loan-nama-field']),

                    Select::make('kelas')
                        ->label('Kelas / Asal')
                        ->disabled(fn (): bool => self::lockedToSelf())
                        ->default(fn () => self::selfStudent()?->kelas)
                        ->required()
                        ->searchable()
                        ->options(fn () => SchoolClass::orderBy('label')->pluck('label', 'label'))
                        ->extraAttributes(['id' => 'loan-kelas-field']),
                ]),

            Section::make('Langkah 3 — Kunci dengan PIN')
                ->description('PIN ini WAJIB dibawa saat mengembalikan alat. Catat / foto layar ini.')
                ->schema([
                    TextInput::make('return_pin')
                        ->label('PIN Pengembalian (6 digit)')
                        ->required()
                        ->length(6)
                        ->rule('regex:/^[0-9]{6}$/')
                        ->default(fn (): string => sprintf('%06d', random_int(0, 999999)))
                        ->helperText('Sudah terisi otomatis — boleh diganti dengan yang mudah kamu ingat.'),
                ]),
        ]);
    }
}
