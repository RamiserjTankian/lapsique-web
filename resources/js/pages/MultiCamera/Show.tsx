import {
    useEffect,
    useMemo,
    useRef,
    useState,
    type CSSProperties,
    type KeyboardEvent,
} from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Maximize2,
    Pause,
    Play,
    Volume2,
    VolumeX,
} from 'lucide-react';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import {
    BookingWidget,
    type BookingWidgetProduct,
} from '@/components/lapsique/BookingWidget';
import { SeoHead } from '@/components/lapsique/SeoHead';
import {
    ServiceFunnelDeliverables,
    ServiceFunnelFaq,
    ServiceFunnelFinalCta,
    ServiceFunnelHeading,
    ServiceFunnelHero,
    ServiceFunnelProcess,
    ServiceFunnelSection,
    PortfolioMediaRail,
    ServiceProofBand,
    ServiceWhatsAppButton,
    serviceFunnelPrimaryActionClass,
} from '@/components/lapsique/ServiceFunnel';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import SiteLayout from '@/layouts/SiteLayout';
import type {
    BookingSlot,
    PageProps,
    PortfolioItemData,
    ServicePortfolioBundle,
} from '@/types';

interface LocalizedText {
    es: string;
    en: string;
}

interface MultiCameraVideo {
    id: string;
    src: string;
    poster: string | null;
    orientation: 'horizontal' | 'vertical';
    has_audio: boolean;
}

interface MultiCameraPhoto {
    id: string;
    src: string;
    orientation: 'horizontal' | 'vertical';
    kind: 'photograph' | 'film_still';
}

interface MultiCameraCoverage {
    id: string;
    name: LocalizedText;
    context: LocalizedText;
    summary: LocalizedText;
    videos: MultiCameraVideo[];
    vertical_videos: MultiCameraVideo[];
    photos: MultiCameraPhoto[];
}

interface MultiCameraShowProps {
    price: number;
    slots: BookingSlot[];
    coverages: MultiCameraCoverage[];
    heroVideo: MultiCameraVideo | null;
    photos: PortfolioItemData[];
    servicePortfolio: ServicePortfolioBundle;
    errors?: Record<string, string>;
}

