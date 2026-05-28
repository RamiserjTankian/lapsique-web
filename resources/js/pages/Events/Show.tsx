import { Head, Link, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { Button } from '@/components/ui/button';
import { glassCardVariants } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { useTranslations } from '@/hooks/useTranslations';
import { getDateFnsLocale } from '@/lib/dateLocale';
import { route } from '@/lib/route';
import type { EventItem, PageProps } from '@/types';
import { format, parseISO } from 'date-fns';

interface EventsShowProps {
    event: EventItem & { description?: string | null };
}

export default function EventsShow({ event }: EventsShowProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const dateLabel = event.starts_at
        ? format(parseISO(event.starts_at), 'EEEE d MMMM yyyy', { locale: getDateFnsLocale(locale) })
        : null;

    return (
        <SiteLayout>
            <Head title={event.title} />
            <article className="py-12">
                {event.cover_url && (
                    <img
                        src={event.cover_url}
                        alt={event.title}
                        className={cn(glassCardVariants(), 'w-full rounded-xl object-cover max-h-96')}
                    />
                )}
                <h1 className="font-display mt-8 text-3xl font-bold md:text-4xl">{event.title}</h1>
                {dateLabel && (
                    <p className="mt-2 text-sm text-muted-foreground">{dateLabel}</p>
                )}
                {event.location_name && (
                    <p className="text-sm text-muted-foreground">{event.location_name}</p>
                )}
                {event.description && (
                    <div
                        className="prose prose-invert mt-6 max-w-none text-muted-foreground"
                        dangerouslySetInnerHTML={{ __html: event.description }}
                    />
                )}
                <Button variant="cinematic" className="mt-8" asChild>
                    <Link href={route('tickets.checkout.show', { event: event.slug }, false, ziggy)}>
                        {t('pages.events.buy_tickets')}
                    </Link>
                </Button>
            </article>
        </SiteLayout>
    );
}
