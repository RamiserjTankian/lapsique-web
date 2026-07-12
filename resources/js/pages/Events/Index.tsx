import { Link, usePage } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { EventItem, PageProps } from '@/types';

interface EventsIndexProps {
    upcomingEvents: EventItem[];
    archivedEvents: EventItem[];
}

export default function EventsIndex({ upcomingEvents, archivedEvents }: EventsIndexProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';

    return (
        <SiteLayout>
            <SeoHead />
            <section className="relative left-1/2 w-screen -translate-x-1/2 bg-[#07090b] text-white">
                <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 md:py-24">
                    <p className="alpha-kicker text-primary">Lapsique Originals / Events</p>
                    <div className="mt-6 grid gap-8 border-b border-white/20 pb-12 lg:grid-cols-[0.72fr_0.28fr] lg:items-end">
                        <h1 className="max-w-5xl text-6xl font-semibold leading-[0.84] text-white sm:text-7xl md:text-9xl">{en ? 'Events' : 'Eventos'}</h1>
                        <p className="text-base leading-relaxed text-white/60">{en ? 'Shows, collaborations, residences, and nightlife productions documented by Lapsique Media.' : 'Shows, colaboraciones, residencias y producciones nightlife documentadas por Lapsique Media.'}</p>
                    </div>

                    <div className="mt-10">
                        <p className="alpha-kicker text-primary">{en ? 'Upcoming' : 'Próximos'}</p>
                        {upcomingEvents.length > 0 ? (
                            <div className="mt-6 grid gap-px bg-white/20 md:grid-cols-2">
                                {upcomingEvents.map((event) => <EventFeature key={event.id} event={event} locale={locale} />)}
                            </div>
                        ) : (
                            <div className="mt-6 grid gap-5 border-y border-white/20 py-7 md:grid-cols-[1fr_auto] md:items-center">
                                <p className="text-xl text-white/65">{en ? 'The next date will be announced here. Meanwhile, explore the production archive.' : 'La próxima fecha se anunciará aquí. Mientras tanto, explora el archivo de producciones.'}</p>
                                <a href="#archivo" className="font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-primary">{en ? 'Open archive' : 'Abrir archivo'} ↓</a>
                            </div>
                        )}
                    </div>
                </div>
            </section>

            <section id="archivo" className="scroll-mt-24 py-14 md:py-20">
                <div className="mb-8 grid gap-5 border-b border-foreground/20 pb-6 md:grid-cols-[1fr_auto] md:items-end">
                    <div>
                        <p className="alpha-kicker text-primary">Archive / {String(archivedEvents.length).padStart(2, '0')}</p>
                        <h2 className="mt-3 text-4xl font-semibold md:text-6xl">{en ? 'Produced events' : 'Eventos producidos'}</h2>
                    </div>
                    <p className="max-w-sm text-sm leading-relaxed text-muted-foreground">{en ? 'Every page documents lineup, place, atmosphere, and audiovisual outcome.' : 'Cada página documenta lineup, locación, atmósfera y resultado audiovisual.'}</p>
                </div>
                {archivedEvents.length > 0 ? (
                    <div className="divide-y divide-foreground/20 border-y border-foreground/20">
                        {archivedEvents.map((event, index) => (
                            <Link key={event.id} href={route('events.show', { event: event.slug }, false, ziggy)} className="group grid gap-5 py-6 md:grid-cols-[4rem_12rem_1fr_auto] md:items-center">
                                <span className="font-mono text-xs text-primary">{String(index + 1).padStart(2, '0')}</span>
                                {event.cover_url ? <img src={event.cover_url} alt={event.title} loading="lazy" className="aspect-video w-full object-cover grayscale transition group-hover:grayscale-0" /> : <span className="alpha-kicker text-muted-foreground">Lapsique Event</span>}
                                <div>
                                    <h3 className="text-2xl font-semibold leading-tight group-hover:text-primary md:text-3xl">{event.title}</h3>
                                    <p className="mt-2 text-sm text-muted-foreground">{event.location_name || event.venue || event.city || 'Riviera Maya'}</p>
                                </div>
                                <span className="inline-flex items-center gap-2 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-primary">{formatDate(event.starts_at, locale)} <ArrowRight className="size-4" /></span>
                            </Link>
                        ))}
                    </div>
                ) : <p className="py-14 text-muted-foreground">{en ? 'No archived events yet.' : 'Todavía no hay eventos archivados.'}</p>}
            </section>
        </SiteLayout>
    );
}

function EventFeature({ event, locale }: { event: EventItem; locale: string }) {
    const { ziggy } = usePage<PageProps>().props;
    return (
        <Link href={route('events.show', { event: event.slug }, false, ziggy)} className="group bg-[#07090b]">
            {event.cover_url ? <img src={event.cover_url} alt={event.title} className="aspect-[16/10] w-full object-cover" /> : null}
            <div className="p-6">
                <p className="alpha-kicker text-primary">{formatDate(event.starts_at, locale)}</p>
                <h2 className="mt-3 text-3xl font-semibold leading-tight text-white group-hover:text-primary">{event.title}</h2>
                <p className="mt-3 text-sm text-white/50">{event.location_name || event.venue || event.city}</p>
            </div>
        </Link>
    );
}

function formatDate(value: string | null, locale: string): string {
    if (!value) return locale === 'en' ? 'Archive' : 'Archivo';
    return new Intl.DateTimeFormat(locale === 'en' ? 'en-US' : 'es-MX', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}