const COPY = {
    es: {
        heroEyebrow: 'Producción multicámara para DJ sets y eventos',
        heroTitle: 'Graba el set completo. Sal con una campaña lista para publicar.',
        heroDescription: 'Tres cámaras Sony, audio sincronizado y piezas horizontales y verticales construidas a partir de una sola noche.',
        locations: 'Playa del Carmen · Tulum · Cancún · Mérida · Riviera Maya',
        heroProof: 'Master en Log · 10 piezas cortas · 15 fotografías · proyecto DaVinci Resolve 21',
        book: 'Ver fechas disponibles',
        whatsapp: 'Cotizar por WhatsApp',
        proofEyebrow: 'Coberturas reales',
        proofTitle: 'Mira una entrega antes de reservar.',
        proofDescription: 'Explora cada cobertura completa. El contador, la duración y el audio corresponden al archivo publicado.',
        archiveEyebrow: 'Tres producciones verificadas',
        archiveTitle: 'Cabina abierta, Danzahaus y MTRX: tres noches, tres necesidades de entrega.',
        archiveDescription: 'Reproduce las piezas publicadas, cambia de cobertura y compara horizontal, vertical, duración y audio sin controles nativos.',
        photosEyebrow: 'Galería de producción',
        photosTitle: '12 fotografías para revisar la cobertura completa.',
        photosDescription: 'Cabina, público, venue y detalles organizados por producción. Abre cualquier imagen para recorrer la selección en el visor.',
        photosCta: 'Reservar esta producción',
        coverage: 'Cobertura',
        horizontal: 'Horizontal',
        vertical: 'Vertical',
        availableAudio: 'audio disponible',
        noAudio: 'sin audio',
        play: 'Reproducir video',
        pause: 'Pausar video',
        mute: 'Silenciar video',
        unmute: 'Activar audio',
        fullscreen: 'Ver en pantalla completa',
        previous: 'Video anterior',
        next: 'Video siguiente',
        goToVideo: 'Ir al video',
        deliverablesEyebrow: 'La entrega',
        deliverablesTitle: 'Una grabación. Cuatro entregables.',
        deliverablesDescription: 'El material conserva la noche completa y también te da piezas listas para mover la fecha durante semanas.',
        deliverables: [
            ['Master continuo en Log', 'El set completo sincronizado para archivo, revisión o una edición posterior.'],
            ['10 piezas cortas', 'Selección vertical y horizontal lista para campañas, anuncios y redes.'],
            ['15 fotografías', 'DJ, público, venue y detalles elegidos para contar la atmósfera sin repetir encuadres.'],
            ['Proyecto DaVinci Resolve 21', 'Timeline organizado, cámaras sincronizadas y audio listo para continuar la edición.'],
        ],
        processEyebrow: 'Cómo se produce',
        processTitle: 'La cobertura empieza antes de encender la cámara.',
        process: [
            ['01', 'Fecha y venue', 'Revisamos horario, acceso, cabina, luces y viabilidad del dron.'],
            ['02', 'Plan de cámara y audio', 'Alineamos los momentos clave, las tres posiciones Sony y la fuente de audio.'],
            ['03', 'Edición y entrega', 'Organizamos el master, seleccionamos los drops y construimos la galería final.'],
        ],
        gearEyebrow: 'Equipo de grabación',
        gearTitle: 'Un kit compacto para cubrir la cabina, el venue y la pista.',
        gearDescription: 'El equipo se adapta al acceso y a la luz del evento. El dron se utiliza únicamente cuando el venue, el clima y la normativa lo permiten.',
        cameras: 'Cámaras y vuelo',
        lenses: 'Óptica',
        cameraGear: ['DJI Air 3', 'Sony α7 V', 'Sony α7 IV', 'Sony α6700'],
        lensGear: ['28–70 mm f/2.8', '11 mm ultra gran angular f/1.8', '30 mm f/1.4', '35 mm f/1.8', '50 mm f/1.8'],
        offerEyebrow: 'Producción completa',
        offerTitle: 'Una sola fecha, lista para seguir comunicándose.',
        offerDescription: 'Reserva el bloque de producción y después alineamos venue, horarios, shot list y entregables contigo.',
        offerIncludes: ['3 cámaras Sony', 'Audio sincronizado', 'Master continuo en Log', '10 piezas cortas', '15 fotografías', 'Proyecto DaVinci Resolve 21'],
        from: 'Desde',
        booking: {
            checkoutLabel: 'Reservar producción',
            headerTitle: 'Reserva tu producción multicámara',
            headerDescription: 'Elige fecha y horario. Después alineamos venue, acceso, lineup, audio y shot list.',
            summaryTitle: 'Producción multicámara para DJ set',
            summaryDescription: 'Tres cámaras Sony, audio sincronizado, master continuo, piezas cortas, fotografías y proyecto editable.',
            cartService: 'Producción multicámara',
            cartDuration: 'Cobertura de DJ set o evento',
            perks: ['3 cámaras Sony', 'Master continuo en Log', '10 piezas cortas', '15 fotografías', 'Proyecto DaVinci Resolve 21'],
            terms: [
                'La fecha queda sujeta a disponibilidad del calendario.',
                'El venue, los accesos y la fuente de audio se confirman antes de producir.',
                'El dron depende de clima, normativa, seguridad y autorización del venue.',
                'Los tiempos de entrega se confirman con el alcance final.',
                'El uso del material en portafolio se coordina previamente.',
            ],
            paymentCopy: 'Pago seguro con tarjeta procesado por Stripe.',
            unavailableWhatsApp: 'Hola, quiero revisar disponibilidad para una producción multicámara.',
        },
        faqEyebrow: 'Preguntas directas',
        faqs: [
            ['¿Qué recibo al finalizar?', 'Recibes el master continuo en Log, 10 piezas cortas, 15 fotografías editadas y el proyecto organizado en DaVinci Resolve 21.'],
            ['¿El audio sale de la consola?', 'Sí, siempre que el venue entregue una salida estable. También registramos audio de referencia para sincronización y respaldo.'],
            ['¿Puedo pedir piezas verticales y horizontales?', 'Sí. Antes de grabar definimos la mezcla de formatos según tu campaña, canales y calendario de publicación.'],
            ['¿El dron siempre está incluido?', 'El DJI Air 3 forma parte del kit, pero el vuelo depende del venue, la ubicación, el clima, la normativa y la seguridad operativa.'],
            ['¿Dónde trabajan?', 'Cubrimos Playa del Carmen, Tulum, Cancún, Mérida y otros puntos de Riviera Maya. Revisamos traslados y logística antes de confirmar.'],
        ],
        finalTitle: 'Tu set puede seguir trabajando después de la noche.',
        finalDescription: 'Comparte fecha, venue y horario. Te respondemos con disponibilidad y el siguiente paso.',
        photosLabel: 'Fotografías de la cobertura',
    },
    en: {
        heroEyebrow: 'Multi-camera production for DJ sets and events',
        heroTitle: 'Record the full set. Leave with a campaign ready to publish.',
        heroDescription: 'Three Sony cameras, synchronized audio, and horizontal and vertical pieces built from one night.',
        locations: 'Playa del Carmen · Tulum · Cancun · Merida · Riviera Maya',
        heroProof: 'Log master · 10 short edits · 15 photographs · DaVinci Resolve 21 project',
        book: 'See available dates',
        whatsapp: 'Quote on WhatsApp',
        proofEyebrow: 'Real coverage',
        proofTitle: 'Review a delivery before you book.',
        proofDescription: 'Explore every coverage set. The counter, duration, and audio belong to the published file.',
        archiveEyebrow: 'Three verified productions',
        archiveTitle: 'Open booth, Danzahaus, and MTRX: three nights with three delivery needs.',
        archiveDescription: 'Play the published pieces, switch coverage, and compare horizontal, vertical, duration, and audio without native controls.',
        photosEyebrow: 'Production gallery',
        photosTitle: '12 photographs to review the full coverage.',
        photosDescription: 'Booth, crowd, venue, and details organized by production. Open any image to move through the selection in the viewer.',
        photosCta: 'Book this production',
        coverage: 'Coverage',
        horizontal: 'Horizontal',
        vertical: 'Vertical',
        availableAudio: 'audio available',
        noAudio: 'no audio',
        play: 'Play video',
        pause: 'Pause video',
        mute: 'Mute video',
        unmute: 'Turn audio on',
        fullscreen: 'View fullscreen',
        previous: 'Previous video',
        next: 'Next video',
        goToVideo: 'Go to video',
        deliverablesEyebrow: 'Delivery',
        deliverablesTitle: 'One recording. Four deliverables.',
        deliverablesDescription: 'The material preserves the full night and gives you pieces ready to promote the date for weeks.',
        deliverables: [
            ['Continuous Log master', 'The full synchronized set for archive, review, or a later edit.'],
            ['10 short edits', 'A vertical and horizontal selection ready for campaigns, ads, and social.'],
            ['15 photographs', 'DJ, audience, venue, and details selected to tell the atmosphere without repeating frames.'],
            ['DaVinci Resolve 21 project', 'Organized timeline, synchronized cameras, and audio ready for further editing.'],
        ],
        processEyebrow: 'How it is produced',
        processTitle: 'Coverage begins before the camera turns on.',
        process: [
            ['01', 'Date and venue', 'We review schedule, access, booth, lighting, and drone feasibility.'],
            ['02', 'Camera and audio plan', 'We align key moments, three Sony positions, and the audio source.'],
            ['03', 'Edit and delivery', 'We organize the master, select the drops, and build the final gallery.'],
        ],
        gearEyebrow: 'Recording equipment',
        gearTitle: 'A compact kit for the booth, venue, and dance floor.',
        gearDescription: 'The equipment adapts to access and event lighting. The drone is used only when the venue, weather, and regulations allow it.',
        cameras: 'Cameras and flight',
        lenses: 'Lenses',
        cameraGear: ['DJI Air 3', 'Sony α7 V', 'Sony α7 IV', 'Sony α6700'],
        lensGear: ['28–70 mm f/2.8', '11 mm ultra wide f/1.8', '30 mm f/1.4', '35 mm f/1.8', '50 mm f/1.8'],
        offerEyebrow: 'Complete production',
        offerTitle: 'One date, ready to keep communicating.',
        offerDescription: 'Book the production block, then we align venue, schedule, shot list, and deliverables with you.',
        offerIncludes: ['3 Sony cameras', 'Synchronized audio', 'Continuous Log master', '10 short edits', '15 photographs', 'DaVinci Resolve 21 project'],
        from: 'From',
        booking: {
            checkoutLabel: 'Book production',
            headerTitle: 'Book your multi-camera production',
            headerDescription: 'Choose a date and time. We then align venue, access, lineup, audio, and shot list.',
            summaryTitle: 'Multi-camera DJ set production',
            summaryDescription: 'Three Sony cameras, synchronized audio, continuous master, short edits, photographs, and editable project.',
            cartService: 'Multi-camera production',
            cartDuration: 'DJ set or event coverage',
            perks: ['3 Sony cameras', 'Continuous Log master', '10 short edits', '15 photographs', 'DaVinci Resolve 21 project'],
            terms: [
                'The date is subject to calendar availability.',
                'Venue, access, and the audio source are confirmed before production.',
                'Drone use depends on weather, regulations, safety, and venue approval.',
                'Delivery timing is confirmed with the final scope.',
                'Portfolio use is coordinated in advance.',
            ],
            paymentCopy: 'Secure card payment processed by Stripe.',
            unavailableWhatsApp: 'Hi, I want to check availability for a multi-camera production.',
        },
        faqEyebrow: 'Direct questions',
        faqs: [
            ['What do I receive?', 'You receive the continuous Log master, 10 short edits, 15 edited photographs, and the organized DaVinci Resolve 21 project.'],
            ['Does the audio come from the mixer?', 'Yes, when the venue provides a stable output. We also record reference audio for synchronization and backup.'],
            ['Can I request vertical and horizontal edits?', 'Yes. Before filming, we define the format mix based on your campaign, channels, and publishing calendar.'],
            ['Is the drone always included?', 'The DJI Air 3 is part of the kit, but flight depends on venue, location, weather, regulations, and operational safety.'],
            ['Where do you work?', 'We cover Playa del Carmen, Tulum, Cancun, Merida, and other Riviera Maya locations. Travel and logistics are reviewed before confirmation.'],
        ],
        finalTitle: 'Your set can keep working after the night.',
        finalDescription: 'Share the date, venue, and schedule. We will reply with availability and the next step.',
        photosLabel: 'Coverage photographs',
    },
} as const;

