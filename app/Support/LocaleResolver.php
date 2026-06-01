<?php

namespace App\Support;

use Illuminate\Http\Request;

class LocaleResolver
{
    public const SUPPORTED = ['es', 'en'];

    public static function resolve(Request $request): string
    {
        if (config('trascendental.enabled_as_primary') || $request->is('trascendental*')) {
            if ($session = $request->session()->get('trascendental_locale')) {
                return self::normalize($session);
            }

            return 'en';
        }

        if ($session = $request->session()->get('locale')) {
            return self::normalize($session);
        }

        if ($cookie = $request->cookie('locale')) {
            return self::normalize($cookie);
        }

        return self::fromAcceptLanguage($request->header('Accept-Language'))
            ?? config('app.locale', 'es');
    }

    public static function normalize(?string $locale): string
    {
        $locale = strtolower((string) $locale);

        if (str_starts_with($locale, 'es')) {
            return 'es';
        }

        if (str_starts_with($locale, 'en')) {
            return 'en';
        }

        return in_array($locale, self::SUPPORTED, true) ? $locale : 'es';
    }

    public static function fromAcceptLanguage(?string $header): ?string
    {
        if (! filled($header)) {
            return null;
        }

        $languages = [];

        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $segments = explode(';', $part, 2);
            $tag = strtolower(trim($segments[0]));
            $quality = 1.0;

            if (isset($segments[1]) && preg_match('/q=([0-9.]+)/', $segments[1], $matches)) {
                $quality = (float) $matches[1];
            }

            $languages[] = ['tag' => $tag, 'quality' => $quality];
        }

        usort($languages, fn (array $a, array $b) => $b['quality'] <=> $a['quality']);

        foreach ($languages as $language) {
            $normalized = self::normalize($language['tag']);

            if (in_array($normalized, self::SUPPORTED, true)) {
                return $normalized;
            }
        }

        return null;
    }
}
