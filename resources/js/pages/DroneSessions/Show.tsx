import { useEffect, useMemo, useState, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Camera,
    Clock3,
    CreditCard,
    Drone,
    Film,
    MapPin,
    MessageCircle,
    Palette,
    Ruler,
    ShieldCheck,
    Video,
} from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { ServiceLandingSections } from '@/components/lapsique/ServiceLandingSections';
import { Button } from '@/components/ui/button';
import {
    DRONE_SESSION_BOOKING_CLIP,
    DRONE_SESSION_CLIPS,
    DRONE_SESSION_CONSTRUCTION_CLIPS,
    DRONE_SESSION_HERO_CLIP,
    type DroneSessionClip,
} from '@/data/droneSessions';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { getDroneSessionProduct } from '@/lib/bookingProducts';
import { openBookingModal } from '@/lib/openBookingModal';
import { formatMxn } from '@/lib/utils';
import type { BookingSlot, PageProps } from '@/types';

interface DroneSessionsShowProps {
    price: number;
    slots: BookingSlot[];
    errors?: Record<string, string>;
}

const DRONE_PAGE_COPY = {
    es: {
        locations: ['Cancún', 'Playa del Carmen', 'Tulum'],
        analyticsName: 'Sesión de vuelo con dron DJI Air 3',
        heroTitle: 'Sesiones de dron en Riviera Maya para hoteles, propiedades y negocios',
        heroTagline: 'Muestra ubicación, arquitectura, escala y experiencia.',
        heroDescription: 'Tomas aéreas profesionales para mostrar ubicación, arquitectura, escala, ambiente y experiencia con una imagen más premium para redes, web, anuncios y presentaciones comerciales.',
        priceLabel: 'Sesión completa',
        priceNote: '1 hora de vuelo · agenda online · pago seguro con tarjeta.',
        whatsappCta: 'Cotizar por WhatsApp',
        bookingCta: 'Cotizar sesión de dron',
        metrics: [
            { value: '15', label: 'tomas' },
            { value: '30 s', label: 'máx.' },
            { value: '10', label: 'fotos max.' },
        ],
        rangeNote: '*Altura y distancia máximas de referencia. El vuelo real se ajusta a normativa, permisos, clima, señal, batería y seguridad.',
        materialEyebrow: 'Material real',
        materialTitle: 'Clips seleccionados desde vuelos de dron',
        materialDescription: 'La landing usa tomas reales del archivo de dron: yates, obra, terrenos, eventos, condominios, hotelería, Airbnb y DJ sets.',
        packageEyebrow: 'Paquete',
        packageTitle: 'Qué incluye la sesión de $3,000 MXN',
        packageDescription: 'Un paquete directo para vender propiedades, experiencias y avances con material aéreo limpio.',
        packageCta: 'Agendar',
        packageColumns: ['Incluye', 'Detalle'],
        packageRows: [
            {
                label: 'Vuelo dirigido',
                detail: '1 hora con DJI Air 3 para capturar ubicación, escala, accesos y tomas prioritarias.',
            },
            {
                label: 'Video',
                detail: '15 tomas aéreas de hasta 30 seg seleccionadas para reels, landings, anuncios, presentaciones y recap comercial.',
            },
            {
                label: 'Fotografía',
                detail: 'Hasta 10 fotos aéreas para listings, brochure, redes, preventa y reportes ejecutivos.',
            },
            {
                label: 'Color',
                detail: 'Captura en Rec.709 y D-Log para entrega rápida o colorización controlada en DaVinci Resolve.',
            },
            {
                label: 'Cobertura',
                detail: 'Cancún, Playa del Carmen y Tulum con agenda online y vuelo ajustado a permisos, clima y seguridad.',
            },
        ],
        constructionEyebrow: 'Avances de obra',
        constructionTitle: 'Avances de obra con dron para vender progreso real.',
        constructionDescription: 'Goba editado queda como muestra principal: se ve avance, contexto de zona y escala del desarrollo sin depender de renders. También sumamos material reciente de OKOM normalizado desde D-Log para mostrar cómo se puede documentar obra por etapas.',
        constructionPills: ['Reportes para inversionistas', 'Preventas y avances', 'Comparables por etapa'],
        technicalEyebrow: 'Técnico',
        technicalTitle: 'Captura pensada para edición y color',
        technicalDescription: 'Se entrega material con suficiente margen para usarlo en reels, videos comerciales, presentaciones o edición posterior.',
        specs: ['DJI Air 3', '1 hora de vuelo', '15 tomas de hasta 30 seg', 'Hasta 10 fotos', 'Rec.709 + D-Log', '500 m / 2 km*'],
        finalEyebrow: 'Listo para despegar',
        finalTitle: 'Aparta la fecha y definimos el plan de vuelo.',
        finalDescription: 'Al reservar revisamos ubicación, objetivo visual, permisos, clima y restricciones de vuelo para capturar el material más útil para tu proyecto.',
        finalPayCta: 'Pagar y agendar',
        finalWhatsappCta: 'Preguntar por WhatsApp',
        clips: {
            hero: {
                title: 'Goba editado desde dron',
                caption: 'Obra, vegetación y costa en una toma limpia para vender contexto.',
                useCase: 'Avances de obra',
            },
            yacht: {
                title: 'Yates y lifestyle',
                caption: 'Planos amplios para presentar navegación, club, marina o servicio premium.',
                useCase: 'Yates',
            },
            construction: {
                title: 'Avances de obra',
                caption: 'Goba editado para reportes, inversionistas y comunicación de progreso.',
                useCase: 'Obra',
            },
            lot: {
                title: 'Terrenos y lotes',
                caption: 'Lectura clara de entorno, accesos, colindancias y dimensión del predio.',
                useCase: 'Terrenos',
            },
            event: {
                title: 'Eventos sociales',
                caption: 'Tomas de ubicación y ambiente para aftermovie, invitación o recap comercial.',
                useCase: 'Eventos',
            },
            condo: {
                title: 'Condominios, Airbnb y hotelería',
                caption: 'Una sola toma para vender fachada, amenidades, playa cercana y estancia.',
                useCase: 'Condominios + Airbnb',
            },
            djset: {
                title: 'DJ sets y experiencias',
                caption: 'Toma aérea para ubicar venue, ambiente y tamaño real del evento.',
                useCase: 'DJ set',
            },
            'goba-construction': {
                title: 'Goba / The Reserve',
                caption: 'Video editado con color final para presentar avance, escala y contexto de zona.',
                useCase: 'Obra editada',
            },
            'okom-construction': {
                title: 'OKOM Living Tulum',
                caption: 'Avance reciente normalizado desde material D-Log para revisión de progreso.',
                useCase: 'Obra reciente',
            },
            'construction-goba-aerial': {
                title: 'Goba',
                caption: 'Plano superior con cuadrilla y estructura para mostrar progreso real, actividad y escala.',
                useCase: 'Avance aéreo',
                location: 'The Reserve · Riviera Maya',
            },
            'construction-goba-detail': {
                title: 'Goba',
                caption: 'Material editado de trabajo en sitio para reforzar que el reporte muestra avance tangible.',
                useCase: 'Detalle de obra',
                location: 'The Reserve · obra en sitio',
            },
            'construction-goba-wide': {
                title: 'Goba',
                caption: 'Vista amplia para explicar ubicación, accesos, entorno verde y dimensión del proyecto.',
                useCase: 'Contexto de zona',
                location: 'The Reserve · entorno',
            },
            'construction-okom-nov-aerial': {
                title: 'OKOM',
                caption: 'Vista superior de etapa inicial para documentar progreso y comparar visitas con precisión.',
                useCase: 'Avance temprano',
                location: 'Tulum · noviembre 2024',
            },
            'construction-okom-nov-site': {
                title: 'OKOM',
                caption: 'Recorrido de cuadrilla y estructura para dar contexto técnico al reporte de avance.',
                useCase: 'Obra en sitio',
                location: 'Tulum · noviembre 2024',
            },
            'construction-okom-jun-interior': {
                title: 'OKOM',
                caption: 'Material reciente para mostrar avances interiores, instalaciones y detalles terminados.',
                useCase: 'Interiores e instalaciones',
                location: 'Tulum · junio 2026',
            },
            'construction-okom-jun-context': {
                title: 'OKOM',
                caption: 'Plano de contexto para conectar avance, ubicación y dimensión actual del desarrollo.',
                useCase: 'Contexto reciente',
                location: 'Tulum · junio 2026',
            },
        },
    },
    en: {
        locations: ['Cancun', 'Playa del Carmen', 'Tulum'],
        analyticsName: 'DJI Air 3 drone flight session',
        heroTitle: 'Drone sessions in Riviera Maya for hotels, properties, and businesses',
        heroTagline: 'Show location, architecture, scale, and experience.',
        heroDescription: 'Professional aerial shots to show location, architecture, scale, atmosphere, and experience with a more premium image for social, web, ads, and commercial presentations.',
        priceLabel: 'Complete session',
        priceNote: '1-hour flight · online booking · secure card payment.',
        whatsappCta: 'Quote on WhatsApp',
        bookingCta: 'Quote drone session',
        metrics: [
            { value: '15', label: 'shots' },
            { value: '30 s', label: 'max.' },
            { value: '10', label: 'photos max.' },
        ],
        rangeNote: '*Reference maximum altitude and distance. Actual flight follows regulation, permits, weather, signal, battery, and safety.',
        materialEyebrow: 'Real material',
        materialTitle: 'Selected clips from drone flights',
        materialDescription: 'This landing uses real drone footage: yachts, construction, lots, events, condos, hospitality, Airbnb, and DJ sets.',
        packageEyebrow: 'Package',
        packageTitle: 'What the $3,000 MXN session includes',
        packageDescription: 'A direct package to sell properties, experiences, and progress with clean aerial material.',
        packageCta: 'Book',
        packageColumns: ['Included', 'Detail'],
        packageRows: [
            {
                label: 'Directed flight',
                detail: '1 hour with DJI Air 3 to capture location, scale, access, and priority shots.',
            },
            {
                label: 'Video',
                detail: '15 selected aerial shots up to 30 sec each for reels, landing pages, ads, presentations, and commercial recaps.',
            },
            {
                label: 'Photography',
                detail: 'Up to 10 aerial photos for listings, brochures, social, presales, and executive reports.',
            },
            {
                label: 'Color',
                detail: 'Rec.709 and D-Log capture for fast delivery or controlled color in DaVinci Resolve.',
            },
            {
                label: 'Coverage',
                detail: 'Cancun, Playa del Carmen, and Tulum with online booking and flights adjusted to permits, weather, and safety.',
            },
        ],
        constructionEyebrow: 'Construction progress',
        constructionTitle: 'Drone construction progress that sells real progress.',
        constructionDescription: 'The edited Goba clip is the main proof: it shows progress, area context, and development scale without relying on renders. We also include recent OKOM material normalized from D-Log to show how work can be documented by stage.',
        constructionPills: ['Investor reports', 'Presales and updates', 'Stage comparisons'],
        technicalEyebrow: 'Technical',
        technicalTitle: 'Captured for editing and color',
        technicalDescription: 'Material is captured with enough latitude to use in reels, commercial videos, presentations, or later editing.',
        specs: ['DJI Air 3', '1-hour flight', '15 shots up to 30 sec', 'Up to 10 photos', 'Rec.709 + D-Log', '500 m / 2 km*'],
        finalEyebrow: 'Ready to fly',
        finalTitle: 'Reserve the date and we define the flight plan.',
        finalDescription: 'After booking, we review location, visual objective, permits, weather, and flight restrictions to capture the most useful material for your project.',
        finalPayCta: 'Pay and book',
        finalWhatsappCta: 'Ask on WhatsApp',
        clips: {
            hero: {
                title: 'Edited Goba drone clip',
                caption: 'Construction, green surroundings, and coastline in one clean context shot.',
                useCase: 'Construction progress',
            },
            yacht: {
                title: 'Yachts and lifestyle',
                caption: 'Wide shots to present navigation, club, marina, or premium service.',
                useCase: 'Yachts',
            },
            construction: {
                title: 'Construction progress',
                caption: 'Edited Goba footage for reports, investors, and progress communication.',
                useCase: 'Construction',
            },
            lot: {
                title: 'Land and lots',
                caption: 'Clear reading of surroundings, access, boundaries, and property dimension.',
                useCase: 'Lots',
            },
            event: {
                title: 'Social events',
                caption: 'Location and atmosphere shots for aftermovies, invitations, or commercial recaps.',
                useCase: 'Events',
            },
            condo: {
                title: 'Condos, Airbnb, and hospitality',
                caption: 'One shot to sell facade, amenities, nearby beach, and guest value.',
                useCase: 'Condos + Airbnb',
            },
            djset: {
                title: 'DJ sets and experiences',
                caption: 'Aerial context to show venue, atmosphere, and true event scale.',
                useCase: 'DJ set',
            },
            'goba-construction': {
                title: 'Goba / The Reserve',
                caption: 'Edited color footage to present progress, scale, and area context.',
                useCase: 'Edited construction',
            },
            'okom-construction': {
                title: 'OKOM Living Tulum',
                caption: 'Recent progress material normalized from D-Log for stage review.',
                useCase: 'Recent progress',
            },
            'construction-goba-aerial': {
                title: 'Goba',
                caption: 'Top-down view with crew and structure to show real progress, activity, and scale.',
                useCase: 'Aerial progress',
                location: 'The Reserve · Riviera Maya',
            },
            'construction-goba-detail': {
                title: 'Goba',
                caption: 'Edited on-site work material to reinforce that the report shows tangible progress.',
                useCase: 'Site detail',
                location: 'The Reserve · active site',
            },
            'construction-goba-wide': {
                title: 'Goba',
                caption: 'Wide view to explain location, access, green surroundings, and project dimension.',
                useCase: 'Area context',
                location: 'The Reserve · surroundings',
            },
            'construction-okom-nov-aerial': {
                title: 'OKOM',
                caption: 'Top view of the initial stage to document progress and compare visits with precision.',
                useCase: 'Early progress',
                location: 'Tulum · November 2024',
            },
            'construction-okom-nov-site': {
                title: 'OKOM',
                caption: 'Crew and structure pass to give technical context to the progress report.',
                useCase: 'Active site',
                location: 'Tulum · November 2024',
            },
            'construction-okom-jun-interior': {
                title: 'OKOM',
                caption: 'Recent material to show interior progress, installations, and finished details.',
                useCase: 'Interiors and installs',
                location: 'Tulum · June 2026',
            },
            'construction-okom-jun-context': {
                title: 'OKOM',
                caption: 'Context shot to connect progress, location, and the current development scale.',
                useCase: 'Recent context',
                location: 'Tulum · June 2026',
            },
        },
    },
} as const;