export default function MultiCameraShow({
    price,
    slots,
    coverages,
    photos,
    servicePortfolio,
    errors = {},
}: MultiCameraShowProps) {
    const { locale } = useTranslations();
    const { site } = usePage<PageProps>().props;
    const copy = locale === 'en' ? COPY.en : COPY.es;
    const proofRef = useSectionEvent<HTMLDivElement>('multi_camera_portfolio_engaged', {
        section: 'multi_camera_coverages',
        service_type: 'multi_camera',
    });
    const processRef = useSectionEvent<HTMLDivElement>('multi_camera_workflow_viewed', {
        section: 'multi_camera_workflow',
        service_type: 'multi_camera',
    });
    const offerRef = useSectionEvent<HTMLDivElement>('multi_camera_package_viewed', {
        section: 'multi_camera_package',
        service_type: 'multi_camera',
        value: price,
        currency: 'MXN',
    });
    const analyticsPayload = useMemo(() => ({
        content_name: copy.heroTitle,
        content_category: 'multi_camera_booking',
        service_type: 'multi_camera',
        currency: 'MXN',
        value: price,
    }), [copy.heroTitle, price]);
    const bookingProduct = useMemo<BookingWidgetProduct>(() => ({
        checkoutLabel: copy.booking.checkoutLabel,
        headerTitle: copy.booking.headerTitle,
        headerDescription: copy.booking.headerDescription,
        summaryTitle: copy.booking.summaryTitle,
        summaryDescription: copy.booking.summaryDescription,
        cartService: copy.booking.cartService,
        cartDuration: copy.booking.cartDuration,
        summaryPerks: [...copy.booking.perks],
        terms: [...copy.booking.terms],
        paymentCopy: copy.booking.paymentCopy,
        unavailableWhatsApp: copy.booking.unavailableWhatsApp,
    }), [copy]);
    const whatsappHref = `https://wa.me/${site.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(copy.booking.unavailableWhatsApp)}`;
    const curatedCoverages = useMemo(
        () => curateCoverages(coverages, servicePortfolio.hero.src),
        [coverages, servicePortfolio.hero.src],
    );
    const photoPortfolio = useMemo(
        () => portfolioWithOnlyPhotos(servicePortfolio),
        [servicePortfolio],
    );

    useEffect(() => {
        trackBookingEvent('multi_camera_page_viewed', {
            ...analyticsPayload,
            section: 'multi_camera',
        });
    }, [analyticsPayload]);

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('multi_camera_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };
    const deliverables = copy.deliverables.map(([title, description]) => ({ title, description }));
    const process = copy.process.map(([, title, description]) => ({ title, description }));
    const faqs = copy.faqs.map(([question, answer]) => ({ question, answer }));
    const primaryAction = (source: string, label: string = copy.book) => (
        <BookingCtaButton
            type="button"
            className={serviceFunnelPrimaryActionClass}
            opensBookingModal
            bookingSource={`multi_camera_${source}`}
            bookingAnalytics={{
                analyticsEvent: 'multi_camera_booking_cta_clicked',
                analyticsPayload,
            }}
        >
            <CalendarDays className="size-5" aria-hidden />
            {label}
            <ArrowRight className="size-4" aria-hidden />
        </BookingCtaButton>
    );
    const whatsappAction = (source: string) => (
        <ServiceWhatsAppButton
            href={whatsappHref}
            label={copy.whatsapp}
            onClick={() => trackWhatsApp(source)}
        />
    );

    return (
        <SiteLayout>
            <SeoHead />

            <ServiceFunnelHero
                eyebrow={copy.heroEyebrow}
                title={copy.heroTitle}
                description={copy.heroDescription}
                locations={copy.locations}
                price={price}
                priceLabel={copy.from}
                priceNote={copy.heroProof}
                media={<ServiceHeroMedia portfolio={servicePortfolio} />}
                mediaLabel="Sony Alpha · DaVinci Resolve 21"
                mediaCaption={servicePortfolio.hero.projectLabel}
                primaryAction={primaryAction('hero')}
                secondaryAction={whatsappAction('hero')}
            />

            <ServiceFunnelSection innerClassName="py-0 sm:py-0 lg:py-0">
                <ServiceProofBand
                    portfolio={servicePortfolio}
                    eyebrow={copy.proofEyebrow}
                    title={copy.proofTitle}
                    description={copy.proofDescription}
                />
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="dark" innerClassName="max-w-none px-0 py-16 sm:px-0 sm:py-20 lg:py-24">
                <div ref={proofRef} className="mx-auto max-w-6xl px-4 sm:px-6">
                    <ServiceFunnelHeading
                        eyebrow={copy.archiveEyebrow}
                        title={copy.archiveTitle}
                        description={copy.archiveDescription}
                        inverse
                    />
                </div>
                <CoverageShowcase coverages={curatedCoverages} locale={locale} copy={copy} />
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.photosEyebrow}
                    title={copy.photosTitle}
                    description={copy.photosDescription}
                />
                <div className="mt-10 space-y-14">
                    {photoPortfolio.projects.map((project, index) => (
                        <div key={project.key}>
                            <div className="mb-5 flex flex-wrap items-end justify-between gap-3 border-b border-border pb-4">
                                <h3 className="text-balance font-display text-3xl font-bold leading-none text-foreground">
                                    {project.label}
                                </h3>
                                <p className="font-mono text-[0.65rem] uppercase tracking-[0.15em] text-muted-foreground">
                                    {project.media.length} {locale === 'en' ? 'photographs' : 'fotografías'}
                                </p>
                            </div>
                            <PortfolioMediaRail
                                portfolio={photoPortfolio}
                                projectKey={project.key}
                                ariaLabel={`${copy.photosTitle}: ${project.label}`}
                                action={index === photoPortfolio.projects.length - 1 ? (
                                    <div className="grid w-full gap-3 sm:max-w-2xl sm:grid-cols-2">
                                        {primaryAction('portfolio', copy.photosCta)}
                                        {whatsappAction('portfolio')}
                                    </div>
                                ) : undefined}
                            />
                        </div>
                    ))}
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.deliverablesEyebrow}
                    title={copy.deliverablesTitle}
                    description={copy.deliverablesDescription}
                />
                <div className="mt-10">
                    <ServiceFunnelDeliverables items={deliverables} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="dark">
                <div ref={processRef}>
                    <ServiceFunnelHeading eyebrow={copy.processEyebrow} title={copy.processTitle} inverse />
                    <div className="mt-10">
                        <ServiceFunnelProcess items={process} inverse />
                    </div>
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft" id="reservar">
                <div ref={offerRef}>
                    <ServiceFunnelHeading
                        eyebrow={copy.offerEyebrow}
                        title={copy.booking.headerTitle}
                        description={copy.booking.headerDescription}
                    />
                    <BookingWidget
                        slots={slots}
                        price={price}
                        whatsapp={site.whatsapp}
                        errors={errors}
                        className="mt-10"
                        checkoutRoute="multi-camera.checkout"
                        paymentProvider="stripe"
                        product={bookingProduct}
                        popupVariant="multiCamera"
                        popupPortfolioItems={photos}
                        popupHeroProofVideo={servicePortfolio.hero.kind === 'video' ? {
                            title: servicePortfolio.hero.projectLabel,
                            media_type: 'video',
                            embed_url: null,
                            playback_url: servicePortfolio.hero.src,
                            poster_url: servicePortfolio.hero.poster ?? null,
                        } : null}
                        highlight
                        analyticsPayload={analyticsPayload}
                        analyticsOpenEvent="multi_camera_booking_opened"
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.faqEyebrow}
                    title={locale === 'en' ? 'What to know before production' : 'Lo que debes saber antes de producir'}
                />
                <div className="mt-10">
                    <ServiceFunnelFaq items={faqs} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelFinalCta
                eyebrow="Lapsique Originals"
                title={copy.finalTitle}
                description={copy.finalDescription}
                primaryAction={primaryAction('final')}
                secondaryAction={whatsappAction('final')}
            />
        </SiteLayout>
    );
}

