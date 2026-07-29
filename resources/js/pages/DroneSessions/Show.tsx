import { useEffect, useMemo, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays } from 'lucide-react';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
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
import { DRONE_SESSION_BOOKING_CLIP } from '@/data/droneSessions';
import { localized, SERVICE_LANDING_CONFIGS } from '@/data/serviceLandingPages';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import SiteLayout from '@/layouts/SiteLayout';
import { getDroneSessionProduct } from '@/lib/bookingProducts';
import { openBookingModal } from '@/lib/openBookingModal';
import type {
    BookingSlot,
    PageProps,
    ServicePortfolioBundle,
    ServicePortfolioMedia,
} from '@/types';

interface DroneSessionsShowProps {
    price: number;
    slots: BookingSlot[];
    servicePortfolio: ServicePortfolioBundle;
    errors?: Record<string, string>;
}

type DroneCategory = 'hospitality' | 'yachts' | 'land' | 'events' | 'construction';

const CATEGORY_PROJECTS: Record<Exclude<DroneCategory, 'construction'>, string[]> = {
    hospitality: ['hospitality-property'],
    yachts: ['yacht-charter'],
    land: ['coast-and-lot'],
    events: ['electronic-event-aerial', 'dj-set-aerial'],
};

const COPY = {
    es: {
        analyticsName: 'Sesión de vuelo con dron',
        eyebrow: 'Producción aérea · Riviera Maya',
        title: 'Video y foto con dron para vender mejor tu espacio',
        description: 'Ubicamos el proyecto, mostramos su escala y conectamos arquitectura y entorno en una pieza clara para ventas.',
        locations: 'Cancún · Playa del Carmen · Tulum · Mérida',
        priceLabel: 'Sesión completa',
        priceNote: 'Una producción dirigida, entrega editada y reserva segura.',
        bookingCta: 'Reservar sesión de dron',
        whatsappCta: 'Cotizar por WhatsApp',
        proofEyebrow: 'Archivo aéreo real',
        proofTitle: 'Propiedades, mar y experiencias vistas en contexto',
        proofDescription: 'Explora vuelos entregados para hospitalidad, navegación, terrenos y eventos. La selección cambia según lo que necesitas vender.',
        portfolioEyebrow: 'Selecciona un tipo de proyecto',
        portfolioTitle: 'Cada vuelo responde a una pregunta comercial distinta',
        categories: {
            hospitality: {
                label: 'Propiedades y hospitality',
                title: 'Ubicación, fachada y amenidades en una sola lectura',
                description: 'Tomas para hoteles, villas y propiedades que necesitan explicar acceso, arquitectura, playa y valor de estancia.',
            },
            yachts: {
                label: 'Yates',
                title: 'La experiencia comienza antes de zarpar',
                description: 'Mostramos embarcación, marina, navegación y entorno para vender una experiencia premium completa.',
            },
            land: {
                label: 'Terrenos',
                title: 'Dimensión, accesos y relación con el entorno',
                description: 'Planos que ayudan a entender el predio y su ubicación sin depender de mapas o fotografías aisladas.',
            },
            events: {
                label: 'Eventos',
                title: 'El venue y su escala desde el aire',
                description: 'Aperturas que ubican la experiencia antes de entrar a pista, cabina o cobertura en tierra.',
            },
            construction: {
                label: 'Obra',
                title: 'El avance de obra tiene su propio archivo',
                description: 'La documentación recurrente necesita comparación por fecha, cámara en sitio y un recorrido consistente.',
            },
        },
        constructionLink: 'Ver avances de obra',
        visualEyebrow: 'Cómo se construye el vuelo',
        visualTitle: 'Tres ángulos que convierten el espacio en información útil',
        visualDescription: 'No volamos por acumular planos. Cada movimiento responde a una duda concreta de quien evalúa el lugar.',
        visualHeaders: ['Lo que debe entender', 'Cómo lo mostramos', 'Dónde aporta valor'],
        visualAngles: [
            ['Ubicación', 'Una apertura conecta el proyecto con costa, vialidad, marina o zona.', 'Hero web y presentación'],
            ['Escala', 'Una órbita o reveal incorpora una referencia humana, arquitectónica o natural.', 'Anuncios y listings'],
            ['Acceso', 'El recorrido sigue la llegada hasta la entrada o el punto principal.', 'Ventas y reservaciones'],
        ],
        packageEyebrow: 'Entrega comercial',
        packageTitle: 'Material listo para presentar, publicar o vender',
        packageDescription: 'Definimos primero dónde se usará el contenido y después trazamos el vuelo.',
        deliverables: [
            ['Ruta dirigida', 'Planeamos ubicación, orientación y movimientos a partir del objetivo comercial.'],
            ['Clips editados', 'Entregamos tomas verticales y horizontales seleccionadas, estabilizadas y colorizadas.'],
            ['Fotografías aéreas', 'Stills en alta resolución para listings, campañas, presentaciones y web.'],
            ['Archivo ordenado', 'Material identificado por proyecto y uso para que tu equipo encuentre cada pieza.'],
        ],
        processEyebrow: 'Del objetivo a la entrega',
        bookingEyebrow: 'Reserva',
        bookingTitle: 'Elige una fecha para volar',
        bookingDescription: 'Después de reservar revisamos ubicación, permisos, clima, seguridad y las tomas prioritarias.',
        faqEyebrow: 'Antes de volar',
        faqTitle: 'Preguntas sobre la sesión',
        finalEyebrow: 'Producción aérea',
        finalTitle: 'Muestra el lugar completo.',
        finalDescription: 'Reserva una sesión puntual o cuéntanos el proyecto para preparar una ruta de tomas.',
    },
    en: {
        analyticsName: 'Drone flight session',
        eyebrow: 'Aerial production · Riviera Maya',
        title: 'Drone video and photography that sells your space',
        description: 'We locate the project, show its scale, and connect architecture with its surroundings in a clear sales asset.',
        locations: 'Cancun · Playa del Carmen · Tulum · Merida',
        priceLabel: 'Complete session',
        priceNote: 'Directed production, edited delivery, and secure booking.',
        bookingCta: 'Book a drone session',
        whatsappCta: 'Quote on WhatsApp',
        proofEyebrow: 'Real aerial archive',
        proofTitle: 'Properties, sea, and experiences shown in context',
        proofDescription: 'Explore delivered flights for hospitality, navigation, lots, and events. The selection changes around what you need to sell.',
        portfolioEyebrow: 'Choose a project type',
        portfolioTitle: 'Each flight answers a different commercial question',
        categories: {
            hospitality: {
                label: 'Properties and hospitality',
                title: 'Location, facade, and amenities in one view',
                description: 'Footage for hotels, villas, and properties that need to explain access, architecture, beach, and stay value.',
            },
            yachts: {
                label: 'Yachts',
                title: 'The experience starts before departure',
                description: 'We show the vessel, marina, navigation, and surroundings to sell a complete premium experience.',
            },
            land: {
                label: 'Lots',
                title: 'Scale, access, and relationship to the surroundings',
                description: 'Shots that make the property and its location clear without relying on maps or isolated photographs.',
            },
            events: {
                label: 'Events',
                title: 'The venue and its scale from the air',
                description: 'Opening shots that establish the experience before moving into the dancefloor, booth, or ground coverage.',
            },
            construction: {
                label: 'Construction',
                title: 'Construction progress has its own archive',
                description: 'Recurring documentation needs date comparisons, on-site camera work, and a consistent route.',
            },
        },
        constructionLink: 'View construction progress',
        visualEyebrow: 'How the flight is built',
        visualTitle: 'Three angles that turn the space into useful information',
        visualDescription: 'We do not fly to collect random shots. Every movement answers a concrete question from someone evaluating the location.',
        visualHeaders: ['What must be understood', 'How we show it', 'Where it adds value'],
        visualAngles: [
            ['Location', 'An opening shot connects the project to the coast, road, marina, or district.', 'Web hero and presentation'],
            ['Scale', 'An orbit or reveal includes a human, architectural, or natural reference.', 'Ads and listings'],
            ['Access', 'The route follows arrival through the entrance or primary point.', 'Sales and bookings'],
        ],
        packageEyebrow: 'Commercial delivery',
        packageTitle: 'Material ready to present, publish, or sell',
        packageDescription: 'We define where the content will be used before mapping the flight.',
        deliverables: [
            ['Directed route', 'We plan location, orientation, and movement around the commercial objective.'],
            ['Edited clips', 'Selected, stabilized, and colorized vertical and horizontal shots.'],
            ['Aerial photography', 'High-resolution stills for listings, campaigns, presentations, and web.'],
            ['Organized archive', 'Material identified by project and use so your team can find every asset.'],
        ],
        processEyebrow: 'From objective to delivery',
        bookingEyebrow: 'Booking',
        bookingTitle: 'Choose a date to fly',
        bookingDescription: 'After booking, we review location, permits, weather, safety, and priority shots.',
        faqEyebrow: 'Before the flight',
        faqTitle: 'Session questions',
        finalEyebrow: 'Aerial production',
        finalTitle: 'Show the complete location.',
        finalDescription: 'Book a single session or tell us about the project so we can prepare a shot route.',
    },
} as const;

