<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang')
            ?? $request->header('X-Language')
            ?? $request->getPreferredLanguage(
                Language::activeCached()->pluck('code')->all() ?: null,
            );

        $language = Language::byCode($requested);

        if ($language === null || ! $language->is_active) {
            $language = Language::defaultLanguage();
        }

        if ($language !== null) {
            app()->setLocale($language->code);
        }

        return $next($request);
    }
}