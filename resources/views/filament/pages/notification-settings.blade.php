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

                <div class="grid items-end gap-x-6 gap-y-5 md:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 text-sm">
                        <x-filament::input.checkbox wire:model="form.recap_enabled" />
                        <span>Kirim otomatis tiap hari</span>
                    </label>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Jam kirim (WIB)</label>
                        <x-filament::input type="time" wire:model="form.recap_time" class="w-full" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">WhatsApp via GOWA</x-slot>
                <x-slot name="description">Prioritas utama. Isi base URL GOWA + target nomor/grup, lalu Tes Koneksi.</x-slot>

                <div class="grid items-end gap-x-6 gap-y-5 md:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 text-sm md:col-span-2">
                        <x-filament::input.checkbox wire:model="form.gowa_enabled" />
                        <span>Aktifkan kirim via GOWA</span>
                    </label>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium">Base URL GOWA</label>
                        <x-filament::input wire:model="form.gowa_base_url" placeholder="http://127.0.0.1:3000" class="w-full font-mono" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Basic Auth User <span class="font-normal text-gray-400">(opsional)</span></label>
                        <x-filament::input wire:model="form.gowa_user" class="w-full" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Basic Auth Password <span class="font-normal text-gray-400">(opsional)</span></label>
                        <x-filament::input type="password" wire:model="form.gowa_pass" class="w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium">Target (nomor 628.. / JID grup ....@g.us)</label>
                        <x-filament::input wire:model="form.gowa_target" placeholder="6281234567890" class="w-full font-mono" />
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-filament::button wire:click="testGowa" color="gray" icon="heroicon-o-signal">
                        Tes Koneksi GOWA
                    </x-filament::button>
                    @if($gowaTest)<span class="text-sm text-gray-500">{{ $gowaTest }}</span>@endif
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Webhook Umum</x-slot>
                <x-slot name="description">POST JSON {event, generated_at, total, items} ke URL apa pun (n8n, bot sendiri, dll).</x-slot>

                <div class="grid items-end gap-x-6 gap-y-5 md:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 text-sm md:col-span-2">
                        <x-filament::input.checkbox wire:model="form.webhook_enabled" />
                        <span>Aktifkan webhook</span>
                    </label>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium">Webhook URL</label>
                        <x-filament::input wire:model="form.webhook_url" placeholder="https://..." class="w-full font-mono" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium">Secret <span class="font-normal text-gray-400">(header X-Webhook-Secret, opsional)</span></label>
                        <x-filament::input wire:model="form.webhook_secret" class="w-full font-mono" />
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-filament::button wire:click="testWebhook" color="gray" icon="heroicon-o-signal">
                        Tes Webhook
                    </x-filament::button>
                    @if($webhookTest)<span class="text-sm text-gray-600">{{ $webhookTest }}</span>@endif
                </div>
            </x-filament::section>

            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Simpan Semua Pengaturan
                </x-filament::button>
            </div>
        </form>

        <x-filament::section>
            <x-slot name="heading">Riwayat Pengiriman (10 terakhir)</x-slot>

            <div class="-mx-1 overflow-x-auto text-sm">
                <table class="w-full">
                    <thead class="text-left text-xs uppercase text-gray-400">
                        <tr><th class="px-3 py-2">Waktu</th><th class="px-3 py-2">Kanal</th><th class="px-3 py-2">Target</th><th class="px-3 py-2">Unit</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Respon</th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="whitespace-nowrap px-3 py-2.5">{{ $log->created_at->format('d M H:i') }}</td>
                            <td class="px-3 py-2.5">{{ $log->channel }}</td>
                            <td class="px-3 py-2.5 font-mono text-xs">{{ $log->target }}</td>
                            <td class="px-3 py-2.5">{{ $log->total }}</td>
                            <td class="px-3 py-2.5">{{ $log->status }}</td>
                            <td class="px-3 py-2.5 font-mono text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($log->response ?? '', 60) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-3 py-4 text-gray-400">Belum ada pengiriman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
