<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\RecapLog;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

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
                'peminjam' => $loan->nama_siswa,
                'kelas' => $loan->kelas,
                'tanggal_pinjam' => $loan->tanggal_pinjam,
                'hari' => max(0, $now->diffInDays($loan->tanggal_pinjam)),
            ])
            ->all();
    }

    public static function formatWa(array $items): string
    {
        $now = Carbon::now()->locale('id');
        $lines = [];
        $lines[] = '*REKAP ALAT BELUM KEMBALI*';
        $lines[] = $now->translatedFormat('l, d M Y H:i').' WIB';
        $lines[] = 'Total: '.count($items).' unit';
        $lines[] = '';

        foreach ($items as $i => $it) {
            $tgl = $it['tanggal_pinjam'] ? Carbon::parse($it['tanggal_pinjam'])->translatedFormat('d M H:i') : '-';
            $lama = $it['hari'] === 0 ? 'hari ini' : $it['hari'].' hari lalu';
            $lines[] = ($i + 1).'. '.$it['qr'].' ('.$it['alat'].')';
            $lines[] = '   '.$it['peminjam'].' — '.$it['kelas'];
            $lines[] = '   Dipinjam '.$lama.' ('.$tgl.')';
        }

        if ($items === []) {
            $lines[] = 'Semua alat sudah kembali. Mantap!';
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
            $out['gowa'] = $res['ok'] ? 'terkirim ke '.$target : 'gagal ('.$res['body'].')';
        }

        if (Setting::boolean('webhook_enabled') && Setting::get('webhook_url')) {
            $url = (string) Setting::get('webhook_url');
            $secret = (string) Setting::get('webhook_secret', '');

            try {
                $req = Http::timeout(15)->acceptJson();
                if ($secret !== '') {
                    $req = $req->withHeaders(['X-Webhook-Secret' => $secret]);
                }
                $res = $req->post($url, [
                    'event' => 'recap.unreturned',
                    'generated_at' => Carbon::now()->toDateTimeString(),
                    'total' => count($items),
                    'items' => $items,
                ]);
                RecapLog::create([
                    'channel' => 'webhook',
                    'target' => $url,
                    'total' => count($items),
                    'status' => $res->successful() ? 'ok' : 'fail',
                    'response' => $res->status().': '.mb_substr((string) $res->body(), 0, 300),
                ]);
                $out['webhook'] = $res->successful() ? 'terkirim' : 'gagal ('.$res->status().')';
            } catch (\Throwable $e) {
                RecapLog::create([
                    'channel' => 'webhook',
                    'target' => $url,
                    'total' => count($items),
                    'status' => 'fail',
                    'response' => mb_substr($e->getMessage(), 0, 300),
                ]);
                $out['webhook'] = 'gagal ('.mb_substr($e->getMessage(), 0, 100).')';
            }
        }

        if ($out === []) {
            $out['info'] = 'Tidak ada kanal aktif. Aktifkan GOWA / webhook di halaman Notifikasi.';
        }

        return ['total' => count($items), 'channels' => $out];
    }
}
