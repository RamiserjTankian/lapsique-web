import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export function LanguageToggle({ className }: { className?: string }) {
    const { locale, ziggy } = usePage<PageProps>().props;

    const locales = [
        { code: 'es', label: 'ES' },
        { code: 'en', label: 'EN' },
    ] as const;

    return (
        <div
            className={cn(
                'inline-flex items-center rounded-lg border border-border/80 bg-muted/40 p-0.5 text-xs font-medium',
                className,
            )}
            role="group"
            aria-label="Language"
        >
            {locales.map(({ code, label }) => {
                const active = locale === code;

                return (
                    <Link
                        key={code}
                        href={route('locale.switch', { locale: code }, false, ziggy)}
                        preserveScroll
                        className={cn(
                            'min-w-[2rem] rounded-md px-2 py-1 text-center transition-colors',
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
