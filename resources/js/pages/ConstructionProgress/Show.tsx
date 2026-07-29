import { useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays } from 'lucide-react';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { SeoHead } from '@/components/lapsique/SeoHead';
import {
    PortfolioMediaRail,
    ServiceFunnelDeliverables,
    ServiceFunnelFaq,
    ServiceFunnelFinalCta,
    ServiceFunnelHeading,
    ServiceFunnelHero,
    ServiceFunnelProcess,
    ServiceFunnelSection,
    ServiceProofBand,
    ServiceWhatsAppButton,
    serviceFunnelPrimaryActionClass,
} from '@/components/lapsique/ServiceFunnel';
import { DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS } from '@/data/droneSessions';
import { localized, SERVICE_LANDING_CONFIGS } from '@/data/serviceLandingPages';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import SiteLayout from '@/layouts/SiteLayout';
import { getConstructionProgressProduct } from '@/lib/bookingProducts';
import { openBookingModal } from '@/lib/openBookingModal';
import type {
    BookingSlot,
    PageProps,
    ServicePortfolioBundle,
    ServicePortfolioMedia,
} from '@/types';

interface ConstructionProgressShowProps {
    price: number;
    slots: BookingSlot[];
    servicePortfolio: ServicePortfolioBundle;
    errors?: Record<string, string>;
}

type ConstructionPhase = 'goba-current' | 'okom-nov-2024' | 'okom-jun-2026';

