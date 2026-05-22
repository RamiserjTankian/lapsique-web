import { Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Film,
    MousePointerClick,
    Play,
    Smartphone,
    Zap,
} from 'lucide-react';
import { useEffect, type ReactNode, type RefObject } from 'react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { HeroProofVideoCard } from '@/components/lapsique/HeroProofVideoCard';
import { PortfolioMediaViewer } from '@/components/lapsique/PortfolioMediaViewer';
import { FunnelDeliverables } from '@/components/lapsique/funnel/FunnelDeliverables';
import { FunnelTeam } from '@/components/lapsique/funnel/FunnelTeam';
import { OfferEquipmentShowcase } from '@/components/lapsique/funnel/OfferEquipmentShowcase';
import { FunnelFAQ } from '@/components/lapsique/funnel/FunnelFAQ';
import { FunnelPopups } from '@/components/lapsique/FunnelPopups';
import { FunnelStickyBar } from '@/components/lapsique/funnel/FunnelStickyBar';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { openBookingModal } from '@/lib/openBookingModal';
import { route } from '@/lib/route';
import { formatMxn } from '@/lib/utils';
import type {
    BookingSlot,
    PageProps,
    HeroProofVideoData,
    PortfolioItemData,
} from '@/types';

interface HomeProps {
    title: string;
    subtitle: string;
    price: number;
    slots: BookingSlot[];
    portfolioItems: PortfolioItemData[];
    heroProofVideo: HeroProofVideoData | null;
    errors?: Record<string, string>;
}

export default function Home({
    title,
    subtitle,
    price,
    slots,
    portfolioItems,
    heroProofVideo,
    errors,
}: HomeProps) {
    const { ziggy, site } = usePage<PageProps>().props;
    const adProofRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', {
        section: 'business_reel_formats',
    });
    const aftermovieRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', {
        section: 'business_aftermovies',
    });
    const portfolioImages = portfolioItems.filter((item) => (
        item.media_type === 'image' && Boolean(item.asset_url || item.poster_url)
    ));
    const portfolioVideos = portfolioItems.filter((item) => (
        item.media_type !== 'image' && Boolean(item.embed_url || item.playback_url || item.asset_url)
    ));
    const heroImage = portfolioImages.find((item) => item.is_featured) ?? portfolioImages[0];
    const featuredVideo = portfolioVideos.find((item) => item.is_featured) ?? portfolioVideos[0];

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
            <SeoHead />

            <BusinessHero
                title={title}
                subtitle={subtitle}
                price={price}
                heroImage={heroImage}
                heroProofVideo={heroProofVideo}
                onBook={openBooking}
            />

            <GlassSection
                eyebrow="Oferta"
                title="Crea contenido cinematográfico para tu negocio y sube de nivel"
                description="Reels y fotos con producción cinematográfica: elevan tu marca, venden tu oferta y salen listas para pauta y redes."
                className="pt-8"
            >
                <BookingCtaSection className="pt-0 pb-2">
                    <BookingCtaButton type="button" onClick={openBooking}>
                        Agendar sesión
                        <ArrowRight className="h-5 w-5" />
                    </BookingCtaButton>
                </BookingCtaSection>
                <BusinessOfferBoard images={portfolioImages} heroProofVideo={heroProofVideo} />
            </GlassSection>

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                popupVariant="home"
                popupPortfolioItems={portfolioItems}
                popupHeroProofVideo={heroProofVideo}
            />

            <GlassSection
                eyebrow="Pauta"
                title="Contenido que un negocio puede usar para atraer, probar y cerrar"
                description="El rodaje se plantea para piezas verticales, hooks rápidos y una oferta entendible antes de pedir clic."
            >
                <BookingCtaSection className="pt-0 pb-4">
                    <BookingCtaButton type="button" onClick={openBooking}>
                        Elegir fecha
                        <CalendarDays className="h-5 w-5" />
                    </BookingCtaButton>
                </BookingCtaSection>
                <BusinessCreativeBoard
                    images={portfolioImages}
                    featuredVideo={featuredVideo}
                    sectionRef={adProofRef}
                />
            </GlassSection>

            <FunnelDeliverables />

            <GlassSection
                eyebrow="Aftermovies"
                title="Para eventos y experiencias, el ticket sube con una historia completa"
                description="Los reels son la puerta de volumen. Un aftermovie convierte venue, lanzamiento o comunidad en prueba de escala."
                action={
                    <Button variant="link" asChild className="text-primary">
                        <Link href={route('portfolio.index', undefined, false, ziggy)}>
                            Ver portafolio →
                        </Link>
                    </Button>
                }
            >
                <AftermovieProof
                    sectionRef={aftermovieRef}
                    onBook={openBooking}
                />
            </GlassSection>

            <FunnelTeam />
            <FunnelFAQ />
            <FunnelStickyBar price={price} label="Contenido para negocios" deferUntilScrolled />

            <FunnelPopups
                variant="home"
                slotsCount={slots.length}
                portfolioItems={portfolioItems}
                heroProofVideo={heroProofVideo}
            />
        </SiteLayout>
    );
}

