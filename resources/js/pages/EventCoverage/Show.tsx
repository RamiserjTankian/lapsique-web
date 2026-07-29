import { useEffect, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import {
    CalendarDays,
} from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget, type BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import { SeoHead } from '@/components/lapsique/SeoHead';
import {
    ServiceFunnelDeliverables,
    ServiceFunnelFaq,
    ServiceFunnelFinalCta,
    ServiceFunnelHeading,
    ServiceFunnelHero,
    ServiceFunnelProcess,
    ServiceFunnelSection,
    ServicePortfolioShowcase,
    ServiceProofBand,
    ServiceWhatsAppButton,
    serviceFunnelPrimaryActionClass,
} from '@/components/lapsique/ServiceFunnel';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import type {
    BookingSlot,
    PageProps,
    PortfolioItemData,
    ServicePortfolioBundle,
    ServicePortfolioMedia,
} from '@/types';

interface EventCoverageShowProps {
    price: number;
    slots: BookingSlot[];
    portfolioItems: PortfolioItemData[];
    servicePortfolio: ServicePortfolioBundle;
    errors?: Record<string, string>;
}

const EVENT_COVERAGE_COPY = {
    es: {
        eyebrow: 'Lapsique Media / Riviera Maya',
        heroTitle: 'Cobertura para eventos de música electrónica.',
        heroDescription: 'Una mirada editorial a la pista, los artistas, la producción y la energía que hizo única tu fecha.',
        location: 'Playa del Carmen · Tulum · Cancún · Riviera Maya',
        priceLabel: 'Cobertura base',
        priceNote: 'Precio fijo · agenda online · pago seguro con tarjeta.',
        bookCta: 'Apartar fecha',
        whatsappCta: 'Consultar por WhatsApp',
        proofEyebrow: 'Coberturas reales',
        proofTitle: 'Fechas distintas. Una cobertura que se reconoce como tuya.',
        proofDescription: 'MTRX, Vatos Locos, Satoshi Tomiie, Pérgola, Proper y Traumer muestran el alcance real: artistas, venue, cabina, público y cierre en una misma narrativa.',
        reelLabel: 'Reel de cobertura',
        galleryEyebrow: 'Foto editorial',
        galleryTitle: 'Proyectos completos, no una colección de tomas sueltas.',
        galleryDescription: 'Cada bloque reúne fotografía y video de una fecha real para que puedas revisar variedad de artistas, venues y públicos antes de reservar.',
        portfolioCta: 'Apartar una fecha como estas',
        storyEyebrow: 'El relato de la noche',
        storyTitle: 'Cinco momentos construyen una cobertura que sí vuelve a sentirse.',
        storyDescription: 'No grabamos únicamente la cabina. La entrega sigue el recorrido que vive tu audiencia y conserva el contexto necesario para volver a comunicar el evento.',
        story: [
            ['01', 'Llegada', 'Ubicación, accesos y primeros detalles que presentan la fecha.'],
            ['02', 'Venue', 'Arquitectura, producción, luces y escala antes del punto más alto.'],
            ['03', 'Cabina', 'Artistas, mezcla y momentos que identifican el sonido del evento.'],
            ['04', 'Público', 'Reacción, energía y encuentros que hacen visible la comunidad.'],
            ['05', 'Cierre', 'El plano final que convierte la noche en una pieza con memoria.'],
        ],
        packageEyebrow: 'Una cobertura clara',
        packageTitle: 'Todo lo esencial para volver a vivir y comunicar la fecha.',
        packageDescription: 'Antes del evento alineamos horario, acceso, lineup y los momentos que importan. El dron se confirma por venue, clima y normativa.',
        includes: [
            ['1 aftermovie editado', 'Una pieza final que concentra la energía y los momentos clave del evento.'],
            ['30 fotos editadas', 'Una selección desde distintos ángulos de artistas, cabina, público, producción y atmósfera.'],
            ['Tomas de dron viables', 'Contexto aéreo cuando la ubicación, el clima y la normativa lo permiten.'],
        ],
        processEyebrow: 'Cómo trabajamos',
        process: [
            ['01', 'Compartes la fecha', 'Venue, horario, lineup y los momentos que no pueden faltar.'],
            ['02', 'Alineamos la cobertura', 'Definimos accesos, puntos de cámara, dirección y viabilidad del dron.'],
            ['03', 'Documentamos y editamos', 'Cubrir, seleccionar y entregar una historia visual pensada para volver a circular.'],
        ],
        faqEyebrow: 'Preguntas directas',
        faqs: [
            ['¿Qué incluye la cobertura de evento?', 'La cobertura base incluye un aftermovie editado, 30 fotografías editadas desde distintos ángulos y tomas de dron cuando la ubicación, el clima y la normativa lo permiten.'],
            ['¿Cuánto cuesta la cobertura?', 'La cobertura base tiene un precio fijo de $4,500 MXN. Si el evento requiere horario extendido, más entregables o logística especial, lo cotizamos antes de reservar.'],
            ['¿Cubren eventos en Playa del Carmen, Tulum y Cancún?', 'Sí. Trabajamos en Playa del Carmen, Tulum, Cancún y Riviera Maya. Confirma tu venue y horario para revisar la cobertura.'],
            ['¿El aftermovie funciona para redes sociales?', 'Sí. Editamos la pieza para comunicar la energía del evento y entregarla lista para publicar en los canales acordados.'],
        ],
        finalTitle: '¿Tu próxima fecha merece volver a sentirse?',
        finalDescription: 'Aparta la cobertura base o escríbenos con la fecha, el venue y el lineup para revisar los detalles.',
        booking: {
            checkoutLabel: 'Reservar cobertura',
            headerTitle: 'Aparta la cobertura de tu evento',
            headerDescription: 'Elige la fecha y horario disponibles. Confirmamos venue, accesos y plan de cobertura antes de la producción.',
            summaryTitle: 'Cobertura de evento electrónico',
            summaryDescription: 'Aftermovie, fotografía editorial y tomas de dron viables para comunicar tu fecha.',
            cartService: 'Cobertura de evento electrónico',
            cartDuration: 'Cobertura base',
            perks: ['1 aftermovie editado', '30 fotografías editadas desde distintos ángulos', 'Tomas de dron sujetas a viabilidad', 'Planeación previa con producción', 'Pago seguro con tarjeta'],
            terms: ['La fecha queda sujeta a disponibilidad del calendario.', 'El venue, acceso, horario y momentos clave se confirman antes de producir.', 'Las tomas de dron dependen de clima, venue, normativa y seguridad.', 'Cambios de fecha se coordinan con anticipación según disponibilidad.', 'Podemos incluir material en el portafolio de Lapsique Media con previa coordinación.'],
            paymentCopy: 'Pago seguro con tarjeta procesado por Stripe.',
            unavailableWhatsApp: 'Hola, quiero consultar disponibilidad para cobertura de un evento de música electrónica.',
        },
    },
    en: {
        eyebrow: 'Lapsique Media / Riviera Maya',
        heroTitle: 'Coverage for electronic music events.',
        heroDescription: 'An editorial view of the dancefloor, artists, production, and the energy that made your date unique.',
        location: 'Playa del Carmen · Tulum · Cancun · Riviera Maya',
        priceLabel: 'Base coverage',
        priceNote: 'Fixed price · online booking · secure card payment.',
        bookCta: 'Reserve a date',
        whatsappCta: 'Ask on WhatsApp',
        proofEyebrow: 'Real coverage',
        proofTitle: 'Different dates. Coverage that still feels unmistakably yours.',
        proofDescription: 'MTRX, Vatos Locos, Satoshi Tomiie, Pérgola, Proper, and Traumer show the real range: artists, venue, booth, crowd, and closing within one narrative.',
        reelLabel: 'Coverage reel',
        galleryEyebrow: 'Editorial photography',
        galleryTitle: 'Complete projects, not a collection of disconnected shots.',
        galleryDescription: 'Each block brings together photography and video from a real date so you can review a range of artists, venues, and crowds before booking.',
        portfolioCta: 'Reserve a date like these',
        storyEyebrow: 'The story of the night',
        storyTitle: 'Five moments build coverage that can still be felt.',
        storyDescription: 'We do not record only the booth. The delivery follows your audience’s journey and keeps the context needed to communicate the event again.',
        story: [
            ['01', 'Arrival', 'Location, access, and first details that introduce the date.'],
            ['02', 'Venue', 'Architecture, production, lighting, and scale before the peak.'],
            ['03', 'Booth', 'Artists, mixing, and moments that identify the event’s sound.'],
            ['04', 'Crowd', 'Reaction, energy, and encounters that make the community visible.'],
            ['05', 'Closing', 'The final frame that turns the night into a piece with memory.'],
        ],
        packageEyebrow: 'One clear coverage package',
        packageTitle: 'Everything essential to relive and communicate the date.',
        packageDescription: 'Before the event, we align schedule, access, lineup, and the moments that matter. Drone coverage is confirmed based on venue, weather, and regulations.',
        includes: [
            ['1 edited aftermovie', 'One final piece that concentrates the energy and key moments of the event.'],
            ['30 edited photos', 'A selection from different angles of artists, booth, crowd, production, and atmosphere.'],
            ['Viable drone footage', 'Aerial context when location, weather, and regulations allow it.'],
        ],
        processEyebrow: 'How we work',
        process: [
            ['01', 'Share the date', 'Venue, schedule, lineup, and the moments that cannot be missed.'],
            ['02', 'Align the coverage', 'We define access, camera points, direction, and drone feasibility.'],
            ['03', 'Document and edit', 'Cover, select, and deliver a visual story designed to keep circulating.'],
        ],
        faqEyebrow: 'Straight answers',
        faqs: [
            ['What does event coverage include?', 'Base coverage includes one edited aftermovie, 30 photographs edited from different angles, and drone footage when location, weather, and regulations allow it.'],
            ['How much does coverage cost?', 'Base coverage has a fixed price of $4,500 MXN. If the event requires extended hours, more deliverables, or special logistics, we quote it before booking.'],
            ['Do you cover events in Playa del Carmen, Tulum, and Cancun?', 'Yes. We work in Playa del Carmen, Tulum, Cancun, and Riviera Maya. Share your venue and schedule so we can review coverage.'],
            ['Does the aftermovie work for social media?', 'Yes. We edit the piece to communicate the event energy and deliver it ready to publish on the agreed channels.'],
        ],
        finalTitle: 'Should your next date still feel alive afterwards?',
        finalDescription: 'Reserve base coverage or send us the date, venue, and lineup so we can review the details.',
        booking: {
            checkoutLabel: 'Reserve coverage',
            headerTitle: 'Reserve coverage for your event',
            headerDescription: 'Choose an available date and time. We confirm venue, access, and coverage plan before production.',
            summaryTitle: 'Electronic event coverage',
            summaryDescription: 'Aftermovie, editorial photography, and viable drone shots to communicate your date.',
            cartService: 'Electronic event coverage',
            cartDuration: 'Base coverage',
            perks: ['1 edited aftermovie', '30 photographs edited from different angles', 'Drone footage subject to feasibility', 'Pre-production planning', 'Secure card payment'],
            terms: ['The date is subject to calendar availability.', 'Venue, access, schedule, and key moments are confirmed before production.', 'Drone shots depend on weather, venue, regulations, and safety.', 'Date changes are coordinated in advance based on availability.', 'We may include material in the Lapsique Media portfolio with prior coordination.'],
            paymentCopy: 'Secure card payment processed by Stripe.',
            unavailableWhatsApp: 'Hi, I would like to check availability for electronic music event coverage.',
        },
    },
} as const;

export default function EventCoverageShow({
    price,
    slots,
    portfolioItems,
    servicePortfolio,
    errors,
}: EventCoverageShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const copy = EVENT_COVERAGE_COPY[locale === 'en' ? 'en' : 'es'];
    const heroMedia = servicePortfolio.hero;
    const whatsappHref = useMemo(
        () => buildWhatsAppHref(site.whatsapp, copy.booking.unavailableWhatsApp),
        [copy.booking.unavailableWhatsApp, site.whatsapp],
    );
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
    const analyticsPayload = useMemo(() => ({
        content_name: copy.heroTitle,
        content_category: 'electronic_event_coverage_booking',
        service_type: 'electronic_event_coverage',
        currency: 'MXN',
        value: price,
    }), [copy.heroTitle, price]);
    const portfolioSectionRef = useSectionEvent<HTMLDivElement>(
        'electronic_event_coverage_portfolio_engaged',
        { ...analyticsPayload, section: 'event_coverage_portfolio' },
    );

    useEffect(() => {
        trackBookingEvent('electronic_event_coverage_page_viewed', {
            ...analyticsPayload,
            section: 'event_coverage',
        });
    }, [analyticsPayload]);

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('electronic_event_coverage_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };
    const primaryAction = (source: string, label: string = copy.bookCta) => (
        <BookingCtaButton
            type="button"
            opensBookingModal
            bookingSource={`event_coverage_${source}`}
            bookingAnalytics={{
                analyticsEvent: 'electronic_event_coverage_booking_cta_clicked',
                analyticsPayload,
            }}
            className={serviceFunnelPrimaryActionClass}
        >
            <CalendarDays className="size-5" aria-hidden />
            {label}
        </BookingCtaButton>
    );
    const secondaryAction = (source: string) => (
        <ServiceWhatsAppButton
            href={whatsappHref}
            label={copy.whatsappCta}
            onClick={() => trackWhatsApp(source)}
        />
    );

    return (
        <SiteLayout>
            <SeoHead />

            <ServiceFunnelHero
                eyebrow={copy.eyebrow}
                title={copy.heroTitle}
                description={copy.heroDescription}
                locations={copy.location}
                price={price}
                priceLabel={copy.priceLabel}
                priceNote={copy.priceNote}
                primaryAction={primaryAction('hero')}
                secondaryAction={secondaryAction('hero')}
                media={<ServiceHeroMedia media={heroMedia} />}
                mediaLabel={copy.reelLabel}
                mediaCaption={heroMedia.projectLabel}
            />

            <ServiceFunnelSection innerClassName="py-0 sm:py-0 lg:py-0">
                <ServiceProofBand
                    portfolio={servicePortfolio}
                    eyebrow={copy.proofEyebrow}
                    title={copy.proofTitle}
                    description={copy.proofDescription}
                />
            </ServiceFunnelSection>

            <div ref={portfolioSectionRef}>
                <ServicePortfolioShowcase
                    portfolio={servicePortfolio}
                    eyebrow={copy.galleryEyebrow}
                    title={copy.galleryTitle}
                    description={copy.galleryDescription}
                    className="relative left-1/2 w-screen -translate-x-1/2"
                    action={(
                        <div className="grid w-full gap-3 sm:max-w-2xl sm:grid-cols-2">
                            {primaryAction('portfolio', copy.portfolioCta)}
                            {secondaryAction('portfolio')}
                        </div>
                    )}
                />
            </div>

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.storyEyebrow}
                    title={copy.storyTitle}
                    description={copy.storyDescription}
                />
                <ol className="mt-10 grid gap-x-5 gap-y-8 border-t border-border pt-6 sm:grid-cols-2 lg:grid-cols-5">
                    {copy.story.map(([number, title, description]) => (
                        <li key={number} className="grid grid-cols-[2rem_1fr] gap-3 lg:block">
                            <span className="font-mono text-xs tabular-nums text-primary" aria-hidden>{number}</span>
                            <div>
                                <h3 className="text-balance font-display text-2xl font-bold leading-[1.05] text-foreground lg:mt-6">
                                    {title}
                                </h3>
                                <p className="mt-3 text-pretty text-sm leading-[1.6] text-muted-foreground">
                                    {description}
                                </p>
                            </div>
                        </li>
                    ))}
                </ol>
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="dark">
                <div className="grid gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                    <ServiceFunnelHeading eyebrow={copy.packageEyebrow} title={copy.packageTitle} description={copy.packageDescription} inverse />
                    <div>
                        <ServiceFunnelDeliverables
                            items={copy.includes.map(([title, description]) => ({ title, description }))}
                            inverse
                        />
                        <p className="mt-6 text-xs leading-relaxed text-white/52">
                            * {locale === 'en'
                                ? 'Drone shots are subject to venue access, weather, regulations, and flight safety.'
                                : 'Las tomas de dron dependen de acceso al venue, clima, normativa y seguridad de vuelo.'}
                        </p>
                    </div>
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.processEyebrow}
                    title={locale === 'en' ? 'From date to final delivery.' : 'De la fecha a la entrega final.'}
                />
                <div className="mt-10">
                    <ServiceFunnelProcess
                        items={copy.process.map(([, title, description]) => ({ title, description }))}
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft">
                <ServiceFunnelHeading
                    eyebrow={copy.priceLabel}
                    title={copy.booking.headerTitle}
                    description={copy.booking.headerDescription}
                />
                <div className="mt-10">
                    <BookingWidget
                        slots={slots}
                        price={price}
                        whatsapp={site.whatsapp}
                        errors={errors}
                        checkoutRoute="electronic-event-coverage.checkout"
                        paymentProvider="stripe"
                        product={bookingProduct}
                        popupVariant="eventCoverage"
                        popupPortfolioItems={portfolioItems}
                        popupHeroProofVideo={heroMedia.kind === 'video' ? {
                            title: heroMedia.projectLabel,
                            media_type: 'video',
                            embed_url: null,
                            playback_url: heroMedia.src,
                            poster_url: heroMedia.poster ?? null,
                        } : null}
                        highlight
                        analyticsPayload={analyticsPayload}
                        analyticsOpenEvent="electronic_event_coverage_booking_opened"
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <div className="grid gap-10 lg:grid-cols-[0.72fr_1.28fr]">
                    <ServiceFunnelHeading eyebrow={copy.faqEyebrow} title={locale === 'en' ? 'Questions before booking' : 'Preguntas antes de reservar'} />
                    <ServiceFunnelFaq
                        items={copy.faqs.map(([question, answer]) => ({ question, answer }))}
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelFinalCta
                eyebrow={copy.eyebrow}
                title={copy.finalTitle}
                description={copy.finalDescription}
                primaryAction={primaryAction('final')}
                secondaryAction={secondaryAction('final')}
            />
        </SiteLayout>
    );
}

function ServiceHeroMedia({ media }: { media: ServicePortfolioMedia }) {
    return (
        media.kind === 'video' ? (
            <AutoplayVideo
                src={media.src}
                poster={media.poster}
                title={media.alt}
                eager
                pauseWhenOffscreen
            />
        ) : (
            <img
                src={media.src}
                alt={media.alt}
                fetchPriority="high"
                className="h-full w-full object-cover"
            />
        )
    );
}

function buildWhatsAppHref(number: string | null | undefined, message: string): string {
    if (!number) return '#agenda';

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
