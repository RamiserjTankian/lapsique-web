import { useEffect, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Camera,
    CheckCircle2,
    ClipboardList,
    Clock3,
    CreditCard,
    Drone,
    MapPin,
    MessageCircle,
    Palette,
    Route,
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
    DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS,
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
        heroTitle: 'Avances de obra con dron, foto y video en Riviera Maya',
        heroDescription: 'Documentamos el progreso de tu construcción con contenido visual profesional para inversionistas, clientes, reportes, redes y ventas.',
        priceLabel: 'Sesión de avance',
        priceNote: '1 hora de vuelo · agenda online · pago seguro con tarjeta.',
        whatsappCta: 'Cotizar por WhatsApp',
        bookingCta: 'Cotizar plan mensual de obra',
        locations: ['Cancún', 'Playa del Carmen', 'Tulum'],
        heroSpecs: ['DJI Air 3', 'Edición profesional', 'Entrega para reportes', 'Pago seguro'],
        agendaLead: 'Capturamos los avances destacados de tu obra basados en tu lineamiento para comunicar lo mejor para tus clientes.',
        proofTitle: 'Impulsa la seriedad de tu constructora o desarrolladora inmobiliaria',
        proofDescription: 'Convierte el avance real de obra en evidencia profesional para clientes, dirección, inversionistas y preventa.',
        cameraEyebrow: 'Cámara + obra',
        cameraTitle: 'Tomas de cámara profesional en sitio',
        cameraDescription: 'Además del dron, integramos planos a nivel de obra: cuadrilla, colados, bomba de concreto y detalles para que el reporte se sienta producido y confiable.',
        tableTitle: 'Qué incluye tu sesión de avance',
        tableDescription: 'Un paquete puntual para documentar una etapa de obra con material útil para reportes y presentaciones.',
        tableCta: 'Agendar',
        tableColumns: ['Incluye', 'Detalle'],
        tableRows: [
            ['1 sesión de vuelo', '1 hora de vuelo con DJI Air 3 y plan de vuelo adaptado al objetivo de la obra.'],
            ['Clips de avance editados', 'Selección de hasta 10 tomas editadas de dron y cámara en sitio, colorizadas y listas para uso comercial.'],
            ['Fotografías aéreas', 'Hasta 10 fotografías aéreas de alta resolución para reportes y presentaciones.'],
            ['Tomas de contexto y ruta', 'Cobertura de accesos, alrededores y contexto del proyecto para ubicación y escala.'],
            ['Entrega para reportes', 'Material listo para informes, comités, avances mensuales y comunicación con inversionistas.'],
            ['Equipo y formatos', 'DJI Air 3, captura en Rec.709 y D-Log para máxima flexibilidad en postproducción.'],
        ],
        progressClipsTitle: 'Highlights de avance para reportes y comunicación',
        progressClipsDescription: 'Selección de GOBA y OKOM para mostrar progreso, entorno, accesos, interiores y escala sin repetir la misma toma.',
        progressClips: [
            ['Reporte de avance', 'Planos claros para dirección, comités, clientes e inversionistas.'],
            ['Comparativo por etapa', 'Material útil para enseñar qué cambió entre visitas y meses de obra.'],
        ],
        finalTitle: 'Documenta. Compara. Decide con confianza.',
        finalDescription: 'Agenda tu sesión de avance o cotiza por WhatsApp. Estamos listos para volar en Cancún, Playa del Carmen y Tulum.',
        finalPayCta: 'Agendar avance',
        finalWhatsappCta: 'Cotizar por WhatsApp',
    },
    en: {
        analyticsName: 'DJI Air 3 construction progress flight',
        heroTitle: 'Construction progress with drone, photo, and video in Riviera Maya',
        heroDescription: 'We document construction progress with professional visual content for investors, clients, reports, social media, and sales.',
        priceLabel: 'Progress session',
        priceNote: '1-hour flight · online booking · secure card payment.',
        whatsappCta: 'Quote on WhatsApp',
        bookingCta: 'Quote monthly construction plan',
        locations: ['Cancun', 'Playa del Carmen', 'Tulum'],
        heroSpecs: ['DJI Air 3', 'Professional edit', 'Report-ready delivery', 'Secure payment'],
        agendaLead: 'We capture the strongest progress highlights from your site based on your direction, so your clients see the best of the project.',
        proofTitle: 'Make your builder or real estate developer look more serious',
        proofDescription: 'Turn real construction progress into professional evidence for clients, leadership, investors, and presales.',
        cameraEyebrow: 'Camera + site work',
        cameraTitle: 'Professional camera shots on site',
        cameraDescription: 'Beyond the drone, we add ground-level shots of crews, concrete pours, equipment, and construction details so the report feels produced and trustworthy.',
        tableTitle: 'What your progress session includes',
        tableDescription: 'A focused package to document one construction stage with material useful for reports and presentations.',
        tableCta: 'Book',
        tableColumns: ['Included', 'Detail'],
        tableRows: [
            ['1 flight session', '1 hour of DJI Air 3 flight with a flight plan adapted to the project objective.'],
            ['Edited progress clips', 'Selection of up to 10 drone and on-site camera shots, colorized and ready for commercial use.'],
            ['Aerial photos', 'Up to 10 high-resolution aerial photos for reports and presentations.'],
            ['Context and route shots', 'Coverage of access, surroundings, and project context for location and scale.'],
            ['Report delivery', 'Material ready for reports, committees, monthly updates, and investor communication.'],
            ['Equipment and formats', 'DJI Air 3, Rec.709 and D-Log capture for maximum postproduction flexibility.'],
        ],
        progressClipsTitle: 'Progress highlights for reports and communication',
        progressClipsDescription: 'GOBA and OKOM selections to show progress, surroundings, access, interiors, and scale without repeating the same shot.',
        progressClips: [
            ['Progress report', 'Clear shots for leadership, committees, clients, and investors.'],
            ['Stage comparison', 'Material that helps show what changed between site visits and project months.'],
        ],
        finalTitle: 'Document. Compare. Decide with confidence.',
        finalDescription: 'Book your progress session or quote on WhatsApp. Ready to fly in Cancun, Playa del Carmen, and Tulum.',
        finalPayCta: 'Book progress',
        finalWhatsappCta: 'Quote on WhatsApp',
    },
} as const;

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

    const heroClip = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.hero;
    const bookingPopupClip = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.bookingPopup;
    const sideClip = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.side;
    const proofClips = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.proof;
    const cameraClips = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.camera;
    const progressClips = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.progress;
    const finalClip = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.finalCta;
    const gobaHorizontalClip = DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.closing;

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-black text-white">
                <div className="absolute inset-0">
                    <AutoplayVideo
                        src={heroClip.src}
                        poster={heroClip.poster}
                        title={heroClip.title}
                        eager
                        pauseWhenOffscreen={false}
                        className="h-full w-full"
                        videoClassName="h-full w-full object-cover object-center"
                    />
                    <div className="absolute inset-0 bg-[linear-gradient(90deg,oklch(0.08_0.02_250/0.72)_0%,oklch(0.09_0.02_245/0.46)_44%,oklch(0.10_0.02_240/0.12)_80%,transparent_100%)]" />
                    <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.04_0.01_250/0.08)_0%,transparent_55%,oklch(0.06_0.02_250/0.55)_100%)]" />
                </div>

                <div className="relative mx-auto grid min-h-[min(760px,78svh)] max-w-6xl content-end px-4 pb-10 pt-16 sm:px-6 lg:grid-cols-[minmax(0,0.78fr)_minmax(320px,0.36fr)] lg:items-end lg:gap-10 lg:pb-14 lg:pt-24">
                    <div className="max-w-3xl">
                        <h1 className="font-display text-[2.45rem] font-bold leading-[0.98] text-white drop-shadow-[0_4px_34px_rgb(0_0_0/0.55)] sm:text-5xl lg:text-6xl xl:text-7xl">
                            {copy.heroTitle}
                        </h1>
                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-white/82 md:text-lg">
                            {copy.heroDescription}
                        </p>

                        <div className="mt-7 grid max-w-2xl gap-5 lg:grid-cols-[190px_minmax(340px,1fr)] lg:items-end">
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
                                    className="min-h-14 w-full rounded-none px-4 text-xs shadow-none sm:text-sm"
                                    onClick={() => openBooking('hero_agenda')}
                                >
                                    <CalendarDays className="h-5 w-5" />
                                    {copy.bookingCta}
                                    <ArrowRight className="h-4 w-4" />
                                </BookingCtaButton>
                                <Button
                                    variant="default"
                                    size="xl"
                                    className="h-auto min-h-14 w-full gap-2 rounded-none border border-primary bg-transparent px-5 text-sm font-bold text-white shadow-none hover:bg-primary hover:text-white sm:text-base"
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
                            src={sideClip.src}
                            poster={sideClip.poster}
                            title={sideClip.title}
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

            <ServiceLandingSections
                serviceKey="avances_de_obra"
                onBook={openBooking}
                className="pt-8"
            />

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                className="relative z-10 mt-8 lg:mt-10"
                checkoutRoute="construction-progress.checkout"
                paymentProvider="stripe"
                product={product}
                popupVariant="construction"
                popupHeroProofVideo={{
                    title: bookingPopupClip.title,
                    media_type: 'video',
                    embed_url: null,
                    playback_url: bookingPopupClip.src,
                    poster_url: bookingPopupClip.poster,
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
                    {proofClips.map((clip) => (
                        <ProofPanel
                            key={clip.id}
                            clip={clip}
                        />
                    ))}
                </div>
            </section>

            <CameraCutsSection
                clips={cameraClips}
                copy={copy}
            />

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

            <ProgressClipsSection
                clips={progressClips}
                copy={copy}
            />

            <section className="relative left-1/2 my-12 w-screen -translate-x-1/2 overflow-hidden bg-black text-white">
                <AutoplayVideo
                    src={finalClip.src}
                    poster={finalClip.poster}
                    title={finalClip.title}
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

            <GobaFormatsSection horizontalClip={gobaHorizontalClip} />
        </SiteLayout>
    );
}