function BusinessHero({
    title,
    subtitle,
    price,
    heroImage,
    heroProofVideo,
    onBook,
}: {
    title: string;
    subtitle: string;
    price: number;
    heroImage?: PortfolioItemData;
    heroProofVideo: HeroProofVideoData | null;
    onBook: () => void;
}) {
    return (
        <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden">
            <div className="absolute inset-0 bg-background">
                {(heroImage?.asset_url || heroImage?.poster_url) && (
                    <img
                        src={heroImage.asset_url ?? heroImage.poster_url ?? ''}
                        alt=""
                        className="h-full w-full object-cover object-center"
                    />
                )}
                <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.11_0.02_280/0.97)_0%,oklch(0.11_0.02_280/0.84)_50%,oklch(0.11_0.02_280/0.46)_100%)]" />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.08_0.01_280/0.25)_0%,oklch(0.08_0.01_280/0.38)_54%,var(--background)_100%)]" />
            </div>

            <div className="relative mx-auto grid min-h-[min(640px,calc(100svh-10rem))] max-w-6xl content-center gap-6 px-4 pb-6 pt-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-center">
                <div className="max-w-4xl">
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                        Lapsique para negocios
                    </p>
                    <h1 className="mt-3 max-w-4xl font-display text-4xl font-bold leading-[0.98] tracking-tight text-white drop-shadow-[0_3px_28px_rgb(0_0_0/0.55)] sm:text-5xl md:text-6xl lg:text-7xl">
                        {title}
                    </h1>
                    <p className="mt-3 max-w-2xl text-base leading-relaxed text-white/80 md:text-xl">
                        {subtitle}
                    </p>

                    <div className="mt-6 space-y-6">
                        <div className="w-full max-w-md rounded-2xl border border-primary/35 bg-black/70 px-5 py-4 shadow-[0_16px_48px_rgb(0_0_0/0.45)] backdrop-blur-md lg:mx-auto">
                            <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                                Sesión lista para reservar
                            </p>
                            <p className="mt-2 font-mono-tabular text-4xl font-bold text-primary drop-shadow-[0_2px_12px_rgb(0_0_0/0.4)] md:text-5xl">
                                {formatMxn(price)}
                            </p>
                            <p className="mt-1 text-sm text-white/85">
                                Checkout seguro y fecha real en agenda
                            </p>
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

                <HeroProofPanel video={heroProofVideo} />
            </div>
        </section>
    );
}

function HeroProofPanel({ video }: { video: HeroProofVideoData | null }) {
    if (!video) {
        return null;
    }

    return (
        <aside className="hidden lg:block">
            <HeroProofVideoCard
                video={video}
                eager
                className="min-h-[min(420px,calc(100svh-14rem))] shadow-2xl"
            />
        </aside>
    );
}

