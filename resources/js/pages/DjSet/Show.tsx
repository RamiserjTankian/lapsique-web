import { useEffect, useMemo, useState, type ReactNode } from 'react';
import SiteLayout from '@/layouts/SiteLayout';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { BookingWidget, type BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import { getDjSetProduct } from '@/lib/bookingProducts';
import { useTranslations } from '@/hooks/useTranslations';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { DjCard } from '@/components/lapsique/DjCard';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { FunnelPopups } from '@/components/lapsique/FunnelPopups';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { Button } from '@/components/ui/button';
import { formatMxn } from '@/lib/utils';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { openBookingModal } from '@/lib/openBookingModal';
import {
    ArrowRight,
    CalendarDays,
    Camera,
    CircleCheckBig,
    CreditCard,
    Drone,
    Film,
    Radio,
} from 'lucide-react';
import { videoSurfaceFrameClass } from '@/lib/videoSurface';
import type { BookingSlot, DjItem, PageProps, PortfolioItemData, VideoItem } from '@/types';
import { usePage } from '@inertiajs/react';

interface DjSetShowProps {
    price: number;
    slots: BookingSlot[];
    originals: VideoItem[];
    portfolioItems: PortfolioItemData[];
    djs: DjItem[];
    errors?: Record<string, string>;
}

export default function DjSetShow({
    price,
    slots,
    originals,
    portfolioItems,
    djs,
    errors,
}: DjSetShowProps) {
    const { site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const djSetProduct = useMemo(() => getDjSetProduct(t), [t]);

    useEffect(() => {
        trackBookingEvent('booking_page_viewed', {
            section: 'djset',
            content_name: t('pages.djset.hero_title'),
            content_category: 'dj_set_booking',
        });
    }, [t]);

    const openBooking = () => {
        openBookingModal({
            source: 'djset',
            analyticsEvent: 'hero_cta_clicked',
            analyticsPayload: {
                content_name: t('pages.djset.hero_title'),
                content_category: 'dj_set_booking',
            },
        });
    };

    const portfolioImages = portfolioItems.filter((item) => item.media_type === 'image' && item.asset_url);
    const heroImage = portfolioImages.find((item) => item.is_featured) ?? portfolioImages[0];
    const proofImages = portfolioImages.filter((item) => item.id !== heroImage?.id);
    const galleryImages = proofImages.length > 0 ? proofImages : portfolioImages;
    const portfolioVideos = portfolioItems.filter((item) => (
        item.media_type === 'youtube' || item.media_type === 'video'
    ));

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden">
                <div className="absolute inset-0 bg-background">
                    {heroImage?.asset_url && (
                        <img
                            src={heroImage.asset_url}
                            alt=""
                            className="h-full w-full object-cover object-center"
                        />
                    )}
                    <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.12_0.02_280/0.96)_0%,oklch(0.12_0.02_280/0.78)_48%,oklch(0.12_0.02_280/0.42)_100%)]" />
                    <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.08_0.01_280/0.22)_0%,oklch(0.08_0.01_280/0.42)_56%,var(--background)_100%)]" />
                </div>

                <div className="relative mx-auto grid min-h-[min(850px,92vh)] max-w-6xl content-end gap-8 px-4 pb-36 pt-24 sm:px-6 md:grid-cols-[minmax(0,1fr)_320px] md:items-end">
                    <div className="max-w-3xl">
                        <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                            {t('pages.djset.hero_eyebrow')}
                        </p>
                        <h1 className="mt-5 max-w-4xl font-display text-5xl font-bold tracking-tight text-white drop-shadow-[0_3px_22px_rgb(0_0_0/0.5)] md:text-7xl">
                            {t('pages.djset.hero_title')}
                        </h1>
                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-white/80 md:text-xl">
                            {t('pages.djset.hero_subtitle')}
                        </p>

                        <div className="mt-7 flex flex-wrap gap-2">
                            <SpecBadge highlight>{t('pages.djset.spec_cameras')}</SpecBadge>
                            <SpecBadge>{t('pages.djset.spec_drone')}</SpecBadge>
                            <SpecBadge>{t('pages.djset.spec_final_video')}</SpecBadge>
                        </div>

                        <div className="mt-8 space-y-6">
                            <div className="w-full lg:mx-auto lg:max-w-md">
                                <p className="font-mono-tabular text-4xl font-semibold text-white md:text-5xl">
                                    {formatMxn(price)}
                                </p>
                                <p className="mt-1 text-sm text-white/65">{t('booking.djset.price_note')}</p>
                                <PaymentTrustOrTestMode
                                    variant="stripe"
                                    layout="compact"
                                    onDark
                                    className="mt-4"
                                />
                            </div>
                            <BookingCtaSection hero className="py-0">
                                <BookingCtaButton type="button" hero onClick={openBooking}>
                                    {t('booking.djset.cta_book_production')}
                                </BookingCtaButton>
                            </BookingCtaSection>
                            <div className="w-full lg:mx-auto lg:max-w-md">
                                <Button variant="glass" size="xl" className="w-full" asChild>
                                    <a href="#sets">{t('booking.djset.cta_view_sets')}</a>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div className="hidden rounded-xl border border-white/15 bg-black/35 p-4 text-white shadow-2xl backdrop-blur-md md:mb-1 md:block">
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            {t('pages.djset.sidebar_badge')}
                        </p>
                        <p className="mt-3 font-display text-2xl font-bold">{t('pages.djset.sidebar_title')}</p>
                        <p className="mt-2 text-sm leading-relaxed text-white/70">
                            {t('pages.djset.sidebar_description')}
                        </p>
                        <PaymentTrustOrTestMode
                            variant="stripe"
                            layout="compact"
                            onDark
                            className="mt-4"
                        />
                        <button
                            type="button"
                            onClick={openBooking}
                            className="mt-5 flex w-full items-center justify-between rounded-lg border border-white/15 bg-white/10 px-4 py-3 text-left text-sm font-semibold text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        >
                            {t('booking.djset.cta_open_calendar')}
                            <CalendarDays className="h-4 w-4 text-primary" />
                        </button>
                    </div>
                </div>
            </section>

            <GlassSection
                eyebrow={t('pages.djset.showcase_eyebrow')}
                title={t('pages.djset.showcase_title')}
                description={t('pages.djset.showcase_description')}
            >
                <BookingCtaSection className="pt-0 pb-4">
                    <BookingCtaButton type="button" onClick={openBooking}>
                        {t('booking.djset.cta_hold_date')}
                        <ArrowRight className="h-5 w-5" />
                    </BookingCtaButton>
                </BookingCtaSection>
                <MediaSalesBoard
                    images={portfolioImages}
                    videos={originals}
                    djs={djs}
                    price={price}
                    onBook={openBooking}
                />
            </GlassSection>

            <GlassSection
                eyebrow={t('pages.djset.production_eyebrow')}
                title={t('pages.djset.production_title')}
                description={t('pages.djset.production_description')}
            >
                <div className="grid gap-4 lg:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
                    <PortfolioPreview images={galleryImages} />
                    <div className="grid content-start gap-3">
                        <OfferPoint icon={<Camera className="h-5 w-5" />} title={t('pages.djset.offer_camera_title')} copy={t('pages.djset.offer_camera_copy')} />
                        <OfferPoint icon={<Drone className="h-5 w-5" />} title={t('pages.djset.offer_drone_title')} copy={t('pages.djset.offer_drone_copy')} />
                        <OfferPoint icon={<Film className="h-5 w-5" />} title={t('pages.djset.offer_delivery_title')} copy={t('pages.djset.offer_delivery_copy')} />
                    </div>
                </div>
            </GlassSection>

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                checkoutRoute="djset.checkout"
                paymentProvider="stripe"
                product={djSetProduct}
                popupVariant="djset"
                popupPortfolioItems={portfolioItems}
                popupOriginals={originals}
            />

            {originals.length > 0 && (
                <GlassSection
                    eyebrow={t('pages.djset.originals_eyebrow')}
                    title={t('pages.djset.originals_title')}
                    description={t('pages.djset.originals_description')}
                >
                    <OriginalsShowcase videos={originals} onBook={openBooking} />
                </GlassSection>
            )}

            <GlassSection
                eyebrow={t('pages.djset.booking_eyebrow')}
                title={t('pages.djset.booking_title')}
                description={t('pages.djset.booking_description')}
            >
                <PaymentTrustOrTestMode variant="stripe" layout="card" className="mb-5" />
                <div className="grid gap-3 md:grid-cols-3">
                    <ProcessPoint icon={<Radio className="h-5 w-5" />} title={t('pages.djset.process_step_1_title')} copy={t('pages.djset.process_step_1_copy')} />
                    <ProcessPoint icon={<CreditCard className="h-5 w-5" />} title={t('pages.djset.process_step_2_title')} copy={t('pages.djset.process_step_2_copy')} />
                    <ProcessPoint icon={<CircleCheckBig className="h-5 w-5" />} title={t('pages.djset.process_step_3_title')} copy={t('pages.djset.process_step_3_copy')} />
                </div>
            </GlassSection>

            {(galleryImages.length > 0 || portfolioVideos.length > 0) && (
                <GlassSection
                    eyebrow={t('pages.djset.nightlife_eyebrow')}
                    title={t('pages.djset.nightlife_title')}
                    description={t('pages.djset.nightlife_description')}
                >
                    {portfolioVideos.length > 0 && (
                        <PortfolioVideoProof video={portfolioVideos[0]} images={galleryImages} onBook={openBooking} />
                    )}
                    <PortfolioEditorialGrid images={galleryImages} />
                </GlassSection>
            )}

            {djs.length > 0 && (
                <GlassSection
                    eyebrow={t('pages.djset.artists_eyebrow')}
                    title={t('pages.djset.artists_title')}
                    description={t('pages.djset.artists_description')}
                >
                    <div className="grid grid-cols-3 gap-1 sm:grid-cols-4 md:grid-cols-6">
                        {djs.slice(0, 6).map((dj, index) => (
                            <DjCard key={dj.id} dj={dj} index={index} />
                        ))}
                    </div>
                </GlassSection>
            )}

            <GlassSection
                eyebrow={t('pages.djset.faq_eyebrow')}
                title={t('pages.djset.faq_title')}
                description={t('pages.djset.faq_description')}
            >
                <div className="grid gap-3 md:grid-cols-2">
                    <Faq answer={t('pages.djset.faq_video_duration_a')} question={t('pages.djset.faq_video_duration_q')} />
                    <Faq answer={t('pages.djset.faq_pay_on_book_a')} question={t('pages.djset.faq_pay_on_book_q')} />
                    <Faq answer={t('pages.djset.faq_no_session_a')} question={t('pages.djset.faq_no_session_q')} />
                    <Faq answer={t('pages.djset.faq_drone_a')} question={t('pages.djset.faq_drone_q')} />
                    <Faq answer={t('pages.djset.faq_calendar_a')} question={t('pages.djset.faq_calendar_q')} />
                </div>
            </GlassSection>

            <FunnelPopups
                variant="djset"
                slotsCount={slots.length}
                portfolioItems={portfolioItems}
                originals={originals}
            />
        </SiteLayout>
    );
}

