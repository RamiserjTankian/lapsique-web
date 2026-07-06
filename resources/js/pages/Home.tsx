import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Clapperboard, Disc3, Music2 } from 'lucide-react';
import { useEffect, type RefObject } from 'react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { PortfolioPhotoCarousel } from '@/components/lapsique/PortfolioPhotoCarousel';
import { LandingPageSection } from '@/components/lapsique/LandingPageSection';
import { ReelPlayerModal } from '@/components/lapsique/ReelPlayerModal';
import { ReelLoopCard } from '@/components/lapsique/ReelLoopCard';
import { useReelLibraryPlayback } from '@/hooks/useReelLibraryPlayback';
import { ReelPlayerProvider } from '@/hooks/useReelPlayerModal';
import { openBookingModal } from '@/lib/openBookingModal';
import { videoSurfaceFrameClass } from '@/lib/videoSurface';
import { HeroProofVideoCard } from '@/components/lapsique/HeroProofVideoCard';
import {
    LoopingVideoBackground,
    PortfolioPhotoBackground,
} from '@/components/lapsique/LoopingVideoBackground';
import { ContentPackageSection } from '@/components/lapsique/funnel/ContentPackageSection';
import { ProblemSection } from '@/components/lapsique/funnel/ProblemSection';
import { GuaranteeUrgencySection } from '@/components/lapsique/funnel/GuaranteeUrgencySection';
import { PortfolioTrustSection } from '@/components/lapsique/funnel/PortfolioTrustSection';
import { RecordingGearSection } from '@/components/lapsique/funnel/RecordingGearSection';
import { WorkflowSection } from '@/components/lapsique/funnel/WorkflowSection';
import { MetaOfferReelShowcase } from '@/components/lapsique/funnel/MetaOfferReelShowcase';
import { FunnelFAQ } from '@/components/lapsique/funnel/FunnelFAQ';
import { FunnelPopups } from '@/components/lapsique/FunnelPopups';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { StickyBookingBar } from '@/components/lapsique/StickyBookingBar';
import { useTranslations } from '@/hooks/useTranslations';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { landingPageStackClass } from '@/lib/landingSection';
import { route } from '@/lib/route';
import { cn, formatMxn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
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
    reelLibraryPreview,
    errors,
}: HomeProps) {
    const { site, ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const adProofRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', {
        section: 'business_reel_formats',
    });
    const reelLibraryRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', {
        section: 'business_reel_library',
    });
    const portfolioImages = portfolioItems.filter((item) => (
        item.media_type === 'image' && Boolean(item.asset_url || item.poster_url)
    ));
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

            {/* 2. Problema/dolor — agita por qué el contenido genérico no vende */}
            <ProblemSection />

            {/* 3. Oferta/solución — bloque glass de conversión */}
            <GlassSection
                title={t('pages.home.offer_title')}
                description={t('pages.home.offer_description', {
                    seconds: CONTENT_REEL_DURATION_SECONDS,
                    drone_shots: CONTENT_DRONE_SHOTS,
                })}
                surfaceClassName="relative overflow-hidden border-emerald-500/25 shadow-[0_28px_80px_oklch(0.58_0.12_145/0.14)]"
                surfaceStyle={{
                    background: 'radial-gradient(circle at 12% 14%, oklch(0.82 0.13 145 / 0.22), transparent 33%), radial-gradient(circle at 88% 18%, oklch(0.84 0.15 78 / 0.2), transparent 30%), linear-gradient(135deg, oklch(0.995 0.01 96), oklch(0.94 0.04 140 / 0.9))',
                }}
            >
                <div className="relative z-[1]">
                    <PaymentTrustOrTestMode variant="stripe" layout="card" />
                    <div className="mt-6">
                        <MetaOfferReelShowcase
                            price={price}
                            images={portfolioImages}
                            landingOffer={landingVideos?.offer ?? null}
                            equipmentVideos={landingVideos?.equipment ?? []}
                            onBook={openBooking}
                        />
                    </div>
                </div>
            </GlassSection>

            <DjSetHomeCta href={route('djset.show', undefined, false, ziggy)} />

            {/* 4. Prueba en video consolidada (antes 3 FeaturedReel separados) */}
            <FeaturedReel
                videos={[
                    landingVideos?.pauta ?? null,
                    landingVideos?.package ?? landingVideos?.gear ?? null,
                    landingVideos?.floats?.[0] ?? null,
                ]}
                fallbackPool={featuredReelFallbackPool}
                bookingSource="featured_reel_proof"
            />

            {/* 5. Deseo — contenido que da seriedad a la marca */}
            <GlassSection
                title={t('pages.home.ads_title')}
                description={t('pages.home.ads_description')}
            >
                <BookingCtaSection className="pt-0 pb-4">
                    <BookingCtaButton type="button" onClick={openBooking}>
                        {t('pages.home.ads_cta')}
                        <CalendarDays className="h-5 w-5" />
                    </BookingCtaButton>
                </BookingCtaSection>
                <BusinessCreativeBoard
                    images={portfolioImages}
                    landingCreative={landingVideos?.creative ?? []}
                    sectionRef={adProofRef}
                />
            </GlassSection>

            {/* 6. Qué incluye el paquete */}
            <ContentPackageSection />

            {/* 7. Prueba social + portafolio */}
            <PortfolioTrustSection portfolioItems={portfolioItems} />

            {/* 8. Cómo funciona */}
            <WorkflowSection bookingSource="workflow_reel" />

            {/* 9. Equipo (credibilidad) */}
            <RecordingGearSection bookingSource="gear_reel" />

            {/* 10. Prueba final en video — biblioteca de reels */}
            <GlassSection
                surface="solid"
                title={t('pages.home.success_title')}
                description={t('pages.home.success_description')}
            >
                <ReelLibraryShowcase
                    sectionRef={reelLibraryRef}
                    clips={reelLibraryPreview}
                />
            </GlassSection>

            {/* 11. Garantía + urgencia (reversión de riesgo antes de reservar) */}
            <GuaranteeUrgencySection onBook={openBooking} />

            {/* 12. Reserva — conversión principal */}
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

            {/* 13. FAQ — manejo de objeciones */}
            <FunnelFAQ variant="home" />

            {/* 14. Sobre el estudio */}
            <AboutLapsique portfolioItems={portfolioItems} />

            <FunnelPopups
                variant="home"
                slotsCount={slots.length}
                portfolioItems={portfolioItems}
                heroProofVideo={heroProofVideo}
            />
            <StickyBookingBar whatsapp={site.whatsapp} />
            </div>
            <ReelPlayerModal />
            </ReelPlayerProvider>
        </SiteLayout>
    );
}