function ServiceHeroMedia({ portfolio }: { portfolio: ServicePortfolioBundle }) {
    const media = portfolio.hero;
    const still = media.kind === 'image'
        ? media.src
        : media.poster ?? portfolio.projects
            .flatMap((project) => project.media)
            .find((item) => item.kind === 'image' && item.src !== media.src)
            ?.src;

    if (still) {
        return (
            <img
                src={still}
                alt={media.alt}
                fetchPriority="high"
                className="h-full w-full object-cover"
            />
        );
    }

    return (
        <video
            src={media.src}
            poster={media.poster ?? undefined}
            muted
            playsInline
            preload="metadata"
            aria-label={media.alt}
            className="h-full w-full object-cover"
        />
    );
}

function curateCoverages(
    coverages: MultiCameraCoverage[],
    excludedHeroSrc: string,
): MultiCameraCoverage[] {
    const sanitized = coverages.map((coverage) => ({
        ...coverage,
        videos: uniqueBySource(
            coverage.videos.filter((video) => video.src !== excludedHeroSrc),
        ),
        vertical_videos: uniqueBySource(
            coverage.vertical_videos.filter((video) => video.src !== excludedHeroSrc),
        ),
        photos: uniqueBySource(
            coverage.photos.filter((photo) => !photo.src.toLowerCase().includes('karen-echev')),
        ),
    }));
    const openBooth = sanitized.find((coverage) => coverage.id === 'coverage-01');
    const danzahaus = sanitized.find((coverage) => coverage.id === 'coverage-02');
    const formatStudy = sanitized.find((coverage) => coverage.id === 'coverage-03');
    const mtrx = sanitized.find((coverage) => coverage.id === 'coverage-04');

    if (!openBooth || !danzahaus || !mtrx) {
        return sanitized;
    }

    const mergedDanzahaus = formatStudy ? {
        ...danzahaus,
        context: {
            es: 'Una cobertura · entregas horizontales y verticales',
            en: 'One coverage · horizontal and vertical delivery',
        },
        summary: {
            es: 'Drops completos, cortes de campaña y formatos para archivo y redes, todos organizados dentro de la misma producción.',
            en: 'Full drops, campaign cuts, and formats for archive and social, all organized within the same production.',
        },
        videos: uniqueBySource([...danzahaus.videos, ...formatStudy.videos]),
        vertical_videos: uniqueBySource([
            ...danzahaus.vertical_videos,
            ...formatStudy.vertical_videos,
        ]),
        photos: uniqueBySource([...danzahaus.photos, ...formatStudy.photos]),
    } : danzahaus;

    return [openBooth, mergedDanzahaus, mtrx];
}

