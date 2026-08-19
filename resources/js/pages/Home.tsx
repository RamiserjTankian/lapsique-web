import { Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    ChevronLeft,
    ChevronRight,
    MessageCircle,
} from 'lucide-react';
import { useEffect, useMemo, useState, type ReactNode } from 'react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { LandingPageSection } from '@/components/lapsique/LandingPageSection';
import { ReelPlayerModal } from '@/components/lapsique/ReelPlayerModal';
import { ReelLoopCard } from '@/components/lapsique/ReelLoopCard';
import { ReelPlayerProvider } from '@/hooks/useReelPlayerModal';
import { openBookingModal } from '@/lib/openBookingModal';
import { route } from '@/lib/route';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { EditorialVideoPlayer } from '@/components/lapsique/EditorialVideoPlayer';
import { FunnelFAQ } from '@/components/lapsique/funnel/FunnelFAQ';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { useTranslations } from '@/hooks/useTranslations';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { landingPageStackClass } from '@/lib/landingSection';
import { cn, formatMxn } from '@/lib/utils';
import type {
    BookingSlot,
    DjItem,
    EventItem,
    HeroBackgroundImageData,
    HeroProofVideoData,
    HomePortfolioOverview,
    LandingVideoEntry,
    LandingVideosProps,
    PageProps,
    PortfolioItemData,
    ServicePortfolioKey,
    ServicePortfolioMedia,
    VideoItem,
} from '@/types';

interface HomeProps {
    title: string;
    subtitle: string;
    price: number;
    slots: BookingSlot[];
    portfolioItems: PortfolioItemData[];
    heroBackgroundImage: HeroBackgroundImageData | null;
    landingVideos: LandingVideosProps | null;
    heroProofVideo: HeroProofVideoData | null;
    sceneDjs: DjItem[];
    sceneVideos: VideoItem[];
    sceneEvents: EventItem[];
    sceneMedia: PortfolioItemData[];
    portfolioOverview?: HomePortfolioOverview | null;
    errors?: Record<string, string>;
}

function isPlayableLandingVideo(
    entry: LandingVideoEntry | null | undefined,
): entry is LandingVideoEntry {
    return Boolean(entry?.src?.trim());
}

function pickUniquePlayableVideos(
    entries: Array<LandingVideoEntry | null | undefined>,
    limit = 3,
): LandingVideoEntry[] {
    const seen = new Set<string>();
    const result: LandingVideoEntry[] = [];

    for (const entry of entries) {
        if (!isPlayableLandingVideo(entry) || seen.has(entry.src)) {
            continue;
        }

        seen.add(entry.src);
        result.push(entry);

        if (result.length >= limit) {
            break;
        }
    }

    return result;
}

function buildFeaturedReelFallbackPool(
    landingVideos: LandingVideosProps | null | undefined,
): Array<LandingVideoEntry | null | undefined> {
    if (!landingVideos) {
        return [];
    }

    return [
        landingVideos.pauta,
        landingVideos.package ?? landingVideos.gear,
        landingVideos.floats?.[0],
        landingVideos.floats?.[1],
        ...landingVideos.creative,
        ...landingVideos.equipment,
        ...landingVideos.aftermovies,
    ];
}

export default function Home({
    title,
    subtitle,
    price,
    slots,
    portfolioItems,
    heroBackgroundImage,
    landingVideos,
    heroProofVideo,
    sceneDjs,
    sceneVideos,
    sceneEvents,
    sceneMedia,
    portfolioOverview = null,
    errors,
}: HomeProps) {
    const { site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const featuredReelFallbackPool = buildFeaturedReelFallbackPool(landingVideos);
    useEffect(() => {
        trackBookingEvent('booking_page_viewed', {
            section: 'home_business_content',
            content_name: t('pages.home.hero_eyebrow'),
            content_category: 'business_content_booking',
        });
    }, [t]);

    const openBooking = () => {
        openBookingModal({
            source: 'home_business',
            analyticsEvent: 'hero_cta_clicked',
            analyticsPayload: {
                content_name: t('pages.home.hero_eyebrow'),
                content_category: 'business_content_booking',
            },
        });
    };

    return (
        <SiteLayout>
            <ReelPlayerProvider>
            <SeoHead />

            <div className={landingPageStackClass}>
            <BusinessHero
                title={title}
                subtitle={subtitle}
                price={price}
                heroBackgroundImage={heroBackgroundImage}
                landingHero={landingVideos?.hero ?? null}
                heroProofVideo={heroProofVideo}
                portfolioItems={portfolioItems}
                portfolioOverview={portfolioOverview}
                whatsapp={site.whatsapp}
                onBook={openBooking}
            />

            <FeaturedSafeEvent events={sceneEvents} />

            <ServiceLandingLinks portfolioOverview={portfolioOverview} />

            <HomePortfolioProof
                portfolioOverview={portfolioOverview}
                portfolioItems={portfolioItems}
                landingVideos={landingVideos}
                onBook={openBooking}
            />

            {!portfolioOverview ? (
                <FeaturedReel
                    videos={[
                        landingVideos?.pauta ?? null,
                        landingVideos?.package ?? landingVideos?.gear ?? null,
                        landingVideos?.floats?.[0] ?? null,
                    ]}
                    fallbackPool={featuredReelFallbackPool}
                    bookingSource="featured_reel_proof"
                />
            ) : null}

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                paymentProvider="stripe"
                popupVariant="home"
                popupPortfolioItems={portfolioItems}
                popupHeroProofVideo={heroProofVideo}
            />

            <FunnelFAQ variant="home" />

            <SceneArchive
                djs={sceneDjs}
                videos={sceneVideos}
                events={sceneEvents}
                media={sceneMedia}
                whatsapp={site.whatsapp}
            />

            </div>
            <ReelPlayerModal />
            </ReelPlayerProvider>
        </SiteLayout>
    );
}

function FeaturedSafeEvent({ events }: { events: EventItem[] }) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';
    const event = events.find((item) => item.slug === 'safe-by-varuna-1-edition');

    if (!event) return null;

    const product = event.ticket_products?.[0] ?? null;
    const eventUrl = route('events.show', { event: event.slug }, false, ziggy);

    return (
        <section className="border-y border-foreground/20 py-8 sm:py-10" aria-labelledby="home-safe-event-title">
            <div className="grid gap-6 md:grid-cols-[12rem_minmax(0,1fr)_auto] md:items-center">
                {event.cover_url ? (
                    <Link href={eventUrl} className="block overflow-hidden bg-muted focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
                        <img
                            src={event.cover_url}
                            alt={en ? 'Safe by Varuna 1st Edition event poster' : 'Cartel del evento Safe by Varuna 1 edition'}
                            className="aspect-[16/10] w-full object-cover md:aspect-[4/3]"
                        />
                    </Link>
                ) : null}
                <div>
                    <p className="alpha-kicker text-primary">Lapsique Originals / {en ? 'Next event' : 'Próximo evento'}</p>
                    <h2 id="home-safe-event-title" className="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">Safe by Varuna 1 edition</h2>
                    <p className="mt-3 text-sm leading-6 text-muted-foreground">
                        {en ? 'KAPI · August 27 · 10:00 p.m. · Casa Luma, CDMX' : 'KAPI · 27 de agosto · 10:00 p. m. · Casa Luma, CDMX'}
                    </p>
                </div>
                <div className="md:text-right">
                    {product ? <p className="mb-3 text-sm font-semibold tabular-nums">{formatMxn(product.total)} MXN</p> : null}
                    <Link
                        href={eventUrl}
                        className="inline-flex min-h-12 w-full items-center justify-center gap-2 bg-foreground px-5 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-background focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary hover:bg-primary hover:text-primary-foreground md:w-auto"
                    >
                        {en ? 'View event' : 'Ver evento'} <ArrowRight className="size-4" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </section>
    );
}

