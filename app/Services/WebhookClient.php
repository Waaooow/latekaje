<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class WebhookClient
{
    /**
     * POST JSON ke webhook dengan mode auth dari pengaturan.
     * Return [ok, status, body].
     */
    public static function post(string $url, array $payload): array
    {
        $mode = (string) Setting::get('webhook_auth', 'header');
        $secret = (string) Setting::get('webhook_secret', '');

        try {
            $req = Http::timeout(15)->acceptJson();

            if ($mode === 'bearer' && $secret !== '') {
                $req = $req->withToken($secret);
            } elseif ($mode === 'basic') {
                $req = $req->withBasicAuth(
                    (string) Setting::get('webhook_user', ''),
                    (string) Setting::get('webhook_pass', '')
                );
            } elseif ($mode === 'query' && $secret !== '') {
                $url .= (str_contains($url, '?') ? '&' : '?').'secret='.urlencode($secret);
            } elseif ($secret !== '') {
                $req = $req->withHeaders(['X-Webhook-Secret' => $secret]);
            }

            $res = $req->post($url, $payload);

            return ['ok' => $res->successful(), 'status' => $res->status(), 'body' => mb_substr((string) $res->body(), 0, 300)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => mb_substr($e->getMessage(), 0, 300)];
        }
    }
}
