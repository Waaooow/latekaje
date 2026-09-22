<?php

namespace App\Filament\Pages;

use App\Models\RecapLog;
use App\Models\Setting;
use App\Services\GowaClient;
use App\Services\RecapService;
use App\Services\WebhookClient;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class NotificationSettings extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notifikasi';

    protected static ?string $title = 'Notifikasi';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.notification-settings';

    public array $form = [];

    public string $gowaTest = '';

    public string $webhookTest = '';

    public string $testTarget = '';

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
            'gowa_relay_url' => Setting::get('gowa_relay_url', ''),
            'gowa_relay_secret' => Setting::get('gowa_relay_secret', ''),
            'webhook_enabled' => Setting::boolean('webhook_enabled', false),
            'webhook_url' => Setting::get('webhook_url', ''),
            'webhook_auth' => Setting::get('webhook_auth', 'header'),
            'webhook_secret' => Setting::get('webhook_secret', ''),
            'webhook_user' => Setting::get('webhook_user', ''),
            'webhook_pass' => Setting::get('webhook_pass', ''),
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

    public function sendTestWa(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->save();

        $target = trim($this->testTarget) !== '' ? trim($this->testTarget) : (string) Setting::get('gowa_target', '');

        if ($target === '') {
            $this->gowaTest = 'Isi Nomor Tes dulu.';
            return;
        }

        $res = GowaClient::fromSettings()->sendMessage(
            $target,
            'Tes LATEKAJE OK — '.now()->format('d M Y H:i').'. Balas pesan ini bila diterima.'
        );
        $this->gowaTest = $res['ok']
            ? 'Pesan tes terkirim ke '.$target.'! (HTTP '.$res['status'].')'
            : 'Gagal kirim ke '.$target.': '.$res['body'];

        Notification::make()
            ->title($res['ok'] ? 'Pesan tes terkirim' : 'Pesan tes gagal')
            ->body(mb_substr($this->gowaTest, 0, 200))
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

        $res = WebhookClient::post($url, ['event' => 'recap.test', 'message' => 'Tes koneksi webhook LATEKAJE']);
        $this->webhookTest = $res['ok']
            ? 'Terkirim! (HTTP '.$res['status'].')'
            : 'Gagal (HTTP '.$res['status'].'): '.$res['body'];
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