function SceneArchive({
    djs,
    videos,
    events,
    media,
    whatsapp,
}: {
    djs: DjItem[];
    videos: VideoItem[];
    events: EventItem[];
    media: PortfolioItemData[];
    whatsapp: string;
}) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';
    const videosUrl = route('videos.index', undefined, false, ziggy);
    const djsUrl = route('djs.index', undefined, false, ziggy);
    const eventsUrl = route('events.index', undefined, false, ziggy);
    const djSetUrl = route('djset.show', undefined, false, ziggy);
    const whatsappUrl = `https://wa.me/${whatsapp}?text=${encodeURIComponent(
        en
            ? 'Hi, I want to produce audiovisual content for an event with Lapsique Media.'
            : 'Hola, quiero producir contenido audiovisual para un evento con Lapsique Media.',
    )}`;

    return (
        <section
            id="escena"
            className="relative left-1/2 w-screen -translate-x-1/2 scroll-mt-20 overflow-hidden bg-[#07090b] text-white"
            data-analytics-section="lapsique_scene_archive"
        >
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 md:py-24">
                <header className="grid gap-8 border-b border-white/20 pb-12 lg:grid-cols-[0.7fr_0.3fr] lg:items-end">
                    <div>
                        <p className="alpha-kicker text-primary">Lapsique Originals / Psique Sessions</p>
                        <h2 className="mt-5 max-w-5xl text-5xl font-semibold leading-[0.86] text-white sm:text-6xl md:text-8xl">
                            {en ? 'Electronic music, visuals, and live sets.' : 'Música electrónica, visuales y sets en vivo.'}
                        </h2>
                    </div>
                    <p className="max-w-md text-base leading-relaxed text-white/62">
                        {en
                            ? 'An audiovisual archive of the electronic scene in Riviera Maya: complete DJ sets, aftermovies, artists, venues, and collaborations produced by Lapsique Media.'
                            : 'Un archivo audiovisual de la escena electrónica en Riviera Maya: DJ sets completos, aftermovies, artistas, locaciones y colaboraciones producidas por Lapsique Media.'}
                    </p>
                </header>

                {videos.length > 0 ? (
                    <EditorialBlock
                        eyebrow="Psique Sessions"
                        title={en ? 'Complete sets, not disposable clips.' : 'Sets completos, no clips desechables.'}
                        href={videosUrl}
                        cta={en ? 'View all sessions' : 'Ver todas las sesiones'}
                    >
                        <div className="grid gap-px bg-white/20 md:grid-cols-3">
                            {videos.slice(0, 3).map((video) => (
                                <Link
                                    key={video.id}
                                    href={route('videos.show', { video: video.slug }, false, ziggy)}
                                    className="group min-w-0 bg-[#07090b]"
                                    onClick={() => trackBookingEvent('scene_content_opened', { content_name: video.title, content_category: 'psique_session' })}
                                >
                                    {video.thumbnail_url ? (
                                        <div className="aspect-video overflow-hidden bg-black">
                                            <img src={video.thumbnail_url} alt={video.title} loading="lazy" className="h-full w-full object-cover opacity-85 transition duration-500 group-hover:scale-[1.03] group-hover:opacity-100" />
                                        </div>
                                    ) : null}
                                    <div className="border-t border-white/15 p-5">
                                        <p className="alpha-kicker text-primary">Session {String(video.id).padStart(3, '0')}</p>
                                        <h3 className="mt-3 line-clamp-3 text-2xl font-semibold leading-tight text-white">{shortVideoTitle(video.title)}</h3>
                                        {video.location ? <p className="mt-3 text-sm text-white/50">{video.location}</p> : null}
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </EditorialBlock>
                ) : null}

                {djs.length > 0 ? (
                    <EditorialBlock
                        eyebrow={en ? 'Artists' : 'Artistas'}
                        title={en ? 'DJs documented by Lapsique.' : 'DJs documentados por Lapsique.'}
                        href={djsUrl}
                        cta={en ? 'Explore DJs' : 'Explorar DJs'}
                    >
                        <div className="grid grid-cols-2 gap-px bg-white/20 sm:grid-cols-3 lg:grid-cols-6">
                            {djs.slice(0, 6).map((dj) => (
                                <Link key={dj.id} href={route('djs.show', { dj: dj.slug }, false, ziggy)} className="group bg-[#07090b]">
                                    {dj.avatar_url ? (
                                        <div className="aspect-[3/4] overflow-hidden bg-black">
                                            <img src={dj.avatar_url} alt={dj.name} loading="lazy" className="h-full w-full object-cover grayscale transition duration-500 group-hover:scale-[1.03] group-hover:grayscale-0" />
                                        </div>
                                    ) : null}
                                    <div className="border-t border-white/15 p-4">
                                        <h3 className="font-ui-display text-lg font-bold uppercase leading-none text-white">{dj.name}</h3>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </EditorialBlock>
                ) : null}

                {events.length > 0 ? (
                    <EditorialBlock
                        eyebrow={en ? 'Produced events' : 'Eventos producidos'}
                        title={en ? 'Shows, collaborations, and residences.' : 'Shows, colaboraciones y residencias.'}
                        href={eventsUrl}
                        cta={en ? 'Open event archive' : 'Abrir archivo de eventos'}
                    >
                        <div className="divide-y divide-white/20 border-y border-white/20">
                            {events.slice(0, 3).map((event) => (
                                <Link
                                    key={event.id}
                                    href={route('events.show', { event: event.slug }, false, ziggy)}
                                    className="group grid gap-5 py-5 sm:grid-cols-[1fr_auto] sm:items-center"
                                >
                                    <div>
                                        <h3 className="text-2xl font-semibold leading-tight text-white group-hover:text-primary md:text-3xl">{event.title}</h3>
                                        <p className="mt-2 text-sm text-white/50">{event.location_name || event.venue || event.city || (en ? 'Riviera Maya' : 'Riviera Maya')}</p>
                                    </div>
                                    <span className="alpha-kicker text-white/55">{formatEventDate(event.starts_at, locale)}</span>
                                </Link>
                            ))}
                        </div>
                    </EditorialBlock>
                ) : null}

                {media.length > 0 ? (
                    <EditorialBlock
                        eyebrow="Nightlife / Aftermovies"
                        title={en ? 'The energy of every location.' : 'La energía de cada locación.'}
                        href={`${videosUrl}#aftermovies`}
                        cta={en ? 'View aftermovies' : 'Ver aftermovies'}
                    >
                        <div className="grid grid-cols-2 gap-2 md:grid-cols-5 md:grid-rows-2">
                            {media.slice(0, 7).map((item, index) => {
                                const image = item.poster_url || item.asset_url;
                                if (!image) return null;
                                return (
                                    <Link
                                        key={item.id}
                                        href={route('portfolio.index', undefined, false, ziggy)}
                                        className={cn(
                                            'group relative min-h-44 overflow-hidden bg-black',
                                            index === 0 && 'col-span-2 row-span-2 min-h-80 md:min-h-[31rem]',
                                            index > 4 && 'max-md:hidden',
                                        )}
                                    >
                                        <img src={image} alt={item.title || (en ? 'Lapsique nightlife archive' : 'Archivo nightlife de Lapsique')} loading="lazy" className="absolute inset-0 h-full w-full object-cover opacity-80 transition duration-500 group-hover:scale-[1.025] group-hover:opacity-100" />
                                        <span className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 to-transparent p-4 pt-12 font-ui-display text-xs font-bold uppercase tracking-[0.08em] text-white">
                                            {item.title || item.type}
                                        </span>
                                    </Link>
                                );
                            })}
                        </div>
                    </EditorialBlock>
                ) : null}

                <div className="mt-16 grid gap-8 border border-primary/70 p-6 md:grid-cols-[1fr_auto] md:items-end md:p-10">
                    <div>
                        <p className="alpha-kicker text-primary">Lapsique Media / Production</p>
                        <h2 className="mt-4 max-w-3xl text-4xl font-semibold leading-[0.92] text-white md:text-6xl">
                            {en ? 'Bring your next set or event into the archive.' : 'Haz que tu próximo set o evento forme parte del archivo.'}
                        </h2>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                        <Link href={djSetUrl} className="inline-flex min-h-13 items-center justify-center bg-primary px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-white hover:bg-white hover:text-black">
                            {en ? 'Record my DJ set' : 'Grabar mi DJ set'}
                        </Link>
                        <a href={whatsappUrl} target="_blank" rel="noopener noreferrer" className="inline-flex min-h-13 items-center justify-center border border-white/35 px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-white hover:border-white hover:bg-white hover:text-black">
                            {en ? 'Produce an event' : 'Producir un evento'}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}

function EditorialBlock({
    eyebrow,
    title,
    href,
    cta,
    children,
}: {
    eyebrow: string;
    title: string;
    href: string;
    cta: string;
    children: ReactNode;
}) {
    return (
        <div className="mt-16 md:mt-24">
            <div className="mb-7 grid gap-5 border-t border-white/20 pt-5 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <p className="alpha-kicker text-white/45">{eyebrow}</p>
                    <h2 className="mt-3 max-w-4xl text-4xl font-semibold leading-[0.9] text-white md:text-6xl">{title}</h2>
                </div>
                <Link href={href} className="font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-primary hover:text-white">{cta} →</Link>
            </div>
            {children}
        </div>
    );
}

function shortVideoTitle(title: string): string {
    return title.split('|')[0]?.replace(/\s{2,}/g, ' ').trim() || title;
}

function formatEventDate(value: string | null, locale: string): string {
    if (!value) return locale === 'en' ? 'Archive' : 'Archivo';
    return new Intl.DateTimeFormat(locale === 'en' ? 'en-US' : 'es-MX', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}

type HomeServiceDefinition = {
    key: ServicePortfolioKey;
    href: string;
    es: {
        title: string;
        copy: string;
    };
    en: {
        title: string;
        copy: string;
    };
};

const HOME_SERVICE_DEFINITIONS: HomeServiceDefinition[] = [
    {
        key: 'content_creation',
        href: '/creacion-de-contenido-riviera-maya',
        es: {
            title: 'Contenido para redes',
            copy: 'Reels y fotografía para Instagram, TikTok y anuncios.',
        },
        en: {
            title: 'Social content',
            copy: 'Reels and photography for Instagram, TikTok, and ads.',
        },
    },
    {
        key: 'business_reels',
        href: '/reels-para-negocios',
        es: {
            title: 'Reels para negocios',
            copy: 'Contenido comercial pensado para anuncios.',
        },
        en: {
            title: 'Business reels',
            copy: 'Commercial content designed for ads.',
        },
    },
    {
        key: 'food_reels',
        href: '/reels-de-comida',
        es: {
            title: 'Reels para restaurantes',
            copy: 'Comida, ambiente y servicio listos para vender.',
        },
        en: {
            title: 'Restaurant reels',
            copy: 'Food, atmosphere, and service ready to sell.',
        },
    },
    {
        key: 'dj_set',
        href: '/dj-set',
        es: {
            title: 'Grabar un DJ set',
            copy: 'Registro cinematográfico de tu set, listo para publicar.',
        },
        en: {
            title: 'Record a DJ set',
            copy: 'A cinematic record of your set, ready to publish.',
        },
    },
    {
        key: 'event_coverage',
        href: '/cobertura-eventos-electronica',
        es: {
            title: 'Cobertura de eventos electrónicos',
            copy: 'Aftermovie, tomas de dron y fotografía editada.',
        },
        en: {
            title: 'Electronic event coverage',
            copy: 'Aftermovie, drone footage, and edited photography.',
        },
    },
    {
        key: 'multi_camera',
        href: '/multicamara',
        es: {
            title: 'Producción multicámara',
            copy: 'Drops, video continuo en Log, audio y fotos del evento.',
        },
        en: {
            title: 'Multi-camera production',
            copy: 'Drops, continuous Log video, audio, and event photography.',
        },
    },
    {
        key: 'drone_sessions',
        href: '/sesiones-de-dron',
        es: {
            title: 'Vuelos con dron',
            copy: 'Tomas aéreas para propiedades, venues y campañas.',
        },
        en: {
            title: 'Drone flights',
            copy: 'Aerial footage for properties, venues, and campaigns.',
        },
    },
    {
        key: 'construction_progress',
        href: '/avances-de-obra',
        es: {
            title: 'Avances de obra',
            copy: 'Reportes de avance con foto, video y dron.',
        },
        en: {
            title: 'Construction progress',
            copy: 'Progress reports with photography, video, and drone.',
        },
    },
];

function ServiceLandingLinks({
    portfolioOverview,
}: {
    portfolioOverview: HomePortfolioOverview | null;
}) {
    const { locale } = useTranslations();
    const isEnglish = locale === 'en';
    const previewByService = new Map(
        (portfolioOverview?.servicePreviews ?? []).map((preview) => [
            preview.serviceKey,
            preview,
        ]),
    );
    const services = HOME_SERVICE_DEFINITIONS.map((definition) => {
        const preview = previewByService.get(definition.key);
        const localized = isEnglish ? definition.en : definition.es;

        return {
            ...definition,
            href: preview?.href || definition.href,
            title: preview?.label[isEnglish ? 'en' : 'es'] || localized.title,
            copy: localized.copy,
            media: preview?.media,
            stats: preview?.stats,
        };
    });

    return (
        <section
            id="servicios"
            className="relative left-1/2 w-screen -translate-x-1/2 scroll-mt-24 bg-background"
            data-analytics-section="home_service_portfolio"
        >
            <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6 md:py-20">
                <div className="grid gap-6 border-t border-foreground/20 pt-6 lg:grid-cols-[0.48fr_1fr] lg:items-end">
                    <div>
                        <p className="alpha-kicker text-primary">Lapsique / {isEnglish ? 'Services' : 'Servicios'}</p>
                        <h2 className="mt-4 max-w-3xl text-4xl font-semibold leading-[0.92] text-foreground text-balance md:text-6xl">
                            {isEnglish ? 'Eight ways to turn real work into useful content.' : 'Ocho formas de convertir trabajo real en contenido útil.'}
                        </h2>
                    </div>
                    <p className="max-w-xl text-base leading-relaxed text-muted-foreground text-pretty lg:justify-self-end">
                        {isEnglish
                            ? 'Choose the production that matches your objective. Each service opens a focused page with its own portfolio, deliverables, and booking flow.'
                            : 'Elige la producción que coincide con tu objetivo. Cada servicio abre una página enfocada con portafolio, entregables y reserva propios.'}
                    </p>
                </div>

                <div className="-mx-4 mt-9 grid snap-x snap-mandatory auto-cols-[minmax(17rem,84vw)] grid-flow-col gap-3 overflow-x-auto px-4 pb-3 sm:-mx-6 sm:px-6 lg:mx-0 lg:grid-flow-row lg:grid-cols-4 lg:overflow-visible lg:px-0 lg:pb-0">
                    {services.map((service, index) => (
                        <Link
                            key={service.key}
                            href={service.href}
                            data-no-lightbox="true"
                            className="group/card flex min-w-0 snap-start flex-col overflow-hidden bg-secondary/60 outline outline-1 -outline-offset-1 outline-foreground/12 transition-[background-color,transform] duration-150 hover:bg-secondary active:scale-[0.96] motion-reduce:transition-none"
                            onClick={() => trackBookingEvent('portfolio_project_selected', {
                                section: 'home_service_portfolio',
                                service: service.key,
                                target: service.href,
                            })}
                        >
                            <div className="relative aspect-[4/3] overflow-hidden bg-[#080a0c]">
                                {service.media ? (
                                    service.media.kind === 'image' || service.media.poster ? (
                                        <img
                                            src={service.media.kind === 'image' ? service.media.src : service.media.poster ?? ''}
                                            alt={service.media.alt}
                                            loading="lazy"
                                            data-no-lightbox="true"
                                            className="h-full w-full object-cover opacity-85 outline outline-1 -outline-offset-1 outline-black/10 transition-[opacity,scale] duration-300 group-hover/card:scale-[1.025] group-hover/card:opacity-100 motion-reduce:transition-none"
                                        />
                                    ) : (
                                        <video
                                            src={service.media.src}
                                            aria-label={service.media.alt}
                                            muted
                                            playsInline
                                            preload="none"
                                            data-no-lightbox="true"
                                            className="h-full w-full object-cover opacity-85"
                                        />
                                    )
                                ) : (
                                    <div className="absolute inset-0 bg-[linear-gradient(135deg,oklch(0.2_0_0),oklch(0.12_0_0))]" />
                                )}
                                <span className="absolute left-3 top-3 bg-black/72 px-2.5 py-1.5 font-mono text-[0.62rem] uppercase tracking-[0.16em] text-white">
                                    {String(index + 1).padStart(2, '0')} / 08
                                </span>
                            </div>
                            <div className="flex flex-1 flex-col p-5">
                                <h3 className="text-xl font-semibold leading-[1.05] text-foreground text-balance transition-[color] group-hover/card:text-primary">
                                    {service.title}
                                </h3>
                                <p className="mt-3 text-sm leading-relaxed text-muted-foreground text-pretty">
                                    {service.copy}
                                </p>
                                <div className="mt-auto flex items-end justify-between gap-3 pt-6">
                                    <span className="font-ui-display text-xs font-bold uppercase tracking-[0.08em] text-primary">
                                        {isEnglish ? 'View service' : 'Ver servicio'}
                                    </span>
                                    {service.stats?.projectCount ? (
                                        <span className="font-mono text-[0.62rem] uppercase tracking-[0.12em] text-muted-foreground tabular-nums">
                                            {service.stats.projectCount} {isEnglish ? 'projects' : 'proyectos'}
                                        </span>
                                    ) : (
                                        <ArrowRight className="size-4 text-primary transition-transform group-hover/card:translate-x-0.5" />
                                    )}
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </section>
    );
}

function HomePortfolioProof({
    portfolioOverview,
    portfolioItems,
    landingVideos,
    onBook,
}: {
    portfolioOverview: HomePortfolioOverview | null;
    portfolioItems: PortfolioItemData[];
    landingVideos: LandingVideosProps | null;
    onBook: () => void;
}) {
    const { locale } = useTranslations();
    const en = locale === 'en';
    const featuredMedia = useMemo(
        () => resolveHomeFeaturedMedia(portfolioOverview, portfolioItems, landingVideos),
        [landingVideos, portfolioItems, portfolioOverview],
    );
    const photos = featuredMedia.filter((item) => item.kind === 'image').slice(0, 8);
    const videos = featuredMedia.filter((item) => item.kind === 'video').slice(0, 6);
    const archiveCount = portfolioOverview?.archiveMediaCount ?? 0;
    const projectCount = countDistinctProjects([...photos, ...videos]);
    const sectionRef = useSectionEvent<HTMLElement>('service_portfolio_viewed', {
        section: 'home_portfolio_overview',
        media_count: featuredMedia.length,
        project_count: projectCount,
    });

    if (featuredMedia.length === 0) {
        return null;
    }

    const title = archiveCount >= 200
        ? en
            ? 'More than 200 audiovisual pieces produced by Lapsique.'
            : 'Más de 200 piezas audiovisuales producidas por Lapsique.'
        : archiveCount > 0
            ? en
                ? `${archiveCount} audiovisual pieces in the Lapsique archive.`
                : `${archiveCount} piezas audiovisuales en el archivo de Lapsique.`
            : en
                ? 'A living audiovisual archive made by Lapsique.'
                : 'Un archivo audiovisual vivo producido por Lapsique.';

    const approvedCopy = en
        ? 'A real archive of photography and video for restaurants, brands, artists, events, properties, and developments—created from Riviera Maya and Mérida to sell, document, and endure.'
        : 'Un archivo real de fotografía y video para restaurantes, marcas, artistas, eventos, propiedades y desarrollos, creado desde Riviera Maya y Mérida para vender, documentar y permanecer.';

    return (
        <section
            ref={sectionRef}
            className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#07090b] text-white"
            data-analytics-section="home_portfolio_overview"
        >
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 md:py-24">
                <header className="grid gap-8 border-t border-white/20 pt-6 lg:grid-cols-[0.7fr_0.3fr] lg:items-end">
                    <div>
                        <p className="alpha-kicker text-primary">Lapsique / {en ? 'Real archive' : 'Archivo real'}</p>
                        <h2 className="mt-5 max-w-5xl text-5xl font-semibold leading-[0.88] text-white text-balance sm:text-6xl md:text-8xl">
                            {title}
                        </h2>
                    </div>
                    <div className="lg:justify-self-end">
                        <p className="max-w-md text-base leading-relaxed text-white/68 text-pretty">
                            {approvedCopy}
                        </p>
                        <dl className="mt-7 grid grid-cols-2 gap-5 border-t border-white/20 pt-5">
                            <div>
                                <dt className="font-mono text-[0.62rem] uppercase tracking-[0.16em] text-white/45">
                                    {en ? 'Archive' : 'Archivo'}
                                </dt>
                                <dd className="mt-2 text-3xl font-semibold text-primary tabular-nums">
                                    {archiveCount > 0 ? `${archiveCount}+` : featuredMedia.length}
                                </dd>
                            </div>
                            <div>
                                <dt className="font-mono text-[0.62rem] uppercase tracking-[0.16em] text-white/45">
                                    {en ? 'Projects shown' : 'Proyectos visibles'}
                                </dt>
                                <dd className="mt-2 text-3xl font-semibold text-white tabular-nums">
                                    {projectCount}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </header>

                {photos.length > 0 ? (
                    <div className="mt-12">
                        <div className="mb-5 flex items-end justify-between gap-4">
                            <div>
                                <p className="alpha-kicker text-white/45">{en ? 'Selected photography' : 'Fotografía seleccionada'}</p>
                                <h3 className="mt-2 text-3xl font-semibold leading-none text-white md:text-4xl">
                                    {en ? 'Different briefs. One visual standard.' : 'Proyectos distintos. Un mismo estándar visual.'}
                                </h3>
                            </div>
                            <span className="hidden font-mono text-[0.65rem] uppercase tracking-[0.14em] text-white/45 sm:block">
                                {photos.length} {en ? 'photographs' : 'fotografías'}
                            </span>
                        </div>
                        <div className="grid grid-flow-row-dense auto-rows-[9.5rem] grid-cols-2 gap-2 sm:auto-rows-[12rem] md:grid-cols-4">
                            {photos.map((item, index) => (
                                <button
                                    key={item.id}
                                    type="button"
                                    data-lightbox-trigger="true"
                                    className={cn(
                                        'group/photo relative min-w-0 overflow-hidden bg-black text-left outline outline-1 -outline-offset-1 outline-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.96] motion-reduce:transition-none',
                                        index === 0 && 'col-span-2 row-span-2',
                                        index === 3 && 'row-span-2',
                                        index === 6 && 'col-span-2',
                                    )}
                                    onClick={() => trackBookingEvent('portfolio_project_selected', {
                                        section: 'home_portfolio_overview',
                                        project: item.projectKey,
                                        media_id: item.id,
                                    })}
                                >
                                    <img
                                        src={item.src}
                                        alt={item.alt}
                                        loading="lazy"
                                        className="h-full w-full object-cover opacity-82 outline outline-1 -outline-offset-1 outline-white/10 transition-[opacity,scale] duration-300 group-hover/photo:scale-[1.025] group-hover/photo:opacity-100 motion-reduce:transition-none"
                                    />
                                    <span className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 to-transparent px-3 pb-3 pt-12 font-ui-display text-xs font-bold uppercase tracking-[0.08em] text-white">
                                        {item.projectLabel}
                                    </span>
                                </button>
                            ))}
                        </div>
                    </div>
                ) : null}

                {videos.length > 0 ? (
                    <div className="mt-16">
                        <div className="mb-6 grid gap-4 border-t border-white/20 pt-5 md:grid-cols-[1fr_auto] md:items-end">
                            <div>
                                <p className="alpha-kicker text-white/45">{en ? 'Motion archive' : 'Archivo en movimiento'}</p>
                                <h3 className="mt-3 max-w-4xl text-4xl font-semibold leading-[0.92] text-white text-balance md:text-6xl">
                                    {en ? 'Six real productions. Play only what interests you.' : 'Seis producciones reales. Reproduce solo lo que te interese.'}
                                </h3>
                            </div>
                            <p className="max-w-sm text-sm leading-relaxed text-white/55 text-pretty">
                                {en ? 'Players load on interaction and preserve the original audio.' : 'Los reproductores cargan al interactuar y conservan el audio original.'}
                            </p>
                        </div>
                        <div className="-mx-4 grid snap-x snap-mandatory auto-cols-[minmax(18rem,86vw)] grid-flow-col gap-3 overflow-x-auto px-4 pb-3 sm:-mx-6 sm:px-6 lg:mx-0 lg:grid-flow-row lg:grid-cols-3 lg:overflow-visible lg:px-0 lg:pb-0">
                            {videos.map((item) => (
                                <article key={item.id} className="min-w-0 snap-start bg-black outline outline-1 -outline-offset-1 outline-white/15">
                                    <div>
                                        <EditorialVideoPlayer
                                            src={item.src}
                                            poster={item.poster}
                                            title={item.alt}
                                            preload="none"
                                            autoPlay={false}
                                            muted={false}
                                            hasAudio={item.hasAudio ?? false}
                                            onPlay={() => trackBookingEvent('portfolio_media_played', {
                                                section: 'home_portfolio_overview',
                                                project: item.projectKey,
                                                media_id: item.id,
                                            })}
                                            className={cn(
                                                'w-full',
                                                item.orientation === 'vertical' ? 'aspect-[9/16]' : 'aspect-video',
                                            )}
                                            videoClassName="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div className="border-t border-white/15 p-4">
                                        <p className="alpha-kicker text-primary">{item.sessionLabel || item.location || (en ? 'Real production' : 'Producción real')}</p>
                                        <h4 className="mt-2 text-xl font-semibold leading-tight text-white text-balance">{item.projectLabel}</h4>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                ) : null}

                <div className="mt-16 grid gap-8 border-t border-white/20 pt-8 md:grid-cols-[1fr_auto] md:items-center">
                    <p className="max-w-2xl text-2xl font-semibold leading-tight text-white text-balance md:text-3xl">
                        {en
                            ? 'Your project can be the next real case in this archive.'
                            : 'Tu proyecto puede ser el siguiente caso real de este archivo.'}
                    </p>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <Link
                            href="/portafolio"
                            className="inline-flex min-h-13 items-center justify-center border border-white/35 px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-white transition-[background-color,border-color,color,transform] hover:border-white hover:bg-white hover:text-black active:scale-[0.96] motion-reduce:transition-none"
                            onClick={() => trackBookingEvent('portfolio_cta_clicked', {
                                section: 'home_portfolio_overview',
                                target: '/portafolio',
                            })}
                        >
                            {en ? 'View full portfolio' : 'Ver portafolio completo'}
                        </Link>
                        <button
                            type="button"
                            onClick={() => {
                                trackBookingEvent('portfolio_cta_clicked', {
                                    section: 'home_portfolio_overview',
                                    target: 'booking',
                                });
                                onBook();
                            }}
                            className="inline-flex min-h-13 items-center justify-center gap-2 bg-primary px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-white transition-[background-color,color,transform] hover:bg-white hover:text-black active:scale-[0.96] motion-reduce:transition-none"
                        >
                            {en ? 'Plan my production' : 'Planear mi producción'}
                            <ArrowRight className="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </section>
    );
}

function resolveHomeFeaturedMedia(
    portfolioOverview: HomePortfolioOverview | null,
    portfolioItems: PortfolioItemData[],
    landingVideos: LandingVideosProps | null,
): ServicePortfolioMedia[] {
    if (portfolioOverview) {
        const overviewPool = uniqueServiceMedia([
            ...(portfolioOverview.featuredMedia ?? []),
            ...(portfolioOverview.servicePreviews ?? []).map((preview) => preview.media),
            ...(portfolioOverview.projects ?? []).flatMap((project) => project.media),
        ]);
        const photos = selectDiverseMedia(overviewPool, 'image', 8);
        const videos = selectDiverseMedia(overviewPool, 'video', 6);

        return [...photos, ...videos];
    }

    const fallbackPhotos = portfolioItems
        .filter((item) => item.media_type === 'image' && Boolean(item.asset_url || item.poster_url))
        .map<ServicePortfolioMedia>((item) => ({
            id: `legacy-photo-${item.id}`,
            projectKey: item.slug || `portfolio-${item.id}`,
            projectLabel: item.title || 'Lapsique Media',
            kind: 'image',
            src: item.asset_url || item.poster_url || '',
            orientation: item.orientation === 'horizontal' ? 'horizontal' : 'vertical',
            alt: item.title || 'Producción audiovisual de Lapsique Media',
            site: 'lapsique',
            services: [],
        }));
    const fallbackVideos = buildFeaturedReelFallbackPool(landingVideos)
        .filter(isPlayableLandingVideo)
        .map<ServicePortfolioMedia>((item, index) => ({
            id: `legacy-video-${index}`,
            projectKey: `video-${index + 1}`,
            projectLabel: item.title || 'Lapsique Media',
            kind: 'video',
            src: item.src,
            poster: item.poster,
            orientation: 'vertical',
            alt: item.title || 'Video producido por Lapsique Media',
            site: 'lapsique',
            services: [],
        }));

    return uniqueServiceMedia([...fallbackPhotos, ...fallbackVideos]);
}

function uniqueServiceMedia(items: ServicePortfolioMedia[]): ServicePortfolioMedia[] {
    const seen = new Set<string>();

    return items.filter((item) => {
        if (!item.src || seen.has(item.src)) {
            return false;
        }

        seen.add(item.src);
        return true;
    });
}

function countDistinctProjects(items: ServicePortfolioMedia[]): number {
    return new Set(items.map((item) => item.projectKey).filter(Boolean)).size;
}

function selectDiverseMedia(
    items: ServicePortfolioMedia[],
    kind: ServicePortfolioMedia['kind'],
    limit: number,
): ServicePortfolioMedia[] {
    const candidates = items.filter((item) => item.kind === kind);
    const selected: ServicePortfolioMedia[] = [];
    const selectedIds = new Set<string>();
    const seenProjects = new Set<string>();

    for (const item of candidates) {
        if (seenProjects.has(item.projectKey)) {
            continue;
        }

        selected.push(item);
        selectedIds.add(item.id);
        seenProjects.add(item.projectKey);

        if (selected.length >= limit) {
            return selected;
        }
    }

    for (const item of candidates) {
        if (selectedIds.has(item.id)) {
            continue;
        }

        selected.push(item);

        if (selected.length >= limit) {
            break;
        }
    }

    return selected;
}

function FeaturedReel({
    video,
    videos,
    fallbackPool = [],
    bookingSource,
}: {
    video?: LandingVideoEntry | null;
    videos?: Array<LandingVideoEntry | null | undefined>;
    fallbackPool?: Array<LandingVideoEntry | null | undefined>;
    bookingSource: string;
}) {
    const { t } = useTranslations();
    const playableVideos = pickUniquePlayableVideos(
        [...(videos ?? [video]), ...fallbackPool],
        3,
    );

    if (playableVideos.length === 0) {
        return null;
    }

    const showMultiColumn = playableVideos.length > 1;

    return (
        <LandingPageSection
            aria-label={t('pages.home.featured_reel_aria')}
            data-analytics-section={`featured_reel_${bookingSource}`}
            innerClassName={cn(
                'mx-auto grid w-full gap-4',
                showMultiColumn
                    ? 'max-w-[22rem] grid-cols-1 md:max-w-4xl md:grid-cols-3 xl:max-w-5xl'
                    : 'max-w-[22rem] grid-cols-1',
            )}
        >
            {playableVideos.map((entry, index) => (
                <div
                    key={`${entry.src}-${index}`}
                    className={cn('min-w-0', index > 0 && 'max-md:hidden')}
                >
                    <ReelLoopCard
                        src={entry.src}
                        poster={entry.poster}
                        title={entry.title ?? undefined}
                        bookingSource={bookingSource}
                    />
                </div>
            ))}
        </LandingPageSection>
    );
}

function BusinessHero({
    title,
    subtitle,
    price,
    heroBackgroundImage,
    landingHero,
    heroProofVideo,
    portfolioItems,
    portfolioOverview,
    whatsapp,
    onBook,
}: {
    title: string;
    subtitle: string;
    price: number;
    heroBackgroundImage: HeroBackgroundImageData | null;
    landingHero: LandingVideoEntry | null;
    heroProofVideo: HeroProofVideoData | null;
    portfolioItems: PortfolioItemData[];
    portfolioOverview: HomePortfolioOverview | null;
    whatsapp: string;
    onBook: () => void;
}) {
    const { t, locale } = useTranslations();
    const en = locale === 'en';
    const whatsappUrl = `https://wa.me/${whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(
        en
            ? 'Hi, I want to plan a photo and video production for my business with Lapsique Media.'
            : 'Hola, quiero planear una producción de foto y video para mi negocio con Lapsique Media.',
    )}`;

    return (
        <section className="relative left-1/2 w-screen -translate-x-1/2 border-b border-foreground/20 bg-background">
            <div className="mx-auto grid max-w-[1440px] lg:min-h-[calc(100svh-4.5rem)] lg:grid-cols-[minmax(360px,0.72fr)_minmax(0,1.28fr)]">
                <div className="flex min-w-0 flex-col justify-center border-r border-foreground/15 px-5 py-12 sm:px-8 lg:px-12 lg:py-16">
                    <h1 className="max-w-2xl break-normal text-balance text-[clamp(2.25rem,11.25vw,4rem)] font-semibold leading-[0.9] text-foreground [overflow-wrap:normal] [word-break:normal] hyphens-none sm:text-[3.55rem] lg:text-[3.8rem] xl:text-[4rem]">
                        {title}
                    </h1>
                    <p className="mt-7 max-w-xl text-base leading-relaxed text-muted-foreground text-pretty lg:text-lg">
                        {subtitle}
                    </p>
                    <div className="mt-8 border-y border-foreground/15 py-5">
                        <div className="flex items-end justify-between gap-5">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">
                                    {en ? 'From' : 'Desde'}
                                </p>
                                <p className="mt-2 font-mono-tabular text-4xl font-semibold text-foreground">{formatMxn(price)}</p>
                            </div>
                            <span className="font-mono text-xs font-semibold text-primary">MXN</span>
                        </div>
                        <PaymentTrustOrTestMode variant="stripe" layout="compact" className="mt-3" />
                    </div>
                    <div className="mt-6 grid gap-3 sm:grid-cols-2">
                        <BookingCtaButton
                            type="button"
                            className="min-h-14 w-full rounded-none bg-foreground px-4 text-background shadow-none hover:bg-primary hover:text-white"
                            data-analytics-cta="hero_booking"
                            onClick={onBook}
                        >
                            {t('common.cta.book_now')}
                            <ArrowRight className="h-5 w-5" aria-hidden="true" />
                        </BookingCtaButton>
                        <a
                            href={whatsappUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            data-analytics-cta="home_hero_whatsapp"
                            className="inline-flex min-h-14 items-center justify-center gap-2 bg-[#25D366] px-4 text-center font-ui-display text-sm font-bold uppercase tracking-[0.07em] text-[#061a0f] transition-[background-color,color,transform] hover:bg-[#1fbd5a] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 active:scale-[0.96] motion-reduce:transition-none"
                            onClick={() => trackBookingEvent('content_creation_whatsapp_cta_clicked', {
                                section: 'home_hero',
                                source: 'home_hero',
                                service: 'content_creation',
                            })}
                        >
                            <MessageCircle className="size-5" aria-hidden="true" />
                            {en ? 'Talk on WhatsApp' : 'Hablar por WhatsApp'}
                        </a>
                    </div>
                </div>

                <HeroMediaCarousel
                    title={title}
                    heroBackgroundImage={heroBackgroundImage}
                    landingHero={landingHero}
                    heroProofVideo={heroProofVideo}
                    portfolioItems={portfolioItems}
                    heroMedia={portfolioOverview?.heroMedia ?? []}
                />
            </div>
        </section>
    );
}

type HeroMediaItem = {
    id: string;
    kind: 'image' | 'video';
    src: string;
    poster?: string | null;
    alt: string;
};

function HeroMediaCarousel({
    title,
    heroBackgroundImage,
    landingHero,
    heroProofVideo,
    portfolioItems,
    heroMedia,
}: {
    title: string;
    heroBackgroundImage: HeroBackgroundImageData | null;
    landingHero: LandingVideoEntry | null;
    heroProofVideo: HeroProofVideoData | null;
    portfolioItems: PortfolioItemData[];
    heroMedia: ServicePortfolioMedia[];
}) {
    const media = useMemo<HeroMediaItem[]>(() => {
        const items: HeroMediaItem[] = [];
        const seen = new Set<string>();
        const add = (item: HeroMediaItem | null) => {
            if (!item?.src || seen.has(item.src)) return;
            seen.add(item.src);
            items.push(item);
        };

        if (heroMedia.length > 0) {
            heroMedia.slice(0, 3).forEach((item) => add({
                id: `overview-${item.id}`,
                kind: item.kind,
                src: item.src,
                poster: item.poster,
                alt: item.alt,
            }));
        } else {
            if (heroBackgroundImage?.url) {
                add({
                    id: 'hero-image',
                    kind: 'image',
                    src: heroBackgroundImage.url,
                    alt: heroBackgroundImage.alt || title,
                });
            }

            if (isPlayableLandingVideo(landingHero)) {
                add({
                    id: 'hero-video',
                    kind: 'video',
                    src: landingHero.src,
                    poster: landingHero.poster,
                    alt: landingHero.title || title,
                });
            }

            if (heroProofVideo?.playback_url) {
                add({
                    id: 'proof-video',
                    kind: 'video',
                    src: heroProofVideo.playback_url,
                    poster: heroProofVideo.poster_url,
                    alt: heroProofVideo.title || title,
                });
            }

            portfolioItems
                .filter((item) => item.media_type === 'image' && Boolean(item.asset_url || item.poster_url))
                .slice(0, 3)
                .forEach((item) => add({
                    id: `portfolio-${item.id}`,
                    kind: 'image',
                    src: item.asset_url || item.poster_url || '',
                    alt: item.title || 'Producción audiovisual de Lapsique Media',
                }));
        }

        return items;
    }, [heroBackgroundImage, heroMedia, heroProofVideo, landingHero, portfolioItems, title]);
    const [activeIndex, setActiveIndex] = useState(0);
    const active = media[activeIndex] ?? media[0];

    useEffect(() => {
        if (
            media.length < 2
            || window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
        ) {
            return;
        }

        const timer = window.setInterval(() => {
            setActiveIndex((current) => (current + 1) % media.length);
        }, 6500);

        return () => window.clearInterval(timer);
    }, [media.length]);

    useEffect(() => {
        if (activeIndex >= media.length) setActiveIndex(0);
    }, [activeIndex, media.length]);

    const navigate = (direction: number) => {
        if (media.length < 2) return;
        setActiveIndex((current) => (current + direction + media.length) % media.length);
    };

    return (
        <div className="relative min-h-[62svh] overflow-hidden bg-black text-white lg:min-h-full" data-hero-media-carousel="true">
            {active?.kind === 'video' ? (
                <AutoplayVideo
                    key={active.id}
                    src={active.src}
                    poster={active.poster}
                    title={active.alt}
                    eager
                    pauseWhenOffscreen={false}
                    className="absolute inset-0 h-full w-full rounded-none"
                    videoClassName="h-full w-full object-cover"
                />
            ) : active ? (
                <img
                    key={active.id}
                    src={active.src}
                    alt={active.alt}
                    className="absolute inset-0 h-full w-full object-cover"
                    fetchPriority={activeIndex === 0 ? 'high' : 'auto'}
                />
            ) : null}

            <div className="pointer-events-none absolute inset-0 bg-black/8" />

            {media.length > 1 ? (
                <div className="absolute inset-x-5 bottom-5 z-10 flex items-center justify-between gap-4 border-t border-white/25 pt-4 sm:bottom-8 sm:left-8 sm:right-24">
                    <div className="flex items-center">
                        {media.map((item, index) => (
                            <button
                                key={item.id}
                                type="button"
                                className="group flex h-11 w-7 items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                onClick={() => setActiveIndex(index)}
                                aria-label={`Ver ${item.kind === 'video' ? 'video' : 'fotografía'} ${index + 1}`}
                                aria-current={index === activeIndex ? 'true' : undefined}
                            >
                                <span className={index === activeIndex ? 'h-1.5 w-5 bg-primary transition-[width,background-color]' : 'h-1.5 w-3 bg-white/45 transition-[width,background-color] group-hover:bg-white'} />
                            </button>
                        ))}
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="alpha-kicker mr-2 text-white/75">{active?.kind === 'video' ? 'VIDEO' : 'FOTO'} · {activeIndex + 1}/{media.length}</span>
                        <button type="button" className="flex size-11 items-center justify-center border border-white/35 bg-black/45 text-white transition-[background-color,border-color,transform] hover:border-primary hover:bg-primary active:scale-[0.96] motion-reduce:transition-none" onClick={() => navigate(-1)} aria-label="Anterior">
                            <ChevronLeft className="size-4" />
                        </button>
                        <button type="button" className="flex size-11 items-center justify-center border border-white/35 bg-black/45 text-white transition-[background-color,border-color,transform] hover:border-primary hover:bg-primary active:scale-[0.96] motion-reduce:transition-none" onClick={() => navigate(1)} aria-label="Siguiente">
                            <ChevronRight className="size-4" />
                        </button>
                    </div>
                </div>
            ) : null}
        </div>
    );
}