export default function DroneSessionsShow({
    price,
    slots,
    servicePortfolio,
    errors,
}: DroneSessionsShowProps) {
    const { site } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const language = locale === 'en' ? 'en' : 'es';
    const copy = COPY[language];
    const landingConfig = SERVICE_LANDING_CONFIGS.sesiones_de_dron;
    const [activeCategory, setActiveCategory] = useState<DroneCategory>('hospitality');
    const product = useMemo(() => getDroneSessionProduct(t), [t]);
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
    const inlinePortfolio = useMemo(
        () => excludeHeroFromPortfolio(servicePortfolio),
        [servicePortfolio],
    );
    const selectedPortfolio = useMemo(
        () => activeCategory === 'construction'
            ? null
            : filterPortfolio(inlinePortfolio, CATEGORY_PROJECTS[activeCategory]),
        [activeCategory, inlinePortfolio],
    );
    const bookingProofVideo = useMemo(
        () => selectDistinctBookingVideo(servicePortfolio, CATEGORY_PROJECTS.hospitality),
        [servicePortfolio],
    );
    const process = landingConfig.process.slice(0, 3).map((item) => ({
        title: localized(item.title, locale),
        description: localized(item.description, locale),
    }));
    const faqs = landingConfig.faqs.map((item) => ({
        question: localized(item.question, locale),
        answer: localized(item.answer, locale),
    }));
    const deliverables = copy.deliverables.map(([title, description]) => ({ title, description }));
    const activeCopy = copy.categories[activeCategory];

    useEffect(() => {
        trackBookingEvent('drone_session_page_viewed', {
            ...analyticsPayload,
            section: 'drone_sessions',
            locations: copy.locations,
        });
    }, [analyticsPayload, copy.locations]);

    const openBooking = (source: string) => {
        openBookingModal({
            source,
            analyticsEvent: 'drone_session_booking_cta_clicked',
            analyticsPayload: { ...analyticsPayload, source },
        });
    };

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('drone_session_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };

    const selectCategory = (category: DroneCategory) => {
        setActiveCategory(category);
        trackBookingEvent('portfolio_project_selected', {
            service_type: 'drone_session',
            project_key: category,
            source: 'drone_use_case_selector',
        });
    };

    const primaryAction = (source: string) => (
        <BookingCtaButton
            type="button"
            className={serviceFunnelPrimaryActionClass}
            onClick={() => openBooking(source)}
        >
            <CalendarDays className="size-5" aria-hidden />
            {copy.bookingCta}
            <ArrowRight className="size-4" aria-hidden />
        </BookingCtaButton>
    );
    const whatsappAction = (source: string) => (
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
                title={copy.title}
                description={copy.description}
                locations={copy.locations}
                price={price}
                priceLabel={copy.priceLabel}
                priceNote={copy.priceNote}
                media={<PortfolioHeroMedia media={servicePortfolio.hero} title={copy.title} />}
                mediaLabel={servicePortfolio.hero.projectLabel}
                mediaCaption={servicePortfolio.hero.location ?? servicePortfolio.hero.alt}
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

            <ServiceFunnelSection tone="dark">
                <ServiceFunnelHeading
                    eyebrow={copy.portfolioEyebrow}
                    title={copy.portfolioTitle}
                    description={copy.proofDescription}
                    inverse
                />

                <div className="mt-8 flex gap-2 overflow-x-auto pb-2" aria-label={copy.portfolioEyebrow}>
                    {(Object.keys(copy.categories) as DroneCategory[]).map((category) => {
                        const isActive = category === activeCategory;

                        return (
                            <button
                                key={category}
                                type="button"
                                className={[
                                    'min-h-11 shrink-0 border px-4 py-2 text-sm font-semibold transition-[background-color,border-color,color,transform] duration-150 active:scale-[0.96] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary motion-reduce:transition-none',
                                    isActive
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-white/20 bg-white/[0.04] text-white hover:border-white/45 hover:bg-white/[0.08]',
                                ].join(' ')}
                                aria-pressed={isActive}
                                onClick={() => selectCategory(category)}
                            >
                                {copy.categories[category].label}
                            </button>
                        );
                    })}
                </div>
                <p className="sr-only" role="status">
                    {activeCopy.title}
                </p>

            </ServiceFunnelSection>

            {selectedPortfolio ? (
                <ServicePortfolioShowcase
                    portfolio={selectedPortfolio}
                    eyebrow={activeCopy.label}
                    title={activeCopy.title}
                    description={activeCopy.description}
                    action={primaryAction(`portfolio_${activeCategory}`)}
                />
            ) : (
                <ServiceFunnelSection tone="dark" innerClassName="pt-0 sm:pt-0 lg:pt-0">
                    <article className="grid gap-8 bg-white/[0.05] p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                        <div className="max-w-2xl">
                            <p className="font-mono text-[0.7rem] uppercase tracking-[0.2em] text-primary">
                                {activeCopy.label}
                            </p>
                            <h3 className="mt-4 text-balance font-display text-3xl font-bold leading-[1.05] text-white sm:text-4xl">
                                {activeCopy.title}
                            </h3>
                            <p className="mt-4 max-w-xl text-pretty text-base leading-[1.6] text-white/64">
                                {activeCopy.description}
                            </p>
                        </div>
                        <Link
                            href="/avances-de-obra"
                            className="inline-flex min-h-12 items-center justify-center gap-2 border border-primary bg-primary px-5 py-3 text-sm font-bold text-primary-foreground transition-[background-color,color,transform] duration-150 hover:bg-primary/88 active:scale-[0.96] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary motion-reduce:transition-none"
                        >
                            {copy.constructionLink}
                            <ArrowRight className="size-4" aria-hidden />
                        </Link>
                    </article>
                </ServiceFunnelSection>
            )}

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.visualEyebrow}
                    title={copy.visualTitle}
                    description={copy.visualDescription}
                />
                <div className="mt-10 border-y border-border">
                    <div className="hidden grid-cols-[minmax(0,0.62fr)_2rem_minmax(0,1.15fr)_2rem_minmax(0,0.75fr)] gap-4 border-b border-border py-3 md:grid">
                        <p className="font-mono text-[0.66rem] uppercase tracking-[0.16em] text-muted-foreground">
                            {copy.visualHeaders[0]}
                        </p>
                        <span aria-hidden />
                        <p className="font-mono text-[0.66rem] uppercase tracking-[0.16em] text-muted-foreground">
                            {copy.visualHeaders[1]}
                        </p>
                        <span aria-hidden />
                        <p className="font-mono text-[0.66rem] uppercase tracking-[0.16em] text-muted-foreground">
                            {copy.visualHeaders[2]}
                        </p>
                    </div>
                    <ol className="divide-y divide-border">
                        {copy.visualAngles.map(([subject, shot, use], index) => (
                            <li
                                key={subject}
                                className="grid gap-3 py-6 md:grid-cols-[minmax(0,0.62fr)_2rem_minmax(0,1.15fr)_2rem_minmax(0,0.75fr)] md:items-center md:gap-4"
                            >
                                <div>
                                    <p className="font-mono text-[0.65rem] tabular-nums text-primary">
                                        {String(index + 1).padStart(2, '0')}
                                    </p>
                                    <h3 className="mt-2 text-balance font-display text-2xl font-bold leading-[1.05]">
                                        {subject}
                                    </h3>
                                </div>
                                <ArrowRight className="size-5 rotate-90 text-primary md:rotate-0" aria-hidden />
                                <p className="max-w-xl text-pretty text-sm leading-[1.65] text-muted-foreground">
                                    {shot}
                                </p>
                                <ArrowRight className="size-5 rotate-90 text-primary md:rotate-0" aria-hidden />
                                <p className="text-pretty text-sm font-semibold leading-[1.5] text-foreground">
                                    {use}
                                </p>
                            </li>
                        ))}
                    </ol>
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.packageEyebrow}
                    title={copy.packageTitle}
                    description={copy.packageDescription}
                />
                <div className="mt-10">
                    <ServiceFunnelDeliverables items={deliverables} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft">
                <ServiceFunnelHeading
                    eyebrow={copy.processEyebrow}
                    title={localized(landingConfig.solutionTitle, locale)}
                    description={localized(landingConfig.solution[0], locale)}
                />
                <div className="mt-10">
                    <ServiceFunnelProcess items={process} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection id="reservar">
                <ServiceFunnelHeading
                    eyebrow={copy.bookingEyebrow}
                    title={copy.bookingTitle}
                    description={copy.bookingDescription}
                />
                <BookingWidget
                    slots={slots}
                    price={price}
                    whatsapp={site.whatsapp}
                    errors={errors}
                    className="mt-10"
                    checkoutRoute="drone-sessions.checkout"
                    paymentProvider="stripe"
                    product={product}
                    popupVariant="drone"
                    popupHeroProofVideo={{
                        title: bookingProofVideo?.alt ?? copy.title,
                        media_type: 'video',
                        embed_url: null,
                        playback_url: bookingProofVideo?.src ?? DRONE_SESSION_BOOKING_CLIP.src,
                        poster_url: bookingProofVideo?.poster ?? DRONE_SESSION_BOOKING_CLIP.poster,
                    }}
                    highlight
                    analyticsPayload={analyticsPayload}
                />
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft">
                <ServiceFunnelHeading
                    eyebrow={copy.faqEyebrow}
                    title={copy.faqTitle}
                />
                <div className="mt-10">
                    <ServiceFunnelFaq items={faqs} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelFinalCta
                eyebrow={copy.finalEyebrow}
                title={copy.finalTitle}
                description={copy.finalDescription}
                primaryAction={primaryAction('final')}
                secondaryAction={whatsappAction('final')}
            />
        </SiteLayout>
    );
}

