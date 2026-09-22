<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\AssetItem;
use App\Models\SchoolClass;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('asset_item_id')
                ->required(),

            Placeholder::make('qr_scanner')
                ->hiddenLabel()
                ->content(new HtmlString('
                    <div x-data="latekajeScanner(\'reader-loan-inline\', \'loan-qr-field\')" x-init="init()">
                        <div id="reader-loan-inline" style="min-height:240px;aspect-ratio:4/3;" class="w-full overflow-hidden rounded-lg border border-dashed border-gray-300"></div>
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
                    </div>
                ')),

            TextInput::make('nomor_seri_atau_qr')
                ->label('Scan / Ketik Kode QR Alat')
                ->placeholder('cth: LTKJ-0001 — scan QR atau ketik manual')
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

            TextInput::make('return_pin')
                ->label('PIN Pengembalian (6 digit)')
                ->required()
                ->length(6)
                ->rule('regex:/^[0-9]{6}$/')
                ->default(fn (): string => sprintf('%06d', random_int(0, 999999)))
                ->helperText('Sudah terisi otomatis — boleh diganti. WAJIB diingat: tanpa PIN ini alat tidak bisa dikembalikan.'),

            TextInput::make('nama_siswa')
                ->label('Nama Peminjam')
                ->required()
                ->maxLength(255),

            Select::make('kelas')
                ->label('Kelas / Asal Peminjam')
                ->required()
                ->searchable()
                ->options(fn () => SchoolClass::orderBy('label')->pluck('label', 'label'))
                ->helperText('Kelola daftar via menu Kelas.'),
        ]);
    }
}
