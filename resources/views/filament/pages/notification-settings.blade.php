<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold">Rekap Harian Otomatis</h3>
                    <p class="text-sm text-gray-500">Saat ini ada <strong>{{ $unreturnedCount }} unit</strong> belum kembali. Cron jalan tiap menit, kirim tiap jam yang dikonfigurasi.</p>
                </div>
                <x-filament::button wire:click="sendNow" icon="heroicon-o-paper-airplane">
                    Kirim Rekap Sekarang
                </x-filament::button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-1 font-semibold">Jadwal</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="form.recap_enabled" class="rounded" />
                        Kirim otomatis tiap hari
                    </label>
                    <label class="text-sm">Jam kirim (WIB)
                        <input type="time" wire:model="form.recap_time" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800" />
                    </label>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-1 font-semibold">WhatsApp via GOWA</h3>
                <p class="mb-3 text-xs text-gray-500">Prioritas utama. Isi base URL GOWA + target nomor/grup, lalu Tes Koneksi.</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm md:col-span-2">
                        <input type="checkbox" wire:model="form.gowa_enabled" class="rounded" />
                        Aktifkan kirim via GOWA
                    </label>
                    <label class="text-sm md:col-span-2">Base URL GOWA
                        <input type="text" wire:model="form.gowa_base_url" placeholder="http://127.0.0.1:3000" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm dark:border-gray-700 dark:bg-gray-800" />
                    </label>
                    <label class="text-sm">Basic Auth User (opsional)
                        <input type="text" wire:model="form.gowa_user" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800" />
                    </label>
                    <label class="text-sm">Basic Auth Password (opsional)
                        <input type="password" wire:model="form.gowa_pass" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800" />
                    </label>
                    <label class="text-sm md:col-span-2">Target (nomor 628.. / JID grup ....@g.us)
                        <input type="text" wire:model="form.gowa_target" placeholder="6281234567890" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm dark:border-gray-700 dark:bg-gray-800" />
                    </label>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <x-filament::button wire:click="testGowa" color="gray" icon="heroicon-o-signal">
                        Tes Koneksi GOWA
                    </x-filament::button>
                    @if($gowaTest)<span class="text-sm text-gray-600">{{ $gowaTest }}</span>@endif
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-1 font-semibold">Webhook Umum</h3>
                <p class="mb-3 text-xs text-gray-500">POST JSON {event, generated_at, total, items} ke URL apa pun (n8n, bot sendiri, dll).</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm md:col-span-2">
                        <input type="checkbox" wire:model="form.webhook_enabled" class="rounded" />
                        Aktifkan webhook
                    </label>
                    <label class="text-sm md:col-span-2">Webhook URL
                        <input type="text" wire:model="form.webhook_url" placeholder="https://..." class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm dark:border-gray-700 dark:bg-gray-800" />
                    </label>
                    <label class="text-sm md:col-span-2">Secret (header X-Webhook-Secret, opsional)
                        <input type="text" wire:model="form.webhook_secret" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm dark:border-gray-700 dark:bg-gray-800" />
                    </label>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <x-filament::button wire:click="testWebhook" color="gray" icon="heroicon-o-signal">
                        Tes Webhook
                    </x-filament::button>
                    @if($webhookTest)<span class="text-sm text-gray-600">{{ $webhookTest }}</span>@endif
                </div>
            </div>

            <x-filament::button type="submit" icon="heroicon-o-check">
                Simpan Semua Pengaturan
            </x-filament::button>
        </form>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-3 font-semibold">Riwayat Pengiriman (10 terakhir)</h3>
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
        </div>
    </div>
</x-filament-panels::page>
