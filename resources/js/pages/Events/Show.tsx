import { Link, usePage } from '@inertiajs/react';
import { CalendarDays, MapPin, Play, Ticket, Users } from 'lucide-react';
import { useState } from 'react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { NewsletterCaptureModal } from '@/components/lapsique/NewsletterCaptureModal';
import { SafeByVarunaLanding } from '@/components/lapsique/SafeByVarunaLanding';
import SiteLayout from '@/layouts/SiteLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { EventItem, PageProps } from '@/types';

interface EventsShowProps {
    event: EventItem;
    viewContentEventId: string;
}

export default function EventsShow({ event, viewContentEventId }: EventsShowProps) {
    const { ziggy, site } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';
    const eventLocation = event.location_name || event.venue || event.city || 'Riviera Maya';
    const [interestOpen, setInterestOpen] = useState(false);
    const whatsapp = `https://wa.me/${site.whatsapp}?text=${encodeURIComponent(en ? `Hi, I want to produce an event with Lapsique Media. Reference: ${event.title}` : `Hola, quiero producir un evento con Lapsique Media. Referencia: ${event.title}`)}`;

    if (event.slug === 'safe-by-varuna-1-edition') {
        return (
            <SiteLayout>
                <SeoHead />
                <SafeByVarunaLanding event={event} viewContentEventId={viewContentEventId} />
            </SiteLayout>
        );
    }

    return (
        <SiteLayout>
            <SeoHead />
            <article>
                <header className="relative left-1/2 w-screen -translate-x-1/2 bg-[#07090b] text-white">
                    <div className="mx-auto grid min-h-[70svh] max-w-[1440px] lg:grid-cols-[0.46fr_0.54fr]">
                        <div className="flex flex-col justify-between px-5 py-12 sm:px-8 lg:px-12 lg:py-16">
                            <div>
                                <p className="alpha-kicker text-primary">Lapsique Originals / Event</p>
                                <h1 className="mt-8 text-5xl font-semibold leading-[0.86] text-white sm:text-6xl lg:text-7xl">{event.title}</h1>
                                {event.headline ? <p className="mt-6 max-w-xl text-lg leading-relaxed text-white/65">{event.headline}</p> : null}
                            </div>
                            <div className="mt-14 grid gap-4 border-t border-white/20 pt-6 text-sm text-white/65 sm:grid-cols-2">
                                <p className="flex items-center gap-3"><CalendarDays className="size-4 text-primary" /> {formatDate(event.starts_at, locale, event.time_tba, event.event_timezone)}</p>
                                <p className="flex items-center gap-3"><MapPin className="size-4 text-primary" /> {eventLocation}</p>
                            </div>
                        </div>
                        {event.cover_url ? (
                            <div className="relative min-h-[52svh] overflow-hidden bg-black">
                                <img src={event.cover_url} alt={event.title} className="absolute inset-0 h-full w-full object-cover" />
                                <div className="absolute inset-0 bg-black/10" />
                            </div>
                        ) : null}
                    </div>
                </header>

                <section className="grid gap-10 border-b border-foreground/20 py-14 md:grid-cols-[0.34fr_0.66fr] md:py-20">
                    <div>
                        <p className="alpha-kicker text-primary">Context / Production</p>
                        <h2 className="mt-4 text-4xl font-semibold">{en ? 'The story of the event.' : 'La historia del evento.'}</h2>
                    </div>
                    <div className="max-w-3xl text-lg leading-relaxed text-muted-foreground" dangerouslySetInnerHTML={{ __html: event.description || (en ? 'An event documented and produced within the Lapsique Media archive.' : 'Un evento documentado y producido dentro del archivo de Lapsique Media.') }} />
                </section>

                {event.lineup?.length ? (
                    <section className="border-b border-foreground/20 py-14 md:py-20">
                        <p className="alpha-kicker text-primary">Lineup / Artists</p>
                        <h2 className="mt-4 text-4xl font-semibold md:text-6xl">{en ? 'Confirmed artists' : 'Artistas confirmados'}</h2>
                        <div className="mt-8 grid grid-cols-2 gap-px bg-foreground/20 sm:grid-cols-3 md:grid-cols-4">
                            {event.lineup.map((dj) => (
                                <Link key={dj.id} href={route('djs.show', { dj: dj.slug }, false, ziggy)} className="group bg-background">
                                    {dj.avatar_url ? <img src={dj.avatar_url} alt={dj.name} loading="lazy" className="aspect-[3/4] w-full object-cover grayscale transition group-hover:grayscale-0" /> : null}
                                    <p className="border-t border-foreground/15 p-4 font-ui-display text-xl font-bold uppercase leading-none">{dj.name}</p>
                                </Link>
                            ))}
                        </div>
                    </section>
                ) : null}

                {event.youtube_url ? <EventVideo title={event.title} url={event.youtube_url} locale={locale} /> : null}

                {(event.gallery?.length || event.venue_gallery?.length) ? (
                    <section className="border-b border-foreground/20 py-14 md:py-20">
                        <p className="alpha-kicker text-primary">Gallery / Venue</p>
                        <h2 className="mt-4 text-4xl font-semibold md:text-6xl">{en ? 'Place and atmosphere' : 'Locación y atmósfera'}</h2>
                        <div className="mt-8 columns-2 gap-2 md:columns-3">
                            {[...(event.gallery ?? []), ...(event.venue_gallery ?? [])].map((image, index) => (
                                <img key={`${image.id}-${index}`} src={image.url} alt={`${event.title} ${index + 1}`} loading="lazy" tabIndex={0} className="mb-2 w-full break-inside-avoid object-cover" />
                            ))}
                        </div>
                    </section>
                ) : null}

                <section className="my-14 grid gap-7 border border-foreground/20 p-7 md:grid-cols-[1fr_auto] md:items-end md:p-10">
                    <div>
                        <p className="alpha-kicker text-primary">{event.is_upcoming ? 'Live / Access' : 'Lapsique Media / Production'}</p>
                        <h2 className="mt-4 max-w-3xl text-4xl font-semibold leading-[0.92] md:text-5xl">
                            {event.is_upcoming ? (en ? 'Be part of this date.' : 'Forma parte de esta fecha.') : (en ? 'Produce your next event with this visual language.' : 'Produce tu próximo evento con este lenguaje visual.')}
                        </h2>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                        {event.has_tickets ? (
                            event.ticket_url ? (
                                <a href={event.ticket_url} target="_blank" rel="noopener noreferrer" className={primaryCta}><Ticket className="size-4" /> {en ? 'Buy tickets' : 'Comprar tickets'}</a>
                            ) : (
                                <Link href={route('tickets.checkout.show', { event: event.slug }, false, ziggy)} className={primaryCta}><Ticket className="size-4" /> {en ? 'Buy tickets' : 'Comprar tickets'}</Link>
                            )
                        ) : null}
                        {event.guest_list_url ? <a href={event.guest_list_url} className={secondaryCta}><Users className="size-4" /> Guest list</a> : null}
                        {!event.has_tickets && !event.guest_list_url ? (
                            event.is_upcoming ? (
                                <button type="button" className={primaryCta} onClick={() => setInterestOpen(true)}>
                                    {en ? 'Get access updates' : 'Recibir accesos'}
                                </button>
                            ) : (
                                <a href={whatsapp} target="_blank" rel="noopener noreferrer" className={primaryCta}>{en ? 'Produce an event' : 'Producir un evento'}</a>
                            )
                        ) : null}
                    </div>
                </section>
                <NewsletterCaptureModal
                    open={interestOpen}
                    onOpenChange={setInterestOpen}
                    variant="eventCoverage"
                    source={`event:${event.slug}`}
                    imageUrl={event.cover_url}
                    imageAlt={event.title}
                />
            </article>
        </SiteLayout>
    );
}

