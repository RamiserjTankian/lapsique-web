import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, Mail, MessageCircle } from 'lucide-react';
import { type ReactNode } from 'react';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export interface TrascendentalCase {
    id: number;
    title: string;
    slug: string;
    headline: string | null;
    summary: string | null;
    description: string | null;
    venue: string | null;
    city: string | null;
    metrics: Array<{ label: string; value: string }>;
    services: string[];
    image_url: string | null;
    media?: Array<{
        type: 'image' | 'video';
        src: string;
        alt: string;
        poster?: string;
    }>;
}

export interface TrascendentalTour {
    artist: string;
    status: string;
    nationality: string;
    label: string;
    instagram: string;
    instagram_url: string;
    soundcloud_url: string;
    bio: string;
    image: string;
}

export interface ProducedEvent {
    title: string;
    date: string;
    venue: string;
    city: string;
    lineup: string;
    summary: string;
    image: string;
    source_url: string | null;
}

export function PageShell({
    eyebrow,
    title,
    intro,
    children,
}: {
    eyebrow?: string;
    title: string;
    intro: string;
    children: ReactNode;
}) {
    return (
        <div className="px-4 py-12 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-[1500px]">
                {eyebrow ? <p className="text-xs font-bold uppercase text-black/50">{eyebrow}</p> : null}
                <h1 className="mt-5 max-w-6xl break-words text-4xl font-black uppercase leading-[0.9] sm:text-7xl lg:text-8xl">
                    {title}
                </h1>
                <p className="mt-6 max-w-2xl text-lg leading-relaxed text-black/65">{intro}</p>
                <div className="mt-14">{children}</div>
            </div>
        </div>
    );
}

export function EditorialButton({
    href,
    children,
    dark = false,
}: {
    href: string;
    children: ReactNode;
    dark?: boolean;
}) {
    return (
        <Link
            href={href}
            className={`inline-flex min-h-11 items-center justify-center gap-2 rounded-full border px-5 text-xs font-bold uppercase sm:min-h-12 sm:px-6 sm:text-sm ${
                dark ? 'border-black bg-black text-white' : 'border-black text-black'
            }`}
        >
            {children}
            <ArrowUpRight className="h-4 w-4" />
        </Link>
    );
}

