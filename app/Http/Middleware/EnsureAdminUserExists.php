<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Filament\Facades\Filament;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUserExists
{
    public function handle(Request $request, Closure $next): Response
    {
        $registerUrl = Filament::getRegistrationUrl();
        $loginUrl = Filament::getLoginUrl();

        // KONDISI 1: Jika database masih kosong (Belum ada Admin)
        if (User::doesntExist()) {
            // Paksa semua request untuk pindah ke halaman register
            if ($request->url() !== $registerUrl) {
                return redirect()->to($registerUrl);
            }
        } 
        // KONDISI 2: Jika Admin sudah berhasil dibuat
        else {
            // Kunci mati halaman register! Kalau diakses, tendang kembali ke halaman login
            if ($request->url() === $registerUrl) {
                return redirect()->to($loginUrl);
            }
        }

        return $next($request);
    }
}