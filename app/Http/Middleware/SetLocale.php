<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var array<int,string> */
    public const SUPPORTED = ['id', 'en'];

    public static function resolve(): string
    {
        try {
            $locale = (string) Setting::get('app_locale', config('app.locale', 'id'));
        } catch (\Throwable) {
            $locale = (string) config('app.locale', 'id');
        }

        return in_array($locale, self::SUPPORTED, true) ? $locale : 'id';
    }

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(self::resolve());

        return $next($request);
    }
}
