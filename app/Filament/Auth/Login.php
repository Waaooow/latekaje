<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('NIS / Email')
            ->placeholder('cth: 1001 atau nama@email.com')
            ->required()
            ->autocomplete()
            ->autofocus();
    }
}