const COPY = {
    es: {
        analyticsName: 'Avance de obra con dron, foto y video',
        heroEyebrow: 'Documentación de obra · Riviera Maya',
        heroTitle: 'Avances de obra con dron, foto y video',
        heroDescription: 'Comparamos etapas con un recorrido visual consistente para que dirección, inversionistas y ventas entiendan el progreso.',
        priceLabel: 'Sesión de avance',
        priceNote: 'Visita documentada, archivo por fecha y reserva segura.',
        whatsappCta: 'Cotizar por WhatsApp',
        bookingCta: 'Reservar visita de obra',
        locations: 'Cancún · Playa del Carmen · Tulum',
        proofEyebrow: 'Archivo de obra real',
        proofTitle: 'Tres etapas, una lectura clara del avance',
        proofDescription: 'El portafolio combina tomas aéreas, cámara en sitio y fotografías organizadas por proyecto y fecha.',
        timelineEyebrow: 'Comparación cronológica',
        timelineTitle: 'Selecciona una visita y revisa qué cambió',
        timelineDescription: 'Cada fecha conserva contexto aéreo, actividad, estructura, instalaciones y amenidades sin mezclar proyectos.',
        phases: {
            'goba-current': {
                label: 'GOBA',
                date: 'Avance reciente',
                title: 'Escala, actividad y contexto del desarrollo',
                description: 'Vista aérea y cámara en sitio para comunicar estructura, cuadrillas y relación con el entorno.',
            },
            'okom-nov-2024': {
                label: 'OKOM',
                date: 'Noviembre 2024',
                title: 'Etapa temprana: planta, cimbra y estructura',
                description: 'La primera visita registra la base constructiva y crea un punto de comparación verificable.',
            },
            'okom-jun-2026': {
                label: 'OKOM',
                date: 'Junio 2026',
                title: 'Instalaciones, interiores y amenidades',
                description: 'La visita reciente muestra avances que una toma general no explica: servicios, circulaciones y acabados.',
            },
        },
        compareLabel: 'Dron + cámara en sitio',
        proofCta: 'Reservar seguimiento',
        visualEyebrow: 'Lectura por etapa',
        visualTitle: 'La cámara cambia de prioridad conforme avanza la obra',
        visualDescription: 'Repetimos las vistas que permiten comparar y acercamos la cámara cuando instalaciones y amenidades empiezan a definir el avance.',
        visualHeaders: ['Lectura', 'GOBA · reciente', 'OKOM · nov 2024', 'OKOM · jun 2026'],
        visualComparisons: [
            ['Aéreo', 'Volumen y entorno', 'Planta inicial', 'Contexto actual'],
            ['Exterior', 'Estructura', 'Cimbra y muros', 'Fachadas y circulaciones'],
            ['Actividad', 'Cuadrilla en losa', 'Colado y armado', 'Trabajo exterior'],
            ['Instalaciones', 'Detalle de obra', 'Preparación', 'Ductos y tuberías'],
            ['Amenidades', 'Contexto general', 'Sin priorizar', 'Alberca e impermeabilización'],
        ],
        deliverablesEyebrow: 'Entrega para reportes',
        deliverablesTitle: 'Una visita que tu equipo puede revisar y comparar',
        deliverablesDescription: 'El material llega organizado para dirección de obra, actualización comercial y archivo del proyecto.',
        deliverables: [
            ['Recorrido repetible', 'Conservamos puntos de referencia para que el cambio entre visitas sea legible.'],
            ['Dron y cámara', 'Combinamos contexto aéreo con actividad, detalle e instalaciones a nivel de obra.'],
            ['Fotografía editada', 'Stills en alta resolución para reportes, presentaciones y seguimiento interno.'],
            ['Archivo por fecha', 'Cada entrega queda identificada por proyecto, visita y etapa documentada.'],
        ],
        processEyebrow: 'Seguimiento recurrente',
        bookingEyebrow: 'Siguiente visita',
        bookingTitle: 'Agenda la próxima fecha de obra',
        bookingDescription: 'Después de reservar alineamos recorrido, hitos, accesos, responsables y tomas prioritarias.',
        faqEyebrow: 'Antes de documentar',
        faqTitle: 'Preguntas sobre el seguimiento',
        finalEyebrow: 'Avance de obra',
        finalTitle: 'Documenta el avance antes de que cambie.',
        finalDescription: 'Agenda una visita puntual o cotiza un calendario recurrente para tu proyecto.',
        finalPayCta: 'Agendar avance',
    },
    en: {
        analyticsName: 'Construction progress with drone, photo, and video',
        heroEyebrow: 'Construction documentation · Riviera Maya',
        heroTitle: 'Construction progress with drone, photo, and video',
        heroDescription: 'We compare stages through a consistent visual route so leadership, investors, and sales teams can understand progress.',
        priceLabel: 'Progress session',
        priceNote: 'Documented visit, date-based archive, and secure booking.',
        whatsappCta: 'Quote on WhatsApp',
        bookingCta: 'Book a construction visit',
        locations: 'Cancun · Playa del Carmen · Tulum',
        proofEyebrow: 'Real construction archive',
        proofTitle: 'Three stages, one clear view of progress',
        proofDescription: 'The portfolio combines aerial footage, on-site camera work, and photography organized by project and date.',
        timelineEyebrow: 'Chronological comparison',
        timelineTitle: 'Choose a visit and review what changed',
        timelineDescription: 'Each date preserves aerial context, activity, structure, installations, and amenities without mixing projects.',
        phases: {
            'goba-current': {
                label: 'GOBA',
                date: 'Recent progress',
                title: 'Scale, activity, and development context',
                description: 'Aerial and on-site views communicate structure, crews, and the relationship to the surroundings.',
            },
            'okom-nov-2024': {
                label: 'OKOM',
                date: 'November 2024',
                title: 'Early stage: footprint, formwork, and structure',
                description: 'The first visit records the construction base and creates a verifiable comparison point.',
            },
            'okom-jun-2026': {
                label: 'OKOM',
                date: 'June 2026',
                title: 'Installations, interiors, and amenities',
                description: 'The recent visit shows progress a wide shot cannot explain: services, circulation, and finishes.',
            },
        },
        compareLabel: 'Drone + on-site camera',
        proofCta: 'Book progress coverage',
        visualEyebrow: 'Stage-by-stage reading',
        visualTitle: 'Camera priorities change as construction progresses',
        visualDescription: 'We repeat the views that enable comparison and move closer when installations and amenities begin to define progress.',
        visualHeaders: ['View', 'GOBA · recent', 'OKOM · Nov 2024', 'OKOM · Jun 2026'],
        visualComparisons: [
            ['Aerial', 'Volume and surroundings', 'Initial footprint', 'Current context'],
            ['Exterior', 'Structure', 'Formwork and walls', 'Facades and circulation'],
            ['Activity', 'Crew on slab', 'Pour and reinforcement', 'Exterior work'],
            ['Installations', 'Construction detail', 'Preparation', 'Ducts and pipes'],
            ['Amenities', 'General context', 'Not prioritized', 'Pool and waterproofing'],
        ],
        deliverablesEyebrow: 'Report-ready delivery',
        deliverablesTitle: 'One visit your team can review and compare',
        deliverablesDescription: 'Material arrives organized for construction leadership, sales updates, and the project archive.',
        deliverables: [
            ['Repeatable route', 'We preserve reference points so change between visits remains legible.'],
            ['Drone and camera', 'We combine aerial context with on-site activity, details, and installations.'],
            ['Edited photography', 'High-resolution stills for reports, presentations, and internal tracking.'],
            ['Date-based archive', 'Each delivery is identified by project, visit, and documented stage.'],
        ],
        processEyebrow: 'Recurring tracking',
        bookingEyebrow: 'Next visit',
        bookingTitle: 'Schedule the next construction visit',
        bookingDescription: 'After booking, we align the route, milestones, access, contacts, and priority shots.',
        faqEyebrow: 'Before documentation',
        faqTitle: 'Progress coverage questions',
        finalEyebrow: 'Construction progress',
        finalTitle: 'Document progress before it changes.',
        finalDescription: 'Book a single visit or quote a recurring documentation calendar for your project.',
        finalPayCta: 'Book progress',
    },
} as const;