function BusinessOfferBoard({
    images,
    heroProofVideo,
}: {
    images: PortfolioItemData[];
    heroProofVideo: HeroProofVideoData | null;
}) {
    const imageA = images[2] ?? images[0];
    const offerHeroVideo = heroProofVideo && isPlayableHeroProofVideo(heroProofVideo)
        ? heroProofVideo
        : null;

    return (
        <div id="reels-negocio" className="scroll-mt-24 flex flex-col gap-4">
            <OfferEquipmentShowcase />

            <article className="relative min-h-[220px] overflow-hidden rounded-xl border border-primary/25 bg-black text-white shadow-[0_20px_60px_rgb(0_0_0/0.16)] sm:min-h-[240px] lg:min-h-[260px]">
                {offerHeroVideo ? (
                    <OfferHeroBackground video={offerHeroVideo} />
                ) : imageA ? (
                    <ImageFrame
                        image={imageA}
                        className="absolute inset-0 h-full w-full rounded-none border-0"
                        imageClassName="opacity-80 object-cover object-center"
                    />
                ) : null}
                <div className="absolute inset-0 bg-gradient-to-r from-black via-black/75 to-black/35 md:via-black/65 md:to-black/20" />
                <div className="relative flex h-full min-h-[220px] flex-col justify-between p-5 sm:min-h-[240px] md:p-6 lg:min-h-[260px]">
                    <div className="flex flex-wrap gap-2">
                        <OfferPill icon={<MetaLogoIcon />}>
                            Hecho para Meta
                        </OfferPill>
                    </div>

                    <div className="max-w-xl">
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            Oferta de entrada
                        </p>
                        <h3 className="mt-2 font-display text-2xl font-bold leading-tight md:text-3xl">
                            Sesión desde $3,000 MXN · 1 reel + 10 fotos editadas.
                        </h3>
                        <p className="mt-3 max-w-lg text-sm leading-relaxed text-white/72">
                            Paquete claro para negocios que necesitan verse mejor en Reels,
                            anuncios y campañas sin comprar una producción indefinida.
                        </p>
                    </div>
                </div>
            </article>
        </div>
    );
}

function isPlayableHeroProofVideo(video: HeroProofVideoData): boolean {
    return (
        (video.media_type === 'youtube' && Boolean(video.embed_url))
        || (video.media_type === 'video' && Boolean(video.playback_url))
    );
}

function OfferHeroBackground({ video }: { video: HeroProofVideoData }) {
    const mediaClassName = 'absolute inset-0 h-full w-full opacity-80 object-cover object-center';

    return (
        <div className="absolute inset-0 h-full w-full" aria-hidden>
            {video.media_type === 'youtube' && video.embed_url ? (
                <iframe
                    title={video.title ?? 'Video de portafolio'}
                    src={video.embed_url}
                    className={mediaClassName}
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    loading="lazy"
                />
            ) : video.media_type === 'video' && video.playback_url ? (
                <video
                    src={video.playback_url}
                    poster={video.poster_url ?? undefined}
                    className={mediaClassName}
                    autoPlay
                    loop
                    muted
                    playsInline
                    preload="metadata"
                />
            ) : null}
        </div>
    );
}

