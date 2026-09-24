<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class AboutApp extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    public static function getNavigationLabel(): string
    {
        return __('common.nav_about');
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return __('common.nav_about');
    }

    protected static ?int $navigationSort = 32;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('common.nav_group_system');
    }

    protected string $view = 'filament.pages.about-app';
}