function CameraCutsSection({
    clips,
    copy,
}: {
    clips: DroneSessionClip[];
    copy: (typeof CONSTRUCTION_COPY)['es'] | (typeof CONSTRUCTION_COPY)['en'];
}) {
    return (
        <section className="my-12">
            <div className="mb-6 max-w-3xl">
                <p className="text-xs font-semibold uppercase tracking-[0.22em] text-primary">
                    {copy.cameraEyebrow}
                </p>
                <h2 className="mt-3 font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                    {copy.cameraTitle}
                </h2>
                <p className="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground md:text-base">
                    {copy.cameraDescription}
                </p>
            </div>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {clips.map((clip) => (
                    <article
                        key={clip.id}
                        className="overflow-hidden rounded-2xl border border-border/80 bg-black shadow-xl shadow-black/10"
                    >
                        <AutoplayVideo
                            src={clip.src}
                            poster={clip.poster}
                            title={clip.title}
                            className="aspect-video"
                            videoClassName="h-full w-full object-cover object-center"
                        />
                    </article>
                ))}
            </div>
        </section>
    );
}

function ProofPanel({
    clip,
}: {
    clip: DroneSessionClip;
}) {
    return (
        <article className="overflow-hidden rounded-xl border border-border/75 bg-black shadow-xl shadow-black/12">
            <AutoplayVideo
                src={clip.src}
                poster={clip.poster}
                title={clip.title}
                className="aspect-video"
            />
        </article>
    );
}