function BusinessCreativeBoard({
    images,
    featuredVideo,
    sectionRef,
}: {
    images: PortfolioItemData[];
    featuredVideo?: PortfolioItemData;
    sectionRef: RefObject<HTMLDivElement | null>;
}) {
    const displayImages = [images[4], images[5], images[6]].filter(Boolean) as PortfolioItemData[];
    const creativeImages = displayImages.length > 0 ? displayImages : images.slice(0, 3);

    return (
        <div ref={sectionRef} className="grid gap-4 lg:grid-cols-[minmax(340px,0.92fr)_minmax(0,1.08fr)]">
            <div className="grid min-h-[540px] grid-cols-[minmax(0,1fr)_minmax(0,0.82fr)] gap-3">
                <VerticalCreativeFrame
                    image={creativeImages[0]}
                    label="Gancho"
                    title="Para detener el scroll en el feed"
                    className="row-span-2"
                />
                <VerticalCreativeFrame
                    image={creativeImages[1]}
                    label="Reel"
                    title="Formato vertical listo para pauta"
                    className="min-h-[258px]"
                />
                <VerticalCreativeFrame
                    image={creativeImages[2]}
                    label="Conversión"
                    title="CTA claro para clic o mensaje"
                    className="min-h-[258px]"
                />
            </div>

            <div className="grid gap-3">
                <article className="rounded-xl border border-border/70 bg-secondary p-5 md:p-6">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                        Tema creativo
                    </p>
                    <h3 className="mt-3 max-w-xl font-display text-3xl font-bold text-foreground">
                        Diseñados para Meta Ads
                    </h3>
                    <p className="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground md:text-base">
                        Rodamos Reels y piezas verticales pensadas para pauta en Meta: gancho que frena
                        el scroll, look de marca y un llamado a la acción que puedes medir en Ads Manager.
                    </p>
                    <div className="mt-5 grid gap-3 sm:grid-cols-3">
                        <SignalPoint
                            icon={<Zap className="h-4 w-4" />}
                            title="Gancho"
                            copy="Oferta o dolor en los primeros segundos, antes del skip."
                        />
                        <SignalPoint
                            icon={<Smartphone className="h-4 w-4" />}
                            title="Formato Reels"
                            copy="9:16, ritmo de feed y assets listos para Reels y Stories."
                        />
                        <SignalPoint
                            icon={<MousePointerClick className="h-4 w-4" />}
                            title="Conversión"
                            copy="Reserva, WhatsApp o landing con CTA legible para campaña."
                        />
                    </div>
                </article>

                {featuredVideo ? (
                    <article className="overflow-hidden rounded-xl border border-border/70 bg-black">
                        <PortfolioMediaViewer
                            item={featuredVideo}
                            className="aspect-video min-h-[260px] w-full bg-black object-cover"
                        />
                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 bg-black px-4 py-4 text-white md:px-5">
                            <div>
                                <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                                    Movimiento
                                </p>
                                <p className="mt-1 font-display text-xl font-bold">
                                    {featuredVideo.title ?? 'Portafolio en video'}
                                </p>
                            </div>
                            <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-white/75">
                                <Play className="h-3.5 w-3.5 fill-current text-primary" />
                                Video proof
                            </span>
                        </div>
                    </article>
                ) : (
                    <article className="rounded-xl border border-border/70 bg-secondary p-5 md:p-6">
                        <p className="text-sm leading-7 text-muted-foreground">
                            El portafolio fotográfico sostiene la calidad visual mientras se agregan
                            más aftermovies y piezas de negocio al feed de video.
                        </p>
                    </article>
                )}
            </div>
        </div>
    );
}

function AftermovieProof({
    sectionRef,
    onBook,
}: {
    sectionRef: RefObject<HTMLDivElement | null>;
    onBook: () => void;
}) {
    return (
        <div ref={sectionRef} id="portafolio-negocios" className="scroll-mt-24 space-y-4">
            <article className="flex flex-col justify-between rounded-xl border border-primary/20 bg-primary/10 p-5 md:p-6">
                <div>
                    <div className="flex h-11 w-11 items-center justify-center rounded-xl border border-primary/25 bg-background/50 text-primary">
                        <Film className="h-5 w-5" />
                    </div>
                    <h3 className="mt-4 font-display text-2xl font-bold text-foreground">
                        Primero compra el reel. Después escala la historia.
                    </h3>
                    <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                        Mantén la landing de adquisición enfocada en negocios y usa aftermovies
                        para proyectos donde evento, comunidad y experiencia justifican más producción.
                    </p>
                </div>
            </article>

            <BookingCtaSection>
                <BookingCtaButton type="button" onClick={onBook}>
                    Empezar con sesión
                </BookingCtaButton>
            </BookingCtaSection>
        </div>
    );
}

function VerticalCreativeFrame({
    image,
    label,
    title,
    className = '',
}: {
    image?: PortfolioItemData;
    label: string;
    title: string;
    className?: string;
}) {
    return (
        <figure className={`group relative overflow-hidden rounded-xl border border-border/70 bg-secondary ${className}`}>
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
                <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                    {label}
                </p>
                <p className="mt-2 max-w-[13rem] font-display text-xl font-bold leading-tight">
                    {title}
                </p>
            </figcaption>
        </figure>
    );
}

