<x-filament-panels::page>
    <div class="lk-wrap">
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
