import { Link, usePage } from '@inertiajs/react';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export function LanguageToggle({ className }: { className?: string }) {
    const { locale, ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();

    const locales = [
        { code: 'en', label: 'EN' },
        { code: 'es', label: 'ES' },
    ] as const;

    return (
        <div
            className={cn(
                'inline-flex items-center border border-border/80 bg-muted/40 p-0.5 text-xs font-medium',
                className,
            )}
            role="group"
            aria-label={t('common.nav.language')}
        >
            {locales.map(({ code, label }) => {
                const active = locale === code;

                return (
                    <Link
                        key={code}
                        href={route('locale.switch', { locale: code }, false, ziggy)}
                        preserveScroll
                        className={cn(
                            'inline-flex min-h-11 min-w-11 items-center justify-center px-2 text-center transition-[background-color,color,transform] duration-150 active:scale-[0.96] motion-reduce:transition-none',
                            active
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        )}
                        aria-current={active ? 'true' : undefined}
                    >
                        {label}
                    </Link>
                );
            })}
        </div>
    );
}
