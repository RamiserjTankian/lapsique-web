import { useEffect, useMemo, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Building2,
    CalendarDays,
    Camera,
    CheckCircle2,
    ClipboardList,
    Clock3,
    CreditCard,
    Drone,
    FileText,
    MapPin,
    MessageCircle,
    Palette,
    Repeat2,
    Route,
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
import { Button } from '@/components/ui/button';
import {
    DRONE_SESSION_BOOKING_CLIP,
    DRONE_SESSION_CONSTRUCTION_CLIPS,
    type DroneSessionClip,
} from '@/data/droneSessions';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { getConstructionProgressProduct } from '@/lib/bookingProducts';
import { openBookingModal } from '@/lib/openBookingModal';
import { formatMxn } from '@/lib/utils';
import type { BookingSlot, PageProps } from '@/types';

interface ConstructionProgressShowProps {
    price: number;
    slots: BookingSlot[];
    errors?: Record<string, string>;
}

const CONSTRUCTION_COPY = {
    es: {
        analyticsName: 'Avance de obra con dron DJI Air 3',
        heroTitle: 'Avances de obra con dron para constructoras y desarrollos',
        heroDescription: 'Documentamos progreso, accesos, escala y contexto real para reportes, inversionistas, preventa y toma de decisiones.',
        priceLabel: 'Sesión de avance',
        priceNote: '1 hora de vuelo · agenda online · pago seguro con tarjeta.',
        whatsappCta: 'Cotizar por WhatsApp',
        bookingCta: 'Agendar avance',
        locations: ['Cancún', 'Playa del Carmen', 'Tulum'],
        heroSpecs: ['DJI Air 3', 'Edición profesional', 'Entrega para reportes', 'Pago seguro'],
        agendaLead: 'Capturamos hasta 10 tomas aéreas y hasta 10 fotos para dejar evidencia clara de la etapa actual.',
        proofTitle: 'Evidencia que impulsa decisiones',
        proofDescription: 'Imágenes editadas para mostrar progreso real, contexto y escala sin depender de renders.',
        tableTitle: 'Qué incluye tu sesión de avance',
        tableDescription: 'Un paquete puntual para documentar una etapa de obra con material útil para reportes y presentaciones.',
        tableCta: 'Agendar',
        tableColumns: ['Incluye', 'Detalle'],
        tableRows: [
            ['1 sesión de vuelo', '1 hora de vuelo con DJI Air 3 y plan de vuelo adaptado al objetivo de la obra.'],
            ['Clips de avance editados', 'Selección de hasta 10 tomas aéreas editadas, colorizadas y listas para uso comercial.'],
            ['Fotografías aéreas', 'Hasta 10 fotografías aéreas de alta resolución para reportes y presentaciones.'],
            ['Tomas de contexto y ruta', 'Cobertura de accesos, alrededores y contexto del proyecto para ubicación y escala.'],
            ['Entrega para reportes', 'Material listo para informes, comités, avances mensuales y comunicación con inversionistas.'],
            ['Equipo y formatos', 'DJI Air 3, captura en Rec.709 y D-Log para máxima flexibilidad en postproducción.'],
        ],
        benefitsTitle: 'Beneficios para constructoras y desarrollos',
        benefits: [
            ['Ángulos repetibles', 'Misma ruta de vuelo para comparar progreso con precisión.'],
            ['Seguimiento mensual', 'Opción de documentar etapas recurrentes por WhatsApp.'],
            ['Reportes para inversionistas', 'Evidencia visual clara que genera confianza y acelera decisiones.'],
            ['Material para preventa', 'Contenido profesional para comercializar y comunicar etapas del desarrollo.'],
            ['Acceso y seguridad', 'Planeación de vuelo segura sin interferir con la operación de obra.'],
        ],
        finalTitle: 'Documenta. Compara. Decide con confianza.',
        finalDescription: 'Agenda tu sesión de avance o cotiza por WhatsApp. Estamos listos para volar en Cancún, Playa del Carmen y Tulum.',
        finalPayCta: 'Agendar avance',
        finalWhatsappCta: 'Cotizar por WhatsApp',
        clips: {
            'goba-construction': {
                title: 'Avance de obra · Color editado profesional',
                caption: 'Goba editado para reportes, inversionistas y comunicación. Color normalizado en DaVinci Resolve.',
                useCase: 'Goba / The Reserve',
            },
            'okom-construction': {
                title: 'Revisión de estado y comparativos',
                caption: 'OKOM reciente normalizado desde D-Log para comparar etapas y documentar el estado real de la obra.',
                useCase: 'OKOM Living Tulum',
            },
        },
    },
    en: {
        analyticsName: 'DJI Air 3 construction progress flight',
        heroTitle: 'Drone construction progress for builders and developments',
        heroDescription: 'We document progress, access, scale, and real context for reports, investors, presales, and decision-making.',
        priceLabel: 'Progress session',
        priceNote: '1-hour flight · online booking · secure card payment.',
        whatsappCta: 'Quote on WhatsApp',
        bookingCta: 'Book progress',
        locations: ['Cancun', 'Playa del Carmen', 'Tulum'],
        heroSpecs: ['DJI Air 3', 'Professional edit', 'Report-ready delivery', 'Secure payment'],
        agendaLead: 'We capture up to 10 aerial shots and up to 10 photos to leave a clear record of the current stage.',
        proofTitle: 'Evidence that drives decisions',
        proofDescription: 'Edited visuals to show real progress, context, and scale without relying on renders.',
        tableTitle: 'What your progress session includes',
        tableDescription: 'A focused package to document one construction stage with material useful for reports and presentations.',
        tableCta: 'Book',
        tableColumns: ['Included', 'Detail'],
        tableRows: [
            ['1 flight session', '1 hour of DJI Air 3 flight with a flight plan adapted to the project objective.'],
            ['Edited progress clips', 'Selection of up to 10 aerial shots edited, colorized, and ready for commercial use.'],
            ['Aerial photos', 'Up to 10 high-resolution aerial photos for reports and presentations.'],
            ['Context and route shots', 'Coverage of access, surroundings, and project context for location and scale.'],
            ['Report delivery', 'Material ready for reports, committees, monthly updates, and investor communication.'],
            ['Equipment and formats', 'DJI Air 3, Rec.709 and D-Log capture for maximum postproduction flexibility.'],
        ],
        benefitsTitle: 'Benefits for builders and developments',
        benefits: [
            ['Repeatable angles', 'Same flight route to compare progress with precision.'],
            ['Monthly tracking', 'Option to document recurring stages through WhatsApp.'],
            ['Investor reports', 'Clear visual evidence that builds trust and speeds up decisions.'],
            ['Presales material', 'Professional content to commercialize and communicate development stages.'],
            ['Access and safety', 'Safe flight planning without disrupting construction operations.'],
        ],
        finalTitle: 'Document. Compare. Decide with confidence.',
        finalDescription: 'Book your progress session or quote on WhatsApp. Ready to fly in Cancun, Playa del Carmen, and Tulum.',
        finalPayCta: 'Book progress',
        finalWhatsappCta: 'Quote on WhatsApp',
        clips: {
            'goba-construction': {
                title: 'Construction progress · Professional color edit',
                caption: 'Edited Goba material for reports, investors, and communication. Color normalized in DaVinci Resolve.',
                useCase: 'Goba / The Reserve',
            },
            'okom-construction': {
                title: 'Status review and comparisons',
                caption: 'Recent OKOM footage normalized from D-Log to compare stages and document the real project state.',
                useCase: 'OKOM Living Tulum',
            },
        },
    },
} as const;

const BENEFIT_ICONS = [Drone, CalendarDays, FileText, Building2, ShieldCheck] as const;
const PACKAGE_ICONS = [Drone, Video, Camera, Route, ClipboardList, Palette] as const;

export default function ConstructionProgressShow({ price, slots, errors }: ConstructionProgressShowProps) {
    const { site } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const copy = CONSTRUCTION_COPY[locale === 'en' ? 'en' : 'es'];
    const product = useMemo(() => getConstructionProgressProduct(t), [t]);
    const analyticsPayload = useMemo(
        () => ({
            content_name: copy.analyticsName,
            content_category: 'construction_progress_booking',
            service_type: 'construction_progress',
            currency: 'MXN',
            value: price,
        }),
        [copy.analyticsName, price],
    );
    const whatsappHref = useMemo(
        () => buildWhatsAppHref(site.whatsapp, t('funnel.whatsapp.prefill_construction')),
        [site.whatsapp, t],
    );

    useEffect(() => {
        trackBookingEvent('construction_progress_page_viewed', {
            ...analyticsPayload,
            section: 'construction_progress',
            locations: copy.locations,
        });
    }, [analyticsPayload, copy.locations]);

    const openBooking = (source: string) => {
        openBookingModal({
            source,
            analyticsEvent: 'construction_progress_booking_cta_clicked',
            analyticsPayload: {
                ...analyticsPayload,
                source,
            },
        });
    };

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('construction_progress_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };

    const primaryClip = DRONE_SESSION_CONSTRUCTION_CLIPS[0];
    const secondaryClip = DRONE_SESSION_CONSTRUCTION_CLIPS[1];

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-black text-white">
                <div className="absolute inset-0">
                    <AutoplayVideo
                        src={primaryClip.src}
                        poster={primaryClip.poster}
                        title={copy.clips['goba-construction'].title}
                        eager
                        pauseWhenOffscreen={false}
                        className="h-full w-full"
                        videoClassName="h-full w-full object-cover object-center"
                    />
                    <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.08_0.02_250/0.96)_0%,oklch(0.09_0.02_245/0.76)_40%,oklch(0.10_0.02_240/0.28)_78%,oklch(0.08_0.02_250/0.10)_100%)]" />
                    <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.04_0.01_250/0.18)_0%,transparent_42%,oklch(0.06_0.02_250/0.92)_100%)]" />
                </div>

                <div className="relative mx-auto grid min-h-[min(760px,78svh)] max-w-6xl content-end px-4 pb-10 pt-16 sm:px-6 lg:grid-cols-[minmax(0,0.78fr)_minmax(320px,0.36fr)] lg:items-end lg:gap-10 lg:pb-14 lg:pt-24">
                    <div className="max-w-3xl">
                        <h1 className="font-display text-[2.45rem] font-bold leading-[0.98] text-white drop-shadow-[0_4px_34px_rgb(0_0_0/0.55)] sm:text-5xl lg:text-6xl xl:text-7xl">
                            {copy.heroTitle}
                        </h1>
                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-white/82 md:text-lg">
                            {copy.heroDescription}
                        </p>

                        <div className="mt-7 grid max-w-xl gap-4 xl:max-w-none xl:grid-cols-[minmax(260px,0.48fr)_minmax(280px,0.38fr)] xl:items-end">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.18em] text-primary">
                                    {copy.priceLabel}
                                </p>
                                <p className="mt-1 font-mono-tabular text-4xl font-bold text-primary md:text-5xl">
                                    {formatMxn(price)}
                                </p>
                                <p className="mt-1 text-sm text-white/72">
                                    {copy.priceNote}
                                </p>
                            </div>
                            <div className="space-y-3">
                                <BookingCtaButton
                                    type="button"
                                    className="w-full transition hover:-translate-y-0.5 motion-safe:animate-[drone-booking-cta_3s_ease-in-out_infinite]"
                                    onClick={() => openBooking('hero_agenda')}
                                >
                                    <CalendarDays className="h-5 w-5" />
                                    {copy.bookingCta}
                                    <ArrowRight className="h-4 w-4" />
                                </BookingCtaButton>
                                <Button
                                    variant="default"
                                    size="xl"
                                    className="h-auto min-h-14 w-full gap-2 rounded-xl border border-[#25D366]/65 bg-[#25D366] px-5 text-sm font-bold text-white shadow-[0_16px_42px_oklch(0.66_0.18_145/0.34)] transition hover:-translate-y-0.5 hover:bg-[#1EBE5D] hover:text-white motion-safe:animate-[drone-whatsapp-cta_3.2s_ease-in-out_infinite] sm:text-base"
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
                            </div>
                        </div>

                        <div className="mt-7 flex flex-wrap gap-x-4 gap-y-2 text-xs font-semibold text-white/72">
                            {copy.heroSpecs.map((spec) => (
                                <span key={spec} className="inline-flex items-center gap-2">
                                    <CheckCircle2 className="h-4 w-4 text-primary" />
                                    {spec}
                                </span>
                            ))}
                        </div>
                    </div>

                    <div className="hidden rounded-2xl border border-white/15 bg-black/46 p-3 shadow-2xl shadow-black/40 backdrop-blur-md lg:block">
                        <AutoplayVideo
                            src={secondaryClip.src}
                            poster={secondaryClip.poster}
                            title={copy.clips['okom-construction'].title}
                            eager
                            className="aspect-[4/3] overflow-hidden rounded-xl"
                        />
                        <div className="p-3">
                            <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">
                                {copy.locations.join(' · ')}
                            </p>
                            <p className="mt-2 text-sm leading-relaxed text-white/76">
                                {copy.agendaLead}
                            </p>
                            <PaymentTrustOrTestMode
                                variant="stripe"
                                layout="compact"
                                onDark
                                className="mt-3"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                className="relative z-10 -mt-4 lg:-mt-7"
                checkoutRoute="construction-progress.checkout"
                paymentProvider="stripe"
                product={product}
                popupVariant="construction"
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

            <section className="my-12">
                <div className="mb-7 text-center">
                    <h2 className="font-display text-3xl font-bold text-foreground md:text-4xl">
                        {copy.proofTitle}
                    </h2>
                    <p className="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground md:text-base">
                        {copy.proofDescription}
                    </p>
                </div>
                <div className="grid gap-5 lg:grid-cols-2">
                    {[primaryClip, secondaryClip].map((clip) => (
                        <ProofPanel
                            key={clip.id}
                            clip={clip}
                            copy={copy.clips[clip.id as keyof typeof copy.clips]}
                        />
                    ))}
                </div>
            </section>

            <GlassSection
                title={copy.tableTitle}
                description={copy.tableDescription}
                surface="solid"
                className="mt-8 lg:mt-10"
                action={
                    <BookingCtaButton
                        type="button"
                        variant="secondary"
                        className="motion-safe:animate-[drone-booking-cta_3s_ease-in-out_infinite]"
                        onClick={() => openBooking('package')}
                    >
                        {copy.tableCta}
                    </BookingCtaButton>
                }
            >
                <div className="overflow-hidden rounded-xl border border-border/75 bg-background/90 shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[620px] border-collapse text-left text-sm">
                            <thead>
                                <tr className="border-b border-border/75 bg-[oklch(0.12_0.03_250)] text-white">
                                    <th className="w-[30%] px-4 py-3 text-xs font-semibold uppercase tracking-[0.16em]">
                                        {copy.tableColumns[0]}
                                    </th>
                                    <th className="px-4 py-3 text-xs font-semibold uppercase tracking-[0.16em]">
                                        {copy.tableColumns[1]}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {copy.tableRows.map(([label, detail], index) => {
                                    const Icon = PACKAGE_ICONS[index] ?? Drone;

                                    return (
                                        <tr key={label} className="border-b border-border/60 last:border-b-0">
                                            <td className="px-4 py-4 align-top font-semibold text-foreground">
                                                <span className="inline-flex items-start gap-3">
                                                    <Icon className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                                                    {label}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4 align-top leading-relaxed text-muted-foreground">
                                                {detail}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </GlassSection>

            <section className="my-12">
                <h2 className="text-center font-display text-2xl font-bold text-foreground md:text-3xl">
                    {copy.benefitsTitle}
                </h2>
                <div className="mt-6 grid gap-0 overflow-hidden rounded-xl border border-border/70 bg-background/80 md:grid-cols-5">
                    {copy.benefits.map(([title, description], index) => {
                        const Icon = BENEFIT_ICONS[index] ?? CheckCircle2;

                        return (
                            <article key={title} className="border-b border-border/60 p-5 text-center last:border-b-0 md:border-b-0 md:border-r md:last:border-r-0">
                                <Icon className="mx-auto h-7 w-7 text-foreground" />
                                <h3 className="mt-4 text-sm font-bold text-foreground">
                                    {title}
                                </h3>
                                <p className="mt-2 text-xs leading-relaxed text-muted-foreground">
                                    {description}
                                </p>
                            </article>
                        );
                    })}
                </div>
            </section>

            <section className="relative left-1/2 my-12 w-screen -translate-x-1/2 overflow-hidden bg-black text-white">
                <AutoplayVideo
                    src={primaryClip.src}
                    poster={primaryClip.poster}
                    title={copy.clips['goba-construction'].title}
                    className="absolute inset-0 h-full w-full opacity-40"
                    videoClassName="h-full w-full object-cover object-center"
                />
                <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.07_0.02_250/0.94),oklch(0.07_0.02_250/0.62),oklch(0.07_0.02_250/0.88))]" />
                <div className="relative mx-auto grid max-w-6xl gap-6 px-4 py-10 sm:px-6 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                    <div>
                        <h2 className="font-display text-3xl font-bold leading-tight md:text-4xl">
                            {copy.finalTitle}
                        </h2>
                        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-white/76 md:text-base">
                            {copy.finalDescription}
                        </p>
                        <div className="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-xs text-white/70">
                            <span className="inline-flex items-center gap-2">
                                <MapPin className="h-4 w-4 text-primary" />
                                {copy.locations.join(', ')}
                            </span>
                            <span className="inline-flex items-center gap-2">
                                <Drone className="h-4 w-4 text-primary" />
                                DJI Air 3
                            </span>
                            <span className="inline-flex items-center gap-2">
                                <Clock3 className="h-4 w-4 text-primary" />
                                1 h
                            </span>
                        </div>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                        <BookingCtaButton type="button" onClick={() => openBooking('final_cta')}>
                            <CreditCard className="h-5 w-5" />
                            {copy.finalPayCta}
                        </BookingCtaButton>
                        <Button variant="outline" className="h-12 rounded-xl border-white/25 bg-white/10 text-white hover:bg-white/16 hover:text-white" asChild>
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

function ProofPanel({
    clip,
    copy,
}: {
    clip: DroneSessionClip;
    copy: { title: string; caption: string; useCase: string };
}) {
    return (
        <article className="overflow-hidden rounded-xl border border-border/75 bg-[oklch(0.10_0.03_250)] text-white shadow-xl shadow-black/18">
            <AutoplayVideo
                src={clip.src}
                poster={clip.poster}
                title={copy.title}
                className="aspect-video"
            />
            <div className="flex gap-4 p-5">
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-primary/30 bg-primary/10 text-primary">
                    <Repeat2 className="h-5 w-5" />
                </span>
                <div>
                    <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">
                        {copy.useCase}
                    </p>
                    <h3 className="mt-1 text-base font-semibold text-white">
                        {copy.title}
                    </h3>
                    <p className="mt-2 text-sm leading-relaxed text-white/72">
                        {copy.caption}
                    </p>
                </div>
            </div>
        </article>
    );
}

function buildWhatsAppHref(number: string, message: string): string {
    if (!number) {
        return '#';
    }

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
