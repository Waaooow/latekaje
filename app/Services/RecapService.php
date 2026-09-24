<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\RecapLog;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class RecapService
{
    /** Daftar pinjaman aktif (belum kembali), terlama dulu. */
    public static function unreturned(): array
    {
        $now = Carbon::now();

        return Loan::query()
            ->with(['assetItem.asset'])
            ->where('status', 'aktif')
            ->orderBy('tanggal_pinjam')
            ->get()
            ->map(fn (Loan $loan) => [
                'loan_id' => $loan->id,
                'qr' => $loan->assetItem?->nomor_seri_atau_qr ?? '(unit dihapus)',
                'alat' => $loan->assetItem?->asset?->nama_alat ?? '-',
                'borrower' => $loan->borrower_name,
                'group' => $loan->group,
                'tanggal_pinjam' => $loan->tanggal_pinjam,
                'hari' => max(0, $now->diffInDays($loan->tanggal_pinjam)),
            ])
            ->all();
    }

    public static function formatWa(array $items): string
    {
        $now = Carbon::now()->locale(app()->getLocale());
        $lines = [];
        $lines[] = __('recap.wa_title');
        $lines[] = $now->translatedFormat('l, d M Y H:i').' '.__('recap.wa_datetime_suffix');
        $lines[] = __('recap.wa_total', ['count' => count($items)]);
        $lines[] = '';

        foreach ($items as $i => $it) {
            $tgl = $it['tanggal_pinjam'] ? Carbon::parse($it['tanggal_pinjam'])->translatedFormat('d M H:i') : '-';
            $lama = $it['hari'] === 0 ? __('recap.wa_today') : __('recap.wa_days_ago', ['count' => $it['hari']]);
            $lines[] = ($i + 1).'. '.$it['qr'].' ('.$it['alat'].')';
            $lines[] = '   '.$it['borrower'].' — '.$it['group'];
            $lines[] = '   '.__('recap.wa_borrowed_line', ['lama' => $lama, 'tgl' => $tgl]);
        }

        if ($items === []) {
            $lines[] = __('recap.wa_all_returned');
        }

        return implode("\n", $lines);
    }

    /**
     * Kirim rekap ke kanal yang aktif. Return ringkasan per kanal.
     */
    public static function sendNow(): array
    {
        $items = self::unreturned();
        $out = [];

        if (Setting::boolean('gowa_enabled') && Setting::get('gowa_base_url') && Setting::get('gowa_target')) {
            $target = (string) Setting::get('gowa_target');
            $res = GowaClient::fromSettings()->sendMessage($target, self::formatWa($items));
            RecapLog::create([
                'channel' => 'gowa',
                'target' => $target,
                'total' => count($items),
                'status' => $res['ok'] ? 'ok' : 'fail',
                'response' => $res['status'].': '.$res['body'],
            ]);
            $out['gowa'] = $res['ok'] ? __('recap.gowa_sent_to', ['target' => $target]) : __('recap.gowa_send_fail', ['body' => $res['body']]);
        }

        if (Setting::boolean('webhook_enabled') && Setting::get('webhook_url')) {
            $url = (string) Setting::get('webhook_url');
            $res = WebhookClient::post($url, [
                'event' => 'recap.unreturned',
                'generated_at' => Carbon::now()->toDateTimeString(),
                'total' => count($items),
                'items' => $items,
            ]);
            RecapLog::create([
                'channel' => 'webhook',
                'target' => $url,
                'total' => count($items),
                'status' => $res['ok'] ? 'ok' : 'fail',
                'response' => $res['status'].': '.$res['body'],
            ]);
            $out['webhook'] = $res['ok'] ? __('recap.webhook_sent') : __('recap.webhook_send_fail', ['status' => $res['status'], 'body' => $res['body']]);
        }

        if ($out === []) {
            $out['info'] = __('recap.no_channel');
        }

        return ['total' => count($items), 'channels' => $out];
    }
}
