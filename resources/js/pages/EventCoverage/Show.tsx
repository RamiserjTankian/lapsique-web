import { useEffect, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Camera,
    Check,
    Drone,
    Film,
    MapPin,
    MessageCircle,
} from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget, type BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { formatMxn } from '@/lib/utils';
import type { BookingSlot, PageProps, PortfolioItemData, ReelLibraryEntry } from '@/types';

interface EventCoverageShowProps {
    price: number;
    slots: BookingSlot[];
    portfolioItems: PortfolioItemData[];
    eventReels: ReelLibraryEntry[];
    errors?: Record<string, string>;
}

const HERO_REEL = {
    src: '/videos/reels/2026-07-11-mtrx-dumas-a0794b89f7.mp4',
    poster: '/images/portfolio/video-posters/2026-07-11-mtrx-dumas-a0794b89f7.jpg',
};

const AERIAL_REEL = {
    src: '/videos/drone-sessions/djset.mp4',
    poster: '/images/drone-sessions/djset.jpg',
};

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
        proofTitle: 'La noche, sin imágenes de stock.',
        proofDescription: 'MTRX y Karen Echev: material real de pista, cabina, artistas y público. Estos extractos muestran cómo documentamos una fecha; el aftermovie final suma una narrativa completa del evento.',
        reelLabel: 'Reel de cobertura',
        galleryEyebrow: 'Foto editorial',
        galleryTitle: 'Artistas, cabina, luz y público.',
        galleryDescription: 'La cobertura busca contar el evento desde dentro: contexto, detalles y el momento que la gente se lleva consigo.',
        packageEyebrow: 'Una cobertura clara',
        packageTitle: 'Todo lo esencial para volver a vivir y comunicar la fecha.',
        packageDescription: 'Antes del evento alineamos horario, acceso, lineup y los momentos que importan. El dron se confirma por venue, clima y normativa.',
        includes: [
            ['1 aftermovie editado', 'Una pieza final que concentra la energía y los momentos clave del evento.'],
            ['30 fotos editadas', 'Una selección desde distintos ángulos de artistas, cabina, público, producción y atmósfera.'],
            ['Tomas de dron viables', 'Contexto aéreo cuando la ubicación, el clima y la normativa lo permiten.'],
        ],
        aerialEyebrow: 'Perspectiva aérea',
        aerialTitle: 'Una capa más de contexto cuando el vuelo es viable.',
        aerialDescription: 'El dron ayuda a leer el venue, la llegada y la escala. Esta toma es una referencia genérica de vuelo para DJ set; no corresponde a MTRX ni a Karen Echev.',
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
        proofTitle: 'The night, without stock imagery.',
        proofDescription: 'MTRX and Karen Echev: real material from the dancefloor, booth, artists, and crowd. These excerpts show how we document a date; the final aftermovie adds the event’s complete narrative.',
        reelLabel: 'Coverage reel',
        galleryEyebrow: 'Editorial photography',
        galleryTitle: 'Artists, booth, light, and crowd.',
        galleryDescription: 'Coverage tells the event from inside it: context, details, and the moment people take away with them.',
        packageEyebrow: 'One clear coverage package',
        packageTitle: 'Everything essential to relive and communicate the date.',
        packageDescription: 'Before the event, we align schedule, access, lineup, and the moments that matter. Drone coverage is confirmed based on venue, weather, and regulations.',
        includes: [
            ['1 edited aftermovie', 'One final piece that concentrates the energy and key moments of the event.'],
            ['30 edited photos', 'A selection from different angles of artists, booth, crowd, production, and atmosphere.'],
            ['Viable drone footage', 'Aerial context when location, weather, and regulations allow it.'],
        ],
        aerialEyebrow: 'Aerial perspective',
        aerialTitle: 'Another layer of context when a flight is feasible.',
        aerialDescription: 'Drone footage helps read the venue, arrival, and scale. This is a generic DJ-set flight reference; it does not correspond to MTRX or Karen Echev.',
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
    eventReels,
    errors,
}: EventCoverageShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const copy = EVENT_COVERAGE_COPY[locale === 'en' ? 'en' : 'es'];
    const heroReel = eventReels[0] ?? HERO_REEL;
    const proofReels = eventReels.slice(1);
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
    const portfolioSectionRef = useSectionEvent<HTMLElement>(
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

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#050506] text-white">
                <div className="absolute inset-0">
                    <AutoplayVideo
                        src={heroReel.src}
                        poster={heroReel.poster}
                        title={heroReel.title ?? 'MTRX event coverage reel'}
                        eager
                        pauseWhenOffscreen={false}
                        className="h-full w-full"
                        videoClassName="object-cover object-center opacity-80"
                    />
                    <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,5,6,0.97)_0%,rgba(5,5,6,0.76)_47%,rgba(5,5,6,0.20)_100%)]" />
                    <div className="absolute inset-0 bg-[linear-gradient(0deg,#050506_0%,transparent_48%,rgba(5,5,6,0.38)_100%)]" />
                </div>

                <div className="relative mx-auto grid min-h-[min(820px,90svh)] max-w-6xl content-end gap-10 px-4 pb-14 pt-28 sm:px-6 lg:grid-cols-[minmax(0,1fr)_330px] lg:items-end lg:pb-20">
                    <div className="max-w-4xl">
                        <p className="alpha-kicker text-primary">{copy.eyebrow}</p>
                        <h1 className="mt-5 max-w-4xl font-display text-5xl font-bold leading-[0.9] tracking-tight text-white drop-shadow-[0_12px_42px_rgba(0,0,0,0.72)] sm:text-6xl md:text-8xl">
                            {copy.heroTitle}
                        </h1>
                        <p className="mt-6 max-w-2xl text-base leading-relaxed text-white/80 sm:text-lg md:text-xl">
                            {copy.heroDescription}
                        </p>
                        <p className="mt-4 flex items-center gap-2 text-sm text-white/70">
                            <MapPin className="size-4 text-primary" aria-hidden />
                            {copy.location}
                        </p>

                        <div className="mt-7 flex flex-wrap gap-2">
                            <SpecBadge highlight className="border-primary/40 bg-black/50 text-primary"><Film className="size-3.5" /> Aftermovie</SpecBadge>
                            <SpecBadge className="border-white/20 bg-black/45 text-white"><Camera className="size-3.5" /> 30 {locale === 'en' ? 'edited photos' : 'fotos editadas'}</SpecBadge>
                            <SpecBadge className="border-white/20 bg-black/45 text-white"><Drone className="size-3.5" /> {locale === 'en' ? 'Drone footage*' : 'Tomas de dron*'}</SpecBadge>
                        </div>
                    </div>

                    <div className="border border-white/20 bg-black/55 p-5 shadow-2xl backdrop-blur-md">
                        <p className="alpha-kicker text-primary">{copy.priceLabel}</p>
                        <p className="mt-3 font-mono-tabular text-5xl font-bold tracking-tight text-white">{formatMxn(price)}</p>
                        <p className="mt-2 text-sm leading-relaxed text-white/65">{copy.priceNote}</p>
                        <PaymentTrustOrTestMode variant="stripe" layout="compact" onDark className="mt-4" />
                        <div className="mt-6 grid gap-3">
                            <BookingCtaButton
                                type="button"
                                className="w-full rounded-none"
                                opensBookingModal
                                bookingSource="event_coverage_hero"
                                bookingAnalytics={{ analyticsEvent: 'electronic_event_coverage_booking_cta_clicked', analyticsPayload }}
                            >
                                <CalendarDays className="size-5" />
                                {copy.bookCta}
                            </BookingCtaButton>
                            <Button variant="default" size="xl" className="w-full rounded-none border border-[#25D366] bg-[#25D366] text-white hover:bg-[#1ebe5d] hover:text-white" asChild>
                                <a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={() => trackWhatsApp('hero')}>
                                    <MessageCircle className="size-5" />
                                    {copy.whatsappCta}
                                </a>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6 md:py-24">
                <SectionHeading eyebrow={copy.proofEyebrow} title={copy.proofTitle} description={copy.proofDescription} />
                <div className="mt-8 grid gap-3 md:grid-cols-[1.1fr_0.9fr]">
                    <figure className="group relative aspect-video overflow-hidden bg-black">
                        <AutoplayVideo src={heroReel.src} poster={heroReel.poster} title={heroReel.title} className="absolute inset-0 h-full w-full" videoClassName="transition duration-700 group-hover:scale-[1.02]" />
                        <figcaption className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent px-5 pb-5 pt-16 text-sm text-white/80">
                            <span className="alpha-kicker text-primary">{copy.reelLabel}</span>
                            <span className="mt-2 block font-medium">{heroReel.title ?? 'Dumas en MTRX'}</span>
                        </figcaption>
                    </figure>
                    <div className="grid grid-cols-2 gap-3">
                        {proofReels.map((reel) => <CoverageReel key={reel.id} reel={reel} label={copy.reelLabel} />)}
                        {proofReels.length < 2 ? <CoverageReel reel={eventReels[0] ?? HERO_REEL} label={copy.reelLabel} /> : null}
                    </div>
                </div>
            </section>

            <section ref={portfolioSectionRef} className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#101114] py-16 text-white md:py-24">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.62fr_1fr] lg:items-start">
                    <SectionHeading eyebrow={copy.galleryEyebrow} title={copy.galleryTitle} description={copy.galleryDescription} inverse />
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        {portfolioItems.slice(0, 6).map((item, index) => <CoveragePhoto key={item.id} item={item} priority={index < 2} />)}
                    </div>
                </div>
            </section>

            <section className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                <SectionHeading eyebrow={copy.packageEyebrow} title={copy.packageTitle} description={copy.packageDescription} />
                <div className="divide-y divide-border/70 border-y border-border/70">
                    {copy.includes.map(([title, description], index) => {
                        const Icon = [Film, Camera, Drone][index] ?? Check;

                        return (
                            <div key={title} className="grid gap-4 py-5 sm:grid-cols-[2.4rem_minmax(0,0.8fr)_1.2fr] sm:items-start">
                                <Icon className="size-5 text-primary" aria-hidden />
                                <h3 className="font-display text-2xl font-bold leading-none text-foreground">{title}</h3>
                                <p className="text-sm leading-relaxed text-muted-foreground">{description}</p>
                            </div>
                        );
                    })}
                    <p className="py-4 text-xs leading-relaxed text-muted-foreground">* {locale === 'en' ? 'Drone shots are subject to venue access, weather, regulations, and flight safety.' : 'Las tomas de dron dependen de acceso al venue, clima, normativa y seguridad de vuelo.'}</p>
                </div>
            </section>

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#070809] py-16 text-white md:py-24">
                <div className="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.86fr_1.14fr] lg:items-center">
                    <div className="relative aspect-video overflow-hidden border border-white/15 bg-black">
                        <AutoplayVideo src={AERIAL_REEL.src} poster={AERIAL_REEL.poster} title="Generic aerial DJ set reference" className="absolute inset-0 h-full w-full" />
                        <span className="absolute left-4 top-4 bg-black/70 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-white/80">
                            {locale === 'en' ? 'DJ set / aerial reference' : 'DJ set / referencia aérea'}
                        </span>
                    </div>
                    <SectionHeading eyebrow={copy.aerialEyebrow} title={copy.aerialTitle} description={copy.aerialDescription} inverse />
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6 md:py-24">
                <p className="alpha-kicker text-primary">{copy.processEyebrow}</p>
                <div className="mt-6 grid gap-5 border-t border-border/70 pt-5 md:grid-cols-3">
                    {copy.process.map(([number, title, description]) => (
                        <article key={number} className="border-t border-border/70 pt-4 md:border-t-0">
                            <p className="font-mono text-sm text-primary">{number}</p>
                            <h2 className="mt-4 font-display text-2xl font-bold text-foreground">{title}</h2>
                            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{description}</p>
                        </article>
                    ))}
                </div>
            </section>

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                className="mx-auto max-w-6xl"
                checkoutRoute="electronic-event-coverage.checkout"
                paymentProvider="stripe"
                product={bookingProduct}
                popupVariant="eventCoverage"
                popupPortfolioItems={portfolioItems}
                popupHeroProofVideo={{
                    title: heroReel.title ?? null,
                    media_type: 'video',
                    embed_url: null,
                    playback_url: heroReel.src,
                    poster_url: heroReel.poster ?? null,
                }}
                highlight
                analyticsPayload={analyticsPayload}
                analyticsOpenEvent="electronic_event_coverage_booking_opened"
            />

            <section className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-[0.72fr_1.28fr]">
                <div><p className="alpha-kicker text-primary">{copy.faqEyebrow}</p></div>
                <div className="divide-y divide-border/70 border-y border-border/70">
                    {copy.faqs.map(([question, answer]) => <Faq key={question} question={question} answer={answer} />)}
                </div>
            </section>

            <section className="relative left-1/2 mb-0 w-screen -translate-x-1/2 overflow-hidden bg-primary py-14 text-white md:py-20">
                <div className="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div className="max-w-3xl">
                        <h2 className="font-display text-4xl font-bold leading-[0.94] tracking-tight md:text-6xl">{copy.finalTitle}</h2>
                        <p className="mt-5 text-base leading-relaxed text-white/84 md:text-lg">{copy.finalDescription}</p>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 lg:min-w-[440px]">
                        <BookingCtaButton type="button" variant="glass" className="w-full rounded-none border-white/40 bg-black/15 text-white hover:bg-white hover:text-black" opensBookingModal bookingSource="event_coverage_final" bookingAnalytics={{ analyticsEvent: 'electronic_event_coverage_booking_cta_clicked', analyticsPayload }}>
                            <CalendarDays className="size-5" />
                            {copy.bookCta}
                        </BookingCtaButton>
                        <Button variant="default" size="xl" className="w-full rounded-none border border-[#25D366] bg-[#25D366] text-white hover:bg-[#1ebe5d] hover:text-white" asChild>
                            <a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={() => trackWhatsApp('final')}><MessageCircle className="size-5" />{copy.whatsappCta}</a>
                        </Button>
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}