function uniqueBySource<T extends { id: string; src: string }>(items: T[]): T[] {
    const seen = new Set<string>();

    return items.filter((item) => {
        const key = item.src || item.id;

        if (seen.has(key)) {
            return false;
        }

        seen.add(key);

        return true;
    });
}

function portfolioWithOnlyPhotos(portfolio: ServicePortfolioBundle): ServicePortfolioBundle {
    const projects = portfolio.projects
        .map((project) => ({
            ...project,
            media: project.media.filter((media) => media.kind === 'image'),
        }))
        .filter((project) => project.media.length > 0);
    const imageCount = projects.reduce((total, project) => total + project.media.length, 0);

    return {
        ...portfolio,
        projects,
        stats: {
            ...portfolio.stats,
            mediaCount: imageCount,
            projectCount: projects.length,
            imageCount,
            videoCount: 0,
        },
    };
}

function CoverageShowcase({
    coverages,
    locale,
    copy,
}: {
    coverages: MultiCameraCoverage[];
    locale: string;
    copy: typeof COPY.es | typeof COPY.en;
}) {
    const [coverageIndex, setCoverageIndex] = useState(0);
    const [format, setFormat] = useState<'horizontal' | 'vertical'>('horizontal');
    const coverage = coverages[coverageIndex];

    useEffect(() => {
        setFormat('horizontal');
    }, [coverageIndex]);

    if (!coverage) {
        return null;
    }

    const videos = format === 'vertical' && coverage.vertical_videos.length > 0
        ? coverage.vertical_videos
        : coverage.videos;

    const selectCoverage = (index: number) => {
        setCoverageIndex(index);
        trackBookingEvent('multi_camera_coverage_selected', {
            service_type: 'multi_camera',
            coverage_id: coverages[index]?.id,
            position: index + 1,
        });
    };

    const selectFormat = (nextFormat: 'horizontal' | 'vertical') => {
        setFormat(nextFormat);
        trackBookingEvent('multi_camera_format_selected', {
            service_type: 'multi_camera',
            coverage_id: coverage.id,
            format: nextFormat,
        });
    };
    const onCoverageKeyDown = (
        event: KeyboardEvent<HTMLButtonElement>,
        index: number,
    ) => {
        let nextIndex: number | null = null;

        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
            nextIndex = (index + 1) % coverages.length;
        } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
            nextIndex = (index - 1 + coverages.length) % coverages.length;
        } else if (event.key === 'Home') {
            nextIndex = 0;
        } else if (event.key === 'End') {
            nextIndex = coverages.length - 1;
        }

        if (nextIndex === null) {
            return;
        }

        event.preventDefault();
        selectCoverage(nextIndex);
        const tabs = event.currentTarget.parentElement?.querySelectorAll<HTMLButtonElement>('[role="tab"]');
        tabs?.[nextIndex]?.focus();
    };

    return (
        <div className="mt-10">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div className="flex gap-2 overflow-x-auto pb-2" role="tablist" aria-label={copy.coverage}>
                    {coverages.map((item, index) => (
                        <button
                            key={item.id}
                            type="button"
                            role="tab"
                            id={`multi-camera-tab-${item.id}`}
                            aria-controls={`multi-camera-panel-${item.id}`}
                            aria-selected={coverageIndex === index}
                            tabIndex={coverageIndex === index ? 0 : -1}
                            onClick={() => selectCoverage(index)}
                            onKeyDown={(event) => onCoverageKeyDown(event, index)}
                            className={`min-h-11 shrink-0 border px-4 py-2 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-[#07090c] ${
                                coverageIndex === index
                                    ? 'border-primary bg-primary text-white'
                                    : 'border-white/20 bg-transparent text-white/65 hover:border-white/45 hover:text-white'
                            }`}
                        >
                            <span className="block font-mono text-[9px] uppercase tracking-[0.18em] opacity-65">
                                {copy.coverage} {String(index + 1).padStart(2, '0')}
                            </span>
                            <span className="mt-1 block text-sm font-semibold">{item.name[locale === 'en' ? 'en' : 'es']}</span>
                        </button>
                    ))}
                </div>

                <div
                    id={`multi-camera-panel-${coverage.id}`}
                    role="tabpanel"
                    aria-labelledby={`multi-camera-tab-${coverage.id}`}
                    tabIndex={0}
                    className="mt-6 grid gap-5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary md:grid-cols-[1fr_auto] md:items-end"
                >
                    <div>
                        <p className="alpha-kicker text-primary">{coverage.context[locale === 'en' ? 'en' : 'es']}</p>
                        <h3 className="mt-3 font-display text-4xl font-bold text-white">{coverage.name[locale === 'en' ? 'en' : 'es']}</h3>
                        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-white/60">{coverage.summary[locale === 'en' ? 'en' : 'es']}</p>
                    </div>
                    {coverage.vertical_videos.length > 0 ? (
                        <div className="inline-flex border border-white/20 p-1" role="group" aria-label={locale === 'en' ? 'Video format' : 'Formato de video'}>
                            {(['horizontal', 'vertical'] as const).map((item) => (
                                <button
                                    key={item}
                                    type="button"
                                    aria-pressed={format === item}
                                    onClick={() => selectFormat(item)}
                                    className={`min-h-11 px-4 text-xs font-semibold uppercase tracking-[0.1em] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${
                                        format === item ? 'bg-white text-black' : 'text-white/60 hover:text-white'
                                    }`}
                                >
                                    {item === 'horizontal' ? copy.horizontal : copy.vertical}
                                </button>
                            ))}
                        </div>
                    ) : null}
                </div>
            </div>

            <CustomVideoCarousel
                key={`${coverage.id}-${format}`}
                videos={videos}
                coverageId={coverage.id}
                format={format}
                copy={copy}
            />
        </div>
    );
}

