import { useEffect, useMemo, useState, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Aperture,
    CalendarDays,
    Camera,
    CheckCircle2,
    CircleCheckBig,
    Clock3,
    CreditCard,
    Drone,
    Film,
    Headphones,
    MessageCircle,
    Mic2,
    Moon,
    SlidersHorizontal,
    Sparkles,
    Video,
    Waves,
} from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { DjCard } from '@/components/lapsique/DjCard';
import { FunnelPopups } from '@/components/lapsique/FunnelPopups';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { ReelLoopCard } from '@/components/lapsique/ReelLoopCard';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/useTranslations';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { openBookingModal } from '@/lib/openBookingModal';
import { formatMxn } from '@/lib/utils';
import { getDjSetProduct } from '@/lib/bookingProducts';
import type {
    BookingSlot,
    DjItem,
    PageProps,
    PortfolioItemData,
    ReelLibraryEntry,
    VideoItem,
} from '@/types';

interface DjSetShowProps {
    price: number;
    slots: BookingSlot[];
    originals: VideoItem[];
    portfolioItems: PortfolioItemData[];
    djSetReels: ReelLibraryEntry[];
    djs: DjItem[];
    errors?: Record<string, string>;
}

const HERO_IMAGE_KEYWORDS = [
    'proper-collective',
    'fotos-proper',
    'rebolledo',
    'santino-on-heaven-22-de-marzo',
    'santino-22-de-marzo',
    'traumer-shonky',
    'umi',
];

const GEAR_ITEMS = [
    {
        icon: Camera,
        titleKey: 'pages.djset.gear_sony_title',
        copyKey: 'pages.djset.gear_sony_copy',
    },
    {
        icon: Drone,
        titleKey: 'pages.djset.gear_drone_title',
        copyKey: 'pages.djset.gear_drone_copy',
    },
    {
        icon: Video,
        titleKey: 'pages.djset.gear_ronin_title',
        copyKey: 'pages.djset.gear_ronin_copy',
    },
    {
        icon: Mic2,
        titleKey: 'pages.djset.gear_audio_title',
        copyKey: 'pages.djset.gear_audio_copy',
    },
] as const;

