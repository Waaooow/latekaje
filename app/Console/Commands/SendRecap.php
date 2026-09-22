<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\RecapService;
use Illuminate\Console\Command;

class SendRecap extends Command
{
    protected $signature = 'recap:unreturned
        {--send : kirim ke kanal aktif (default: tampilkan saja)}
        {--force : abaikan saklar recap_enabled}';

    protected $description = 'Rekap alat belum kembali (pinjaman aktif)';

    public function handle(): int
    {
        $items = RecapService::unreturned();

        if (! $this->option('send')) {
            $this->info('Belum kembali: '.count($items).' unit');
            foreach ($items as $it) {
                $this->line('- '.$it['qr'].' | '.$it['peminjam'].' ('.$it['kelas'].') | '.$it['hari'].' hari');
            }

            return self::SUCCESS;
        }

        if (! Setting::boolean('recap_enabled', true) && ! $this->option('force')) {
            $this->warn('Rekap otomatis nonaktif. Nyalakan di halaman Notifikasi atau pakai --force.');

            return self::SUCCESS;
        }

        $result = RecapService::sendNow();
        $this->info('Total: '.$result['total'].' unit');
        foreach ($result['channels'] as $ch => $msg) {
            $this->line("[$ch] $msg");
        }

        return self::SUCCESS;
    }
}
