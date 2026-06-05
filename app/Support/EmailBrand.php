<?php

namespace App\Support;

final class EmailBrand
{
    public const BACKGROUND = '#F8F7F3';

    public const FOREGROUND = '#2B2D38';

    public const PRIMARY = '#D4A84A';

    public const PRIMARY_FOREGROUND = '#262318';

    public const ACCENT = '#4A9FB8';

    public const CARD = '#FFFFFF';

    public const BORDER = 'rgba(43,45,56,0.12)';

    public const MUTED = '#6E717F';

    public const SECONDARY = '#EDECE6';

    public const FONT_SANS = "'DM Sans', ui-sans-serif, system-ui, sans-serif";

    public const FONT_DISPLAY = "'Syne', ui-sans-serif, system-ui, sans-serif";

    public const WORDMARK = 'TRASCENDENTAL.';

    public const TAGLINE = 'Artists · Events · Culture';

    public static function fontFamiliesUrl(): string
    {
        return 'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Syne:wght@500;600;700;800&display=swap';
    }

    public static function bodyStyle(): string
    {
        return 'margin:0;padding:0;background-color:'.self::BACKGROUND.';color:'.self::FOREGROUND.';font-family:'.self::FONT_SANS.';line-height:1.6;';
    }

    public static function wrapperStyle(): string
    {
        return 'max-width:600px;margin:0 auto;background-color:'.self::BACKGROUND.';color:'.self::FOREGROUND.';';
    }

    public static function headingStyle(int $size = 24): string
    {
        return 'margin:0 0 12px;color:'.self::FOREGROUND.';font-family:'.self::FONT_DISPLAY.';font-size:'.$size.'px;font-weight:700;letter-spacing:-0.02em;line-height:1.2;';
    }

    public static function eyebrowStyle(): string
    {
        return 'margin:0 0 16px;color:'.self::ACCENT.';font-size:11px;font-weight:600;letter-spacing:0.24em;text-transform:uppercase;';
    }

    public static function paragraphStyle(): string
    {
        return 'margin:0 0 16px;color:'.self::FOREGROUND.';font-size:16px;';
    }

    public static function mutedStyle(): string
    {
        return 'margin:0;color:'.self::MUTED.';font-size:14px;';
    }

    public static function strongStyle(): string
    {
        return 'color:'.self::FOREGROUND.';font-weight:600;';
    }

    public static function cardStyle(): string
    {
        return 'background-color:'.self::CARD.';padding:20px;border-radius:12px;margin:20px 0;border:1px solid '.self::BORDER.';';
    }

    public static function cardTitleStyle(): string
    {
        return 'margin:0 0 12px;color:'.self::FOREGROUND.';font-family:'.self::FONT_DISPLAY.';font-size:16px;font-weight:600;';
    }

    public static function cardRowStyle(): string
    {
        return 'margin:6px 0;color:'.self::FOREGROUND.';font-size:15px;';
    }

    public static function tipBoxStyle(): string
    {
        return 'background-color:'.self::SECONDARY.';padding:15px;border-left:3px solid '.self::PRIMARY.';border-radius:0 8px 8px 0;margin:20px 0;';
    }

    public static function qrWrapperStyle(): string
    {
        return 'background-color:'.self::CARD.';border:1px solid '.self::BORDER.';padding:20px;border-radius:12px;margin:24px 0;text-align:center;';
    }

    public static function qrFrameStyle(): string
    {
        return 'display:inline-block;padding:12px;background-color:'.self::BACKGROUND.';border-radius:14px;border:1px solid '.self::BORDER.';';
    }

    public static function linkStyle(): string
    {
        return 'color:'.self::ACCENT.';text-decoration:underline;';
    }

    public static function buttonStyle(): string
    {
        return 'display:inline-block;padding:13px 28px;background:'.self::PRIMARY.';color:'.self::PRIMARY_FOREGROUND.' !important;text-decoration:none;border-radius:12px;font-weight:600;font-size:13px;letter-spacing:0.06em;box-shadow:0 4px 20px rgba(212,168,74,0.35);';
    }

    public static function headerStyle(): string
    {
        return 'background-color:'.self::CARD.';padding:28px 30px 22px;text-align:center;border-bottom:1px solid '.self::BORDER.';';
    }

    public static function footerStyle(): string
    {
        return 'background-color:'.self::CARD.';padding:22px 20px;text-align:center;font-size:12px;color:'.self::MUTED.';border-top:1px solid '.self::BORDER.';';
    }

    public static function bodyPaddingStyle(): string
    {
        return 'padding:30px;color:'.self::FOREGROUND.';';
    }

    public static function wordmarkStyle(): string
    {
        return 'margin:0;color:'.self::FOREGROUND.';font-family:'.self::FONT_DISPLAY.';font-size:22px;font-weight:700;letter-spacing:0.12em;text-transform:lowercase;';
    }
}
