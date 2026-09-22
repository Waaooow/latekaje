<?php

namespace App\Http\Responses;

use App\Filament\Resources\Loans\LoanResource;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = Filament::auth()->user();

        if ($user?->isSiswa()) {
            return redirect()->to(LoanResource::getUrl('index'));
        }

        return redirect()->intended(Filament::getUrl());
    }
}
