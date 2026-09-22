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
            self::normalizeBase((string) \App\Models\Setting::get('gowa_base_url', '')),
            (string) \App\Models\Setting::get('gowa_user', '') ?: null,
            (string) \App\Models\Setting::get('gowa_pass', '') ?: null,
        );
    }

    /**
     * Amankan base URL: hanya scheme://host:port, buang path
     * yang tidak sengaja terketik (cth: ...:3000/se).
     */
    public static function normalizeBase(string $base): string
    {
        $base = trim($base);

        if ($base === '') {
            return '';
        }

        if (! preg_match('#^https?://#i', $base)) {
            $base = 'http://'.$base;
        }

        $parts = parse_url($base);

        if (empty($parts['host'])) {
            return trim($base, '/');
        }

        $out = ($parts['scheme'] ?? 'http').'://'.$parts['host'];

        if (! empty($parts['port'])) {
            $out .= ':'.$parts['port'];
        }

        return $out;
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
                $note = '[via relay '.rtrim($relay['url'], '/').'] ';

                return ['ok' => $ok, 'status' => $res->status(), 'body' => $note.mb_substr((string) ($data['body'] ?? $res->body()), 0, 400)];
            } catch (\Throwable $e) {
                return ['ok' => false, 'status' => 0, 'body' => '[via relay] '.$e->getMessage()];
            }
        }

        try {
            $res = $this->client()->get($this->url('/app/devices'));

            return ['ok' => $res->successful(), 'status' => $res->status(), 'body' => '[direct '.$this->baseUrl.'] '.mb_substr((string) $res->body(), 0, 400)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => '[direct '.$this->baseUrl.'] '.$e->getMessage()];
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

                return ['ok' => $ok, 'status' => $res->status(), 'body' => '[via relay] '.mb_substr((string) ($data['body'] ?? $res->body()), 0, 400)];
            } catch (\Throwable $e) {
                return ['ok' => false, 'status' => 0, 'body' => '[via relay] '.$e->getMessage()];
            }
        }

        try {
            $res = $this->client()->post($this->url('/send/message'), [
                'phone' => $target,
                'message' => $message,
            ]);

            return ['ok' => $res->successful(), 'status' => $res->status(), 'body' => '[direct] '.mb_substr((string) $res->body(), 0, 400)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => '[direct] '.$e->getMessage()];
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