function SectionHeading({ eyebrow, title, description, inverse = false }: { eyebrow: string; title: string; description: string; inverse?: boolean }) {
    return (
        <div>
            <p className="alpha-kicker text-primary">{eyebrow}</p>
            <h2 className={`mt-4 font-display text-4xl font-bold leading-[0.94] tracking-tight md:text-5xl ${inverse ? 'text-white' : 'text-foreground'}`}>{title}</h2>
            <p className={`mt-5 max-w-2xl text-sm leading-relaxed md:text-base ${inverse ? 'text-white/65' : 'text-muted-foreground'}`}>{description}</p>
        </div>
    );
}

function CoverageReel({ reel, label }: { reel: ReelLibraryEntry; label: string }) {
    return (
        <figure className="group relative aspect-[9/16] overflow-hidden bg-black">
            <AutoplayVideo src={reel.src} poster={reel.poster} title={reel.title} className="absolute inset-0 h-full w-full" videoClassName="transition duration-700 group-hover:scale-[1.03]" />
            <figcaption className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent px-4 pb-4 pt-12 text-xs text-white/80">
                <span className="font-mono uppercase tracking-[0.12em] text-primary">{label}</span>
                <span className="mt-1 block">{reel.title}</span>
            </figcaption>
        </figure>
    );
}