function OfferPoint({
    icon,
    title,
    copy,
}: {
    icon: ReactNode;
    title: string;
    copy: string;
}) {
    return (
        <div className="rounded-2xl border border-border/70 bg-secondary p-5">
            <span className="flex h-11 w-11 items-center justify-center rounded-xl border border-primary/25 bg-primary/10 text-primary">
                {icon}
            </span>
            <h3 className="mt-4 font-display text-xl font-bold text-foreground">{title}</h3>
            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{copy}</p>
        </div>
    );
}

function ProcessPoint(props: { icon: ReactNode; title: string; copy: string }) {
    return <OfferPoint {...props} />;
}

function Faq({ question, answer }: { question: string; answer: string }) {
    return (
        <details className="rounded-2xl border border-border/70 bg-secondary p-5 open:border-primary/30">
            <summary className="cursor-pointer list-none font-semibold text-foreground">{question}</summary>
            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{answer}</p>
        </details>
    );
}

function MediaSalesBoard({
    images,
    videos,
    djs,
    price,
    onBook,
}: {
    images: PortfolioItemData[];
    videos: VideoItem[];
    djs: DjItem[];
    price: number;
    onBook: () => void;
}) {
    const { t } = useTranslations();
    const featuredVideo = videos.find((video) => video.thumbnail_url) ?? videos[0];
    const supportingVideos = videos.filter((video) => video.id !== featuredVideo?.id).slice(0, 2);
    const artistImage = djs.find((dj) => dj.cover_url || dj.avatar_url);

    return (
        <div className="grid gap-3 lg:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)]">
            <article className="group relative min-h-[420px] overflow-hidden rounded-xl border border-border/70 bg-black text-white">
                {featuredVideo?.thumbnail_url && (
                    <img
                        src={featuredVideo.thumbnail_url}
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover opacity-85 transition duration-700 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                )}
                <div className="absolute inset-0 bg-gradient-to-t from-black via-black/45 to-black/10" />
                <div className="relative flex min-h-[420px] flex-col justify-between p-5 md:p-7">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <span className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-black/35 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] backdrop-blur">
                            <Film className="h-3.5 w-3.5 text-primary" />
                            {t('pages.djset.sales_reference_badge')}
                        </span>
                        <span className="rounded-full border border-primary/35 bg-primary/15 px-3 py-1 font-mono-tabular text-xs font-semibold text-primary backdrop-blur">
                            {formatMxn(price)}
                        </span>
                    </div>

                    <div className="max-w-2xl">
                        <h3 className="font-display text-3xl font-bold leading-tight md:text-4xl">
                            {t('pages.djset.sales_headline')}
                        </h3>
                        <p className="mt-3 line-clamp-2 text-sm leading-relaxed text-white/70 md:text-base">
                            {featuredVideo?.title ?? t('pages.djset.sales_fallback_title')}
                        </p>
                        <div className="mt-5 space-y-4">
                            <Button variant="glass" className="w-full sm:w-auto" asChild>
                                <a href="#sets">{t('pages.djset.sales_cta_view_sessions')}</a>
                            </Button>
                            <BookingCtaSection className="py-0">
                                <BookingCtaButton type="button" onClick={onBook}>
                                    {t('booking.djset.cta_reserve_recording')}
                                </BookingCtaButton>
                            </BookingCtaSection>
                        </div>
                    </div>
                </div>
            </article>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <div className="grid grid-cols-2 gap-3">
                    {images.slice(0, 2).map((image, index) => (
                        <PortfolioFrame key={image.id} image={image} className="min-h-[198px]" priority={index === 0} />
                    ))}
                </div>

                {artistImage && (
                    <figure className="relative min-h-[210px] overflow-hidden rounded-xl border border-border/70 bg-secondary">
                        <img
                            src={artistImage.cover_url ?? artistImage.avatar_url ?? ''}
                            alt={artistImage.name}
                            className="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                        />
                    </figure>
                )}

                {supportingVideos.length > 0 && (
                    <div className="grid gap-3 sm:col-span-2 sm:grid-cols-2 lg:col-span-1">
                        {supportingVideos.map((video) => (
                            <SalesVideoThumb key={video.id} video={video} />
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

function SalesVideoThumb({ video }: { video: VideoItem }) {
    return (
        <a
            href="#sets"
            className={`group relative min-h-[148px] text-white ${videoSurfaceFrameClass}`}
        >
            {video.thumbnail_url && (
                <img
                    src={video.thumbnail_url}
                    alt=""
                    className="absolute inset-0 h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-[1.04]"
                    loading="lazy"
                />
            )}
            <span className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent" />
        </a>
    );
}

function PortfolioPreview({ images }: { images: PortfolioItemData[] }) {
    const { t } = useTranslations();
    const displayImages = images.slice(0, 3);

    if (displayImages.length === 0) {
        return (
            <div className="grid min-h-[360px] place-items-center rounded-xl border border-border/70 bg-secondary p-8 text-center text-sm text-muted-foreground">
                {t('pages.djset.portfolio_preview_empty')}
            </div>
        );
    }

    return (
        <div className="grid min-h-[360px] grid-cols-[minmax(0,1fr)_minmax(120px,0.62fr)] gap-3">
            <PortfolioFrame image={displayImages[0]} className="row-span-2 min-h-[360px]" priority />
            {displayImages.slice(1).map((image) => (
                <PortfolioFrame
                    key={image.id}
                    image={image}
                    className={displayImages.length === 2 ? 'row-span-2 min-h-[360px]' : 'min-h-[174px]'}
                />
            ))}
        </div>
    );
}

function PortfolioEditorialGrid({ images }: { images: PortfolioItemData[] }) {
    if (images.length === 0) {
        return null;
    }

    return (
        <div className="mt-3 grid auto-rows-[160px] grid-cols-2 gap-3 md:auto-rows-[220px] md:grid-cols-4">
            {images.slice(0, 7).map((image, index) => (
                <PortfolioFrame
                    key={image.id}
                    image={image}
                    className={
                        index === 0
                            ? 'col-span-2 row-span-2'
                            : index === 1 || index === 4
                                ? 'row-span-2'
                                : ''
                    }
                />
            ))}
        </div>
    );
}

function PortfolioVideoProof({
    video,
    images,
    onBook,
}: {
    video: PortfolioItemData;
    images: PortfolioItemData[];
    onBook: () => void;
}) {
    const { t } = useTranslations();
    const image = images[0];
    const embedId = video.youtube_id;
    const [isPlaying, setIsPlaying] = useState(false);

    return (
        <div className="mb-3 grid gap-3 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
            <div className={videoSurfaceFrameClass}>
                {embedId && isPlaying ? (
                    <iframe
                        src={`https://www.youtube.com/embed/${embedId}?rel=0&autoplay=1&mute=1&playsinline=1`}
                        title={video.title ?? t('pages.djset.video_nightlife_title')}
                        className="aspect-video h-full min-h-[260px] w-full"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowFullScreen
                    />
                ) : (
                    <button
                        type="button"
                        onClick={() => setIsPlaying(Boolean(embedId))}
                        className="group relative block min-h-[320px] w-full text-left text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        aria-label={embedId ? t('pages.djset.play_video_aria', { title: video.title ?? t('pages.djset.video_nightlife_title') }) : undefined}
                    >
                        {(video.poster_url || video.asset_url) && (
                            <img
                                src={video.poster_url ?? video.asset_url ?? ''}
                                alt=""
                                className="absolute inset-0 h-full w-full object-cover opacity-90 transition duration-700 group-hover:scale-[1.03]"
                                loading="lazy"
                            />
                        )}
                        <span className="absolute inset-0 bg-gradient-to-t from-black via-black/25 to-transparent" />
                    </button>
                )}
            </div>
            <div className="grid gap-3 sm:grid-cols-[minmax(160px,0.7fr)_minmax(0,1fr)] lg:grid-cols-1">
                {image && <PortfolioFrame image={image} className="min-h-[220px]" />}
                <div className="flex flex-col justify-between rounded-xl border border-primary/20 bg-primary/10 p-5">
                    <div>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            {t('pages.djset.video_proof_eyebrow')}
                        </p>
                        <h3 className="mt-3 font-display text-2xl font-bold text-foreground">
                            {t('pages.djset.video_proof_title')}
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {t('pages.djset.video_proof_copy')}
                        </p>
                    </div>
                </div>
            </div>
            <BookingCtaSection>
                <BookingCtaButton type="button" onClick={onBook}>
                    {t('pages.djset.cta_choose_date')}
                </BookingCtaButton>
            </BookingCtaSection>
        </div>
    );
}

function PortfolioFrame({
    image,
    className = '',
    priority = false,
}: {
    image: PortfolioItemData;
    className?: string;
    priority?: boolean;
}) {
    const { t } = useTranslations();

    return (
        <figure className={`group relative overflow-hidden rounded-xl border border-border/70 bg-secondary ${className}`}>
            {(image.asset_url || image.poster_url) && (
                <img
                    src={image.asset_url ?? image.poster_url ?? ''}
                    alt={image.title ?? t('pages.djset.portfolio_nightlife_alt')}
                    className="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                    loading={priority ? 'eager' : 'lazy'}
                />
            )}
        </figure>
    );
}

function OriginalsShowcase({ videos, onBook }: { videos: VideoItem[]; onBook: () => void }) {
    const { t } = useTranslations();
    const playableVideos = videos.filter((video) => getYoutubeId(video));
    const [activeVideoId, setActiveVideoId] = useState(playableVideos[0]?.id);
    const activeVideo = playableVideos.find((video) => video.id === activeVideoId) ?? playableVideos[0];
    const activeYoutubeId = activeVideo ? getYoutubeId(activeVideo) : null;

    if (!activeVideo || !activeYoutubeId) {
        return null;
    }

    return (
        <div id="sets" className="scroll-mt-24 space-y-4">
            <div className="overflow-hidden rounded-xl border border-border/70 bg-black shadow-[0_28px_90px_rgb(0_0_0/0.32)]">
                <div className="aspect-video">
                    <iframe
                        key={activeYoutubeId}
                        src={`https://www.youtube.com/embed/${activeYoutubeId}?rel=0`}
                        title={activeVideo.title}
                        className="h-full w-full"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowFullScreen
                    />
                </div>
                <div className="border-t border-white/10 bg-black px-4 py-4 text-white md:px-5">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                        {t('pages.djset.originals_playing_badge')}
                    </p>
                    <h3 className="mt-2 font-display text-xl font-bold">{activeVideo.title}</h3>
                    {activeVideo.djs && activeVideo.djs.length > 0 && (
                        <p className="mt-1 text-sm text-white/60">
                            {activeVideo.djs.map((dj) => dj.name).join(' · ')}
                        </p>
                    )}
                </div>
            </div>

            <BookingCtaSection>
                <BookingCtaButton type="button" onClick={onBook}>
                    {t('pages.djset.cta_reserve_my_set')}
                    <ArrowRight className="h-5 w-5" />
                </BookingCtaButton>
            </BookingCtaSection>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {playableVideos.slice(0, 4).map((video) => (
                    <OriginalVideoCard
                        key={video.id}
                        video={video}
                        selected={video.id === activeVideo.id}
                        onSelect={() => setActiveVideoId(video.id)}
                    />
                ))}
            </div>
        </div>
    );
}

function OriginalVideoCard({
    video,
    selected,
    onSelect,
}: {
    video: VideoItem;
    selected: boolean;
    onSelect: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onSelect}
            aria-pressed={selected}
            className={`group overflow-hidden rounded-xl border text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${
                selected
                    ? 'border-primary/60 bg-primary/10 shadow-[0_0_32px_oklch(0.78_0.14_75/0.12)]'
                    : 'border-border/70 bg-secondary hover:border-primary/35'
            }`}
        >
            <span className="relative block aspect-video overflow-hidden bg-black">
                {video.thumbnail_url && (
                    <img
                        src={video.thumbnail_url}
                        alt=""
                        className="h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-[1.04]"
                        loading="lazy"
                    />
                )}
                <span className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent" />
            </span>
        </button>
    );
}

function getYoutubeId(video: VideoItem) {
    if (video.youtube_id) {
        return video.youtube_id;
    }

    const match = video.youtube_url?.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([^?&/]+)/);

    return match?.[1] ?? null;
}
