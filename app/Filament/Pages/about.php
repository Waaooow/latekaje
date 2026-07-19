<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;

class AboutApp extends Page
{
    // Ini tetap static karena di Filament memang tipenya static
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $title = 'Tentang Aplikasi';

    protected static ?string $navigationLabel = 'Tentang Aplikasi';

    protected static ?int $navigationSort = 99;

    // 🟢 PERBAIKAN UTAMA: Kata 'static' DAKHIL/DIHAPUS, karena ini properti biasa
    protected string $view = 'filament.pages.about-app';
}