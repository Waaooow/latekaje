<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureAdminUserExists; // 🌟 TAMBAHAN: Import middleware pencegat kita
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // 🌟 REVISI: Aktifkan secara permanen di tingkat Route agar tidak eror saat compile
            ->registration()

            // 🟢 TIGA BARIS SAKTI: Pasang Logo Baru & Favicon Bebas Versi
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.8rem')
            ->favicon(asset('images/favicon.png'))

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // KUNCI PERBAIKAN: Dikosongkan agar kotak Welcome & Info Filament bawaan hilang!
                // Widget buatan kita (AssetSummaryTable) akan otomatis masuk lewat baris discoverWidgets di atas.
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnsureAdminUserExists::class, // 🌟 REVISI: Suntikkan di sini agar bisa mencegat Guest/Login/Register
            ])
            ->authMiddleware([
                Authenticate::class, // Kembalikan authMiddleware hanya berisi Authenticate bawaan
            ])

            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn () => new HtmlString('
                    <footer class="w-full text-center py-4 text-xs text-gray-400 dark:text-gray-500 border-t border-gray-200 dark:border-gray-800 mt-6">
                        &copy; ' . date('Y') . ' <span class="font-semibold text-primary-500">LATEKAJE</span>. All Rights Reserved.
                        <span class="mx-1">|</span> Crafted with ❤️ by <span class="underline">Alfin & Gemini AI</span>
                    </footer>
                ')
            );
    }
}