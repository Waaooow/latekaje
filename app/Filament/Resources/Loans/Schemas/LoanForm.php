<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\AssetItem;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
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
                ->suffixAction(
                    Action::make('scanWebcamQr')
                        ->label('Scan QR')
                        ->icon('heroicon-o-camera')
                        ->modalHeading('Scan QR via Webcam')
                        ->modalWidth('md')
                        ->modalSubmitAction(false)
                        ->modalContent(new HtmlString('
                            <div x-data="latekajeScanner(\'reader-loan-scanner\', \'loan-qr-field\')" x-init="init()">
                                <div id="reader-loan-scanner" style="min-height:260px" class="w-full overflow-hidden rounded-lg border border-dashed border-gray-300 bg-black"></div>
                                <p class="mt-2 text-sm text-gray-500" x-text="status"></p>
                                <p class="mt-1 text-sm text-red-600" x-show="error" x-text="error"></p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <select x-show="cameras.length > 1" x-model="cameraId" @change="restart()" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                                        <template x-for="c in cameras" :key="c.id"><option :value="c.id" x-text="c.label || c.id"></option></template>
                                    </select>
                                    <button type="button" @click="restart()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">Scan Ulang</button>
                                    <button type="button" @click="stop()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">Matikan Kamera</button>
                                </div>
                            </div>
                        '))
                )
                ->extraAttributes(['id' => 'loan-qr-field']),

            TextInput::make('nama_siswa')
                ->label('Nama Siswa')
                ->required()
                ->maxLength(255),

            Select::make('kelas')
                ->label('Kelas')
                ->required()
                ->searchable()
                ->options([
                    'X TJKT 1' => 'X TJKT 1',
                    'X TJKT 2' => 'X TJKT 2',
                    'XI TJKT 1' => 'XI TJKT 1',
                    'XI TJKT 2' => 'XI TJKT 2',
                    'XII TJKT 1' => 'XII TJKT 1',
                    'XII TJKT 2' => 'XII TJKT 2',
                    'GURU / STAF' => 'GURU / STAF',
                ]),
        ]);
    }
}