function EventVideo({ title, url, locale }: { title: string; url: string; locale: string }) {
    const [playing, setPlaying] = useState(false);
    const id = youtubeId(url);
    if (!id) return null;
    const poster = `https://i.ytimg.com/vi/${id}/maxresdefault.jpg`;
    return (
        <section className="relative left-1/2 w-screen -translate-x-1/2 bg-[#07090b] py-14 text-white md:py-20">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <p className="alpha-kicker text-primary">Set / Aftermovie</p>
                <h2 className="mt-4 text-4xl font-semibold md:text-6xl">{locale === 'en' ? 'Watch the event in motion.' : 'Mira el evento en movimiento.'}</h2>
                <div className="relative mt-8 aspect-video overflow-hidden bg-black">
                    {playing ? (
                        <iframe title={title} src={`https://www.youtube-nocookie.com/embed/${id}?autoplay=1`} className="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen />
                    ) : (
                        <button type="button" onClick={() => setPlaying(true)} className="group absolute inset-0" aria-label={locale === 'en' ? 'Play video' : 'Reproducir video'}>
                            <img src={poster} alt={title} className="h-full w-full object-cover opacity-75 transition group-hover:opacity-100" />
                            <span className="absolute left-1/2 top-1/2 flex size-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center bg-primary text-white"><Play className="size-7 fill-current" /></span>
                        </button>
                    )}
                </div>
            </div>
        </section>
    );
}

function youtubeId(url: string): string | null {
    return url.match(/(?:v=|youtu\.be\/|embed\/)([\w-]{11})/)?.[1] ?? null;
}

function formatDate(value: string | null, locale: string, timeTba = false, timeZone?: string): string {
    if (!value) return locale === 'en' ? 'Date to be announced' : 'Fecha por anunciar';
    return new Intl.DateTimeFormat(
        locale === 'en' ? 'en-US' : 'es-MX',
        timeTba ? { dateStyle: 'long', timeZone } : { dateStyle: 'long', timeStyle: 'short', timeZone },
    ).format(new Date(value));
}

const primaryCta = 'inline-flex min-h-13 items-center justify-center gap-2 bg-foreground px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-background hover:bg-primary hover:text-white';
const secondaryCta = 'inline-flex min-h-13 items-center justify-center gap-2 border border-foreground/30 px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] hover:border-primary hover:text-primary';
