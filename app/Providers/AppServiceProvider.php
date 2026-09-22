<?php

namespace App\Providers;

use App\Auth\NisUserProvider;
use App\Models\User;
use App\Support\Acl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // 🟢 Wajib import class URL ini di atas!

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Auth::provider('nis-eloquent', fn ($app, array $config) => new NisUserProvider($app['hash'], $config['model']));
        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability, array $arguments) {
            if ($user->isSuperadmin()) {
                return true;
            }

            $perms = $user->permissions ?? [];
            $key = Acl::key($ability, $arguments[0] ?? null);

            if (in_array($key, $perms['deny'] ?? [], true)) {
                return false;
            }

            if (in_array($key, $perms['allow'] ?? [], true)) {
                return true;
            }

            return null;
        });

        // 🟢 Paksa semua asset, link, dan request AJAX (Livewire) menggunakan HTTPS di server
        if (config('app.env') === 'production' || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}