function CoveragePhoto({ item, priority }: { item: PortfolioItemData; priority: boolean }) {
    const image = item.asset_url ?? item.poster_url;

    if (!image) return null;

    return (
        <figure className={`group relative overflow-hidden bg-black ${item.orientation === 'vertical' ? 'row-span-2 aspect-[3/4]' : 'aspect-[4/3]'}`}>
            <img src={image} alt={item.caption ?? item.title ?? 'Electronic event coverage by Lapsique Media'} loading={priority ? 'eager' : 'lazy'} className="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]" />
            <figcaption className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent px-3 pb-3 pt-12 text-xs text-white/75">{item.title}</figcaption>
        </figure>
    );
}

function Faq({ question, answer }: { question: string; answer: string }) {
    return (
        <details className="group py-5">
            <summary className="flex cursor-pointer list-none items-start justify-between gap-6 font-display text-xl font-bold text-foreground marker:hidden">
                {question}
                <ArrowRight className="mt-1 size-5 shrink-0 text-primary transition group-open:rotate-90" aria-hidden />
            </summary>
            <p className="mt-4 max-w-2xl text-sm leading-relaxed text-muted-foreground">{answer}</p>
        </details>
    );
}

function buildWhatsAppHref(number: string | null | undefined, message: string): string {
    if (!number) return '#agenda';

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
