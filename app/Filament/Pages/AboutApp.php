<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AboutApp extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationLabel = 'Tentang Aplikasi';

    protected static ?string $title = 'Tentang Aplikasi';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.about-app';
}
