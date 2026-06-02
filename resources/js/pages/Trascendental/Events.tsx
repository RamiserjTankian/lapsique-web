import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';
import { PageShell, type ProducedEvent } from './Partials';
import { useTranslations } from '@/hooks/useTranslations';

interface EventsProps {
    events: ProducedEvent[];
    upcomingEvents: UpcomingEvent[];
    pagination: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

interface UpcomingEvent {
    category: 'produced' | 'announce' | 'roster';
    title: string;
    date: string;
    venue: string;
    city: string;
    lineup: string;
    image?: string | null;
    details_url: string | null;
    tickets_url: string | null;
}

export default function Events({ events, upcomingEvents, pagination }: EventsProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const producedUpcoming = upcomingEvents.filter((event) => event.category === 'produced');
    const announceUpcoming = upcomingEvents.filter((event) => event.category === 'announce');
    const rosterAppearances = upcomingEvents.filter((event) => event.category === 'roster');

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
                <section className="mb-14 border-y border-black/15 py-8">
                    <div className="mb-8">
                        <div>
                            <p className="text-xs font-bold uppercase text-black/45">{t('trascendental.events.upcoming_eyebrow')}</p>
                            <h2 className="text-5xl font-black uppercase leading-none">{t('trascendental.events.upcoming_title')}</h2>
                        </div>
                    </div>
                    <div className="grid gap-8 lg:grid-cols-3">
                        <UpcomingGroup title="Produced by Trascendental" events={producedUpcoming} />
                        <UpcomingGroup title="To be announce" events={announceUpcoming} />
                        <UpcomingGroup title="Roster appearances" events={rosterAppearances} />
                    </div>
                </section>

                <div className="mb-8">
                    <p className="text-xs font-bold uppercase text-black/45">{t('trascendental.produced.eyebrow')}</p>
                    <h2 className="mt-3 text-5xl font-black uppercase leading-none">{t('trascendental.produced.title')}</h2>
                </div>

                <div className="grid gap-x-5 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                    {events.map((event) => (
                        <article key={`${event.title}-${event.date}`} className="border-t border-black pt-3">
                            <a href={event.source_url ?? '#'} target={event.source_url ? '_blank' : undefined} rel={event.source_url ? 'noopener noreferrer' : undefined} className={event.source_url ? 'group block' : 'pointer-events-none block'}>
                                <img src={event.image} alt={`${event.title} flyer`} className="aspect-[4/5] w-full bg-black/5 object-contain" loading="lazy" />
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

function UpcomingGroup({ title, events }: { title: string; events: UpcomingEvent[] }) {
    return (
        <section>
            <h3 className="border-t border-black pt-3 text-2xl font-black uppercase leading-none">{title}</h3>
            <div className="mt-5 grid gap-4">
                {events.map((event) => (
                    <article
                        key={`${event.title}-${event.date}-${event.city}`}
                        className={`grid gap-4 border-t border-black/15 pt-4 md:items-end ${event.image ? 'md:grid-cols-[minmax(0,0.42fr)_minmax(0,1fr)_auto]' : 'md:grid-cols-[1fr_auto]'}`}
                    >
                        {event.image ? (
                            <img src={event.image} alt={`${event.title} flyer`} className="aspect-[4/5] w-full bg-black/5 object-contain md:max-w-40" loading="lazy" />
                        ) : null}
                        <div>
                            <p className="text-xs font-bold uppercase text-black/45">{[event.date, event.city].filter(Boolean).join(' / ')}</p>
                            <h4 className="mt-2 text-2xl font-black uppercase leading-none">{event.title}</h4>
                            {event.venue ? <p className="mt-2 text-sm font-bold uppercase text-black/55">{event.venue}</p> : null}
                            {event.lineup ? <p className="mt-3 text-xs font-bold uppercase leading-relaxed text-black/70">{event.lineup}</p> : null}
                        </div>
                        {event.details_url || event.tickets_url ? (
                            <div className="flex gap-2 text-xs font-bold uppercase md:flex-col">
                                <EventAction href={event.details_url}>{eventActionLabel(event.details_url, 'Event details')}</EventAction>
                                <EventAction href={event.tickets_url}>{eventActionLabel(event.tickets_url, 'Buy tickets')}</EventAction>
                            </div>
                        ) : null}
                    </article>
                ))}
            </div>
        </section>
    );
}

function EventAction({ href, children }: { href: string | null; children: string }) {
    if (!href) {
        return null;
    }

    return (
        <a href={href} target="_blank" rel="noopener noreferrer" className="inline-flex min-h-10 items-center justify-center border border-black px-3 text-black hover:bg-black hover:text-white">
            {children}
        </a>
    );
}

function eventActionLabel(href: string | null, fallback: string) {
    if (!href) {
        return fallback;
    }

    if (href.includes('instagram.com')) {
        return 'Instagram';
    }

    if (href.includes('flashpass') || href.includes('ticketea')) {
        return 'Buy tickets';
    }

    return fallback;
}
