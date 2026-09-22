<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GowaClient
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly ?string $user = null,
        public readonly ?string $pass = null,
    ) {}

    public static function fromSettings(): self
    {
        return new self(
            (string) \App\Models\Setting::get('gowa_base_url', ''),
            (string) \App\Models\Setting::get('gowa_user', '') ?: null,
            (string) \App\Models\Setting::get('gowa_pass', '') ?: null,
        );
    }

    public function url(string $path): string
    {
        return rtrim($this->baseUrl, '/').$path;
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $req = Http::timeout(15)->acceptJson();

        if ($this->user) {
            $req = $req->withBasicAuth($this->user, (string) $this->pass);
        }

        return $req;
    }

    /** Cek koneksi + login. Lewat relay bila dikonfigurasi. */
    public function devices(): array
    {
        if ($relay = self::relayConfig()) {
            try {
                $res = Http::timeout(15)->acceptJson()
                    ->withHeaders(['X-Relay-Secret' => $relay['secret']])
                    ->get(rtrim($relay['url'], '/').'/devices');

                $data = $res->json() ?? [];
                $ok = $res->successful() && ($data['ok'] ?? false);

                return ['ok' => $ok, 'status' => $res->status(), 'body' => mb_substr((string) ($data['body'] ?? $res->body()), 0, 500)];
            } catch (\Throwable $e) {
                return ['ok' => false, 'status' => 0, 'body' => $e->getMessage()];
            }
        }

        try {
            $res = $this->client()->get($this->url('/app/devices'));

            return ['ok' => $res->successful(), 'status' => $res->status(), 'body' => mb_substr((string) $res->body(), 0, 500)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => $e->getMessage()];
        }
    }

    /** Kirim pesan teks. $target = nomor (628..) atau JID grup. */
    public function sendMessage(string $target, string $message): array
    {
        if ($relay = self::relayConfig()) {
            try {
                $res = Http::timeout(20)->acceptJson()->post(rtrim($relay['url'], '/').'/send', [
                    'secret' => $relay['secret'],
                    'target' => $target,
                    'message' => $message,
                ]);

                $data = $res->json() ?? [];
                $ok = $res->successful() && ($data['ok'] ?? false);

                return ['ok' => $ok, 'status' => $res->status(), 'body' => mb_substr((string) ($data['body'] ?? $res->body()), 0, 500)];
            } catch (\Throwable $e) {
                return ['ok' => false, 'status' => 0, 'body' => $e->getMessage()];
            }
        }

        try {
            $res = $this->client()->post($this->url('/send/message'), [
                'phone' => $target,
                'message' => $message,
            ]);

            return ['ok' => $res->successful(), 'status' => $res->status(), 'body' => mb_substr((string) $res->body(), 0, 500)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => $e->getMessage()];
        }
    }

    /** [url, secret] bila mode relay aktif, else null. */
    private static function relayConfig(): ?array
    {
        $url = (string) \App\Models\Setting::get('gowa_relay_url', '');

        if ($url === '') {
            return null;
        }

        return ['url' => $url, 'secret' => (string) \App\Models\Setting::get('gowa_relay_secret', '')];
    }
}
