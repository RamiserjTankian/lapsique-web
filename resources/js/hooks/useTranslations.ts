import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

type TranslationValue = string | Record<string, unknown>;
type Replacements = Record<string, string | number>;

function resolveKey(translations: Record<string, unknown>, key: string): string | undefined {
    const parts = key.split('.');
    let current: unknown = translations;

    for (const part of parts) {
        if (current === null || typeof current !== 'object') {
            return undefined;
        }

        current = (current as Record<string, unknown>)[part];
    }

    return typeof current === 'string' ? current : undefined;
}

function applyReplacements(value: string, replacements?: Replacements): string {
    if (!replacements) {
        return value;
    }

    return Object.entries(replacements).reduce(
        (text, [name, replacement]) => text.replaceAll(`:${name}`, String(replacement)),
        value,
    );
}

export function useTranslations() {
    const { translations, locale } = usePage<PageProps>().props;

    const t = (key: string, replacements?: Replacements): string => {
        const value = resolveKey(translations as Record<string, unknown>, key);

        if (value === undefined) {
            return key;
        }

        return applyReplacements(value, replacements);
    };

    return { t, locale };
}

export type TranslateFn = ReturnType<typeof useTranslations>['t'];
