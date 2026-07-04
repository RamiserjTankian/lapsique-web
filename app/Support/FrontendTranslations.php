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

    public static function all(?string $locale = null, bool $includeTrascendental = true): array
    {
        $locale = $locale ?? app()->getLocale();
        $translations = [];

        $files = $includeTrascendental
            ? self::FILES
            : array_values(array_filter(self::FILES, fn (string $file): bool => $file !== 'trascendental'));

        foreach ($files as $file) {
            $translations[$file] = trans($file, [], $locale);
        }

        return $translations;
    }
}
