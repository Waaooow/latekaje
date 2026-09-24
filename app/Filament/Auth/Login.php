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
            ->label(__('loans.login_code_label'))
            ->placeholder(__('loans.login_code_placeholder'))
            ->required()
            ->autocomplete()
            ->autofocus();
    }
}
