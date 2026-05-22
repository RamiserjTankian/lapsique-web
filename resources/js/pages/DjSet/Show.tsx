import { useEffect, useState, type ReactNode } from 'react';
import SiteLayout from '@/layouts/SiteLayout';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { BookingWidget, type BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { DjCard } from '@/components/lapsique/DjCard';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { FunnelStickyBar } from '@/components/lapsique/funnel/FunnelStickyBar';
import { Button } from '@/components/ui/button';
import { formatMxn } from '@/lib/utils';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import {
    ArrowRight,
    CalendarDays,
    Camera,
    CircleCheckBig,
    CreditCard,
    Drone,
    Film,
    Play,
    Radio,
} from 'lucide-react';
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

const djSetProduct: BookingWidgetProduct = {
    checkoutLabel: 'Checkout DJ set',
    headerTitle: 'Elige la fecha de tu DJ set',
    headerDescription: 'Aparta producción con 3 cámaras fijas + dron y paga con tarjeta',
    summaryTitle: 'Grabación de DJ Set',
    summaryDescription: 'Video final de 1 hora con 3 cámaras fijas + dron',
    cartService: 'DJ set grabado',
    cartDuration: 'Video final 1 hora',
    summaryPerks: [
        '3 cámaras fijas para performance y ambiente',
        'Tomas aéreas con dron',
        'Video final de 1 hora',
        'Producción enfocada en DJs',
        'Checkout con tarjeta en Stripe',
    ],
    terms: [
        'La reserva queda sujeta a disponibilidad real del horario elegido y a confirmación del pago.',
        'La oferta incluye una grabación de DJ set con 3 cámaras fijas y dron para entregar un video final de hasta 1 hora.',
        'Locaciones, permisos, clima y condiciones de vuelo del dron deben permitir una operación segura; cambios de alcance se cotizan aparte.',
        'Puedes solicitar cambios de fecha con mínimo 24 horas de anticipación. Cambios tardíos o inasistencias pueden perder el horario reservado.',
        'Autorizas el uso del material producido para portafolio de lapsique.media salvo acuerdo de confidencialidad previo.',
    ],
    paymentCopy: 'Pago con tarjeta protegido por Stripe.',
    unavailableWhatsApp: 'Hola, quiero grabar un DJ set y no veo horarios disponibles en la agenda.',
};

export default function DjSetShow({
    price,
    slots,
    originals,
    portfolioItems,
    djs,
    errors,
}: DjSetShowProps) {
    const { site } = usePage<PageProps>().props;

    useEffect(() => {
        trackBookingEvent('booking_page_viewed', {
            section: 'djset',
            content_name: 'Grabación de DJ Set',
            content_category: 'dj_set_booking',
        });
    }, []);

    const scrollToAgenda = () => {
        trackBookingEvent('hero_cta_clicked', {
            target: 'djset_agenda',
            content_name: 'Grabación de DJ Set',
            content_category: 'dj_set_booking',
        });
        document.getElementById('agenda')?.scrollIntoView({ behavior: 'smooth' });
    };

    const portfolioImages = portfolioItems.filter((item) => item.media_type === 'image' && item.asset_url);
    const heroImage = portfolioImages.find((item) => item.is_featured) ?? portfolioImages[0];
    const proofImages = portfolioImages.filter((item) => item.id !== heroImage?.id);
    const galleryImages = proofImages.length > 0 ? proofImages : portfolioImages;

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

                <div className="relative mx-auto grid min-h-[min(850px,92vh)] max-w-6xl content-end gap-8 px-4 pb-36 pt-24 sm:px-6 md:grid-cols-[minmax(0,1fr)_320px] md:items-end md:pb-24">
                    <div className="max-w-3xl">
                        <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                            Lapsique para DJs
                        </p>
                        <h1 className="mt-5 max-w-4xl font-display text-5xl font-bold tracking-tight text-white drop-shadow-[0_3px_22px_rgb(0_0_0/0.5)] md:text-7xl">
                            Grabación de DJ Set
                        </h1>
                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-white/80 md:text-xl">
                            Produce un set largo que muestre tu sonido, la cabina y la energía de la pista.
                            Lapsique lo captura con una puesta visual hecha para DJs.
                        </p>

                        <div className="mt-7 flex flex-wrap gap-2">
                            <SpecBadge highlight>3 cámaras fijas</SpecBadge>
                            <SpecBadge>Dron</SpecBadge>
                            <SpecBadge>Video final 1 hora</SpecBadge>
                        </div>

                        <div className="mt-8 flex flex-wrap items-end gap-x-5 gap-y-3">
                            <div>
                                <p className="font-mono-tabular text-4xl font-semibold text-white md:text-5xl">
                                    {formatMxn(price)}
                                </p>
                                <p className="mt-1 text-sm text-white/65">MXN · paga con tarjeta al reservar</p>
                            </div>
                            <div className="flex flex-wrap gap-3">
                                <Button type="button" variant="cinematic" size="xl" onClick={scrollToAgenda}>
                                    Elegir fecha
                                </Button>
                                <Button variant="glass" size="xl" asChild>
                                    <a href="#sets">Ver sets grabados</a>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div className="hidden rounded-xl border border-white/15 bg-black/35 p-4 text-white shadow-2xl backdrop-blur-md md:mb-1 md:block">
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            Reserva directa
                        </p>
                        <p className="mt-3 font-display text-2xl font-bold">Una hora final, no un clip suelto.</p>
                        <p className="mt-2 text-sm leading-relaxed text-white/70">
                            Selecciona tu fecha disponible y sal a Stripe sin esperar una cotización manual.
                        </p>
                        <button
                            type="button"
                            onClick={scrollToAgenda}
                            className="mt-5 flex w-full items-center justify-between rounded-lg border border-white/15 bg-white/10 px-4 py-3 text-left text-sm font-semibold text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        >
                            Abrir agenda
                            <CalendarDays className="h-4 w-4 text-primary" />
                        </button>
                    </div>
                </div>
            </section>

            <GlassSection
                eyebrow="Producción"
                title="Tu set primero entra por imagen"
                description="La propuesta combina prueba visual real, grabación larga y reserva inmediata para que el artista llegue con una fecha cerrada."
            >
                <div className="grid gap-4 lg:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
                    <PortfolioPreview images={galleryImages} />
                    <div className="grid content-start gap-3">
                        <OfferPoint icon={<Camera className="h-5 w-5" />} title="3 cámaras fijas" copy="Performance, manos y atmósfera con cobertura pensada para mezclar planos." />
                        <OfferPoint icon={<Drone className="h-5 w-5" />} title="Dron" copy="Aperturas, contexto y escala cuando la locación y el clima permiten vuelo." />
                        <OfferPoint icon={<Film className="h-5 w-5" />} title="Entrega larga" copy="Un video final de una hora para enseñar el set, no solo un teaser." />
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
            />

            {originals.length > 0 && (
                <GlassSection
                    eyebrow="Psique Originals"
                    title="Sets grabados por Lapsique"
                    description="Mira una referencia completa antes de reservar: performance, ambiente y ritmo visual hechos para artistas de la escena."
                    action={
                        <Button type="button" variant="glass" onClick={scrollToAgenda}>
                            Reservar mi set
                            <ArrowRight className="h-4 w-4" />
                        </Button>
                    }
                >
                    <OriginalsShowcase videos={originals} />
                </GlassSection>
            )}

            <GlassSection
                eyebrow="Reserva"
                title="Fecha cerrada en tres pasos"
                description="Selecciona disponibilidad real del equipo, deja tus datos y completa Stripe."
            >
                <div className="grid gap-3 md:grid-cols-3">
                    <ProcessPoint icon={<Radio className="h-5 w-5" />} title="1. Elige slot" copy="La agenda se bloquea contra las demás sesiones para no duplicar producción." />
                    <ProcessPoint icon={<CreditCard className="h-5 w-5" />} title="2. Paga tarjeta" copy="Stripe procesa el cobro de la grabación y confirma tu reserva." />
                    <ProcessPoint icon={<CircleCheckBig className="h-5 w-5" />} title="3. Produce" copy="Coordinamos set y locación para capturar una sesión visualmente sólida." />
                </div>
            </GlassSection>

            {galleryImages.length > 0 && (
                <GlassSection
                    eyebrow="Nightlife"
                    title="Portafolio con cabina, artista y pista"
                    description="Imágenes de eventos que sostienen la atmósfera del DJ set y muestran cómo se ve una producción Lapsique."
                >
                    <PortfolioEditorialGrid images={galleryImages} />
                </GlassSection>
            )}

            {djs.length > 0 && (
                <GlassSection
                    eyebrow="Artistas"
                    title="DJs en la órbita de Lapsique"
                    description="Talento y proyectos con los que hemos documentado sesiones y presencia en escena."
                >
                    <div className="grid grid-cols-3 gap-1 sm:grid-cols-4 md:grid-cols-6">
                        {djs.slice(0, 6).map((dj, index) => (
                            <DjCard key={dj.id} dj={dj} index={index} />
                        ))}
                    </div>
                </GlassSection>
            )}

            <GlassSection
                eyebrow="FAQ"
                title="Antes de reservar tu set"
                description="Lo esencial para cerrar la grabación sin perder tiempo en una cotización manual."
            >
                <div className="grid gap-3 md:grid-cols-2">
                    <Faq answer="La oferta incluye un video final de hasta 1 hora. La coordinación del set se cierra después de apartar fecha." question="¿La hora es la duración del video?" />
                    <Faq answer="Sí. El checkout de esta landing cobra la grabación DJ set con tarjeta vía Stripe." question="¿Puedo pagar al reservar?" />
                    <Faq answer="Incluye tomas con dron cuando la locación, permisos, clima y seguridad de vuelo lo permiten." question="¿El dron siempre vuela?" />
                    <Faq answer="Sí. Los slots son los mismos del equipo de producción, así no se vende una fecha ya tomada por otra sesión." question="¿La agenda bloquea otras sesiones?" />
                </div>
            </GlassSection>

            <FunnelStickyBar price={price} label="DJ set 3 cámaras fijas + dron" />
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

function PortfolioPreview({ images }: { images: PortfolioItemData[] }) {
    const displayImages = images.slice(0, 3);

    if (displayImages.length === 0) {
        return (
            <div className="grid min-h-[360px] place-items-center rounded-xl border border-border/70 bg-secondary p-8 text-center text-sm text-muted-foreground">
                La referencia visual del set vive en los videos de Psique Originals.
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
    return (
        <div className="grid auto-rows-[160px] grid-cols-2 gap-3 md:auto-rows-[220px] md:grid-cols-4">
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

function PortfolioFrame({
    image,
    className = '',
    priority = false,
}: {
    image: PortfolioItemData;
    className?: string;
    priority?: boolean;
}) {
    return (
        <figure className={`group relative overflow-hidden rounded-xl border border-border/70 bg-secondary ${className}`}>
            {image.asset_url && (
                <img
                    src={image.asset_url}
                    alt={image.title ?? 'Portafolio nightlife de Lapsique'}
                    className="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                    loading={priority ? 'eager' : 'lazy'}
                />
            )}
            <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/15 to-transparent" />
            <figcaption className="absolute inset-x-0 bottom-0 p-3 text-xs font-semibold text-white">
                {image.title ?? 'Nightlife por Lapsique'}
            </figcaption>
        </figure>
    );
}

function OriginalsShowcase({ videos }: { videos: VideoItem[] }) {
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
                <div className="flex flex-wrap items-end justify-between gap-3 border-t border-white/10 bg-black px-4 py-4 text-white md:px-5">
                    <div>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            Reproduciendo referencia
                        </p>
                        <h3 className="mt-2 font-display text-xl font-bold">{activeVideo.title}</h3>
                        {activeVideo.djs && activeVideo.djs.length > 0 && (
                            <p className="mt-1 text-sm text-white/60">
                                {activeVideo.djs.map((dj) => dj.name).join(' · ')}
                            </p>
                        )}
                    </div>
                    <Button type="button" variant="cinematic" onClick={() => window.dispatchEvent(new Event('booking:open'))}>
                        Reservar video
                    </Button>
                </div>
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
                <span className="absolute bottom-3 left-3 flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/15 text-white backdrop-blur">
                    <Play className="ml-0.5 h-4 w-4 fill-current" />
                </span>
            </span>
            <span className="block min-h-20 p-3">
                <span className="block text-[10px] font-semibold uppercase tracking-[0.2em] text-primary">
                    Psique Original
                </span>
                <span className="mt-1 line-clamp-2 block text-sm font-semibold leading-snug text-foreground">
                    {video.title}
                </span>
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
