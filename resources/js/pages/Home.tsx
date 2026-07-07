import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays } from 'lucide-react';
import { useEffect } from 'react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { LandingPageSection } from '@/components/lapsique/LandingPageSection';
import { ReelPlayerModal } from '@/components/lapsique/ReelPlayerModal';
import { ReelLoopCard } from '@/components/lapsique/ReelLoopCard';
import { ReelPlayerProvider } from '@/hooks/useReelPlayerModal';
import { openBookingModal } from '@/lib/openBookingModal';
import { HeroProofVideoCard } from '@/components/lapsique/HeroProofVideoCard';
import {
    LoopingVideoBackground,
    PortfolioPhotoBackground,
} from '@/components/lapsique/LoopingVideoBackground';
import { FunnelFAQ } from '@/components/lapsique/funnel/FunnelFAQ';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { useTranslations } from '@/hooks/useTranslations';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { landingPageStackClass } from '@/lib/landingSection';
import { cn, formatMxn } from '@/lib/utils';
import {
    CONTENT_DRONE_SHOTS,
    CONTENT_REEL_DURATION_SECONDS,
    getContentOfferShort,
    LANDING_VIDEO_LOOP_SECONDS,
} from '@/data/contentOffer';
import type {
    BookingSlot,
    HeroBackgroundImageData,
    HeroProofVideoData,
    LandingVideoEntry,
    LandingVideosProps,
    PageProps,
    PortfolioItemData,
    ReelLibraryEntry,
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
    errors?: Record<string, string>;
}

function landingEntryToHeroProof(entry: LandingVideoEntry): HeroProofVideoData {
    return {
        title: null,
        media_type: 'video',
        embed_url: null,
        playback_url: entry.src,
        poster_url: entry.poster,
    };
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
                onBook={openBooking}
            />

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

            <AboutLapsique portfolioItems={portfolioItems} />
            </div>
            <ReelPlayerModal />
            </ReelPlayerProvider>
        </SiteLayout>
    );
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
        <section className="mx-auto w-full max-w-6xl px-4 sm:px-6">
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
    onBook,
}: {
    title: string;
    subtitle: string;
    price: number;
    heroBackgroundImage: HeroBackgroundImageData | null;
    landingHero: LandingVideoEntry | null;
    heroProofVideo: HeroProofVideoData | null;
    onBook: () => void;
}) {
    const { t } = useTranslations();
    const offerShort = getContentOfferShort(t);
    const proofVideo =
        heroProofVideo
        ?? (landingHero ? landingEntryToHeroProof(landingHero) : null);
    const heroVideoEager = !heroBackgroundImage?.url;

    return (
        <section className="relative -mx-4 overflow-hidden rounded-b-2xl shadow-[0_20px_50px_var(--glass-panel-shadow)] sm:-mx-6">
            <div className="absolute inset-0 bg-background">
                {heroBackgroundImage?.url ? (
                    <PortfolioPhotoBackground
                        src={heroBackgroundImage.url}
                        alt={heroBackgroundImage.alt}
                        eager
                    />
                ) : isPlayableLandingVideo(landingHero) ? (
                    <LoopingVideoBackground
                        src={landingHero.src}
                        poster={landingHero.poster}
                        eager={heroVideoEager}
                        loopSegmentSeconds={LANDING_VIDEO_LOOP_SECONDS}
                    />
                ) : (
                    <div className="absolute inset-0 bg-background" aria-hidden>
                        <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.11_0.02_280/0.97)_0%,oklch(0.11_0.02_280/0.84)_50%,oklch(0.11_0.02_280/0.46)_100%)]" />
                        <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.08_0.01_280/0.25)_0%,oklch(0.08_0.01_280/0.38)_54%,var(--background)_100%)]" />
                    </div>
                )}
            </div>

            <div className="relative mx-auto grid min-h-[min(520px,85svh)] max-w-6xl content-center gap-6 px-4 pb-6 pt-8 sm:px-6 lg:min-h-[min(640px,calc(100svh-10rem))] lg:grid-cols-[minmax(0,1fr)_360px] lg:items-center">
                <div className="order-1 max-w-4xl lg:order-none">
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                        {t('pages.home.hero_eyebrow')}
                    </p>
                    <h1 className="mt-3 max-w-4xl font-display text-4xl font-bold leading-[0.98] tracking-tight text-white drop-shadow-[0_3px_28px_rgb(0_0_0/0.55)] sm:text-5xl md:text-6xl lg:text-7xl">
                        {title}
                    </h1>
                    <p className="mt-3 max-w-2xl text-base leading-relaxed text-white/80 md:text-xl">
                        {subtitle}
                    </p>
                    <p className="mt-2 text-sm font-medium uppercase tracking-[0.14em] text-primary/90">
                        {offerShort}
                    </p>

                    <div className="mt-6 space-y-6">
                        <div className="w-full max-w-md rounded-2xl border border-primary/35 bg-black/70 px-5 py-4 shadow-[0_16px_48px_rgb(0_0_0/0.45)] backdrop-blur-md lg:mx-auto">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                                {t('pages.home.ready_to_book')}
                            </p>
                            <p className="mt-2 font-mono-tabular text-4xl font-bold text-primary drop-shadow-[0_2px_12px_rgb(0_0_0/0.4)] md:text-5xl">
                                {formatMxn(price)}
                            </p>
                            <PaymentTrustOrTestMode
                                variant="stripe"
                                layout="compact"
                                onDark
                                className="mt-3"
                            />
                        </div>
                        <BookingCtaSection hero className="py-0">
                            <BookingCtaButton type="button" hero onClick={onBook}>
                                <CalendarDays className="h-5 w-5" />
                                {t('common.cta.book_now')}
                                <ArrowRight className="h-5 w-5" />
                            </BookingCtaButton>
                        </BookingCtaSection>
                    </div>
                </div>

                <HeroProofPanel video={proofVideo} eager />
            </div>
        </section>
    );
}

function HeroProofPanel({ video, eager = false }: { video: HeroProofVideoData | null; eager?: boolean }) {
    if (!video) {
        return null;
    }

    return (
        <aside className="order-2 w-full lg:order-none lg:flex lg:w-auto lg:justify-end">
            <HeroProofVideoCard
                video={video}
                eager={eager}
                className="w-full shadow-2xl lg:max-w-[360px]"
            />
        </aside>
    );
}