function SignalPoint({
    icon,
    title,
    copy,
}: {
    icon: ReactNode;
    title: string;
    copy: string;
}) {
    return (
        <div className="rounded-lg border border-border/70 bg-background/55 p-3">
            <span className="flex h-8 w-8 items-center justify-center rounded-lg border border-primary/20 bg-primary/10 text-primary">
                {icon}
            </span>
            <p className="mt-3 text-sm font-semibold text-foreground">{title}</p>
            <p className="mt-1 text-xs leading-relaxed text-muted-foreground">{copy}</p>
        </div>
    );
}

function MetaLogoIcon({ className = 'h-3.5 w-3.5' }: { className?: string }) {
    return (
        <svg className={className} viewBox="0 0 36 15" fill="currentColor" aria-hidden="true">
            <path d="M8.02 5.49C7.4 4.33 7.03 3.51 7.03 2.67 7.03 1.16 8.23 0 9.76 0c1.12 0 1.99.62 2.76 1.67l.93 1.24 1.11-1.24C15.33.62 16.2 0 17.32 0c1.53 0 2.73 1.16 2.73 2.67 0 .84-.37 1.66-.99 2.82l-2.34 4.05c-.41.71-.68 1.15-1.02 1.44-.41.41-.91.66-1.5.66s-1.09-.25-1.5-.66c-.34-.29-.61-.73-1.02-1.44L9.77 6.32 7.49 11.4c-.41.71-.68 1.15-1.02 1.44-.41.41-.91.66-1.5.66s-1.09-.25-1.5-.66c-.34-.29-.61-.73-1.02-1.44L.98 5.49h2.59zm9.5 0c-.62-1.16-.99-1.98-.99-2.82 0-1.51 1.2-2.67 2.73-2.67 1.12 0 1.99.62 2.76 1.67l.93 1.24 1.11-1.24c.77-1.05 1.64-1.67 2.76-1.67 1.53 0 2.73 1.16 2.73 2.67 0 .84-.37 1.66-.99 2.82l-2.34 4.05c-.41.71-.68 1.15-1.02 1.44-.41.41-.91.66-1.5.66s-1.09-.25-1.5-.66c-.34-.29-.61-.73-1.02-1.44l-2.34-4.05-2.34 4.05c-.41.71-.68 1.15-1.02 1.44-.41.41-.91.66-1.5.66s-1.09-.25-1.5-.66c-.34-.29-.61-.73-1.02-1.44l-2.34-4.05z" />
        </svg>
    );
}

function OfferPill({ icon, children }: { icon: ReactNode; children: ReactNode }) {
    return (
        <span className="inline-flex items-center gap-2 rounded-full border border-white/18 bg-black/35 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-white backdrop-blur">
            {icon}
            {children}
        </span>
    );
}

function ImageFrame({
    image,
    className = '',
    imageClassName = '',
    eager = false,
}: {
    image: PortfolioItemData;
    className?: string;
    imageClassName?: string;
    eager?: boolean;
}) {
    return (
        <figure className={`group relative overflow-hidden rounded-xl border border-border/70 bg-secondary ${className}`}>
            {(image.asset_url || image.poster_url) && (
                <img
                    src={image.asset_url ?? image.poster_url ?? ''}
                    alt={image.title ?? 'Portafolio visual de Lapsique'}
                    className={`h-full w-full object-cover transition duration-700 group-hover:scale-[1.03] ${imageClassName}`}
                    loading={eager ? 'eager' : 'lazy'}
                />
            )}
            <div className="absolute inset-0 bg-gradient-to-t from-black/78 via-black/12 to-transparent" />
            {image.title && (
                <figcaption className="absolute inset-x-0 bottom-0 p-3 text-xs font-semibold text-white">
                    {image.title}
                </figcaption>
            )}
        </figure>
    );
}
