import { usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Play } from 'lucide-react';
import { useEffect, useRef, type RefObject } from 'react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { ReelPlayerModal } from '@/components/lapsique/ReelPlayerModal';
import { ReelLoopCard } from '@/components/lapsique/ReelLoopCard';
import { useReelLibraryPlayback } from '@/hooks/useReelLibraryPlayback';
import { ReelPlayerProvider } from '@/hooks/useReelPlayerModal';
import { openBookingModal } from '@/lib/openBookingModal';
import { HeroProofVideoCard } from '@/components/lapsique/HeroProofVideoCard';
import {
    LoopingVideoBackground,
    PortfolioPhotoBackground,
} from '@/components/lapsique/LoopingVideoBackground';
import { ContentPackageSection } from '@/components/lapsique/funnel/ContentPackageSection';
import { RecordingGearSection } from '@/components/lapsique/funnel/RecordingGearSection';
import { WorkflowSection } from '@/components/lapsique/funnel/WorkflowSection';
import { FunnelTeam } from '@/components/lapsique/funnel/FunnelTeam';
import { MetaOfferReelShowcase } from '@/components/lapsique/funnel/MetaOfferReelShowcase';
import { FunnelFAQ } from '@/components/lapsique/funnel/FunnelFAQ';
import { FunnelPopups } from '@/components/lapsique/FunnelPopups';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { StickyBookingBar } from '@/components/lapsique/StickyBookingBar';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useIsMobileViewport } from '@/hooks/useMediaQuery';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { formatMxn } from '@/lib/utils';
import {
    CONTENT_DRONE_SHOTS,
    CONTENT_OFFER_SHORT,
    CONTENT_REEL_DURATION_SECONDS,
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
    const { site } = usePage<PageProps>().props;
    const adProofRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', {
        section: 'business_reel_formats',
    });
    const reelLibraryRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', {
        section: 'business_reel_library',
    });
    const portfolioImages = portfolioItems.filter((item) => (
        item.media_type === 'image' && Boolean(item.asset_url || item.poster_url)
    ));
    useEffect(() => {
        trackBookingEvent('booking_page_viewed', {
            section: 'home_business_content',
            content_name: 'Contenido para negocios',
            content_category: 'business_content_booking',
        });
    }, []);

    const openBooking = () => {
        openBookingModal({
            source: 'home_business',
            analyticsEvent: 'hero_cta_clicked',
            analyticsPayload: {
                content_name: 'Contenido para negocios',
                content_category: 'business_content_booking',
            },
        });
    };

    return (
        <SiteLayout>
            <ReelPlayerProvider>
            <SeoHead />

            <BusinessHero
                title={title}
                subtitle={subtitle}
                price={price}
                heroBackgroundImage={heroBackgroundImage}
                landingHero={landingVideos?.hero ?? null}
                heroProofVideo={heroProofVideo}
                onBook={openBooking}
            />

            <GlassSection
                eyebrow="Oferta"
                title="Crea contenido cinematográfico para tu negocio y sube de nivel"
                description={`Reel de ${CONTENT_REEL_DURATION_SECONDS} segundos con cámara Sony, ${CONTENT_DRONE_SHOTS} tomas de dron DJI, fotos editadas y producción lista para pauta en Meta.`}
                className="pt-8"
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

            <FeaturedReel video={landingVideos?.pauta ?? null} bookingSource="featured_reel_pauta" />

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

            <FeaturedReel
                video={landingVideos?.package ?? landingVideos?.gear ?? null}
                bookingSource="featured_reel_package"
            />

            <GlassSection
                eyebrow="Pauta"
                title="Contenido que muestra seriedad en tu marca"
                description="Cuando tu marca se ve cinematográfica, el cliente percibe orden, criterio y nivel. Esa seriedad abre la puerta a quienes pagan más y deciden con confianza."
            >
                <BookingCtaSection className="pt-0 pb-4">
                    <BookingCtaButton type="button" onClick={openBooking}>
                        Elegir fecha
                        <CalendarDays className="h-5 w-5" />
                    </BookingCtaButton>
                </BookingCtaSection>
                <BusinessCreativeBoard
                    images={portfolioImages}
                    landingCreative={landingVideos?.creative ?? []}
                    sectionRef={adProofRef}
                />
            </GlassSection>

            <ContentPackageSection />

            <FeaturedReel
                video={landingVideos?.floats?.[0] ?? null}
                bookingSource="featured_reel_pre_workflow"
            />

            <WorkflowSection
                videos={[
                    landingVideos?.aftermovies?.[0] ?? null,
                    landingVideos?.aftermovies?.[1] ?? null,
                ]}
                bookingSource="workflow_reel"
            />

            <RecordingGearSection
                videos={[
                    landingVideos?.aftermovies?.[2] ?? null,
                    landingVideos?.aftermovies?.[3] ?? null,
                ]}
                bookingSource="gear_reel"
            />

            <GlassSection
                eyebrow="Casos de éxito"
                title="Más de 100 producciones exitosas"
                description="Amplia experiencia para mostrar tu marca al mejor nivel: más de 100 producciones realizadas para más de 60 clientes únicos en marcas y negocios de la economía local."
            >
                <ReelLibraryShowcase
                    sectionRef={reelLibraryRef}
                    clips={reelLibraryPreview}
                />
            </GlassSection>

            <FunnelTeam />
            <FunnelFAQ variant="home" />
            <FunnelPopups
                variant="home"
                slotsCount={slots.length}
                portfolioItems={portfolioItems}
                heroProofVideo={heroProofVideo}
            />
            <StickyBookingBar whatsapp={site.whatsapp} />
            <ReelPlayerModal />
            </ReelPlayerProvider>
        </SiteLayout>
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
                        footer={
                            <div className="pointer-events-none absolute inset-x-0 bottom-0 flex justify-end p-3">
                                <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/20 bg-black/35 text-primary backdrop-blur">
                                    <Play className="h-3 w-3 fill-current" />
                                </span>
                            </div>
                        }
                    />
                ))}
            </div>
        </div>
    );
}