function DjSetHomeCta({ href }: { href: string }) {
    const { t } = useTranslations();

    return (
        <GlassSection
            surface="solid"
            title={t('pages.home.djset_cta_title')}
            description={t('pages.home.djset_cta_description')}
            action={(
                <BookingCtaButton asChild className="w-full sm:w-auto">
                    <Link href={href}>
                        <Music2 className="h-5 w-5" />
                        {t('pages.home.djset_cta_button')}
                        <ArrowRight className="h-5 w-5" />
                    </Link>
                </BookingCtaButton>
            )}
        >
            <div className="grid gap-3 sm:grid-cols-3">
                <div className={cn(glassCardVariants(), 'border border-border/70 p-4')}>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                        {t('pages.home.djset_cta_point_1_label')}
                    </p>
                    <p className="mt-2 text-sm font-medium text-foreground">
                        {t('pages.home.djset_cta_point_1')}
                    </p>
                </div>
                <div className={cn(glassCardVariants(), 'border border-border/70 p-4')}>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                        {t('pages.home.djset_cta_point_2_label')}
                    </p>
                    <p className="mt-2 text-sm font-medium text-foreground">
                        {t('pages.home.djset_cta_point_2')}
                    </p>
                </div>
                <div className={cn(glassCardVariants(), 'border border-border/70 p-4')}>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                        {t('pages.home.djset_cta_point_3_label')}
                    </p>
                    <p className="mt-2 text-sm font-medium text-foreground">
                        {t('pages.home.djset_cta_point_3')}
                    </p>
                </div>
            </div>
        </GlassSection>
    );
}

