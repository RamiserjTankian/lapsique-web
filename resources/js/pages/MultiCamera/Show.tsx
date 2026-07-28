import {
    useEffect,
    useMemo,
    useRef,
    useState,
    type CSSProperties,
    type ReactNode,
} from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Camera,
    Check,
    ChevronLeft,
    ChevronRight,
    CircleDot,
    Clock3,
    Film,
    Focus,
    Maximize2,
    MessageCircle,
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
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import SiteLayout from '@/layouts/SiteLayout';
import { formatMxn } from '@/lib/utils';
import type { BookingSlot, PageProps, PortfolioItemData } from '@/types';

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
    heroVideo,
    photos,
    errors = {},
}: MultiCameraShowProps) {
    const { locale } = useTranslations();
    const { site } = usePage<PageProps>().props;
    const copy = locale === 'en' ? COPY.en : COPY.es;
    const proofRef = useSectionEvent<HTMLElement>('multi_camera_portfolio_engaged', {
        section: 'multi_camera_coverages',
        service_type: 'multi_camera',
    });
    const processRef = useSectionEvent<HTMLElement>('multi_camera_workflow_viewed', {
        section: 'multi_camera_workflow',
        service_type: 'multi_camera',
    });
    const gearRef = useSectionEvent<HTMLElement>('multi_camera_gear_viewed', {
        section: 'multi_camera_equipment',
        service_type: 'multi_camera',
    });
    const offerRef = useSectionEvent<HTMLElement>('multi_camera_package_viewed', {
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

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#050607] text-white">
                <HeroBackground video={heroVideo} />
                <div className="relative mx-auto grid min-h-[min(860px,92svh)] max-w-6xl content-end gap-10 px-4 pb-14 pt-28 sm:px-6 lg:grid-cols-[minmax(0,0.96fr)_minmax(380px,0.8fr)] lg:items-end lg:pb-20">
                    <div className="max-w-4xl">
                        <p className="alpha-kicker text-primary">{copy.heroEyebrow}</p>
                        <h1 className="mt-5 max-w-4xl font-display text-5xl font-bold leading-[0.9] tracking-tight text-white drop-shadow-[0_12px_42px_rgba(0,0,0,0.8)] sm:text-6xl md:text-8xl">
                            {copy.heroTitle}
                        </h1>
                        <p className="mt-6 max-w-2xl text-base leading-relaxed text-white/78 sm:text-lg md:text-xl">
                            {copy.heroDescription}
                        </p>
                        <p className="mt-5 font-mono text-[11px] uppercase tracking-[0.2em] text-primary">
                            {copy.locations}
                        </p>
                        <div className="mt-8 grid max-w-2xl gap-3 sm:grid-cols-[1fr_auto]">
                            <BookingCtaButton
                                type="button"
                                className="h-14 w-full rounded-none border-primary bg-primary text-white hover:border-white hover:bg-white hover:text-black"
                                opensBookingModal
                                bookingSource="multi_camera_hero"
                                bookingAnalytics={{
                                    analyticsEvent: 'multi_camera_booking_cta_clicked',
                                    analyticsPayload,
                                }}
                            >
                                <CalendarDays className="size-5" />
                                {copy.book}
                                <ArrowRight className="size-5" />
                            </BookingCtaButton>
                            <Button
                                size="xl"
                                className="h-14 rounded-none border border-[#25D366] bg-[#25D366] px-6 text-white hover:bg-[#1ebe5d] hover:text-white"
                                asChild
                            >
                                <a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={() => trackWhatsApp('hero')}>
                                    <MessageCircle className="size-5" />
                                    {copy.whatsapp}
                                </a>
                            </Button>
                        </div>
                        <p className="mt-5 max-w-2xl border-t border-white/15 pt-4 font-mono text-[10px] uppercase tracking-[0.16em] text-white/55">
                            {copy.heroProof}
                        </p>
                    </div>

                    {heroVideo ? (
                        <div className="hidden border border-white/20 bg-black/55 p-3 shadow-2xl backdrop-blur-sm lg:block">
                            <img
                                src={heroVideo.poster ?? '/images/og/multicamara.jpg'}
                                alt=""
                                className="aspect-video w-full object-cover"
                            />
                            <div className="flex items-center justify-between border-t border-white/15 px-1 pt-3 font-mono text-[10px] uppercase tracking-[0.16em] text-white/55">
                                <span>{copy.proofEyebrow}</span>
                                <span>01 / {String(coverages.length).padStart(2, '0')}</span>
                            </div>
                        </div>
                    ) : null}
                </div>
            </section>

            <section ref={proofRef} className="relative left-1/2 w-screen -translate-x-1/2 bg-[#07090c] py-16 text-white md:py-24">
                <div className="mx-auto max-w-6xl px-4 sm:px-6">
                    <SectionHeading
                        eyebrow={copy.proofEyebrow}
                        title={copy.proofTitle}
                        description={copy.proofDescription}
                        inverse
                    />
                </div>
                <CoverageShowcase coverages={coverages} locale={locale} copy={copy} />
            </section>

            <section className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                <SectionHeading
                    eyebrow={copy.deliverablesEyebrow}
                    title={copy.deliverablesTitle}
                    description={copy.deliverablesDescription}
                />
                <div className="divide-y divide-border/70 border-y border-border/70">
                    {copy.deliverables.map(([title, description], index) => {
                        const Icon = [Film, CircleDot, Camera, Focus][index] ?? Check;

                        return (
                            <article key={title} className="grid gap-3 py-5 sm:grid-cols-[2.5rem_minmax(0,0.8fr)_1.2fr] sm:gap-5">
                                <Icon className="size-5 text-primary" aria-hidden />
                                <h3 className="font-display text-2xl font-bold leading-none text-foreground">{title}</h3>
                                <p className="text-sm leading-relaxed text-muted-foreground">{description}</p>
                            </article>
                        );
                    })}
                </div>
            </section>

            <section ref={processRef} className="relative left-1/2 w-screen -translate-x-1/2 bg-[#0a0b0d] py-16 text-white md:py-24">
                <div className="mx-auto max-w-6xl px-4 sm:px-6">
                    <SectionHeading eyebrow={copy.processEyebrow} title={copy.processTitle} inverse />
                    <div className="mt-10 grid border-y border-white/15 md:grid-cols-3 md:divide-x md:divide-white/15">
                        {copy.process.map(([number, title, description]) => (
                            <article key={number} className="border-b border-white/15 px-1 py-7 last:border-b-0 md:border-b-0 md:px-6 md:first:pl-0 md:last:pr-0">
                                <p className="font-mono text-xs tracking-[0.2em] text-primary">{number}</p>
                                <h3 className="mt-6 font-display text-3xl font-bold leading-none text-white">{title}</h3>
                                <p className="mt-4 text-sm leading-relaxed text-white/60">{description}</p>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            <section ref={gearRef} className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                <SectionHeading
                    eyebrow={copy.gearEyebrow}
                    title={copy.gearTitle}
                    description={copy.gearDescription}
                />
                <div className="grid gap-8 sm:grid-cols-2">
                    <GearList title={copy.cameras} items={copy.cameraGear} icon={<Camera className="size-5" />} />
                    <GearList title={copy.lenses} items={copy.lensGear} icon={<Focus className="size-5" />} />
                </div>
            </section>

            <section ref={offerRef} className="relative left-1/2 w-screen -translate-x-1/2 bg-[#101114] py-16 text-white md:py-24">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_0.72fr] lg:items-end">
                    <div>
                        <p className="alpha-kicker text-primary">{copy.offerEyebrow}</p>
                        <h2 className="mt-4 max-w-3xl font-display text-4xl font-bold leading-[0.94] tracking-tight text-white md:text-6xl">
                            {copy.offerTitle}
                        </h2>
                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-white/64">{copy.offerDescription}</p>
                        <ul className="mt-8 grid gap-x-8 gap-y-3 border-y border-white/15 py-5 sm:grid-cols-2">
                            {copy.offerIncludes.map((item) => (
                                <li key={item} className="flex items-center gap-3 text-sm text-white/75">
                                    <Check className="size-4 text-primary" aria-hidden />
                                    {item}
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div className="border-l border-white/15 pl-0 lg:pl-8">
                        <p className="font-mono text-xs uppercase tracking-[0.18em] text-white/48">{copy.from}</p>
                        <p className="mt-2 font-mono-tabular text-5xl font-bold text-primary">{formatMxn(price)}</p>
                        <div className="mt-7 grid gap-3">
                            <BookingCtaButton
                                type="button"
                                className="h-14 w-full rounded-none border-primary bg-primary text-white hover:border-white hover:bg-white hover:text-black"
                                opensBookingModal
                                bookingSource="multi_camera_package"
                                bookingAnalytics={{
                                    analyticsEvent: 'multi_camera_booking_cta_clicked',
                                    analyticsPayload,
                                }}
                            >
                                <CalendarDays className="size-5" />
                                {copy.book}
                                <ArrowRight className="size-5" />
                            </BookingCtaButton>
                            <Button size="xl" className="h-14 w-full rounded-none border border-[#25D366] bg-[#25D366] text-white hover:bg-[#1ebe5d] hover:text-white" asChild>
                                <a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={() => trackWhatsApp('package')}>
                                    <MessageCircle className="size-5" />
                                    {copy.whatsapp}
                                </a>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                className="mx-auto max-w-6xl"
                checkoutRoute="multi-camera.checkout"
                paymentProvider="stripe"
                product={bookingProduct}
                popupVariant="multiCamera"
                popupPortfolioItems={photos}
                popupHeroProofVideo={heroVideo ? {
                    title: null,
                    media_type: 'video',
                    embed_url: null,
                    playback_url: heroVideo.src,
                    poster_url: heroVideo.poster,
                } : null}
                highlight
                analyticsPayload={analyticsPayload}
                analyticsOpenEvent="multi_camera_booking_opened"
            />

            <section className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-[0.72fr_1.28fr]">
                <div><p className="alpha-kicker text-primary">{copy.faqEyebrow}</p></div>
                <div className="divide-y divide-border/70 border-y border-border/70">
                    {copy.faqs.map(([question, answer]) => (
                        <Faq key={question} question={question} answer={answer} />
                    ))}
                </div>
            </section>

            <section className="relative left-1/2 mb-0 w-screen -translate-x-1/2 bg-primary py-14 text-white md:py-20">
                <div className="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div className="max-w-3xl">
                        <h2 className="font-display text-4xl font-bold leading-[0.94] tracking-tight md:text-6xl">{copy.finalTitle}</h2>
                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-white/84 md:text-lg">{copy.finalDescription}</p>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 lg:min-w-[460px]">
                        <BookingCtaButton
                            type="button"
                            className="h-14 w-full rounded-none border-black bg-black text-white hover:border-white hover:bg-white hover:text-black"
                            opensBookingModal
                            bookingSource="multi_camera_final"
                            bookingAnalytics={{
                                analyticsEvent: 'multi_camera_booking_cta_clicked',
                                analyticsPayload,
                            }}
                        >
                            <CalendarDays className="size-5" />
                            {copy.book}
                        </BookingCtaButton>
                        <Button size="xl" className="h-14 w-full rounded-none border border-[#25D366] bg-[#25D366] text-white hover:bg-[#1ebe5d] hover:text-white" asChild>
                            <a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={() => trackWhatsApp('final')}>
                                <MessageCircle className="size-5" />
                                {copy.whatsapp}
                            </a>
                        </Button>
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}

function HeroBackground({ video }: { video: MultiCameraVideo | null }) {
    const videoRef = useRef<HTMLVideoElement>(null);

    useEffect(() => {
        if (!videoRef.current || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        void videoRef.current.play().catch(() => undefined);
    }, [video?.src]);

    return (
        <div className="absolute inset-0">
            {video ? (
                <video
                    ref={videoRef}
                    src={video.src}
                    poster={video.poster ?? undefined}
                    muted
                    loop
                    playsInline
                    preload="metadata"
                    className="h-full w-full object-cover opacity-55"
                    aria-hidden
                    tabIndex={-1}
                />
            ) : (
                <img src="/images/og/multicamara.jpg" alt="" className="h-full w-full object-cover opacity-55" />
            )}
            <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,6,7,0.98)_0%,rgba(5,6,7,0.78)_52%,rgba(5,6,7,0.26)_100%)]" />
            <div className="absolute inset-0 bg-[linear-gradient(0deg,#050607_0%,transparent_56%,rgba(5,6,7,0.44)_100%)]" />
        </div>
    );
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

    return (
        <div className="mt-10">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div className="flex gap-2 overflow-x-auto pb-2" role="tablist" aria-label={copy.coverage}>
                    {coverages.map((item, index) => (
                        <button
                            key={item.id}
                            type="button"
                            role="tab"
                            aria-selected={coverageIndex === index}
                            onClick={() => selectCoverage(index)}
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

                <div className="mt-6 grid gap-5 md:grid-cols-[1fr_auto] md:items-end">
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

            {coverage.photos.length > 0 ? (
                <div className="mx-auto mt-6 max-w-6xl px-4 sm:px-6">
                    <p className="mb-3 font-mono text-[9px] uppercase tracking-[0.18em] text-white/45">{copy.photosLabel}</p>
                    <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        {coverage.photos.slice(0, 4).map((photo, index) => (
                            <button
                                key={photo.id}
                                type="button"
                                data-lightbox-trigger="true"
                                className="group relative min-h-11 overflow-hidden bg-black focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                aria-label={`${copy.photosLabel} ${index + 1}`}
                            >
                                <img
                                    src={photo.src}
                                    alt=""
                                    loading="lazy"
                                    className="aspect-[4/3] h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.025]"
                                />
                                <span className="absolute bottom-2 right-2 bg-black/70 px-2 py-1 font-mono text-[8px] uppercase tracking-[0.14em] text-white/70">
                                    {String(index + 1).padStart(2, '0')}
                                </span>
                            </button>
                        ))}
                    </div>
                </div>
            ) : null}
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
    const videoRef = useRef<HTMLVideoElement>(null);
    const shellRef = useRef<HTMLDivElement>(null);
    const milestonesRef = useRef<Set<number>>(new Set());
    const startedRef = useRef(false);
    const activeVideo = videos[activeIndex];

    useEffect(() => {
        setActiveIndex(0);
    }, [videos]);

    useEffect(() => {
        const video = videoRef.current;

        setCurrentTime(0);
        setDuration(0);
        setPlaying(false);
        milestonesRef.current.clear();
        startedRef.current = false;

        if (!video || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        video.muted = true;
        setMuted(true);
        void video.play().then(() => setPlaying(true)).catch(() => undefined);
    }, [activeIndex, activeVideo?.src]);

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

        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && videos.length > 1) {
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
                    aria-label={`${copy.coverage} ${coverageId}, ${activeIndex + 1} / ${videos.length}`}
                />

                <div className="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black via-black/60 to-transparent px-4 pb-4 pt-24 text-white sm:px-6">
                    <div className="pointer-events-auto">
                        <div className="mb-4 flex items-end justify-between gap-5">
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
                            <div className="mt-4 flex items-center gap-2 overflow-x-auto pb-1" aria-label={copy.goToVideo}>
                                {videos.map((video, index) => (
                                    <button
                                        key={video.id}
                                        type="button"
                                        onClick={() => goTo(index, 'index')}
                                        aria-label={`${copy.goToVideo} ${index + 1}`}
                                        aria-current={index === activeIndex ? 'true' : undefined}
                                        className={`h-1.5 min-w-8 flex-1 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${
                                            index === activeIndex ? 'bg-primary' : 'bg-white/25 hover:bg-white/55'
                                        }`}
                                    />
                                ))}
                            </div>
                        ) : null}
                    </div>
                </div>
            </div>
        </div>
    );
}

function SectionHeading({
    eyebrow,
    title,
    description,
    inverse = false,
}: {
    eyebrow: string;
    title: string;
    description?: string;
    inverse?: boolean;
}) {
    return (
        <div>
            <p className="alpha-kicker text-primary">{eyebrow}</p>
            <h2 className={`mt-4 max-w-4xl font-display text-4xl font-bold leading-[0.94] tracking-tight md:text-6xl ${inverse ? 'text-white' : 'text-foreground'}`}>
                {title}
            </h2>
            {description ? (
                <p className={`mt-5 max-w-2xl text-sm leading-relaxed md:text-base ${inverse ? 'text-white/60' : 'text-muted-foreground'}`}>
                    {description}
                </p>
            ) : null}
        </div>
    );
}

function GearList({ title, items, icon }: { title: string; items: readonly string[]; icon: ReactNode }) {
    return (
        <div>
            <h3 className="flex items-center gap-3 border-b border-border/70 pb-4 font-display text-2xl font-bold text-foreground">
                <span className="text-primary">{icon}</span>
                {title}
            </h3>
            <ul className="divide-y divide-border/70">
                {items.map((item) => (
                    <li key={item} className="flex min-h-12 items-center justify-between gap-3 py-3 text-sm text-foreground">
                        <span>{item}</span>
                        <span className="font-mono text-[10px] uppercase tracking-[0.16em] text-muted-foreground">1 ×</span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

function Faq({ question, answer }: { question: string; answer: string }) {
    return (
        <details className="group">
            <summary className="flex min-h-14 cursor-pointer list-none items-center justify-between gap-6 py-4 font-display text-xl font-bold text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary [&::-webkit-details-marker]:hidden">
                {question}
                <span className="font-mono text-lg text-primary transition-transform group-open:rotate-45" aria-hidden>+</span>
            </summary>
            <p className="max-w-3xl pb-5 pr-10 text-sm leading-relaxed text-muted-foreground">{answer}</p>
        </details>
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
