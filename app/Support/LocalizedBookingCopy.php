<?php

namespace App\Support;

class LocalizedBookingCopy
{
    /**
     * Hero title for home / booking funnel. Admin copy is shown in Spanish only;
     * English uses the translated marketing default.
     */
    public static function title(?string $adminTitle): string
    {
        $default = __('pages.home.hero_title');

        if (app()->getLocale() !== 'es') {
            return $default;
        }

        return filled($adminTitle) ? $adminTitle : $default;
    }

    /**
     * Hero subtitle. Admin copy is Spanish; non-Spanish locales use the localized offer subtitle.
     */
    public static function subtitle(?string $adminSubtitle): string
    {
        if (app()->getLocale() !== 'es') {
            return ContentSessionOffer::defaultSubtitle();
        }

        return filled($adminSubtitle) ? $adminSubtitle : ContentSessionOffer::defaultSubtitle();
    }

    public static function bookingPageTitle(?string $adminTitle): string
    {
        $default = __('pages.booking.show_title');

        if (app()->getLocale() !== 'es') {
            return $default;
        }

        return filled($adminTitle) ? $adminTitle : $default;
    }
}
