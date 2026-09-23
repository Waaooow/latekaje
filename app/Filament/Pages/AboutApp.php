<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AboutApp extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    public static function getNavigationLabel(): string
    {
        return __('common.nav_about');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('common.nav_about');
    }

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.about-app';
}
