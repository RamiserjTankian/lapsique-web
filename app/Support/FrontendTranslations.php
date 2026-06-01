<?php

namespace App\Support;

class FrontendTranslations
{
    public const FILES = [
        'common',
        'booking',
        'funnel',
        'customer',
        'pages',
        'seo',
        'trascendental',
    ];

    public static function all(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $translations = [];

        foreach (self::FILES as $file) {
            $translations[$file] = trans($file, [], $locale);
        }

        return $translations;
    }
}
