<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->profile()
            ->brandLogo('/images/logo.png')
            ->brandLogoHeight('2.8rem')
            ->favicon('/images/favicon.png')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => <<<'HTML'
                    <link rel="stylesheet" href="/css/latekaje.css?v=1" />
                    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
                    <style>
                    #reader-loan-inline, #reader-table-return { background: #000; }
                    </style>
                    <script>
                    window.latekajeQr = {
                        _pending: null,
                        _active: {},
                        ensureLib: function () {
                            if (window.Html5Qrcode) return Promise.resolve();
                            if (this._pending) return this._pending;
                            this._pending = new Promise(function (resolve, reject) {
                                var s = document.createElement('script');
                                s.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                                s.onload = function () { window.Html5Qrcode ? resolve() : reject(new Error('lib')); };
                                s.onerror = function () { reject(new Error('lib')); };
                                document.head.appendChild(s);
                            });
                            return this._pending;
                        }
                    };

                    function latekajeScanner(readerId, inputId) {
                        return {
                            scanner: null,
                            cameras: [],
                            cameraId: '',
                            status: 'Menyiapkan kamera…',
                            error: '',
                            locked: false,
                            observer: null,
                            _gen: 0,
                            async init() {
                                const gen = (++this._gen);
                                const alive = () => gen === this._gen;
                                try {
                                    const prev = window.latekajeQr._active[readerId];
                                    if (prev && prev !== this) { await prev.stopQuiet(); }
                                } catch (e) {}
                                window.latekajeQr._active[readerId] = this;
                                const el = document.getElementById(readerId);
                                if (el) el.innerHTML = '';
                                if (!window.isSecureContext) {
                                    this.status = '';
                                    this.error = 'Akses kamera membutuhkan HTTPS. Buka aplikasi lewat alamat https://';
                                    return;
                                }
                                try {
                                    await window.latekajeQr.ensureLib();
                                } catch (e) {
                                    this.status = '';
                                    this.error = 'Library scanner gagal dimuat. Periksa koneksi internet lalu tekan Scan Ulang.';
                                    return;
                                }
                                if (!alive()) return;
                                let cams = [];
                                try {
                                    cams = await Html5Qrcode.getCameras();
                                } catch (e) {
                                    this.status = '';
                                    this.error = 'Izin kamera ditolak atau tidak ada kamera. Izinkan akses kamera di browser (ikon kamera di address bar), lalu tekan Scan Ulang.';
                                    return;
                                }
                                if (!alive()) return;
                                if (!cams || !cams.length) {
                                    this.status = '';
                                    this.error = 'Tidak ada kamera yang ditemukan di perangkat ini. Ketik kode manual.';
                                    return;
                                }
                                this.cameras = cams;
                                const back = cams.find((c) => /back|rear|environment/i.test(c.label || ''));
                                this.cameraId = (back || cams[0]).id;
                                this.watchRemoval();
                                await this.start();
                            },
                            async start() {
                                const gen = this._gen;
                                this.error = '';
                                this.locked = false;
                                this.status = 'Membuka kamera…';
                                const elw = document.getElementById(readerId);
                                const box = Math.max(160, Math.min(250, (elw ? elw.clientWidth : 300) - 32));
                                try {
                                    this.scanner = new Html5Qrcode(readerId);
                                } catch (e) {
                                    this.status = '';
                                    this.error = 'Scanner gagal diinisialisasi.';
                                    return;
                                }
                                try {
                                    await this.scanner.start(
                                        this.cameraId,
                                        { fps: 10, qrbox: { width: box, height: box } },
                                        (txt) => this.onScan(txt),
                                        () => {}
                                    );
                                    if (gen !== this._gen) { await this.stopQuiet(); return; }
                                    this.status = 'Arahkan QR alat ke kamera…';
                                } catch (e) {
                                    if (gen !== this._gen) return;
                                    this.status = '';
                                    this.error = 'Kamera tidak bisa dibuka (' + ((e && e.message) || e) + ').';
                                }
                            },
                            async restart() { await this.stopQuiet(); await this.start(); },
                            async onScan(txt) {
                                if (this.locked) return;
                                this.locked = true;
                                const root = document.getElementById(inputId);
                                const input = root ? (root.tagName === 'INPUT' ? root : root.querySelector('input')) : null;
                                if (input) {
                                    input.focus();
                                    input.value = txt;
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                    input.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                                await this.stopQuiet();
                                this.status = input
                                    ? 'Terdeteksi: ' + txt + ' — sudah mengisi form, silakan lanjutkan.'
                                    : 'Terdeteksi: ' + txt + ' — form tidak ditemukan!';
                            },
                            async stop() { await this.stopQuiet(); this.status = 'Kamera dimatikan.'; },
                            async stopQuiet() {
                                try { if (this.scanner) { await this.scanner.stop(); this.scanner.clear(); } } catch (e) {}
                                this.scanner = null;
                                if (window.latekajeQr._active[readerId] === this) delete window.latekajeQr._active[readerId];
                            },
                            watchRemoval() {
                                const self = this;
                                try { if (self.observer) self.observer.disconnect(); } catch (e) {}
                                this.observer = new MutationObserver(function () {
                                    if (!document.getElementById(readerId)) {
                                        self._gen++;
                                        self.stopQuiet();
                                        if (self.observer) self.observer.disconnect();
                                    }
                                });
                                this.observer.observe(document.body, { childList: true, subtree: true });
                            }
                        };
                    }
                    </script>
                    HTML,
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                static fn () => view('ws-listener'),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): string => Blade::render('<div class="lk-footer">LATEKAJE © {{ date("Y") }}, Crafted by Alfin</div>'),
            );
    }
}
