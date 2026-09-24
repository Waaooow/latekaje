<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\AssetItem;
use App\Models\Group;
use App\Models\Member;
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
    private static function selfMember(): ?\App\Models\Member
    {
        return auth()->user()?->member;
    }

    private static function selfName(): ?string
    {
        $user = auth()->user();

        if (! $user || (! $user->member_id && ! $user->code)) {
            return null;
        }

        return self::selfMember()?->name ?? $user->name;
    }

    private static function selfGroup(): ?string
    {
        $user = auth()->user();

        if (! $user || (! $user->member_id && ! $user->code)) {
            return null;
        }

        return self::selfMember()?->group;
    }

    private static function selfCode(): ?string
    {
        return auth()->user()?->code;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('asset_item_id')
                ->required(),

            Section::make(__('loans.step1_title'))
                ->description(__('loans.step1_desc'))
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
                                    <button type="button" @click="restart()" class="lk-btn lk-btn-primary">'.__('loans.scan_retry').'</button>
                                    <button type="button" @click="stop()" class="lk-btn">'.__('loans.camera_off').'</button>
                                </div>
                            </div>
                        ')),

                    TextInput::make('nomor_seri_atau_qr')
                        ->label(__('loans.qr_code_label'))
                        ->placeholder(__('loans.qr_code_placeholder'))
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
                                    $fail(__('loans.val_code_unknown'));

                                    return;
                                }

                                if ($item->status === 'dipinjam' || $item->activeLoan()->exists()) {
                                    $fail(__('loans.val_borrowed'));

                                    return;
                                }

                                if ($item->kondisi !== 'baik') {
                                    $fail(__('loans.val_damaged'));
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

            Section::make(__('loans.step2_title'))
                ->description(__('loans.step2_desc'))
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
                                ">'.__('loans.reuse_prefix').'<span x-text="label"></span></button>
                            </div>
                        ')),

                    Select::make('member_id')
                        ->label(__('loans.select_member'))
                        ->default(fn () => auth()->user()?->member_id)
                        ->searchable()
                        ->options(fn () => Member::where('aktif', true)->orderBy('name')->get()->mapWithKeys(fn ($st) => [$st->id => $st->label()]))
                        ->afterStateUpdated(function (Set $set, $state): void {
                            $st = $state ? Member::find($state) : null;
                            $set('code', $st?->code);
                            if ($st) {
                                $set('borrower_name', $st->name);
                                $set('group', $st->group);
                            }
                        })
                        ->createOptionForm([
                            TextInput::make('code')
                                ->label(__('loans.code_label'))
                                ->maxLength(64),
                            TextInput::make('name')
                                ->label(__('loans.full_name'))
                                ->required()
                                ->maxLength(255),
                            Select::make('group')
                                ->label(__('loans.group_label'))
                                ->required()
                                ->searchable()
                                ->options(fn () => Group::orderBy('label')->pluck('label', 'label')),
                        ])
                        ->createOptionUsing(fn (array $data): int => Member::create([
                            'code' => blank($data['code'] ?? null) ? null : trim((string) $data['code']),
                            'name' => trim((string) $data['name']),
                            'group' => $data['group'],
                            'aktif' => true,
                        ])->getKey())
                        ->helperText(__('loans.helper_new_member')),

                    Hidden::make('code'),

                    TextInput::make('borrower_name')
                        ->label(__('loans.full_name'))
                        ->default(fn () => self::selfName())
                        ->placeholder(__('loans.name_auto_placeholder'))
                        ->required()
                        ->maxLength(255)
                        ->extraAttributes(['id' => 'loan-nama-field']),

                    Select::make('group')
                        ->label(__('loans.group_origin'))
                        ->default(fn () => self::selfGroup())
                        ->required()
                        ->searchable()
                        ->options(fn () => Group::orderBy('label')->pluck('label', 'label'))
                        ->extraAttributes(['id' => 'loan-kelas-field']),
                ]),

            Section::make(__('loans.step3_title'))
                ->description(__('loans.step3_desc'))
                ->schema([
                    TextInput::make('return_pin')
                        ->label(__('loans.form_pin_label'))
                        ->required()
                        ->length(6)
                        ->rule('regex:/^[0-9]{6}$/')
                        ->default(fn (): string => sprintf('%06d', random_int(0, 999999)))
                        ->helperText(__('loans.pin_helper')),
                ]),
        ]);
    }
}