export default function DjSetShow({
    price,
    slots,
    originals,
    portfolioItems,
    djSetReels,
    djs,
    errors,
}: DjSetShowProps) {
    const { site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const djSetProduct = useMemo(() => getDjSetProduct(t), [t]);
    const whatsappHref = useMemo(
        () => buildWhatsAppHref(site.whatsapp, t('funnel.whatsapp.prefill_djset')),
        [site.whatsapp, t],
    );

    useEffect(() => {
        trackBookingEvent('booking_page_viewed', {
            section: 'djset',
            content_name: t('pages.djset.hero_title'),
            content_category: 'dj_set_booking',
        });
    }, [t]);

    const openBooking = (source = 'djset') => {
        openBookingModal({
            source,
            analyticsEvent: 'djset_booking_cta_clicked',
            analyticsPayload: {
                content_name: t('pages.djset.hero_title'),
                content_category: 'dj_set_booking',
            },
        });
    };

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('djset_whatsapp_cta_clicked', {
            source,
            target: 'whatsapp',
            content_category: 'dj_set_booking',
        });
    };

    const portfolioImages = portfolioItems.filter((item) => (
        item.media_type === 'image' && Boolean(item.asset_url || item.poster_url)
    ));
    const heroImage = pickHeroImage(portfolioImages);
    const proofImages = portfolioImages.filter((item) => item.id !== heroImage?.id);
    const galleryImages = proofImages.length > 0 ? proofImages : portfolioImages;

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden">
                <div className="absolute inset-0 bg-background">
                    {heroImage && (
                        <img
                            src={imageUrl(heroImage)}
                            alt=""
                            className="h-full w-full object-cover object-center"
                        />
                    )}
                    <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.10_0.02_280/0.98)_0%,oklch(0.10_0.02_280/0.82)_52%,oklch(0.10_0.02_280/0.40)_100%)]" />
                    <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.08_0.01_280/0.16)_0%,oklch(0.08_0.01_280/0.42)_58%,var(--background)_100%)]" />
                </div>

                <div className="relative mx-auto grid min-h-[min(820px,92svh)] max-w-6xl content-end gap-8 px-4 pb-28 pt-24 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-end">
                    <div className="max-w-4xl">
                        <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                            {t('pages.djset.hero_eyebrow')}
                        </p>
                        <h1 className="mt-4 max-w-4xl font-display text-4xl font-bold leading-[0.98] tracking-tight text-white drop-shadow-[0_3px_28px_rgb(0_0_0/0.55)] sm:text-5xl md:text-7xl">
                            {t('pages.djset.hero_title')}
                        </h1>
                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-white/82 md:text-xl">
                            {t('pages.djset.hero_subtitle')}
                        </p>

                        <div className="mt-7 flex flex-wrap gap-2">
                            <SpecBadge highlight>
                                <Camera className="h-3.5 w-3.5" />
                                {t('pages.djset.spec_cameras')}
                            </SpecBadge>
                            <SpecBadge>
                                <Drone className="h-3.5 w-3.5" />
                                {t('pages.djset.spec_drone')}
                            </SpecBadge>
                            <SpecBadge>
                                <Headphones className="h-3.5 w-3.5" />
                                {t('pages.djset.spec_audio')}
                            </SpecBadge>
                            <SpecBadge>
                                <Film className="h-3.5 w-3.5" />
                                {t('pages.djset.spec_final_video')}
                            </SpecBadge>
                        </div>

                        <div className="mt-8 grid gap-4 sm:grid-cols-[minmax(0,0.88fr)_minmax(260px,0.7fr)] sm:items-end">
                            <div className="rounded-2xl border border-primary/35 bg-black/70 px-5 py-4 shadow-[0_16px_48px_rgb(0_0_0/0.45)] backdrop-blur-md">
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                                    {t('pages.djset.hero_price_label')}
                                </p>
                                <p className="mt-2 font-mono-tabular text-4xl font-bold text-primary md:text-5xl">
                                    {formatMxn(price)}
                                </p>
                                <p className="mt-2 text-sm text-white/70">{t('booking.djset.price_note')}</p>
                                <PaymentTrustOrTestMode
                                    variant="stripe"
                                    layout="compact"
                                    onDark
                                    className="mt-3"
                                />
                            </div>

                            <div className="space-y-3">
                                <Button
                                    variant="default"
                                    size="xl"
                                    className="h-auto min-h-14 w-full gap-2 rounded-xl border border-[#25D366]/70 bg-[#25D366] px-5 text-center text-sm font-bold leading-tight text-white shadow-[0_16px_42px_oklch(0.66_0.18_145/0.34)] hover:bg-[#1EBE5D] hover:text-white hover:shadow-[0_18px_52px_oklch(0.66_0.18_145/0.46)] focus-visible:ring-[#25D366]/45 sm:text-base"
                                    asChild
                                >
                                    <a
                                        href={whatsappHref}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        onClick={() => trackWhatsApp('hero')}
                                    >
                                        <WhatsAppIcon className="size-5 fill-current" />
                                        <span className="min-w-0">{t('pages.djset.cta_whatsapp')}</span>
                                    </a>
                                </Button>
                                <BookingCtaButton
                                    type="button"
                                    variant="glass"
                                    className="w-full"
                                    onClick={() => openBooking('hero_agenda')}
                                >
                                    <CalendarDays className="h-5 w-5" />
                                    {t('booking.djset.cta_book_production')}
                                </BookingCtaButton>
                            </div>
                        </div>
                    </div>

                    <aside className="rounded-xl border border-white/15 bg-black/42 p-4 text-white shadow-2xl backdrop-blur-md">
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            {t('pages.djset.sidebar_badge')}
                        </p>
                        <p className="mt-3 font-display text-2xl font-bold">{t('pages.djset.sidebar_title')}</p>
                        <p className="mt-2 text-sm leading-relaxed text-white/70">
                            {t('pages.djset.sidebar_description')}
                        </p>
                        <div className="mt-4 grid gap-2">
                            <MiniSpec icon={<Clock3 className="h-4 w-4" />} text={t('pages.djset.sidebar_spec_recording')} />
                            <MiniSpec icon={<Film className="h-4 w-4" />} text={t('pages.djset.sidebar_spec_edit')} />
                            <MiniSpec icon={<Headphones className="h-4 w-4" />} text={t('pages.djset.sidebar_spec_audio')} />
                        </div>
                        <Button
                            variant="glass"
                            className="mt-5 w-full justify-between"
                            asChild
                        >
                            <a href="#sets">
                                {t('booking.djset.cta_view_sets')}
                                <ArrowRight className="h-4 w-4 text-primary" />
                            </a>
                        </Button>
                    </aside>
                </div>
            </section>

            <div className="flex flex-col gap-6 pt-6 md:gap-8 md:pt-8">
            <GlassSection
                eyebrow={t('pages.djset.showcase_eyebrow')}
                title={t('pages.djset.showcase_title')}
                description={t('pages.djset.showcase_description')}
            >
                <MediaSalesBoard
                    images={portfolioImages}
                    videos={originals}
                    price={price}
                    whatsappHref={whatsappHref}
                    onWhatsApp={() => trackWhatsApp('showcase')}
                    onBook={() => openBooking('showcase')}
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
                        <OfferPoint icon={<Waves className="h-5 w-5" />} title={t('pages.djset.offer_audio_title')} copy={t('pages.djset.offer_audio_copy')} />
                    </div>
                </div>
            </GlassSection>

            <GlassSection
                eyebrow={t('pages.djset.gear_eyebrow')}
                title={t('pages.djset.gear_title')}
                description={t('pages.djset.gear_description')}
            >
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {GEAR_ITEMS.map(({ icon: Icon, titleKey, copyKey }) => (
                        <OfferPoint
                            key={titleKey}
                            icon={<Icon className="h-5 w-5" />}
                            title={t(titleKey)}
                            copy={t(copyKey)}
                        />
                    ))}
                </div>
            </GlassSection>

            <NightRecordingSection />

            {djSetReels.length > 0 && (
                <GlassSection
                    eyebrow={t('pages.djset.reels_eyebrow')}
                    title={t('pages.djset.reels_title')}
                    description={t('pages.djset.reels_description')}
                >
                    <DjSetReelGrid clips={djSetReels} />
                </GlassSection>
            )}

            {originals.length > 0 && (
                <GlassSection
                    eyebrow={t('pages.djset.originals_eyebrow')}
                    title={t('pages.djset.originals_title')}
                    description={t('pages.djset.originals_description')}
                >
                    <OriginalsShowcase
                        videos={originals}
                        whatsappHref={whatsappHref}
                        onWhatsApp={() => trackWhatsApp('originals')}
                        onBook={() => openBooking('originals')}
                    />
                </GlassSection>
            )}

            <GlassSection
                eyebrow={t('pages.djset.scope_eyebrow')}
                title={t('pages.djset.scope_title')}
                description={t('pages.djset.scope_description')}
            >
                <div className="grid gap-3 md:grid-cols-2">
                    <ScopePoint icon={<CheckCircle2 className="h-5 w-5" />} title={t('pages.djset.scope_included_title')} copy={t('pages.djset.scope_included_copy')} />
                    <ScopePoint muted icon={<SlidersHorizontal className="h-5 w-5" />} title={t('pages.djset.scope_not_included_title')} copy={t('pages.djset.scope_not_included_copy')} />
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
                highlight
            />

            <GlassSection
                eyebrow={t('pages.djset.booking_eyebrow')}
                title={t('pages.djset.booking_title')}
                description={t('pages.djset.booking_description')}
            >
                <PaymentTrustOrTestMode variant="stripe" layout="card" className="mb-5" />
                <div className="grid gap-3 md:grid-cols-3">
                    <ProcessPoint icon={<MessageCircle className="h-5 w-5" />} title={t('pages.djset.process_step_1_title')} copy={t('pages.djset.process_step_1_copy')} />
                    <ProcessPoint icon={<CreditCard className="h-5 w-5" />} title={t('pages.djset.process_step_2_title')} copy={t('pages.djset.process_step_2_copy')} />
                    <ProcessPoint icon={<CircleCheckBig className="h-5 w-5" />} title={t('pages.djset.process_step_3_title')} copy={t('pages.djset.process_step_3_copy')} />
                </div>
            </GlassSection>

            {djs.length > 0 && (
                <GlassSection
                    eyebrow={t('pages.djset.artists_eyebrow')}
                    title={t('pages.djset.artists_title')}
                    description={t('pages.djset.artists_description')}
                    surfaceClassName="relative overflow-hidden border-primary/35 shadow-[0_26px_70px_oklch(0.24_0.04_250/0.16)]"
                    surfaceStyle={{
                        background: 'radial-gradient(circle at 12% 18%, oklch(0.84 0.15 78 / 0.24), transparent 34%), radial-gradient(circle at 88% 12%, oklch(0.76 0.12 205 / 0.18), transparent 34%), linear-gradient(135deg, oklch(0.99 0.01 93 / 0.96), oklch(0.91 0.05 245 / 0.78))',
                    }}
                >
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {djs.slice(0, 6).map((dj, index) => (
                            <DjCard key={dj.id} dj={dj} index={index} variant="spotlight" />
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
                    <Faq answer={t('pages.djset.faq_location_a')} question={t('pages.djset.faq_location_q')} />
                </div>
            </GlassSection>

            <GlassSection
                showHeader={false}
                title={t('pages.djset.final_cta_title')}
                className="text-center"
            >
                <div className="mx-auto flex max-w-2xl flex-col items-center gap-4 text-center">
                    <h2 className="font-display text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                        {t('pages.djset.final_cta_title')}
                    </h2>
                    <p className="text-sm leading-relaxed text-muted-foreground md:text-base">
                        {t('pages.djset.final_cta_description')}
                    </p>
                    <div className="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:justify-center">
                        <Button variant="cinematic" size="xl" className="h-auto min-h-12 w-full whitespace-normal px-5 py-3 sm:w-auto" asChild>
                            <a
                                href={whatsappHref}
                                target="_blank"
                                rel="noopener noreferrer"
                                onClick={() => trackWhatsApp('final')}
                            >
                                <MessageCircle className="h-5 w-5" />
                                {t('pages.djset.cta_whatsapp')}
                            </a>
                        </Button>
                        <BookingCtaButton type="button" variant="outline" className="w-full sm:w-auto" onClick={() => openBooking('final')}>
                            <CalendarDays className="h-5 w-5" />
                            {t('booking.djset.cta_open_calendar')}
                        </BookingCtaButton>
                    </div>
                </div>
            </GlassSection>
            </div>

            <FunnelPopups
                variant="djset"
                slotsCount={slots.length}
                portfolioItems={portfolioItems}
                originals={originals}
            />
        </SiteLayout>
    );
}

function MiniSpec({ icon, text }: { icon: ReactNode; text: string }) {
    return (
        <div className="flex items-start gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/75">
            <span className="mt-0.5 text-primary">{icon}</span>
            <span>{text}</span>
        </div>
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
        <div className="rounded-xl border border-border/70 bg-secondary p-5">
            <span className="flex h-11 w-11 items-center justify-center rounded-xl border border-primary/25 bg-primary/10 text-primary">
                {icon}
            </span>
            <h3 className="mt-4 font-display text-lg font-bold text-foreground">{title}</h3>
            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{copy}</p>
        </div>
    );
}

function NightRecordingSection() {
    const { t } = useTranslations();

    return (
        <GlassSection
            surface="solid"
            eyebrow={t('pages.djset.night_gear_eyebrow')}
            title={t('pages.djset.night_gear_title')}
            description={t('pages.djset.night_gear_description')}
        >
            <div className="grid gap-3 md:grid-cols-3">
                <OfferPoint
                    icon={<Moon className="h-5 w-5" />}
                    title={t('pages.djset.night_gear_lowlight_title')}
                    copy={t('pages.djset.night_gear_lowlight_copy')}
                />
                <OfferPoint
                    icon={<Aperture className="h-5 w-5" />}
                    title={t('pages.djset.night_gear_dynamic_title')}
                    copy={t('pages.djset.night_gear_dynamic_copy')}
                />
                <OfferPoint
                    icon={<Sparkles className="h-5 w-5" />}
                    title={t('pages.djset.night_gear_color_title')}
                    copy={t('pages.djset.night_gear_color_copy')}
                />
            </div>
        </GlassSection>
    );
}

function DjSetReelGrid({ clips }: { clips: ReelLibraryEntry[] }) {
    return (
        <div className="grid gap-3 rounded-xl border border-border/70 bg-black/45 p-3 sm:grid-cols-2 lg:grid-cols-4">
            {clips.slice(0, 8).map((clip) => (
                <ReelLoopCard
                    key={clip.id}
                    src={clip.src}
                    poster={clip.poster}
                        title={clip.title}
                        bookingSource="djset_reel_reference"
                        pauseWhenOffscreen
                        preload="none"
                        openPlayerOnClick={false}
                        articleClassName="min-h-[360px]"
                    />
            ))}
        </div>
    );
}

function ProcessPoint(props: { icon: ReactNode; title: string; copy: string }) {
    return <OfferPoint {...props} />;
}

function ScopePoint({
    icon,
    title,
    copy,
    muted = false,
}: {
    icon: ReactNode;
    title: string;
    copy: string;
    muted?: boolean;
}) {
    return (
        <article className="rounded-xl border border-border/70 bg-secondary p-5">
            <span className={`flex h-11 w-11 items-center justify-center rounded-xl border ${
                muted
                    ? 'border-border/70 bg-muted text-muted-foreground'
                    : 'border-primary/25 bg-primary/10 text-primary'
            }`}>
                {icon}
            </span>
            <h3 className="mt-4 font-display text-xl font-bold text-foreground">{title}</h3>
            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{copy}</p>
        </article>
    );
}

function Faq({ question, answer }: { question: string; answer: string }) {
    return (
        <details className="rounded-xl border border-border/70 bg-secondary p-5 open:border-primary/30">
            <summary className="cursor-pointer list-none font-semibold text-foreground">{question}</summary>
            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{answer}</p>
        </details>
    );
}

function MediaSalesBoard({
    images,
    videos,
    price,
    whatsappHref,
    onWhatsApp,
    onBook,
}: {
    images: PortfolioItemData[];
    videos: VideoItem[];
    price: number;
    whatsappHref: string;
    onWhatsApp: () => void;
    onBook: () => void;
}) {
    const { t } = useTranslations();
    const featuredVideo = videos.find((video) => getYoutubeId(video)) ?? videos.find((video) => video.thumbnail_url) ?? videos[0];
    const supportingVideos = videos
        .filter((video) => video.id !== featuredVideo?.id && Boolean(getYoutubeId(video)))
        .slice(0, 2);

    return (
        <div className="grid gap-3 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
            <article className="group relative min-h-[440px] overflow-hidden rounded-xl border border-border/70 bg-black text-white">
                {featuredVideo?.thumbnail_url ? (
                    <img
                        src={featuredVideo.thumbnail_url}
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover opacity-85 transition duration-700 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                ) : null}
                <div className="absolute inset-0 bg-gradient-to-t from-black via-black/45 to-black/10" />
                <div className="relative flex min-h-[440px] flex-col justify-between p-5 md:p-7">
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
                        <p className="mt-3 text-sm leading-relaxed text-white/70 md:text-base">
                            {featuredVideo?.title ?? t('pages.djset.sales_fallback_title')}
                        </p>
                        <div className="mt-5 grid gap-3 sm:grid-cols-2">
                            <Button
                                variant="default"
                                className="h-12 w-full rounded-xl border border-[#25D366]/70 bg-[#25D366] px-4 text-sm font-bold text-white shadow-[0_14px_34px_oklch(0.66_0.18_145/0.32)] hover:bg-[#1EBE5D] hover:text-white focus-visible:ring-[#25D366]/45"
                                asChild
                            >
                                <a
                                    href={whatsappHref}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    onClick={onWhatsApp}
                                >
                                    <WhatsAppIcon className="size-4 fill-current" />
                                    {t('pages.djset.cta_whatsapp_short')}
                                </a>
                            </Button>
                            <BookingCtaButton
                                type="button"
                                variant="default"
                                className="h-12 w-full rounded-xl border border-white/70 bg-white px-4 text-sm font-bold text-foreground shadow-[0_14px_34px_rgb(0_0_0/0.22)] hover:bg-white/90 hover:text-foreground"
                                onClick={onBook}
                            >
                                <CalendarDays className="h-4 w-4" />
                                {t('booking.djset.cta_reserve_recording')}
                            </BookingCtaButton>
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

                {supportingVideos.length > 0 && (
                    <div className="grid gap-3 sm:col-span-2 sm:grid-cols-2 lg:col-span-1">
                        {supportingVideos.map((video) => (
                            <OriginalReferenceCard key={video.id} video={video} />
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

function OriginalReferenceCard({ video }: { video: VideoItem }) {
    return (
        <a
            href="#sets"
            className="group overflow-hidden rounded-xl border border-border/70 bg-secondary transition hover:border-primary/35 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
            <span className="relative block aspect-video overflow-hidden bg-black">
                {video.thumbnail_url && (
                    <img
                        src={video.thumbnail_url}
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-[1.04]"
                        loading="lazy"
                    />
                )}
                <span className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent" />
                <span className="absolute bottom-3 left-3 right-3 line-clamp-2 text-sm font-semibold leading-tight text-white">
                    {video.title}
                </span>
            </span>
        </a>
    );
}

function PortfolioPreview({ images }: { images: PortfolioItemData[] }) {
    const { t } = useTranslations();
    const image = images[0];

    if (!image) {
        return (
            <div className="grid min-h-[360px] place-items-center rounded-xl border border-border/70 bg-secondary p-8 text-center text-sm text-muted-foreground">
                {t('pages.djset.portfolio_preview_empty')}
            </div>
        );
    }

    return (
        <PortfolioFrame image={image} className="min-h-[520px]" priority />
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
                    src={imageUrl(image)}
                    alt={image.title ?? t('pages.djset.portfolio_nightlife_alt')}
                    className="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                    loading={priority ? 'eager' : 'lazy'}
                />
            )}
        </figure>
    );
}

function OriginalsShowcase({
    videos,
    whatsappHref,
    onWhatsApp,
    onBook,
}: {
    videos: VideoItem[];
    whatsappHref: string;
    onWhatsApp: () => void;
    onBook: () => void;
}) {
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
                        loading="lazy"
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

            <div className="mx-auto grid w-full max-w-2xl gap-3 sm:grid-cols-2">
                <Button
                    variant="default"
                    className="h-12 w-full rounded-xl border border-[#25D366]/70 bg-[#25D366] px-4 text-sm font-bold text-white shadow-[0_14px_34px_oklch(0.66_0.18_145/0.28)] hover:bg-[#1EBE5D] hover:text-white focus-visible:ring-[#25D366]/45"
                    asChild
                >
                    <a
                        href={whatsappHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        onClick={onWhatsApp}
                    >
                        <WhatsAppIcon className="size-4 fill-current" />
                        {t('pages.djset.cta_whatsapp_short')}
                    </a>
                </Button>
                <BookingCtaButton
                    type="button"
                    variant="default"
                    className="h-12 w-full rounded-xl border border-white/70 bg-white px-4 text-sm font-bold text-foreground shadow-[0_14px_34px_rgb(0_0_0/0.14)] hover:bg-white/90 hover:text-foreground"
                    onClick={onBook}
                >
                    {t('pages.djset.cta_reserve_my_set')}
                    <ArrowRight className="h-5 w-5" />
                </BookingCtaButton>
            </div>

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

function imageUrl(item: PortfolioItemData): string {
    return item.asset_url ?? item.poster_url ?? '';
}

function pickHeroImage(images: PortfolioItemData[]): PortfolioItemData | undefined {
    return images.find((item) => {
        const haystack = `${item.slug ?? ''} ${item.asset_url ?? ''} ${item.poster_url ?? ''}`.toLowerCase();

        return HERO_IMAGE_KEYWORDS.some((keyword) => haystack.includes(keyword));
    }) ?? images.find((item) => item.is_featured) ?? images[0];
}

function WhatsAppIcon({ className }: { className?: string }) {
    return (
        <svg viewBox="0 0 24 24" className={className} aria-hidden>
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
    );
}

function buildWhatsAppHref(number: string | undefined, message: string): string {
    if (!number) {
        return '#';
    }

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}

function getYoutubeId(video: VideoItem) {
    if (video.youtube_id) {
        return video.youtube_id;
    }

    const match = video.youtube_url?.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([^?&/]+)/);

    return match?.[1] ?? null;
}
