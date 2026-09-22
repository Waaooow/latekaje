<x-filament-panels::page>
    <div x-data="{
        confirmMaintenance: false,
        confirm() {
            this.confirmMaintenance = true;
        },
        executeMaintenance() {
            this.confirmMaintenance = false;
            @this.toggleMaintenance();
        },
        init() {
            @this.on('confirm-maintenance-toggle', () => this.confirm());
        }
    }" class="lk-wrap">
        <x-filament::section>
            <x-slot name="heading">Aplikasi</x-slot>
            <x-slot name="description">Status: {{ $maintenance ? 'MODE PERAWATAN (tutup untuk umum)' : 'Live normal' }}.</x-slot>

            <x-filament::button wire:click="$dispatch('confirm-maintenance-toggle')" :color="$maintenance ? 'success' : 'danger'" icon="heroicon-o-wrench">
                {{ $maintenance ? 'Matikan Mode Perawatan' : 'Nyalakan Mode Perawatan' }}
            </x-filament::button>

            <!-- Confirmation Modal for Maintenance Toggle -->
            <div x-show="confirmMaintenance" x-transition x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="confirmMaintenance" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50"></div>
                    <div x-show="confirmMaintenance" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-xl p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center">
                                <x-filament::icon icon="heroicon-o-wrench" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $maintenance ? 'Matikan Mode Perawatan?' : 'Nyalakan Mode Perawatan?' }}
                            </h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">
                            {{ $maintenance
                                ? 'Mode perawatan akan dimatikan dan aplikasi akan tersedia untuk semua pengguna. Lanjutkan?'
                                : 'Aplikasi akan masuk mode perawatan. Semua pengguna kecuali superadmin tidak bisa mengakses aplikasi. Lanjutkan?' }}
                        </p>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="confirmMaintenance = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="button" @click="executeMaintenance" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors">
                                {{ $maintenance ? 'Ya, Matikan' : 'Ya, Nyalakan' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">API (untuk integrasi luar)</x-slot>
            <x-slot name="description">Token milik akunmu. Sertakan sebagai header Authorization: Bearer &lt;token&gt;.</x-slot>

            <div class="lk-grid lk-grid-2">
                <div class="lk-field">
                    <label>Nama token baru</label>
                    <input wire:model="newTokenName" placeholder="cth: hp-kiosk-1" class="lk-input" />
                </div>
                <div class="lk-field">
                    <label>&nbsp;</label>
                    <x-filament::button wire:click="createApiToken" icon="heroicon-o-key">
                        Buat Token
                    </x-filament::button>
                </div>
            </div>
            @if($newTokenPlain)
            <div class="lk-btnrow">
                <code class="lk-mono" style="user-select: all;">{{ $newTokenPlain }}</code>
            </div>
            @endif
            <div class="lk-tablewrap" style="margin-top: 1rem;">
                <table class="lk-table">
                    <thead>
                        <tr><th>Nama</th><th>Dibuat</th><th>Terakhir dipakai</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($tokens as $token)
                        <tr>
                            <td>{{ $token->name }}</td>
                            <td>{{ $token->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $token->last_used_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td><x-filament::button wire:click="revokeApiToken({{ $token->id }})" color="danger" size="sm">Cabut</x-filament::button></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="lk-empty">Belum ada token.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Rekap Harian Otomatis</x-slot>
            <x-slot name="description">Jadwal aktif: setiap hari pukul {{ $scheduleTime }} WIB. Saat ini ada {{ $unreturnedCount }} unit belum kembali.</x-slot>

            <x-filament::button wire:click="sendNow" icon="heroicon-o-paper-airplane">
                Kirim Rekap Sekarang
            </x-filament::button>
        </x-filament::section>

        <form wire:submit="save">
            <x-filament::section>
                <x-slot name="heading">Jadwal</x-slot>

                <div class="lk-grid lk-grid-2">
                    <label class="lk-check">
                        <input type="checkbox" wire:model="form.recap_enabled" />
                        <span>Kirim otomatis tiap hari</span>
                    </label>
                    <div class="lk-field">
                        <label>Jam kirim (WIB)</label>
                        <input type="time" wire:model="form.recap_time" class="lk-input" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">WhatsApp via GOWA</x-slot>
                <x-slot name="description">Prioritas utama. Isi base URL GOWA + target nomor/grup, lalu Tes Koneksi.</x-slot>

                <div class="lk-grid lk-grid-2">
                    <label class="lk-check lk-span">
                        <input type="checkbox" wire:model="form.gowa_enabled" />
                        <span>Aktifkan kirim via GOWA</span>
                    </label>
                    <div class="lk-field lk-span">
                        <label>Base URL GOWA</label>
                        <input wire:model="form.gowa_base_url" placeholder="http://127.0.0.1:3000" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field">
                        <label>Basic Auth User <span class="lk-opt">(opsional)</span></label>
                        <input wire:model="form.gowa_user" class="lk-input" />
                    </div>
                    <div class="lk-field">
                        <label>Basic Auth Password <span class="lk-opt">(opsional)</span></label>
                        <input type="password" wire:model="form.gowa_pass" class="lk-input" />
                    </div>
                    <div class="lk-field lk-span">
                        <label>Target (nomor 628.. / JID grup ....@g.us)</label>
                        <input wire:model="form.gowa_target" placeholder="6281234567890" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field lk-span">
                        <label>Relay URL <span class="lk-opt">(opsional — bila GOWA tidak terjangkau langsung dari server)</span></label>
                        <input wire:model="form.gowa_relay_url" placeholder="http://100.64.0.10:8099" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field lk-span">
                        <label>Relay Secret</label>
                        <input type="password" wire:model="form.gowa_relay_secret" class="lk-input lk-mono" />
                    </div>
                </div>
                <div class="lk-btnrow">
                    <x-filament::button wire:click="testGowa" color="gray" icon="heroicon-o-signal">
                        Tes Koneksi GOWA
                    </x-filament::button>
                    <input wire:model="testTarget" placeholder="Nomor tes (kosongkan = pakai Target)" class="lk-input lk-mono" style="max-width: 19rem;" />
                    <x-filament::button wire:click="sendTestWa" color="gray" icon="heroicon-o-chat-bubble-left-right">
                        Kirim Pesan Tes
                    </x-filament::button>
                    @if($gowaTest)<span class="lk-note">{{ $gowaTest }}</span>@endif
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Webhook Umum</x-slot>
                <x-slot name="description">POST JSON {event, generated_at, total, items} ke URL apa pun (n8n, bot sendiri, dll).</x-slot>

                <div class="lk-grid lk-grid-2">
                    <label class="lk-check lk-span">
                        <input type="checkbox" wire:model="form.webhook_enabled" />
                        <span>Aktifkan webhook</span>
                    </label>
                    <div class="lk-field lk-span">
                        <label>Webhook URL</label>
                        <input wire:model="form.webhook_url" placeholder="https://..." class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field">
                        <label>Cara kirim secret</label>
                        <select wire:model="form.webhook_auth" class="lk-input">
                            <option value="header">Header X-Webhook-Secret</option>
                            <option value="bearer">Bearer token</option>
                            <option value="basic">Basic auth (user + password)</option>
                            <option value="query">Query param ?secret=</option>
                            <option value="none">Tanpa secret</option>
                        </select>
                    </div>
                    <div class="lk-field">
                        <label>Secret / Token</label>
                        <input wire:model="form.webhook_secret" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field">
                        <label>Basic User <span class="lk-opt">(bila mode basic)</span></label>
                        <input wire:model="form.webhook_user" class="lk-input" />
                    </div>
                    <div class="lk-field">
                        <label>Basic Password</label>
                        <input type="password" wire:model="form.webhook_pass" class="lk-input" />
                    </div>
                </div>
                <div class="lk-btnrow">
                    <x-filament::button wire:click="testWebhook" color="gray" icon="heroicon-o-signal">
                        Tes Webhook
                    </x-filament::button>
                    @if($webhookTest)<span class="lk-note">{{ $webhookTest }}</span>@endif
                </div>
            </x-filament::section>

            <div style="display: flex; justify-content: flex-end;">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Simpan Semua Pengaturan
                </x-filament::button>
            </div>
        </form>

        <x-filament::section>
            <x-slot name="heading">Riwayat Pengiriman (10 terakhir)</x-slot>

            <div class="lk-tablewrap">
                <table class="lk-table">
                    <thead>
                        <tr><th>Waktu</th><th>Kanal</th><th>Target</th><th>Unit</th><th>Status</th><th>Respon</th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td style="white-space: nowrap;">{{ $log->created_at->format('d M H:i') }}</td>
                            <td>{{ $log->channel }}</td>
                            <td class="lk-mono">{{ $log->target }}</td>
                            <td>{{ $log->total }}</td>
                            <td>{{ $log->status }}</td>
                            <td class="lk-mono" style="color: #6b7280;">{{ \Illuminate\Support\Str::limit($log->response ?? '', 60) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="lk-empty">Belum ada pengiriman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
