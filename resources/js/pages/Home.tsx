import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays, ChevronLeft, ChevronRight } from 'lucide-react';
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
import { FunnelFAQ } from '@/components/lapsique/funnel/FunnelFAQ';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { useTranslations } from '@/hooks/useTranslations';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { landingPageStackClass } from '@/lib/landingSection';
import { cn, formatMxn } from '@/lib/utils';
import {
    CONTENT_DRONE_SHOTS,
    CONTENT_REEL_DURATION_SECONDS,
    getContentOfferShort,
} from '@/data/contentOffer';
import type {
    BookingSlot,
    DjItem,
    EventItem,
    HeroBackgroundImageData,
    HeroProofVideoData,
    LandingVideoEntry,
    LandingVideosProps,
    PageProps,
    PortfolioItemData,
    ReelLibraryEntry,
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
    reelLibraryPreview: ReelLibraryEntry[];
    sceneDjs: DjItem[];
    sceneVideos: VideoItem[];
    sceneEvents: EventItem[];
    sceneMedia: PortfolioItemData[];
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
                onBook={openBooking}
            />

            <AlphaEquipmentStrip />

            <ServiceLandingLinks />

            <HomeEditorialSection
                title={t('pages.home.offer_title')}
                description={t('pages.home.offer_description', {
                    seconds: CONTENT_REEL_DURATION_SECONDS,
                    drone_shots: CONTENT_DRONE_SHOTS,
                })}
                price={price}
                onBook={openBooking}
            />

            <FeaturedReel
                videos={[
                    landingVideos?.pauta ?? null,
                    landingVideos?.package ?? landingVideos?.gear ?? null,
                    landingVideos?.floats?.[0] ?? null,
                ]}
                fallbackPool={featuredReelFallbackPool}
                bookingSource="featured_reel_proof"
            />

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

            <AboutLapsique portfolioItems={portfolioItems} />
            </div>
            <ReelPlayerModal />
            </ReelPlayerProvider>
        </SiteLayout>
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
                        index="01"
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
                        index="02"
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
                        index="03"
                        eyebrow={en ? 'Produced events' : 'Eventos producidos'}
                        title={en ? 'Shows, collaborations, and residences.' : 'Shows, colaboraciones y residencias.'}
                        href={eventsUrl}
                        cta={en ? 'Open event archive' : 'Abrir archivo de eventos'}
                    >
                        <div className="divide-y divide-white/20 border-y border-white/20">
                            {events.slice(0, 3).map((event, index) => (
                                <Link
                                    key={event.id}
                                    href={route('events.show', { event: event.slug }, false, ziggy)}
                                    className="group grid gap-5 py-5 sm:grid-cols-[4rem_1fr_auto] sm:items-center"
                                >
                                    <span className="font-mono text-xs text-primary">{String(index + 1).padStart(2, '0')}</span>
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
                        index="04"
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
    index,
    eyebrow,
    title,
    href,
    cta,
    children,
}: {
    index: string;
    eyebrow: string;
    title: string;
    href: string;
    cta: string;
    children: ReactNode;
}) {
    return (
        <div className="mt-16 md:mt-24">
            <div className="mb-7 grid gap-5 border-t border-white/20 pt-5 md:grid-cols-[4rem_1fr_auto] md:items-end">
                <span className="font-mono text-xs text-primary">{index}</span>
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

function ServiceLandingLinks() {
    const { locale } = useTranslations();
    const isEnglish = locale === 'en';
    const services = isEnglish
        ? [
            {
                href: '/reels-de-comida',
                title: 'Food reels for restaurants',
                copy: 'Visual content for dishes, drinks, and restaurant experience without making every post feel improvised.',
            },
            {
                href: '/sesiones-de-dron',
                title: 'Drone sessions for hotels and properties',
                copy: 'Aerial footage that explains location, scale, architecture, and surroundings.',
            },
            {
                href: '/avances-de-obra',
                title: 'Construction progress with photo, video, and drone',
                copy: 'Professional progress evidence for reports, sales, investors, and project records.',
            },
        ]
        : [
            {
                href: '/reels-de-comida',
                title: 'Reels de comida para restaurantes',
                copy: 'Contenido visual para platillos, bebidas y experiencia sin improvisar cada publicación.',
            },
            {
                href: '/sesiones-de-dron',
                title: 'Sesiones de dron para hoteles y propiedades',
                copy: 'Tomas aéreas para explicar ubicación, escala, arquitectura y entorno.',
            },
            {
                href: '/avances-de-obra',
                title: 'Avances de obra con foto, video y dron',
                copy: 'Evidencia profesional de progreso para reportes, ventas, inversionistas y archivo de obra.',
            },
        ];

    return (
        <section id="servicios" className="mx-auto w-full max-w-6xl scroll-mt-24 px-4 sm:px-6">
            <div className="grid gap-8 border-y border-border/70 py-8 lg:grid-cols-[0.52fr_1fr] lg:items-start">
                <div>
                    <h2 className="font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                        {isEnglish ? 'Audiovisual services for businesses in Riviera Maya' : 'Servicios audiovisuales para negocios en Riviera Maya'}
                    </h2>
                    <p className="mt-3 text-sm leading-relaxed text-muted-foreground md:text-base">
                        {isEnglish
                            ? 'Content for restaurants, hotels, properties, events, real estate projects, and construction progress.'
                            : 'Contenido para restaurantes, hoteles, propiedades, eventos, proyectos inmobiliarios y avances de obra.'}
                    </p>
                </div>
                <div className="divide-y divide-border/70">
                    {services.map((service) => (
                        <Link
                            key={service.href}
                            href={service.href}
                            className="group grid gap-2 py-4 transition first:pt-0 last:pb-0 md:grid-cols-[0.42fr_1fr_auto] md:items-start md:gap-5"
                        >
                            <h3 className="text-base font-bold leading-snug text-foreground group-hover:text-primary">
                                {service.title}
                            </h3>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                {service.copy}
                            </p>
                            <span className="inline-flex items-center gap-2 text-sm font-semibold text-primary">
                                {isEnglish ? 'Quote' : 'Cotizar'}
                                <ArrowRight className="size-4 transition group-hover:translate-x-0.5" />
                            </span>
                        </Link>
                    ))}
                </div>
            </div>
        </section>
    );
}

function HomeEditorialSection({
    title,
    description,
    price,
    onBook,
}: {
    title: string;
    description: string;
    price: number;
    onBook: () => void;
}) {
    const { t, locale } = useTranslations();

    return (
        <section className="mx-auto grid max-w-6xl gap-7 border-y border-border/70 px-4 py-10 sm:px-6 lg:grid-cols-[0.68fr_0.32fr] lg:items-start">
            <div>
                <h2 className="font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                    {title}
                </h2>
                <p className="mt-4 max-w-3xl text-base leading-relaxed text-muted-foreground">
                    {description}
                </p>
                <p className="mt-5 max-w-3xl text-base leading-relaxed text-foreground">
                    {t('pages.home.ads_description')}
                </p>
            </div>
            <div className="border-t border-border/70 pt-5 lg:border-t-0 lg:pt-0">
                <p className="text-sm font-semibold text-muted-foreground">
                    {locale === 'en' ? 'From' : 'Desde'}
                </p>
                <p className="mt-2 font-mono-tabular text-4xl font-bold text-primary">
                    {formatMxn(price)}
                </p>
                <PaymentTrustOrTestMode variant="stripe" layout="compact" className="mt-3" />
                <BookingCtaButton type="button" className="mt-5 w-full" onClick={onBook}>
                    {t('pages.home.ads_cta')}
                    <CalendarDays className="h-5 w-5" />
                </BookingCtaButton>
            </div>
        </section>
    );
}

function AboutLapsique({ portfolioItems }: { portfolioItems: PortfolioItemData[] }) {
    const { t } = useTranslations();
    const image = portfolioItems.find((item) => item.media_type === 'image' && Boolean(item.asset_url || item.poster_url));

    return (
        <section id="about" className="mx-auto grid max-w-6xl gap-7 border-t border-border/70 px-4 py-10 sm:px-6 lg:grid-cols-[0.48fr_0.52fr] lg:items-center">
            <div>
                <h2 className="font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                    {t('pages.home.about_title')}
                </h2>
                <p className="mt-4 text-base leading-relaxed text-muted-foreground">
                    {t('pages.home.about_description')}
                </p>
                <div className="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <h3 className="font-display text-xl font-bold text-foreground">
                            {t('pages.home.about_film_title')}
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {t('pages.home.about_film_copy')}
                        </p>
                    </div>
                    <div>
                        <h3 className="font-display text-xl font-bold text-foreground">
                            {t('pages.home.about_events_title')}
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {t('pages.home.about_events_copy')}
                        </p>
                    </div>
                </div>
            </div>
            {image ? (
                <img
                    src={image.asset_url ?? image.poster_url ?? ''}
                    alt={image.title ?? ''}
                    className="aspect-[16/10] w-full rounded-xl object-cover"
                    loading="lazy"
                    decoding="async"
                />
            ) : null}
        </section>
    );
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
    onBook,
}: {
    title: string;
    subtitle: string;
    price: number;
    heroBackgroundImage: HeroBackgroundImageData | null;
    landingHero: LandingVideoEntry | null;
    heroProofVideo: HeroProofVideoData | null;
    portfolioItems: PortfolioItemData[];
    onBook: () => void;
}) {
    const { t } = useTranslations();
    const offerShort = getContentOfferShort(t);

    return (
        <section className="relative left-1/2 w-screen -translate-x-1/2 border-b border-foreground/20 bg-background">
            <div className="mx-auto grid max-w-[1440px] lg:min-h-[calc(100svh-4.5rem)] lg:grid-cols-[minmax(360px,0.72fr)_minmax(0,1.28fr)]">
                <div className="flex min-w-0 flex-col justify-center border-r border-foreground/15 px-5 py-12 sm:px-8 lg:px-12 lg:py-16">
                    <p className="alpha-kicker border-l-2 border-primary pl-3 text-muted-foreground">
                        {t('pages.home.hero_eyebrow')}
                    </p>
                    <h1 className="mt-7 max-w-2xl text-[3.15rem] font-semibold leading-[0.88] text-foreground sm:text-[3.55rem] lg:text-[3.8rem] xl:text-[4rem]">
                        {title}
                    </h1>
                    <p className="mt-7 max-w-xl text-base leading-relaxed text-muted-foreground lg:text-lg">
                        {subtitle}
                    </p>
                    <p className="alpha-kicker mt-5 text-foreground/75">
                        {offerShort}
                    </p>
                    <div className="mt-8 border-y border-foreground/15 py-5">
                        <div className="flex items-end justify-between gap-5">
                            <div>
                                <p className="alpha-kicker text-muted-foreground">{t('pages.home.ready_to_book')}</p>
                                <p className="mt-2 font-mono-tabular text-4xl font-semibold text-foreground">{formatMxn(price)}</p>
                            </div>
                            <span className="alpha-kicker text-primary">MXN</span>
                        </div>
                        <PaymentTrustOrTestMode variant="stripe" layout="compact" className="mt-3" />
                    </div>
                    <BookingCtaButton
                        type="button"
                        className="mt-6 min-h-14 w-full rounded-none bg-foreground text-background shadow-none hover:bg-primary hover:text-white"
                        data-meta-event="Lead"
                        data-analytics-cta="hero_booking"
                        onClick={onBook}
                    >
                        {t('common.cta.book_now')}
                        <ArrowRight className="h-5 w-5" />
                    </BookingCtaButton>
                </div>

                <HeroMediaCarousel
                    title={title}
                    heroBackgroundImage={heroBackgroundImage}
                    landingHero={landingHero}
                    heroProofVideo={heroProofVideo}
                    portfolioItems={portfolioItems}
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
}: {
    title: string;
    heroBackgroundImage: HeroBackgroundImageData | null;
    landingHero: LandingVideoEntry | null;
    heroProofVideo: HeroProofVideoData | null;
    portfolioItems: PortfolioItemData[];
}) {
    const media = useMemo<HeroMediaItem[]>(() => {
        const items: HeroMediaItem[] = [];
        const seen = new Set<string>();
        const add = (item: HeroMediaItem | null) => {
            if (!item?.src || seen.has(item.src)) return;
            seen.add(item.src);
            items.push(item);
        };

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

        return items;
    }, [heroBackgroundImage, heroProofVideo, landingHero, portfolioItems, title]);
    const [activeIndex, setActiveIndex] = useState(0);
    const active = media[activeIndex] ?? media[0];

    useEffect(() => {
        if (media.length < 2) return;
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
            <div className="pointer-events-none absolute inset-x-5 top-5 flex items-start justify-between gap-4 sm:inset-x-8 sm:top-8">
                <span className="alpha-kicker border border-white/35 bg-black/45 px-3 py-2 text-white backdrop-blur-sm">REC <span className="text-red-500">●</span></span>
                <div className="alpha-kicker space-y-1 text-right text-white/85">
                    <p>4K · 10 BIT</p><p>S-LOG3</p><p>SONY ALPHA</p>
                </div>
            </div>

            {media.length > 1 ? (
                <div className="absolute inset-x-5 bottom-5 z-10 flex items-center justify-between gap-4 border-t border-white/25 pt-4 sm:inset-x-8 sm:bottom-8">
                    <div className="flex items-center gap-2">
                        {media.map((item, index) => (
                            <button
                                key={item.id}
                                type="button"
                                className={index === activeIndex ? 'h-1.5 w-8 bg-primary' : 'h-1.5 w-4 bg-white/45 transition hover:bg-white'}
                                onClick={() => setActiveIndex(index)}
                                aria-label={`Ver ${item.kind === 'video' ? 'video' : 'fotografía'} ${index + 1}`}
                            />
                        ))}
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="alpha-kicker mr-2 text-white/75">{active?.kind === 'video' ? 'VIDEO' : 'FOTO'} · {activeIndex + 1}/{media.length}</span>
                        <button type="button" className="flex size-9 items-center justify-center border border-white/35 bg-black/45 text-white transition hover:border-primary hover:bg-primary" onClick={() => navigate(-1)} aria-label="Anterior">
                            <ChevronLeft className="size-4" />
                        </button>
                        <button type="button" className="flex size-9 items-center justify-center border border-white/35 bg-black/45 text-white transition hover:border-primary hover:bg-primary" onClick={() => navigate(1)} aria-label="Siguiente">
                            <ChevronRight className="size-4" />
                        </button>
                    </div>
                </div>
            ) : null}
        </div>
    );
}

function AlphaEquipmentStrip() {
    const { locale } = useTranslations();
    const ref = useSectionEvent<HTMLElement>('equipment_viewed', {
        section: 'alpha_equipment_strip',
        equipment: ['sony_a7_v', 'sony_a7_iv', 'sony_a6700', 'dji_air'],
    });
    const equipment = [
        { label: 'Sony α7 V', format: 'Full frame · 10-bit', image: '/images/equipment/official/sony-a7v.webp', newest: true },
        { label: 'Sony α7 IV', format: 'Full frame · 4K', image: '/images/equipment/official/sony-a7iv.webp', newest: false },
        { label: 'Sony α6700', format: 'APS-C · 4K 120p', image: '/images/equipment/official/sony-a6700.webp', newest: false },
        { label: 'DJI Air 3', format: 'Tomas aéreas · 4K', image: '/images/equipment/official/dji-air-3.png', newest: false },
    ];

    return (
        <section ref={ref} data-analytics-section="alpha_equipment" className="mt-6 border-y border-foreground/15 bg-secondary/55">
            <div className="grid lg:grid-cols-[230px_1fr]">
                <div className="border-b border-foreground/15 p-6 lg:border-b-0 lg:border-r lg:p-8">
                    <p className="alpha-kicker text-primary">Equipo de producción</p>
                    <h2 className="mt-4 text-3xl font-semibold leading-none">Herramientas para resultados profesionales.</h2>
                </div>
                <div className="grid grid-cols-2 md:grid-cols-4">
                    {equipment.map((item) => (
                        <article key={item.label} className="relative border-b border-r border-foreground/15 p-4 last:border-r-0 md:border-b-0 md:p-5">
                            {item.newest ? (
                                <span className="absolute right-3 top-3 z-10 bg-foreground px-2.5 py-1 font-mono text-[9px] font-semibold uppercase tracking-[0.13em] text-background">
                                    {locale === 'en' ? 'Newest camera' : 'Cámara más nueva'}
                                </span>
                            ) : null}
                            <div className="flex aspect-[4/3] items-center justify-center overflow-hidden bg-background">
                                <img src={item.image} alt={`${item.label} — equipo de producción Lapsique Media`} className="h-full w-full object-contain p-3" loading="lazy" />
                            </div>
                            <p className="alpha-kicker mt-4 text-muted-foreground">Cámara / sistema</p>
                            <h3 className="mt-1 text-xl font-semibold normal-case">{item.label}</h3>
                            <p className="mt-1 font-mono text-xs text-muted-foreground">{item.format}</p>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