function ProgressClipsSection({
    clips,
    copy,
}: {
    clips: DroneSessionClip[];
    copy: (typeof CONSTRUCTION_COPY)['es'] | (typeof CONSTRUCTION_COPY)['en'];
}) {
    return (
        <section className="my-12 overflow-hidden rounded-2xl border border-[oklch(0.78_0.13_75/0.34)] bg-[oklch(0.09_0.02_250)] text-white shadow-2xl shadow-black/16">
            <div className="grid gap-0 lg:grid-cols-[0.42fr_0.58fr]">
                <div className="flex flex-col justify-between p-6 md:p-8">
                    <div>
                        <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-primary">
                            DJI Air 3 · Rec.709 + D-Log
                        </p>
                        <h2 className="mt-4 font-display text-3xl font-bold leading-tight md:text-4xl">
                            {copy.progressClipsTitle}
                        </h2>
                        <p className="mt-4 text-sm leading-relaxed text-white/72 md:text-base">
                            {copy.progressClipsDescription}
                        </p>
                    </div>
                    <div className="mt-7 grid gap-3">
                        {copy.progressClips.map(([title, description]) => (
                            <div key={title} className="rounded-xl border border-white/10 bg-white/[0.04] p-4">
                                <h3 className="flex items-center gap-2 text-sm font-bold text-white">
                                    <CheckCircle2 className="h-4 w-4 text-primary" />
                                    {title}
                                </h3>
                                <p className="mt-2 text-sm leading-relaxed text-white/64">
                                    {description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="grid gap-px bg-white/10 md:grid-cols-2">
                    {clips.map((clip) => (
                        <article key={clip.id} className="relative min-h-[320px] overflow-hidden bg-black">
                            <AutoplayVideo
                                src={clip.src}
                                poster={clip.poster}
                                title={clip.title}
                                className="absolute inset-0 h-full w-full"
                                videoClassName="h-full w-full object-cover object-center"
                            />
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}

function GobaFormatsSection({
    horizontalClip,
}: {
    horizontalClip: DroneSessionClip;
}) {
    return (
        <section className="my-12">
            <article className="overflow-hidden rounded-2xl border border-border/80 bg-black shadow-2xl shadow-black/12">
                <AutoplayVideo
                    src={horizontalClip.src}
                    poster={horizontalClip.poster}
                    title={horizontalClip.title}
                    className="aspect-video min-h-[260px]"
                    videoClassName="h-full w-full object-cover object-center"
                />
            </article>
        </section>
    );
}

function buildWhatsAppHref(number: string, message: string): string {
    if (!number) {
        return '#';
    }

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
