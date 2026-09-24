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
use Filament\Support\Icons\Heroicon;

class NotificationSettings extends Page
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function getNavigationLabel(): string
    {
        return __('settings.nav_label');
    }

    protected static ?string $slug = 'setting';

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('settings.title');
    }

    protected static ?int $navigationSort = 31;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('common.nav_group_system');
    }

    protected string $view = 'filament.pages.notification-settings';

    public array $form = [];

    public string $gowaTest = '';

    public string $webhookTest = '';

    public string $testTarget = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperadmin() || $user->isAdmin());
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);

        $this->form = [
            'app_locale' => Setting::get('app_locale', config('app.locale', 'id')),
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

        if (isset($this->form['app_locale']) && ! in_array($this->form['app_locale'], \App\Http\Middleware\SetLocale::SUPPORTED, true)) {
            $this->form['app_locale'] = config('app.locale', 'id');
        }

        foreach ($this->form as $key => $value) {
            Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : $value);
        }

        app()->setLocale(\App\Http\Middleware\SetLocale::resolve());

        Notification::make()->title(__('settings.saved'))->success()->send();
    }

    public function testGowa(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->save();

        $res = GowaClient::fromSettings()->devices();
        $this->gowaTest = $res['ok']
            ? __('settings.gowa_connected', ['status' => $res['status']])
            : __('settings.gowa_failed', ['body' => $res['body']]);

        Notification::make()
            ->title($res['ok'] ? __('settings.gowa_ok_title') : __('settings.gowa_fail_title'))
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
            $this->gowaTest = __('settings.fill_test_number');
            return;
        }

        $res = GowaClient::fromSettings()->sendMessage(
            $target,
            __('settings.test_wa_body', ['time' => now()->format('d M Y H:i')])
        );
        $this->gowaTest = $res['ok']
            ? __('settings.test_sent_to', ['target' => $target, 'status' => $res['status']])
            : __('settings.test_send_fail', ['target' => $target, 'body' => $res['body']]);

        Notification::make()
            ->title($res['ok'] ? __('settings.test_sent_title') : __('settings.test_failed_title'))
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
            $this->webhookTest = __('settings.fill_webhook');
            return;
        }

        $res = WebhookClient::post($url, ['event' => 'recap.test', 'message' => __('settings.webhook_test_message')]);
        $this->webhookTest = $res['ok']
            ? __('settings.webhook_sent', ['status' => $res['status']])
            : __('settings.webhook_failed', ['status' => $res['status'], 'body' => $res['body']]);
    }

    public string $apiTest = '';

    public string $newTokenName = '';

    public ?string $newTokenPlain = null;

    public function toggleMaintenance(): void
    {
        abort_unless(self::canAccess(), 403);

        if (app()->isDownForMaintenance()) {
            \Illuminate\Support\Facades\Artisan::call('up');

            Notification::make()->title(__('settings.maintenance_off'))->success()->send();
        } else {
            \Illuminate\Support\Facades\Artisan::call('down', ['--render' => 'errors::503']);

            Notification::make()->title(__('settings.maintenance_on'))->warning()->persistent()->send();
        }
    }

    public function createApiToken(): void
    {
        abort_unless(auth()->user()?->isSuperadmin(), 403);

        $name = trim($this->newTokenName);

        if ($name === '') {
            Notification::make()->title(__('settings.token_name_required'))->danger()->send();

            return;
        }

        $token = auth()->user()->createToken($name, ['*']);
        $this->newTokenPlain = $token->plainTextToken;
        $this->newTokenName = '';

        Notification::make()->title(__('settings.token_created'))->success()->persistent()->send();
    }

    public function revokeApiToken(int $id): void
    {
        abort_unless(auth()->user()?->isSuperadmin(), 403);

        auth()->user()->tokens()->whereKey($id)->delete();

        Notification::make()->title(__('settings.token_revoked'))->success()->send();
    }

    public function sendNow(): void
    {
        abort_unless(self::canAccess(), 403);

        $result = RecapService::sendNow();

        Notification::make()
            ->title(__('recap.sent_title', ['total' => $result['total']]))
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
            'maintenance' => app()->isDownForMaintenance(),
            'tokens' => auth()->user()?->isSuperadmin() ? auth()->user()->tokens()->latest()->get() : collect(),
        ];
    }
}