export default function ConstructionProgressShow({
    price,
    slots,
    servicePortfolio,
    errors,
}: ConstructionProgressShowProps) {
    const { site } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const language = locale === 'en' ? 'en' : 'es';
    const copy = COPY[language];
    const landingConfig = SERVICE_LANDING_CONFIGS.avances_de_obra;
    const [activePhase, setActivePhase] = useState<ConstructionPhase>('goba-current');
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
    const inlinePortfolio = useMemo(
        () => excludeHeroFromPortfolio(servicePortfolio),
        [servicePortfolio],
    );
    const activeProject = inlinePortfolio.projects.find((project) => project.key === activePhase);
    const bookingProofVideo = useMemo(
        () => selectDistinctBookingVideo(servicePortfolio, ['goba-current']),
        [servicePortfolio],
    );
    const phaseCopy = copy.phases[activePhase];
    const deliverables = copy.deliverables.map(([title, description]) => ({ title, description }));
    const process = landingConfig.process.slice(0, 3).map((item) => ({
        title: localized(item.title, locale),
        description: localized(item.description, locale),
    }));
    const faqs = landingConfig.faqs.map((item) => ({
        question: localized(item.question, locale),
        answer: localized(item.answer, locale),
    }));

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
            analyticsPayload: { ...analyticsPayload, source },
        });
    };

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('construction_progress_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };

    const selectPhase = (phase: ConstructionPhase) => {
        setActivePhase(phase);
        trackBookingEvent('portfolio_project_selected', {
            service_type: 'construction_progress',
            project_key: phase,
            source: 'construction_timeline',
        });
    };

    const primaryAction = (source: string, label: string = copy.bookingCta) => (
        <BookingCtaButton
            type="button"
            className={serviceFunnelPrimaryActionClass}
            onClick={() => openBooking(source)}
        >
            <CalendarDays className="size-5" aria-hidden />
            {label}
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
                eyebrow={copy.heroEyebrow}
                title={copy.heroTitle}
                description={copy.heroDescription}
                locations={copy.locations}
                price={price}
                priceLabel={copy.priceLabel}
                priceNote={copy.priceNote}
                media={<PortfolioHeroMedia media={servicePortfolio.hero} title={copy.heroTitle} />}
                mediaLabel={servicePortfolio.hero.projectLabel}
                mediaCaption={servicePortfolio.hero.sessionLabel ?? servicePortfolio.hero.alt}
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
                    eyebrow={copy.timelineEyebrow}
                    title={copy.timelineTitle}
                    description={copy.timelineDescription}
                    inverse
                />

                <div className="mt-8 grid gap-3 lg:grid-cols-3" aria-label={copy.timelineEyebrow}>
                    {(Object.keys(copy.phases) as ConstructionPhase[]).map((phase) => {
                        const item = copy.phases[phase];
                        const isActive = phase === activePhase;
                        const mediaCount = inlinePortfolio.projects.find((project) => project.key === phase)?.media.length ?? 0;

                        return (
                            <button
                                key={phase}
                                type="button"
                                className={[
                                    'min-h-28 p-5 text-start transition-[background-color,border-color,color,transform] duration-150 active:scale-[0.96] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary motion-reduce:transition-none',
                                    isActive
                                        ? 'border border-primary bg-primary text-primary-foreground'
                                        : 'border border-white/18 bg-white/[0.04] text-white hover:border-white/45 hover:bg-white/[0.08]',
                                ].join(' ')}
                                aria-pressed={isActive}
                                onClick={() => selectPhase(phase)}
                            >
                                <span className="flex items-center justify-between gap-4 font-mono text-[0.68rem] uppercase tracking-[0.18em]">
                                    <span>{item.label}</span>
                                    <span className={isActive ? 'text-primary-foreground/72' : 'text-primary'}>
                                        {mediaCount} {language === 'en' ? 'pieces' : 'piezas'}
                                    </span>
                                </span>
                                <span className="mt-5 block text-balance font-display text-2xl font-bold leading-[1.05]">
                                    {item.date}
                                </span>
                            </button>
                        );
                    })}
                </div>
                <p className="sr-only" role="status">
                    {phaseCopy.label} · {phaseCopy.date}: {phaseCopy.title}
                </p>

                <div className="mt-8 grid gap-6 bg-white/[0.04] p-5 sm:p-7 lg:grid-cols-[minmax(0,0.72fr)_minmax(0,1.28fr)] lg:items-start">
                    <div className="max-w-xl">
                        <p className="font-mono text-[0.7rem] uppercase tracking-[0.2em] text-primary">
                            {phaseCopy.label} · {phaseCopy.date}
                        </p>
                        <h3 className="mt-4 text-balance font-display text-3xl font-bold leading-[1.05] text-white sm:text-4xl">
                            {phaseCopy.title}
                        </h3>
                        <p className="mt-4 text-pretty text-base leading-[1.6] text-white/64">
                            {phaseCopy.description}
                        </p>
                        <p className="mt-6 inline-flex min-h-10 items-center border border-white/18 px-4 font-mono text-[0.68rem] uppercase tracking-[0.18em] text-white/72">
                            {copy.compareLabel}
                        </p>
                    </div>

                    {activeProject ? (
                        <PortfolioMediaRail
                            portfolio={inlinePortfolio}
                            projectKey={activePhase}
                            ariaLabel={`${phaseCopy.label} · ${phaseCopy.date}`}
                            action={primaryAction(`portfolio_${activePhase}`, copy.proofCta)}
                        />
                    ) : null}
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.visualEyebrow}
                    title={copy.visualTitle}
                    description={copy.visualDescription}
                />
                <div className="mt-10 overflow-x-auto border-y border-border">
                    <table className="w-full min-w-[46rem] border-collapse text-start">
                        <thead>
                            <tr className="border-b border-border">
                                {copy.visualHeaders.map((header, index) => (
                                    <th
                                        key={header}
                                        scope="col"
                                        className={[
                                            'px-4 py-4 text-start font-mono text-[0.66rem] uppercase tracking-[0.14em] text-muted-foreground first:pl-0 last:pr-0',
                                            index === 0 ? 'w-[17%]' : 'w-[27.66%]',
                                        ].join(' ')}
                                    >
                                        {header}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {copy.visualComparisons.map(([view, goba, okomEarly, okomRecent]) => (
                                <tr key={view}>
                                    <th
                                        scope="row"
                                        className="py-5 pr-4 text-start font-display text-xl font-bold leading-[1.05] text-foreground"
                                    >
                                        {view}
                                    </th>
                                    {[goba, okomEarly, okomRecent].map((value, index) => (
                                        <td
                                            key={`${view}-${index}`}
                                            className="px-4 py-5 text-sm leading-[1.55] text-muted-foreground last:pr-0"
                                        >
                                            {value}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
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
                    className="relative z-10 mt-10"
                    checkoutRoute="construction-progress.checkout"
                    paymentProvider="stripe"
                    product={product}
                    popupVariant="construction"
                    popupHeroProofVideo={{
                        title: bookingProofVideo?.alt ?? copy.heroTitle,
                        media_type: 'video',
                        embed_url: null,
                        playback_url: bookingProofVideo?.src ?? DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.bookingPopup.src,
                        poster_url: bookingProofVideo?.poster ?? DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS.bookingPopup.poster,
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
                primaryAction={primaryAction('final', copy.finalPayCta)}
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