function FeaturedReel({
    video,
    bookingSource,
}: {
    video: LandingVideoEntry | null;
    bookingSource: string;
}) {
    if (!isPlayableLandingVideo(video)) {
        return null;
    }

    return (
        <section className="pb-8 pt-2" aria-label="Ejemplo de reel para pauta">
            <div className="w-full">
                <ReelLoopCard
                    src={video.src}
                    poster={video.poster}
                    title={video.title ?? undefined}
                    bookingSource={bookingSource}
                    articleClassName="rounded-xl border border-border/70 bg-black shadow-lg"
                    footer={
                        <div className="pointer-events-none absolute inset-x-0 bottom-0 flex justify-end p-3">
                            <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/20 bg-black/35 text-primary backdrop-blur">
                                <Play className="h-3 w-3 fill-current" />
                            </span>
                        </div>
                    }
                />
            </div>
        </section>
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
    const isMobile = useIsMobileViewport();
    const proofVideo =
        heroProofVideo
        ?? (landingHero ? landingEntryToHeroProof(landingHero) : null);
    const heroVideoEager = !isMobile && !heroBackgroundImage?.url;

    return (
        <section className="relative -mx-4 overflow-hidden sm:-mx-6">
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
                        Lapsique para negocios
                    </p>
                    <h1 className="mt-3 max-w-4xl font-display text-4xl font-bold leading-[0.98] tracking-tight text-white drop-shadow-[0_3px_28px_rgb(0_0_0/0.55)] sm:text-5xl md:text-6xl lg:text-7xl">
                        {title}
                    </h1>
                    <p className="mt-3 max-w-2xl text-base leading-relaxed text-white/80 md:text-xl">
                        {subtitle}
                    </p>
                    <p className="mt-2 text-sm font-medium uppercase tracking-[0.14em] text-primary/90">
                        {CONTENT_OFFER_SHORT}
                    </p>

                    <div className="mt-6 space-y-6">
                        <div className="w-full max-w-md rounded-2xl border border-primary/35 bg-black/70 px-5 py-4 shadow-[0_16px_48px_rgb(0_0_0/0.45)] backdrop-blur-md lg:mx-auto">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                                Sesión lista para reservar
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
                                Agendar ahora
                                <ArrowRight className="h-5 w-5" />
                            </BookingCtaButton>
                        </BookingCtaSection>
                    </div>
                </div>

                <HeroProofPanel video={proofVideo} eager={!isMobile} />
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
    const displayImages = [images[4], images[5], images[6]].filter(Boolean) as PortfolioItemData[];
    const creativeImages = displayImages.length > 0 ? displayImages : images.slice(0, 3);
    const creativeSlots = [
        { label: 'Gancho', title: 'Para detener el scroll en el feed' },
        { label: 'Reel', title: 'Formato vertical listo para pauta' },
        { label: 'Conversión', title: 'CTA claro para clic o mensaje' },
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
    label,
    title,
}: {
    video: LandingVideoEntry;
    label: string;
    title: string;
}) {
    return (
        <figure className="group relative aspect-[9/16] w-full overflow-hidden rounded-xl border border-border/70 bg-black">
            <ReelLoopCard
                src={video.src}
                poster={video.poster}
                title={title}
                bookingSource="creative_reel"
                articleClassName="absolute inset-0 h-full w-full rounded-none border-0"
                fillContainer
                videoClassName="group-hover:scale-[1.03]"
                footer={
                    <figcaption className="pointer-events-none absolute inset-x-0 bottom-0 p-4 text-white">
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                            {label}
                        </p>
                        <p className="mt-2 max-w-[13rem] font-display text-xl font-bold leading-tight">
                            {title}
                        </p>
                    </figcaption>
                }
            />
        </figure>
    );
}

function VerticalCreativeFrame({
    image,
    label,
    title,
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
            <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent" />
            <figcaption className="absolute inset-x-0 bottom-0 p-4 text-white">
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                    {label}
                </p>
                <p className="mt-2 max-w-[13rem] font-display text-xl font-bold leading-tight">
                    {title}
                </p>
            </figcaption>
        </figure>
    );
}

