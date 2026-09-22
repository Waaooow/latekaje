<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Rekap Harian Otomatis</x-slot>
            <x-slot name="description">Jadwal aktif: setiap hari pukul {{ $scheduleTime }} WIB. Saat ini ada {{ $unreturnedCount }} unit belum kembali.</x-slot>

            <x-filament::button wire:click="sendNow" icon="heroicon-o-paper-airplane">
                Kirim Rekap Sekarang
            </x-filament::button>
        </x-filament::section>

        <form wire:submit="save" class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">Jadwal</x-slot>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm">
                        <x-filament::input.checkbox wire:model="form.recap_enabled" />
                        Kirim otomatis tiap hari
                    </label>
                    <label class="text-sm">Jam kirim (WIB)
                        <x-filament::input type="time" wire:model="form.recap_time" class="mt-1 w-full" />
                    </label>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">WhatsApp via GOWA</x-slot>
                <x-slot name="description">Prioritas utama. Isi base URL GOWA + target nomor/grup, lalu Tes Koneksi.</x-slot>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm md:col-span-2">
                        <x-filament::input.checkbox wire:model="form.gowa_enabled" />
                        Aktifkan kirim via GOWA
                    </label>
                    <label class="text-sm md:col-span-2">Base URL GOWA
                        <x-filament::input wire:model="form.gowa_base_url" placeholder="http://127.0.0.1:3000" class="mt-1 w-full font-mono" />
                    </label>
                    <label class="text-sm">Basic Auth User (opsional)
                        <x-filament::input wire:model="form.gowa_user" class="mt-1 w-full" />
                    </label>
                    <label class="text-sm">Basic Auth Password (opsional)
                        <x-filament::input type="password" wire:model="form.gowa_pass" class="mt-1 w-full" />
                    </label>
                    <label class="text-sm md:col-span-2">Target (nomor 628.. / JID grup ....@g.us)
                        <x-filament::input wire:model="form.gowa_target" placeholder="6281234567890" class="mt-1 w-full font-mono" />
                    </label>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <x-filament::button wire:click="testGowa" color="gray" icon="heroicon-o-signal">
                        Tes Koneksi GOWA
                    </x-filament::button>
                    @if($gowaTest)<span class="text-sm text-gray-500">{{ $gowaTest }}</span>@endif
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Webhook Umum</x-slot>
                <x-slot name="description">POST JSON {event, generated_at, total, items} ke URL apa pun (n8n, bot sendiri, dll).</x-slot>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm md:col-span-2">
                        <x-filament::input.checkbox wire:model="form.webhook_enabled" />
                        Aktifkan webhook
                    </label>
                    <label class="text-sm md:col-span-2">Webhook URL
                        <x-filament::input wire:model="form.webhook_url" placeholder="https://..." class="mt-1 w-full font-mono" />
                    </label>
                    <label class="text-sm md:col-span-2">Secret (header X-Webhook-Secret, opsional)
                        <x-filament::input wire:model="form.webhook_secret" class="mt-1 w-full font-mono" />
                    </label>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <x-filament::button wire:click="testWebhook" color="gray" icon="heroicon-o-signal">
                        Tes Webhook
                    </x-filament::button>
                    @if($webhookTest)<span class="text-sm text-gray-600">{{ $webhookTest }}</span>@endif
                </div>
            </x-filament::section>

            <x-filament::button type="submit" icon="heroicon-o-check">
                Simpan Semua Pengaturan
            </x-filament::button>
        </form>

        <x-filament::section>
            <x-slot name="heading">Riwayat Pengiriman (10 terakhir)</x-slot>

            <div class="overflow-x-auto text-sm">
                <table class="w-full">
                    <thead class="text-left text-xs uppercase text-gray-400">
                        <tr><th class="py-2 pr-3">Waktu</th><th class="py-2 pr-3">Kanal</th><th class="py-2 pr-3">Target</th><th class="py-2 pr-3">Unit</th><th class="py-2 pr-3">Status</th><th class="py-2">Respon</th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="py-2 pr-3">{{ $log->created_at->format('d M H:i') }}</td>
                            <td class="py-2 pr-3">{{ $log->channel }}</td>
                            <td class="py-2 pr-3 font-mono text-xs">{{ $log->target }}</td>
                            <td class="py-2 pr-3">{{ $log->total }}</td>
                            <td class="py-2 pr-3">{{ $log->status }}</td>
                            <td class="py-2 font-mono text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($log->response ?? '', 60) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-3 text-gray-400">Belum ada pengiriman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
