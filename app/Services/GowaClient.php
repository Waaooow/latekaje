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

    /** Cek koneksi + login (GET /app/devices). */
    public function devices(): array
    {
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
}