export function CaseRows({ cases }: { cases: TrascendentalCase[] }) {
    return (
        <div className="divide-y divide-black/15 border-y border-black/15">
            {cases.map((item) => (
                <article key={item.id} className="grid gap-6 py-8 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.25fr)] lg:items-end">
                    <CaseMedia item={item} />
                    <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,0.6fr)]">
                        <div>
                            <p className="text-xs font-bold uppercase text-black/45">{[item.venue, item.city].filter(Boolean).join(' / ')}</p>
                            <h2 className="mt-3 text-4xl font-black uppercase leading-none sm:text-5xl">{item.title}</h2>
                            <p className="mt-4 max-w-xl text-base leading-relaxed text-black/65">{item.summary ?? item.headline}</p>
                        </div>
                        <dl className="grid content-end gap-3">
                            {item.metrics.map((metric) => (
                                <div key={`${item.id}-${metric.label}`} className="flex justify-between border-b border-black/15 pb-2 text-sm">
                                    <dt className="font-bold uppercase text-black/45">{metric.label}</dt>
                                    <dd className="font-black">{metric.value}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                </article>
            ))}
        </div>
    );
}

export function ProducedEventsSection({ events }: { events: ProducedEvent[] }) {
    const { ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const visibleEvents = events.slice(0, 8);

    if (events.length === 0) {
        return null;
    }

    return (
        <section className="px-4 py-14 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-[1500px]">
                <div className="mb-8 grid gap-5 border-b border-black/15 pb-7 md:grid-cols-[1fr_0.72fr] md:items-end">
                    <div>
                        <p className="text-xs font-bold uppercase text-black/45">{t('trascendental.produced.eyebrow')}</p>
                        <h2 className="mt-3 text-5xl font-black uppercase leading-none sm:text-6xl">
                            {t('trascendental.produced.title')}
                        </h2>
                    </div>
                    <div>
                        <p className="text-lg leading-relaxed text-black/65">
                            {t('trascendental.produced.intro')}
                        </p>
                        <div className="mt-5">
                            <EditorialButton href={route('trascendental.events', undefined, false, ziggy)}>
                                {t('trascendental.produced.view_all')}
                            </EditorialButton>
                        </div>
                    </div>
                </div>
                <div className="grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    {visibleEvents.map((event) => (
                        <article key={`${event.title}-${event.date}`} className="border-t border-black pt-3">
                            <a
                                href={event.source_url ?? '#'}
                                target={event.source_url ? '_blank' : undefined}
                                rel={event.source_url ? 'noopener noreferrer' : undefined}
                                className={event.source_url ? 'group block' : 'block pointer-events-none'}
                            >
                                <img
                                    src={event.image}
                                    alt={`${event.title} flyer`}
                                    className="aspect-[4/5] w-full object-cover"
                                    loading="lazy"
                                />
                                <p className="mt-4 text-xs font-bold uppercase text-black/45">
                                    {event.date} / {event.city}
                                </p>
                                <h3 className="mt-2 text-2xl font-black uppercase leading-none group-hover:underline">
                                    {event.title}
                                </h3>
                                <p className="mt-3 text-sm font-bold uppercase text-black/50">{event.venue}</p>
                                <p className="mt-4 text-xs font-bold uppercase leading-relaxed text-black/75">{event.lineup}</p>
                            </a>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}

function CaseMedia({ item }: { item: TrascendentalCase }) {
    const media = item.media?.length
        ? item.media
        : item.image_url
          ? [{ type: 'image' as const, src: item.image_url, alt: item.title }]
          : [];

    if (media.length === 0) {
        return <div aria-hidden="true" />;
    }

    if (media.length === 1) {
        return <MediaItem item={media[0]} className="aspect-[16/10] w-full" />;
    }

    return (
        <div className="grid aspect-[16/10] grid-cols-2 gap-1 overflow-hidden bg-black">
            {media.slice(0, 4).map((mediaItem) => (
                <MediaItem key={`${item.id}-${mediaItem.src}`} item={mediaItem} className="h-full min-h-0 w-full" />
            ))}
        </div>
    );
}

function MediaItem({
    item,
    className,
}: {
    item: NonNullable<TrascendentalCase['media']>[number];
    className: string;
}) {
    if (item.type === 'video') {
        return (
            <video
                className={`${className} object-cover`}
                src={item.src}
                poster={item.poster}
                autoPlay
                muted
                loop
                playsInline
                preload="metadata"
                aria-label={item.alt}
            />
        );
    }

    return <img src={item.src} alt={item.alt} className={`${className} object-cover`} loading="lazy" />;
}

export function TourRows({ tours }: { tours: TrascendentalTour[] }) {
    return (
        <div className="grid gap-x-5 gap-y-9 sm:grid-cols-2 lg:grid-cols-3">
            {tours.map((tour) => (
                <article key={tour.artist} className="border-t border-black pt-4">
                    <img src={tour.image} alt={`${tour.artist} booking portrait`} className="aspect-[4/5] w-full bg-black object-contain" loading="lazy" />
                    <div className="mt-4 flex items-start justify-between gap-4">
                        <div>
                            <p className="text-xs font-bold uppercase text-black/45">{tour.label}</p>
                            <h2 className="mt-2 text-4xl font-black uppercase leading-none">{tour.artist}</h2>
                        </div>
                        <p className="shrink-0 border border-black px-2 py-1 text-[0.65rem] font-black uppercase text-black">{tour.status}</p>
                    </div>
                    <dl className="mt-5 grid gap-2 border-y border-black/15 py-4 text-xs font-bold uppercase">
                        <div className="flex justify-between gap-3">
                            <dt className="text-black/45">Nationality</dt>
                            <dd className="text-right">{tour.nationality}</dd>
                        </div>
                        <div className="flex justify-between gap-3">
                            <dt className="text-black/45">Record label</dt>
                            <dd className="text-right">{tour.label}</dd>
                        </div>
                    </dl>
                    <p className="mt-4 text-sm leading-relaxed text-black/65">{tour.bio}</p>
                    <div className="mt-5 grid grid-cols-2 gap-2 text-xs font-bold uppercase">
                        <a href={tour.instagram_url} target="_blank" rel="noopener noreferrer" className="border border-black px-3 py-2 text-center">
                            Instagram
                        </a>
                        <a href={tour.soundcloud_url} target="_blank" rel="noopener noreferrer" className="border border-black px-3 py-2 text-center">
                            SoundCloud
                        </a>
                    </div>
                </article>
            ))}
        </div>
    );
}

export function FinalCta() {
    const { ziggy, site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const whatsappHref = site.whatsapp
        ? `https://wa.me/${site.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(t('trascendental.whatsapp.default_prefill'))}`
        : null;

    return (
        <section className="border-t border-black/15 px-4 py-16 sm:px-6 lg:px-8">
            <div className="mx-auto grid max-w-[1500px] gap-8 md:grid-cols-[1fr_auto] md:items-end">
                <h2 className="max-w-4xl text-5xl font-black uppercase leading-none sm:text-7xl">
                    {t('trascendental.home.final')}
                </h2>
                <div className="flex flex-col gap-3 sm:flex-row md:flex-col lg:flex-row">
                    {whatsappHref ? (
                        <a
                            href={whatsappHref}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-black bg-black px-5 text-xs font-bold uppercase text-white sm:min-h-12 sm:px-6 sm:text-sm"
                        >
                            WhatsApp
                            <MessageCircle className="h-4 w-4" />
                        </a>
                    ) : (
                        <EditorialButton href={route('trascendental.contact', undefined, false, ziggy)} dark>
                            {t('trascendental.hero.produce_cta')}
                        </EditorialButton>
                    )}
                    {site.email ? (
                        <a
                            href={`mailto:${site.email}`}
                            className="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-black px-5 text-xs font-bold uppercase text-black sm:min-h-12 sm:px-6 sm:text-sm"
                        >
                            {t('trascendental.contact.email_cta')}
                            <Mail className="h-4 w-4" />
                        </a>
                    ) : null}
                </div>
            </div>
        </section>
    );
}