function PortfolioHeroMedia({
    media,
    title,
}: {
    media: ServicePortfolioMedia;
    title: string;
}) {
    if (media.kind === 'video') {
        return (
            <AutoplayVideo
                src={media.src}
                poster={media.poster}
                title={title}
                eager
                pauseWhenOffscreen={false}
                preload="metadata"
            />
        );
    }

    return (
        <img
            src={media.src}
            alt={media.alt}
            className="h-full w-full object-cover"
            loading="eager"
            fetchPriority="high"
        />
    );
}

function filterPortfolio(
    portfolio: ServicePortfolioBundle,
    projectKeys: string[],
): ServicePortfolioBundle {
    const projects = portfolio.projects.filter((project) => projectKeys.includes(project.key));
    const media = projects.flatMap((project) => project.media);

    return {
        ...portfolio,
        projects,
        stats: {
            mediaCount: media.length,
            projectCount: projects.length,
            imageCount: media.filter((item) => item.kind === 'image').length,
            videoCount: media.filter((item) => item.kind === 'video').length,
        },
    };
}

function excludeHeroFromPortfolio(
    portfolio: ServicePortfolioBundle,
): ServicePortfolioBundle {
    const projects = portfolio.projects
        .map((project) => ({
            ...project,
            media: project.media.filter(
                (item) => item.id !== portfolio.hero.id && item.src !== portfolio.hero.src,
            ),
        }))
        .filter((project) => project.media.length > 0);
    const media = projects.flatMap((project) => project.media);

    return {
        ...portfolio,
        projects,
        stats: {
            mediaCount: media.length,
            projectCount: projects.length,
            imageCount: media.filter((item) => item.kind === 'image').length,
            videoCount: media.filter((item) => item.kind === 'video').length,
        },
    };
}

function selectDistinctBookingVideo(
    portfolio: ServicePortfolioBundle,
    avoidedProjectKeys: string[],
): ServicePortfolioMedia | null {
    const videos = portfolio.projects
        .flatMap((project) => project.media)
        .filter(
            (item) => item.kind === 'video'
                && item.id !== portfolio.hero.id
                && item.src !== portfolio.hero.src,
        );

    return videos.find((item) => !avoidedProjectKeys.includes(item.projectKey))
        ?? videos[0]
        ?? null;
}

function buildWhatsAppHref(number: string, message: string): string {
    if (!number) {
        return '#';
    }

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