function CustomVideoCarousel({
    videos,
    coverageId,
    format,
    copy,
}: {
    videos: MultiCameraVideo[];
    coverageId: string;
    format: 'horizontal' | 'vertical';
    copy: typeof COPY.es | typeof COPY.en;
}) {
    const [activeIndex, setActiveIndex] = useState(0);
    const [playing, setPlaying] = useState(false);
    const [muted, setMuted] = useState(true);
    const [currentTime, setCurrentTime] = useState(0);
    const [duration, setDuration] = useState(0);
    const [isInViewport, setIsInViewport] = useState(false);
    const [prefersReducedMotion, setPrefersReducedMotion] = useState(false);
    const videoRef = useRef<HTMLVideoElement>(null);
    const shellRef = useRef<HTMLDivElement>(null);
    const milestonesRef = useRef<Set<number>>(new Set());
    const startedRef = useRef(false);
    const activeVideo = videos[activeIndex];

    useEffect(() => {
        setActiveIndex(0);
    }, [videos]);

    useEffect(() => {
        const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        const updatePreference = () => setPrefersReducedMotion(mediaQuery.matches);

        updatePreference();
        mediaQuery.addEventListener('change', updatePreference);

        return () => mediaQuery.removeEventListener('change', updatePreference);
    }, []);

    useEffect(() => {
        const shell = shellRef.current;

        if (!shell || typeof IntersectionObserver === 'undefined') {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                setIsInViewport(entry.isIntersecting && entry.intersectionRatio >= 0.2);
            },
            { threshold: [0, 0.2, 0.5] },
        );

        observer.observe(shell);

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        const video = videoRef.current;

        setCurrentTime(0);
        setDuration(0);
        setPlaying(false);
        milestonesRef.current.clear();
        startedRef.current = false;

        if (!video) {
            return;
        }

        video.pause();
        video.muted = true;
        setMuted(true);
    }, [activeIndex, activeVideo?.src]);

    useEffect(() => {
        const video = videoRef.current;

        if (!video) {
            return;
        }

        if (!isInViewport || prefersReducedMotion) {
            video.pause();
            setPlaying(false);
            return;
        }

        video.muted = true;
        setMuted(true);
        void video.play().then(() => setPlaying(true)).catch(() => undefined);
    }, [activeIndex, activeVideo?.src, isInViewport, prefersReducedMotion]);

    if (!activeVideo) {
        return null;
    }

    const report = (event: string, extra: Record<string, unknown> = {}) => {
        trackBookingEvent(event, {
            service_type: 'multi_camera',
            coverage_id: coverageId,
            asset_id: activeVideo.id,
            format,
            position: activeIndex + 1,
            ...extra,
        });
    };

    const goTo = (nextIndex: number, source: string) => {
        const normalized = (nextIndex + videos.length) % videos.length;
        setActiveIndex(normalized);
        report('multi_camera_video_navigated', {
            source,
            destination_position: normalized + 1,
        });
    };

    const togglePlayback = () => {
        const video = videoRef.current;

        if (!video) {
            return;
        }

        if (video.paused) {
            void video.play().then(() => setPlaying(true)).catch(() => undefined);
        } else {
            video.pause();
            setPlaying(false);
        }
    };

    const toggleMuted = () => {
        const video = videoRef.current;

        if (!video || !activeVideo.has_audio) {
            return;
        }

        video.muted = !video.muted;
        setMuted(video.muted);
        report('multi_camera_audio_toggled', { muted: video.muted });
    };

    const updateProgress = () => {
        const video = videoRef.current;

        if (!video || !Number.isFinite(video.duration)) {
            return;
        }

        setCurrentTime(video.currentTime);
        setDuration(video.duration);

        if (!startedRef.current && video.currentTime > 0.15) {
            startedRef.current = true;
            report('multi_camera_video_started', { duration_seconds: Math.round(video.duration) });
        }

        const percent = video.duration > 0 ? (video.currentTime / video.duration) * 100 : 0;

        [25, 50, 75].forEach((milestone) => {
            if (percent >= milestone && !milestonesRef.current.has(milestone)) {
                milestonesRef.current.add(milestone);
                report('multi_camera_video_progress', { percent: milestone });
            }
        });
    };

    const handleEnded = () => {
        setPlaying(false);
        report('multi_camera_video_completed', { duration_seconds: Math.round(duration) });

        if (isInViewport && !prefersReducedMotion && videos.length > 1) {
            goTo(activeIndex + 1, 'completed');
        }
    };

    const seek = (value: number) => {
        if (!videoRef.current || !duration) {
            return;
        }

        videoRef.current.currentTime = value;
        setCurrentTime(value);
    };

    const requestFullscreen = () => {
        void shellRef.current?.requestFullscreen?.();
        report('multi_camera_video_fullscreen_opened');
    };

    const progress = duration > 0 ? (currentTime / duration) * 100 : 0;
    const rangeStyle = { '--progress': `${progress}%` } as CSSProperties;

    return (
        <div className="mt-6 w-screen">
            <div ref={shellRef} className="multicam-video-shell relative mx-auto max-w-[1600px] overflow-hidden bg-[#050607]">
                <video
                    ref={videoRef}
                    key={activeVideo.src}
                    src={activeVideo.src}
                    poster={activeVideo.poster ?? undefined}
                    playsInline
                    preload="metadata"
                    muted={muted}
                    onPlay={() => setPlaying(true)}
                    onPause={() => setPlaying(false)}
                    onLoadedMetadata={(event) => setDuration(event.currentTarget.duration)}
                    onTimeUpdate={updateProgress}
                    onEnded={handleEnded}
                    onClick={togglePlayback}
                    className={`multicam-fullbleed-video ${format === 'vertical' ? 'object-contain' : 'object-cover'}`}
                    style={{ aspectRatio: format === 'vertical' ? '9 / 16' : '16 / 9' }}
                    aria-label={`${copy.coverage} ${coverageId}, ${activeIndex + 1} / ${videos.length}`}
                />

                <div className="relative z-10 bg-[#050607] px-4 py-4 text-white sm:pointer-events-none sm:absolute sm:inset-x-0 sm:bottom-0 sm:bg-gradient-to-t sm:from-black sm:via-black/60 sm:to-transparent sm:px-6 sm:pb-4 sm:pt-24">
                    <div className="sm:pointer-events-auto">
                        <div className="mb-3 flex flex-wrap items-end justify-between gap-3 sm:mb-4 sm:flex-nowrap sm:gap-5">
                            <div>
                                <p className="font-mono text-[9px] uppercase tracking-[0.18em] text-primary">
                                    {copy.coverage} · {String(activeIndex + 1).padStart(2, '0')} / {String(videos.length).padStart(2, '0')}
                                </p>
                                <p className="mt-2 flex items-center gap-2 text-xs text-white/65">
                                    <Clock3 className="size-3.5" aria-hidden />
                                    {formatTime(duration)}
                                    <span aria-hidden>·</span>
                                    {activeVideo.has_audio ? copy.availableAudio : copy.noAudio}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                <button type="button" className="multicam-video-control" onClick={() => goTo(activeIndex - 1, 'previous')} aria-label={copy.previous}>
                                    <ChevronLeft className="size-5" />
                                </button>
                                <button type="button" className="multicam-video-control" onClick={() => goTo(activeIndex + 1, 'next')} aria-label={copy.next}>
                                    <ChevronRight className="size-5" />
                                </button>
                            </div>
                        </div>

                        <input
                            type="range"
                            min={0}
                            max={duration || 0}
                            step={0.05}
                            value={Math.min(currentTime, duration || 0)}
                            onChange={(event) => seek(Number(event.target.value))}
                            className="multicam-video-control-range w-full"
                            style={rangeStyle}
                            aria-label={localeRangeLabel(copy)}
                        />

                        <div className="mt-3 flex items-center gap-2">
                            <button type="button" className="multicam-video-control" onClick={togglePlayback} aria-label={playing ? copy.pause : copy.play}>
                                {playing ? <Pause className="size-5" /> : <Play className="size-5" />}
                            </button>
                            <button
                                type="button"
                                className="multicam-video-control"
                                onClick={toggleMuted}
                                disabled={!activeVideo.has_audio}
                                aria-label={muted ? copy.unmute : copy.mute}
                            >
                                {muted ? <VolumeX className="size-5" /> : <Volume2 className="size-5" />}
                            </button>
                            <span className="font-mono text-[10px] text-white/58">
                                {formatTime(currentTime)} / {formatTime(duration)}
                            </span>
                            <button type="button" className="multicam-video-control ml-auto" onClick={requestFullscreen} aria-label={copy.fullscreen}>
                                <Maximize2 className="size-5" />
                            </button>
                        </div>

                        {videos.length > 1 ? (
                            <div className="mt-2 flex items-center overflow-x-auto" aria-label={copy.goToVideo}>
                                {videos.map((video, index) => (
                                    <button
                                        key={video.id}
                                        type="button"
                                        onClick={() => goTo(index, 'index')}
                                        aria-label={`${copy.goToVideo} ${index + 1}`}
                                        aria-current={index === activeIndex ? 'true' : undefined}
                                        className="group flex h-11 min-w-10 flex-1 items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                    >
                                        <span
                                            className={`h-1.5 w-full rounded-full transition-[background-color] duration-150 ${
                                                index === activeIndex ? 'bg-primary' : 'bg-white/25 group-hover:bg-white/55'
                                            }`}
                                        />
                                    </button>
                                ))}
                            </div>
                        ) : null}
                    </div>
                </div>
            </div>
        </div>
    );
}

function formatTime(value: number): string {
    if (!Number.isFinite(value) || value <= 0) {
        return '0:00';
    }

    const minutes = Math.floor(value / 60);
    const seconds = Math.floor(value % 60);

    return `${minutes}:${String(seconds).padStart(2, '0')}`;
}

function localeRangeLabel(copy: typeof COPY.es | typeof COPY.en): string {
    return copy === COPY.en ? 'Video progress' : 'Progreso del video';
}
