import { enUS, es } from 'date-fns/locale';
import type { Locale } from 'date-fns';

export function getDateFnsLocale(locale: string): Locale {
    return locale === 'en' ? enUS : es;
}
