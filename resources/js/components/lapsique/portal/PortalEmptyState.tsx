import { Link, usePage } from '@inertiajs/react';
import { CalendarPlus, Clapperboard } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export function PortalEmptyState() {
    const { ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();

    return (
        <div className="glass-panel flex flex-col items-center rounded-2xl border border-border px-6 py-14 text-center">
            <span className="flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/25 bg-primary/10 text-primary">
                <Clapperboard className="h-8 w-8" />
            </span>
            <h2 className="font-display mt-5 text-xl font-semibold text-foreground">
                {t('customer.portal.empty.title')}
            </h2>
            <p className="mt-2 max-w-md text-sm text-muted-foreground">
                {t('customer.portal.empty.subtitle')}
            </p>
            <Button variant="cinematic" className="mt-6" asChild>
                <Link href={route('booking.show', undefined, false, ziggy)}>
                    <CalendarPlus className="mr-2 h-4 w-4" />
                    {t('customer.portal.empty.cta')}
                </Link>
            </Button>
        </div>
    );
}
