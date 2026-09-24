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

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('recap.console_desc'));
    }

    public function handle(): int
    {
        $items = RecapService::unreturned();

        if (! $this->option('send')) {
            $this->info(__('recap.console_list', ['count' => count($items)]));
            foreach ($items as $it) {
                $this->line('- '.$it['qr'].' | '.$it['borrower'].' ('.$it['group'].') | '.$it['hari'].' hari');
            }

            return self::SUCCESS;
        }

        if (! Setting::boolean('recap_enabled', true) && ! $this->option('force')) {
            $this->warn(__('recap.console_disabled'));

            return self::SUCCESS;
        }

        $result = RecapService::sendNow();
        $this->info(__('recap.console_total', ['total' => $result['total']]));
        foreach ($result['channels'] as $ch => $msg) {
            $this->line("[$ch] $msg");
        }

        return self::SUCCESS;
    }
}
