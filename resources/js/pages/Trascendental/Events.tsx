import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';
import { PageShell, type ProducedEvent } from './Partials';
import { useTranslations } from '@/hooks/useTranslations';

interface EventsProps {
    events: ProducedEvent[];
    pagination: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

export default function Events({ events, pagination }: EventsProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();

    const pageLabel = t('trascendental.events.page')
        .replace(':current', pagination.currentPage.toString())
        .replace(':total', pagination.lastPage.toString());

    const pageHref = (page: number) => {
        const href = route('trascendental.events', undefined, false, ziggy);
        const params = new URLSearchParams();

        if (page > 1) {
            params.set('page', page.toString());
        }

        return params.toString() ? `${href}?${params.toString()}` : href;
    };

    return (
        <TrascendentalLayout>
            <PageShell title={t('trascendental.events.title')} intro={t('trascendental.events.intro')}>
                <div className="grid gap-x-5 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                    {events.map((event) => (
                        <article key={`${event.title}-${event.date}`} className="border-t border-black pt-3">
                            <a href={event.source_url ?? '#'} target={event.source_url ? '_blank' : undefined} rel={event.source_url ? 'noopener noreferrer' : undefined} className={event.source_url ? 'group block' : 'pointer-events-none block'}>
                                <img src={event.image} alt={`${event.title} flyer`} className="aspect-[4/5] w-full bg-black/5 object-cover" loading="lazy" />
                                <p className="mt-4 text-xs font-bold uppercase text-black/45">
                                    {event.date} / {event.city}
                                </p>
                                <h2 className="mt-2 text-2xl font-black uppercase leading-none group-hover:underline">
                                    {event.title}
                                </h2>
                                <p className="mt-3 text-sm font-bold uppercase text-black/50">{event.venue}</p>
                                <p className="mt-4 text-xs font-bold uppercase leading-relaxed text-black/75">{event.lineup}</p>
                            </a>
                        </article>
                    ))}
                </div>

                <nav className="mt-12 flex flex-col gap-4 border-t border-black/15 pt-5 text-sm font-bold uppercase sm:flex-row sm:items-center sm:justify-between" aria-label="Pagination">
                    <Link
                        href={pageHref(Math.max(1, pagination.currentPage - 1))}
                        preserveScroll
                        className={`inline-flex items-center gap-2 ${pagination.currentPage === 1 ? 'pointer-events-none text-black/35' : 'text-black hover:underline'}`}
                    >
                        <ArrowLeft className="h-4 w-4" />
                        {t('trascendental.events.previous')}
                    </Link>
                    <span className="text-black/50">{pageLabel}</span>
                    <Link
                        href={pageHref(Math.min(pagination.lastPage, pagination.currentPage + 1))}
                        preserveScroll
                        className={`inline-flex items-center gap-2 ${pagination.currentPage === pagination.lastPage ? 'pointer-events-none text-black/35' : 'text-black hover:underline'}`}
                    >
                        {t('trascendental.events.next')}
                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </nav>
            </PageShell>
        </TrascendentalLayout>
    );
}
