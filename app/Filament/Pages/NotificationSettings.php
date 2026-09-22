<?php

namespace App\Filament\Pages;

use App\Models\RecapLog;
use App\Models\Setting;
use App\Services\GowaClient;
use App\Services\RecapService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class NotificationSettings extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notifikasi';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.notification-settings';

    public array $form = [];

    public string $gowaTest = '';

    public string $webhookTest = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperadmin() || $user->isToolman());
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);

        $this->form = [
            'recap_enabled' => Setting::boolean('recap_enabled', true),
            'recap_time' => Setting::get('recap_time', '16:00'),
            'gowa_enabled' => Setting::boolean('gowa_enabled', false),
            'gowa_base_url' => Setting::get('gowa_base_url', ''),
            'gowa_user' => Setting::get('gowa_user', ''),
            'gowa_pass' => Setting::get('gowa_pass', ''),
            'gowa_target' => Setting::get('gowa_target', ''),
            'webhook_enabled' => Setting::boolean('webhook_enabled', false),
            'webhook_url' => Setting::get('webhook_url', ''),
            'webhook_secret' => Setting::get('webhook_secret', ''),
        ];
    }

    public function save(): void
    {
        abort_unless(self::canAccess(), 403);

        foreach ($this->form as $key => $value) {
            Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : $value);
        }

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }

    public function testGowa(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->save();

        $res = GowaClient::fromSettings()->devices();
        $this->gowaTest = $res['ok']
            ? 'Terhubung! (HTTP '.$res['status'].')'
            : 'Gagal: '.$res['body'];

        Notification::make()
            ->title($res['ok'] ? 'GOWA terhubung' : 'GOWA gagal')
            ->body(mb_substr($res['body'], 0, 200))
            ->{ $res['ok'] ? 'success' : 'danger' }()
            ->send();
    }

    public function testWebhook(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->save();

        $url = (string) Setting::get('webhook_url', '');
        if ($url === '') {
            $this->webhookTest = 'Isi webhook URL dulu.';
            return;
        }

        try {
            $secret = (string) Setting::get('webhook_secret', '');
            $req = Http::timeout(15)->acceptJson();
            if ($secret !== '') {
                $req = $req->withHeaders(['X-Webhook-Secret' => $secret]);
            }
            $res = $req->post($url, ['event' => 'recap.test', 'message' => 'Tes koneksi webhook LATEKAJE']);
            $this->webhookTest = $res->successful()
                ? 'Terkirim! (HTTP '.$res->status().')'
                : 'Gagal (HTTP '.$res->status().'): '.mb_substr((string) $res->body(), 0, 150);
        } catch (\Throwable $e) {
            $this->webhookTest = 'Gagal: '.mb_substr($e->getMessage(), 0, 150);
        }
    }

    public function sendNow(): void
    {
        abort_unless(self::canAccess(), 403);

        $result = RecapService::sendNow();

        Notification::make()
            ->title('Rekap dikirim ('.$result['total'].' unit)')
            ->body(implode(' | ', array_map(fn ($k, $v) => "[$k] $v", array_keys($result['channels']), $result['channels'])))
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        return [
            'logs' => RecapLog::latest()->limit(10)->get(),
            'unreturnedCount' => \App\Models\Loan::where('status', 'aktif')->count(),
            'scheduleTime' => \App\Models\Setting::get('recap_time', '16:00'),
        ];
    }
}