function AboutLapsique({ portfolioItems }: { portfolioItems: PortfolioItemData[] }) {
    const { t } = useTranslations();
    const pillars = [
        {
            icon: Clapperboard,
            title: t('pages.home.about_film_title'),
            copy: t('pages.home.about_film_copy'),
        },
        {
            icon: Disc3,
            title: t('pages.home.about_events_title'),
            copy: t('pages.home.about_events_copy'),
        },
    ] as const;

    return (
        <GlassSection
            id="about"
            title={t('pages.home.about_title')}
            description={t('pages.home.about_description')}
        >
            <div className="space-y-6">
                <PortfolioPhotoCarousel items={portfolioItems} />
                <div className="grid gap-4 sm:grid-cols-2">
                    {pillars.map(({ icon: Icon, title, copy }) => (
                        <div
                            key={title}
                            className={cn(glassCardVariants(), 'glass-border-glow border p-5')}
                        >
                            <span className="inline-flex h-11 w-11 items-center justify-center rounded-full border border-accent/30 bg-accent/10 text-accent">
                                <Icon className="h-5 w-5" />
                            </span>
                            <h3 className="font-display mt-4 text-lg font-semibold tracking-tight text-foreground">
                                {title}
                            </h3>
                            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                                {copy}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </GlassSection>
    );
}

function ReelLibraryShowcase({
    sectionRef,
    clips,
}: {
    sectionRef: RefObject<HTMLDivElement | null>;
    clips: ReelLibraryEntry[];
}) {
    const { activeIndex, showOverlay, handleLoopComplete, handleBook, reportInView } =
        useReelLibraryPlayback(clips.length);

    if (clips.length === 0) {
        return null;
    }

    return (
        <div ref={sectionRef}>
            <div className="grid gap-3 rounded-xl border border-border/70 bg-black/40 p-3 sm:grid-cols-2 md:grid-cols-5">
                {clips.map((clip, index) => (
                    <ReelLoopCard
                        key={clip.id}
                        src={clip.src}
                        poster={clip.poster}
                        title={clip.title ?? undefined}
                        bookingSource="reel_library"
                        showBookingOverlay={index === activeIndex && showOverlay}
                        overlayAutoHideMs={0}
                        onInViewChange={(inView) => reportInView(index, inView)}
                        onLoopSegmentComplete={
                            index === activeIndex ? () => handleLoopComplete(index) : undefined
                        }
                        onBook={() => {
                            handleBook();
                            openBookingModal({ source: 'reel_library', skipAnalytics: true });
                        }}
                    />
                ))}
            </div>
        </div>
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

function BusinessCreativeBoard({
    images,
    landingCreative,
    sectionRef,
}: {
    images: PortfolioItemData[];
    landingCreative: LandingVideoEntry[];
    sectionRef: RefObject<HTMLDivElement | null>;
}) {
    const { t } = useTranslations();
    const displayImages = [images[4], images[5], images[6]].filter(Boolean) as PortfolioItemData[];
    const creativeImages = displayImages.length > 0 ? displayImages : images.slice(0, 3);
    const creativeSlots = [
        { label: t('pages.home.creative_label_hook'), title: t('pages.home.creative_hook') },
        { label: 'Reel', title: t('pages.home.creative_reel') },
        { label: 'CTA', title: t('pages.home.creative_conversion') },
    ] as const;

    return (
        <div ref={sectionRef} className="mx-auto w-full max-w-5xl">
            <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
                {creativeSlots.map((slot, index) => {
                    const video = landingCreative[index];
                    const image = creativeImages[index];

                    if (video) {
                        return (
                            <VerticalCreativeVideoFrame
                                key={slot.label}
                                video={video}
                                label={slot.label}
                                title={slot.title}
                            />
                        );
                    }

                    return (
                        <VerticalCreativeFrame
                            key={slot.label}
                            image={image}
                            label={slot.label}
                            title={slot.title}
                        />
                    );
                })}
            </div>
        </div>
    );
}

function VerticalCreativeVideoFrame({
    video,
    title,
}: {
    video: LandingVideoEntry;
    label: string;
    title: string;
}) {
    return (
        <figure className={`group relative aspect-[9/16] w-full ${videoSurfaceFrameClass}`}>
            <ReelLoopCard
                src={video.src}
                poster={video.poster}
                title={title}
                bookingSource="creative_reel"
                articleClassName="absolute inset-0 h-full w-full rounded-none border-0"
                fillContainer
                videoClassName="group-hover:scale-[1.03]"
            />
        </figure>
    );
}

function VerticalCreativeFrame({
    image,
}: {
    image?: PortfolioItemData;
    label: string;
    title: string;
}) {
    return (
        <figure className="group relative aspect-[9/16] w-full overflow-hidden rounded-xl border border-border/70 bg-secondary">
            {image && (
                <img
                    src={image.asset_url ?? image.poster_url ?? ''}
                    alt={image.title ?? ''}
                    className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                    loading="lazy"
                />
            )}
        </figure>
    );
}