const SPEC_ICONS = [Drone, Clock3, Video, Camera, Palette, Ruler] as const;

type ClipCopy = {
    title: string;
    caption: string;
    useCase: string;
    location?: string;
};

function resolveClipCopy(
    copy: typeof DRONE_PAGE_COPY.es.clips | typeof DRONE_PAGE_COPY.en.clips,
    clip: DroneSessionClip,
): ClipCopy {
    return (copy as Partial<Record<string, ClipCopy>>)[clip.id] ?? {
        title: clip.title,
        caption: clip.caption,
        useCase: clip.useCase,
    };
}

export default function DroneSessionsShow({ price, slots, errors }: DroneSessionsShowProps) {
    const { site } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const copy = DRONE_PAGE_COPY[locale === 'en' ? 'en' : 'es'];
    const product = useMemo(() => getDroneSessionProduct(t), [t]);
    const specs = useMemo(
        () => copy.specs.map((label, index) => ({ label, icon: SPEC_ICONS[index] ?? Drone })),
        [copy],
    );
    const materialClips = useMemo(
        () => DRONE_SESSION_CLIPS.filter((clip) => clip.id !== 'hero'),
        [],
    );
    const heroPreviewClips = useMemo(
        () => DRONE_SESSION_CLIPS.filter((clip) => ['yacht', 'construction', 'condo', 'djset'].includes(clip.id)),
        [],
    );
    const [heroPreviewIndex, setHeroPreviewIndex] = useState(0);
    const [heroMediaMounted, setHeroMediaMounted] = useState(false);
    const heroPreviewClip = heroPreviewClips[heroPreviewIndex] ?? heroPreviewClips[0] ?? DRONE_SESSION_HERO_CLIP;
    const analyticsPayload = useMemo(
        () => ({
            content_name: copy.analyticsName,
            content_category: 'drone_session_booking',
            service_type: 'drone_session',
            currency: 'MXN',
            value: price,
        }),
        [copy.analyticsName, price],
    );
    const whatsappHref = useMemo(
        () => buildWhatsAppHref(site.whatsapp, t('funnel.whatsapp.prefill_drone')),
        [site.whatsapp, t],
    );

    useEffect(() => {
        trackBookingEvent('drone_session_page_viewed', {
            ...analyticsPayload,
            section: 'drone_sessions',
            locations: copy.locations,
        });
    }, [analyticsPayload, copy.locations]);

    useEffect(() => {
        const frameId = window.requestAnimationFrame(() => setHeroMediaMounted(true));

        return () => window.cancelAnimationFrame(frameId);
    }, []);

    useEffect(() => {
        if (heroPreviewClips.length < 2) {
            return;
        }

        const timer = window.setInterval(() => {
            setHeroPreviewIndex((current) => (current + 1) % heroPreviewClips.length);
        }, 5200);

        return () => window.clearInterval(timer);
    }, [heroPreviewClips.length]);

    const openBooking = (source: string) => {
        openBookingModal({
            source,
            analyticsEvent: 'drone_session_booking_cta_clicked',
            analyticsPayload: {
                ...analyticsPayload,
                source,
            },
        });
    };

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('drone_session_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden">
                <div className="absolute inset-0 bg-black">
                    <img
                        src={DRONE_SESSION_HERO_CLIP.poster}
                        alt=""
                        aria-hidden
                        className="absolute inset-0 h-full w-full object-cover object-center"
                    />
                    {heroMediaMounted ? (
                        <AutoplayVideo
                            src={DRONE_SESSION_HERO_CLIP.src}
                            poster={DRONE_SESSION_HERO_CLIP.poster}
                            title={copy.clips.hero.title}
                            eager
                            pauseWhenOffscreen={false}
                            className="absolute inset-0 h-full w-full"
                            videoClassName="h-full w-full object-cover object-center"
                        />
                    ) : null}
                    <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.09_0.02_260/0.94)_0%,oklch(0.10_0.02_250/0.78)_45%,oklch(0.10_0.02_250/0.34)_100%)]" />
                    <div className="absolute inset-0 bg-[linear-gradient(180deg,transparent_0%,oklch(0.08_0.01_250/0.25)_52%,var(--background)_100%)]" />
                </div>

                <div className="relative mx-auto grid min-h-[min(760px,82svh)] max-w-6xl content-end gap-6 px-4 pb-4 pt-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-end lg:gap-8 lg:pb-14 lg:pt-20">
                    <div className="max-w-4xl">
                        <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                            {copy.locations.join(' · ')}
                        </p>
                        <h1 className="mt-3 max-w-4xl font-display text-[2.05rem] font-bold leading-[0.98] tracking-tight text-white drop-shadow-[0_3px_28px_rgb(0_0_0/0.55)] sm:text-5xl md:text-5xl lg:mt-4">
                            {copy.heroTitle}
                        </h1>
                        <p className="mt-3 text-base font-semibold text-primary md:text-xl">
                            {copy.heroTagline}
                        </p>
                        <p className="mt-4 max-w-2xl text-sm leading-relaxed text-white/80 md:text-xl lg:mt-5">
                            {copy.heroDescription}
                        </p>

                        <div className="mt-5 grid gap-3 sm:grid-cols-[minmax(0,0.9fr)_minmax(260px,0.72fr)] sm:items-end lg:mt-8 lg:gap-4">
                            <div className="rounded-xl border border-primary/35 bg-black/70 px-5 py-4 shadow-[0_16px_48px_rgb(0_0_0/0.45)] backdrop-blur-md">
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                                    {copy.priceLabel}
                                </p>
                                <p className="mt-2 font-mono-tabular text-4xl font-bold text-primary md:text-5xl">
                                    {formatMxn(price)}
                                </p>
                                <p className="mt-2 text-sm text-white/72">
                                    {copy.priceNote}
                                </p>
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
                                    className="h-auto min-h-14 w-full gap-2 rounded-xl border border-[#25D366]/70 bg-[#25D366] px-5 text-center text-sm font-bold leading-tight text-white shadow-[0_16px_42px_oklch(0.66_0.18_145/0.34)] transition hover:-translate-y-0.5 hover:bg-[#1EBE5D] hover:text-white hover:shadow-[0_18px_52px_oklch(0.66_0.18_145/0.46)] focus-visible:ring-[#25D366]/45 motion-safe:animate-[drone-whatsapp-cta_3.2s_ease-in-out_infinite] sm:text-base"
                                    asChild
                                >
                                    <a
                                        href={whatsappHref}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        onClick={() => trackWhatsApp('hero')}
                                    >
                                        <MessageCircle className="size-5" />
                                        {copy.whatsappCta}
                                    </a>
                                </Button>
                                <BookingCtaButton
                                    type="button"
                                    variant="glass"
                                    className="w-full transition hover:-translate-y-0.5 motion-safe:animate-[drone-booking-cta_3s_ease-in-out_infinite]"
                                    onClick={() => openBooking('hero_agenda')}
                                >
                                    <CalendarDays className="h-5 w-5" />
                                    {copy.bookingCta}
                                    <ArrowRight className="h-4 w-4" />
                                </BookingCtaButton>
                            </div>
                        </div>
                    </div>

                    <div className="hidden gap-3 lg:grid">
                        <div className="rounded-xl border border-white/15 bg-black/55 p-3 text-white shadow-2xl backdrop-blur-md">
                            <AutoplayVideo
                                key={heroPreviewClip.id}
                                src={heroPreviewClip.src}
                                poster={heroPreviewClip.poster}
                                title={resolveClipCopy(copy.clips, heroPreviewClip).title}
                                eager
                                className="aspect-video rounded-lg"
                            />
                            <div className="grid grid-cols-3 gap-2 pt-3 text-center">
                                {copy.metrics.map((metric) => (
                                    <MiniMetric key={metric.label} value={metric.value} label={metric.label} />
                                ))}
                            </div>
                            <div className="mt-3 flex items-center justify-center gap-1.5">
                                {heroPreviewClips.map((clip, index) => (
                                    <span
                                        key={clip.id}
                                        className={index === heroPreviewIndex ? 'h-1.5 w-5 rounded-full bg-primary' : 'h-1.5 w-1.5 rounded-full bg-white/35'}
                                        aria-hidden
                                    />
                                ))}
                            </div>
                        </div>
                        <p className="hidden rounded-xl border border-white/15 bg-black/46 px-4 py-3 text-xs leading-relaxed text-white/72 backdrop-blur-md 2xl:block">
                            {copy.rangeNote}
                        </p>
                    </div>
                </div>
            </section>

            <ServiceLandingSections
                serviceKey="sesiones_de_dron"
                onBook={openBooking}
                className="pt-8"
            />

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                className="mt-8 lg:mt-10"
                checkoutRoute="drone-sessions.checkout"
                paymentProvider="stripe"
                product={product}
                popupVariant="drone"
                popupHeroProofVideo={{
                    title: copy.clips['goba-construction'].title,
                    media_type: 'video',
                    embed_url: null,
                    playback_url: DRONE_SESSION_BOOKING_CLIP.src,
                    poster_url: DRONE_SESSION_BOOKING_CLIP.poster,
                }}
                highlight
                analyticsPayload={analyticsPayload}
            />

            <GlassSection
                title={copy.materialTitle}
                description={copy.materialDescription}
                surface="solid"
                className="mt-8 lg:mt-10"
            >
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {materialClips.map((clip) => (
                        <ClipCard
                            key={clip.id}
                            clip={clip}
                            copy={resolveClipCopy(copy.clips, clip)}
                        />
                    ))}
                </div>
            </GlassSection>

            <GlassSection
                eyebrow={copy.packageEyebrow}
                title={copy.packageTitle}
                description={copy.packageDescription}
                className="mt-8 lg:mt-10"
                action={
                    <BookingCtaButton
                        type="button"
                        variant="secondary"
                        className="motion-safe:animate-[drone-booking-cta_3s_ease-in-out_infinite]"
                        onClick={() => openBooking('package')}
                    >
                        {copy.packageCta}
                    </BookingCtaButton>
                }
            >
                <div className="overflow-hidden rounded-xl border border-border/75 bg-background/80 shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[560px] border-collapse text-left text-sm">
                            <thead>
                                <tr className="border-b border-border/75 bg-muted/60">
                                    {copy.packageColumns.map((column) => (
                                        <th key={column} className="px-4 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                                            {column}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {copy.packageRows.map((row) => (
                                    <tr key={row.label} className="border-b border-border/60 last:border-b-0">
                                        <td className="w-[28%] px-4 py-4 align-top font-semibold text-foreground">
                                            {row.label}
                                        </td>
                                        <td className="px-4 py-4 align-top leading-relaxed text-foreground">
                                            {row.detail}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </GlassSection>

            <section className="my-12 overflow-hidden rounded-xl border border-primary/25 bg-[linear-gradient(135deg,oklch(0.14_0.05_235),oklch(0.18_0.07_185))] text-white shadow-2xl shadow-black/25">
                <div className="grid gap-0 lg:grid-cols-[0.95fr_1.05fr]">
                    <div className="p-6 md:p-9">
                        <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                            {copy.constructionEyebrow}
                        </p>
                        <h2 className="mt-3 font-display text-3xl font-bold leading-tight md:text-4xl">
                            {copy.constructionTitle}
                        </h2>
                        <p className="mt-4 max-w-2xl text-sm leading-relaxed text-white/76 md:text-base">
                            {copy.constructionDescription}
                        </p>
                        <div className="mt-6 grid gap-3 sm:grid-cols-3">
                            <SpecPill icon={<Film className="h-4 w-4" />} label={copy.constructionPills[0]} />
                            <SpecPill icon={<ShieldCheck className="h-4 w-4" />} label={copy.constructionPills[1]} />
                            <SpecPill icon={<MapPin className="h-4 w-4" />} label={copy.constructionPills[2]} />
                        </div>
                    </div>
                    <AutoplayVideo
                        src={DRONE_SESSION_CONSTRUCTION_CLIPS[0].src}
                        poster={DRONE_SESSION_CONSTRUCTION_CLIPS[0].poster}
                        title={copy.clips['goba-construction'].title}
                        className="min-h-[320px] lg:h-full"
                    />
                </div>
                <div className="grid gap-4 border-t border-white/10 bg-black/12 p-4 md:grid-cols-2 md:p-5">
                    {DRONE_SESSION_CONSTRUCTION_CLIPS.map((clip) => {
                        const clipCopy = resolveClipCopy(copy.clips, clip);

                        return (
                            <article key={clip.id} className="overflow-hidden rounded-xl border border-white/15 bg-white/8 shadow-xl shadow-black/20">
                                <AutoplayVideo
                                    src={clip.src}
                                    poster={clip.poster}
                                    title={clipCopy.title}
                                    className="aspect-video"
                                />
                                <div className="p-4">
                                    <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">
                                        {clipCopy.useCase}
                                    </p>
                                    <h3 className="mt-2 text-lg font-semibold text-white">
                                        {clipCopy.title}
                                    </h3>
                                    {clipCopy.location ? (
                                        <p className="mt-2 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-white/64">
                                            <MapPin className="h-3.5 w-3.5 text-primary" />
                                            {clipCopy.location}
                                        </p>
                                    ) : null}
                                    <p className="mt-2 text-sm leading-relaxed text-white/72">
                                        {clipCopy.caption}
                                    </p>
                                </div>
                            </article>
                        );
                    })}
                </div>
            </section>

            <section className="my-12 overflow-hidden rounded-xl border border-white/10 bg-[linear-gradient(135deg,oklch(0.09_0.02_250),oklch(0.13_0.03_245))] p-6 text-white shadow-2xl shadow-black/20 md:p-8">
                <div className="mb-7 max-w-3xl">
                    <span className="inline-block rounded-full border border-primary/35 bg-primary/12 px-3 py-0.5 text-xs font-medium uppercase tracking-widest text-primary">
                        {copy.technicalEyebrow}
                    </span>
                    <h2 className="mt-3 font-display text-2xl font-bold tracking-tight text-white md:text-3xl">
                        {copy.technicalTitle}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-white/68 md:text-base">
                        {copy.technicalDescription}
                    </p>
                </div>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {specs.map((spec) => (
                        <div key={spec.label} className="rounded-xl border border-white/10 bg-white/[0.055] p-5 shadow-inner shadow-white/5">
                            <spec.icon className="h-6 w-6 text-primary" />
                            <p className="mt-3 font-semibold text-white">{spec.label}</p>
                        </div>
                    ))}
                </div>
            </section>

            <section className="my-12 rounded-xl border border-border/80 bg-background/80 p-6 shadow-xl shadow-black/5 md:p-8">
                <div className="grid gap-6 md:grid-cols-[1fr_auto] md:items-center">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                            {copy.finalEyebrow}
                        </p>
                        <h2 className="mt-3 font-display text-2xl font-bold text-foreground md:text-3xl">
                            {copy.finalTitle}
                        </h2>
                        <p className="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">
                            {copy.finalDescription}
                        </p>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                        <BookingCtaButton type="button" onClick={() => openBooking('final_cta')}>
                            <CreditCard className="h-5 w-5" />
                            {copy.finalPayCta}
                        </BookingCtaButton>
                        <Button variant="outline" className="h-12 rounded-xl" asChild>
                            <a
                                href={whatsappHref}
                                target="_blank"
                                rel="noopener noreferrer"
                                onClick={() => trackWhatsApp('final_cta')}
                            >
                                <MessageCircle className="h-4 w-4" />
                                {copy.finalWhatsappCta}
                            </a>
                        </Button>
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}

function ClipCard({
    clip,
    copy,
}: {
    clip: DroneSessionClip;
    copy: ClipCopy;
}) {
    return (
        <article className="group overflow-hidden rounded-xl border border-border/75 bg-background/80 shadow-lg shadow-black/5">
            <AutoplayVideo
                src={clip.src}
                poster={clip.poster}
                title={copy.title}
                className="aspect-video"
            />
            <div className="p-4">
                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">
                    {copy.useCase}
                </p>
                <h3 className="mt-2 text-base font-semibold text-foreground">{copy.title}</h3>
                <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{copy.caption}</p>
            </div>
        </article>
    );
}

function MiniMetric({ value, label }: { value: string; label: string }) {
    return (
        <div className="rounded-lg border border-white/15 bg-white/10 px-2 py-2">
            <p className="font-mono-tabular text-lg font-bold text-primary">{value}</p>
            <p className="text-[10px] uppercase tracking-[0.14em] text-white/62">{label}</p>
        </div>
    );
}

function SpecPill({ icon, label }: { icon: ReactNode; label: string }) {
    return (
        <span className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-2 text-xs font-semibold text-white/80">
            {icon}
            {label}
        </span>
    );
}

function buildWhatsAppHref(number: string, message: string): string {
    if (!number) {
        return '#';
    }

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
