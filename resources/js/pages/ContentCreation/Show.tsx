import { useEffect, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays } from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { SeoHead } from '@/components/lapsique/SeoHead';
import {
    PortfolioMediaRail,
    ServicePortfolioShowcase,
    ServiceProofBand,
    ServiceFunnelDeliverables,
    ServiceFunnelFaq,
    ServiceFunnelFinalCta,
    ServiceFunnelHeading,
    ServiceFunnelHero,
    ServiceFunnelProcess,
    ServiceFunnelSection,
    ServiceWhatsAppButton,
    serviceFunnelPrimaryActionClass,
} from '@/components/lapsique/ServiceFunnel';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { getContentSessionProduct } from '@/lib/bookingProducts';
import type {
    BookingSlot,
    PageProps,
    PortfolioItemData,
    ServicePortfolioBundle,
} from '@/types';

interface ContentCreationShowProps {
    variant?: 'content_creation' | 'business_reels';
    price: number;
    slots: BookingSlot[];
    servicePortfolio: ServicePortfolioBundle;
    errors?: Record<string, string>;
}

const COPY = {
    es: {
        heroEyebrow: 'Lapsique / Contenido para redes',
        title: 'Creación de contenido para redes sociales en Riviera Maya',
        intro: 'Producimos reels y fotografías para negocios que necesitan verse claros, actuales y listos para vender en Instagram, TikTok y campañas de Meta.',
        location: 'Playa del Carmen · Tulum · Cancún',
        book: 'Agendar sesión',
        whatsapp: 'Hablar por WhatsApp',
        whatsappMessage: 'Hola, quiero crear contenido para las redes sociales de mi negocio en Riviera Maya.',
        from: 'Sesiones desde',
        proofEyebrow: 'Portafolio contextual',
        proofBandTitle: 'Proyectos reales, dirigidos para cada negocio',
        proofBandCopy: 'La selección reúne clientes, espacios y formatos distintos. Cada proyecto conserva su contexto y su objetivo comercial.',
        proofTitle: 'Trabajo real, producido por Lapsique',
        proofCopy: 'Explora producciones para marcas, servicios, hospitalidad y experiencias. No son imágenes de relleno: cada pieza pertenece a un proyecto documentado.',
        proofCta: 'Quiero una producción así',
        systemEyebrow: 'Dirección por sector',
        systemTitle: 'Una sesión, un sistema de contenido',
        systemCopy: 'La estructura se adapta al negocio para que el reel y las fotografías trabajen juntos, sin grabar piezas aisladas.',
        systemItems: [
            {
                title: 'Marcas y servicios',
                copy: 'Producto, proceso, equipo y resultado para explicar con claridad qué haces y por qué elegirte.',
            },
            {
                title: 'Hospitalidad y espacios',
                copy: 'Llegada, arquitectura, amenidades y detalles para convertir el lugar en una experiencia visible.',
            },
            {
                title: 'Gastronomía y experiencias',
                copy: 'Preparación, producto y personas para mostrar tanto lo que vendes como la sensación de vivirlo.',
            },
        ],
        railLabel: 'Más proyectos de creación de contenido',
        deliverablesTitle: 'Una sesión pensada para publicar y pautar',
        deliverables: [
            {
                title: 'Reel vertical',
                copy: 'Una pieza de 30 segundos con ritmo, encuadres y cierre pensados para Instagram, TikTok o Meta Ads.',
            },
            {
                title: 'Fotografía dirigida',
                copy: 'Diez fotos editadas de producto, equipo, espacio o experiencia, según el objetivo del negocio.',
            },
            {
                title: 'Contexto aéreo',
                copy: 'Tres tomas con dron DJI cuando la ubicación, el clima y las condiciones de vuelo lo permiten.',
            },
        ],
        processTitle: 'Llegas con el negocio; salimos con una historia clara',
        process: [
            ['01', 'Definimos el objetivo', 'Antes de grabar acordamos qué debe entender o hacer la persona que vea el contenido.'],
            ['02', 'Dirigimos la sesión', 'Organizamos tomas, luz, personas y producto para aprovechar el tiempo en locación.'],
            ['03', 'Entregamos para publicar', 'Recibes el reel y las fotos editadas, preparados para tus canales y campañas.'],
        ],
        bookingTitle: 'Reserva la próxima producción',
        bookingCopy: 'Elige una fecha disponible. Al confirmar, definimos locación, objetivo y lista de tomas por WhatsApp.',
        faqTitle: 'Preguntas frecuentes',
        faqs: [
            ['¿Trabajan contenido para Instagram y TikTok?', 'Sí. Grabamos en formato vertical y editamos piezas pensadas para consumo móvil, publicaciones orgánicas y anuncios.'],
            ['¿Puedo usar el material en Meta Ads?', 'Sí. El reel y las fotografías se entregan con uso comercial para la campaña del negocio contratado.'],
            ['¿Qué tipo de negocios atienden?', 'Restaurantes, hoteles, propiedades, desarrollos, experiencias, eventos y marcas de servicio en Riviera Maya.'],
            ['¿La sesión incluye estrategia de social media?', 'La producción incluye dirección visual y una lista de tomas. La gestión mensual de redes se cotiza aparte según volumen y canales.'],
        ],
    },
    en: {
        heroEyebrow: 'Lapsique / Social content',
        title: 'Social media content creation in Riviera Maya',
        intro: 'We produce reels and photography for businesses that need clear, current material built to sell on Instagram, TikTok, and Meta campaigns.',
        location: 'Playa del Carmen · Tulum · Cancun',
        book: 'Book a session',
        whatsapp: 'Talk on WhatsApp',
        whatsappMessage: 'Hi, I want to create social media content for my business in Riviera Maya.',
        from: 'Sessions from',
        proofEyebrow: 'Contextual portfolio',
        proofBandTitle: 'Real projects, directed for each business',
        proofBandCopy: 'This selection brings together different clients, spaces, and formats. Every project keeps its own context and commercial goal.',
        proofTitle: 'Real work produced by Lapsique',
        proofCopy: 'Explore productions for brands, services, hospitality, and experiences. These are not filler images: every piece belongs to a documented project.',
        proofCta: 'I want a production like this',
        systemEyebrow: 'Direction by industry',
        systemTitle: 'One session, one content system',
        systemCopy: 'The structure adapts to the business so the reel and photographs work together instead of becoming isolated assets.',
        systemItems: [
            {
                title: 'Brands and services',
                copy: 'Product, process, team, and result to explain what you do and why customers should choose you.',
            },
            {
                title: 'Hospitality and spaces',
                copy: 'Arrival, architecture, amenities, and details that turn a place into a visible experience.',
            },
            {
                title: 'Food and experiences',
                copy: 'Preparation, product, and people to show both what you sell and what it feels like to experience it.',
            },
        ],
        railLabel: 'More content creation projects',
        deliverablesTitle: 'One session built for publishing and ads',
        deliverables: [
            {
                title: 'Vertical reel',
                copy: 'A 30-second piece with pacing, framing, and a clear ending for Instagram, TikTok, or Meta Ads.',
            },
            {
                title: 'Directed photography',
                copy: 'Ten edited photographs of the product, team, space, or experience, based on the business goal.',
            },
            {
                title: 'Aerial context',
                copy: 'Three DJI drone shots when location, weather, and flight conditions allow it.',
            },
        ],
        processTitle: 'Bring the business; leave with a clear story',
        process: [
            ['01', 'Define the goal', 'Before the shoot, we agree on what viewers need to understand or do after seeing the content.'],
            ['02', 'Direct the session', 'We organize shots, light, people, and product to use the location time well.'],
            ['03', 'Deliver for publishing', 'You receive the edited reel and photographs ready for your channels and campaigns.'],
        ],
        bookingTitle: 'Book the next production',
        bookingCopy: 'Choose an available date. Once confirmed, we define location, objective, and shot list on WhatsApp.',
        faqTitle: 'Frequently asked questions',
        faqs: [
            ['Do you create content for Instagram and TikTok?', 'Yes. We shoot vertically and edit for mobile viewing, organic posts, and advertising.'],
            ['Can I use the material in Meta Ads?', 'Yes. The reel and photographs include commercial use for the campaign of the contracted business.'],
            ['What businesses do you work with?', 'Restaurants, hotels, properties, developments, experiences, events, and service brands in Riviera Maya.'],
            ['Does the session include social media strategy?', 'Production includes visual direction and a shot list. Monthly social media management is quoted separately by volume and channel.'],
        ],
    },
} as const;

const BUSINESS_REELS_COPY = {
    es: {
        ...COPY.es,
        heroEyebrow: 'Lapsique / Reels para negocios',
        title: 'Reels para negocios listos para vender y pautar',
        intro: 'Convertimos tu producto, espacio o servicio en video vertical y fotografía con un objetivo comercial claro.',
        book: 'Reservar producción',
        whatsapp: 'Cotizar por WhatsApp',
        whatsappMessage: 'Hola, quiero cotizar reels para promocionar mi negocio.',
        proofTitle: 'Reels construidos alrededor de una oferta real',
        proofCopy: 'Cada caso parte de un producto, una audiencia y una acción medible. La selección muestra campañas con objetivos distintos, no una plantilla repetida.',
        proofBandTitle: 'Campañas reales con una intención comercial clara',
        proofBandCopy: 'La evidencia está agrupada por campaña para que puedas revisar cómo cambia la dirección según la oferta y el público.',
        systemEyebrow: 'Estructura de anuncio',
        systemTitle: 'Hook · oferta · acción',
        systemCopy: 'Cada reel ordena la atención antes de pedir una conversión. La imagen puede cambiar; la función de cada momento no.',
        systemItems: [
            {
                title: 'Hook',
                copy: 'Abrimos con el producto, el problema o el resultado para detener el scroll en los primeros segundos.',
            },
            {
                title: 'Oferta',
                copy: 'Mostramos el beneficio con detalles y prueba visual, sin llenar la pieza de explicaciones.',
            },
            {
                title: 'Acción',
                copy: 'Cerramos con una instrucción concreta: reservar, pedir información, visitar o comprar.',
            },
        ],
        railLabel: 'Más campañas producidas para negocios',
        deliverablesTitle: 'Una campaña corta, no contenido suelto',
        processTitle: 'De la oferta al anuncio en tres pasos',
        bookingTitle: 'Reserva la producción de tu campaña',
        bookingCopy: 'Elige una fecha. Después definimos oferta, audiencia, locación y lista de tomas antes de grabar.',
    },
    en: {
        ...COPY.en,
        heroEyebrow: 'Lapsique / Business reels',
        title: 'Business reels built to sell and run as ads',
        intro: 'We turn your product, space, or service into vertical video and photography with a clear commercial goal.',
        book: 'Book production',
        whatsapp: 'Quote on WhatsApp',
        whatsappMessage: 'Hi, I want to quote reels to promote my business.',
        proofTitle: 'Reels built around a real offer',
        proofCopy: 'Every case begins with a product, an audience, and a measurable action. This selection shows campaigns with distinct goals instead of one repeated template.',
        proofBandTitle: 'Real campaigns with a clear commercial intention',
        proofBandCopy: 'Evidence is grouped by campaign so you can see how direction changes with the offer and audience.',
        systemEyebrow: 'Ad structure',
        systemTitle: 'Hook · offer · action',
        systemCopy: 'Each reel earns attention before asking for a conversion. The image can change; the role of each moment does not.',
        systemItems: [
            {
                title: 'Hook',
                copy: 'Lead with the product, problem, or result to stop the scroll in the opening seconds.',
            },
            {
                title: 'Offer',
                copy: 'Show the benefit through detail and visual proof without overexplaining the piece.',
            },
            {
                title: 'Action',
                copy: 'End with one direct next step: book, request information, visit, or buy.',
            },
        ],
        railLabel: 'More campaigns produced for businesses',
        deliverablesTitle: 'A short campaign, not loose content',
        processTitle: 'From offer to ad in three steps',
        bookingTitle: 'Book your campaign production',
        bookingCopy: 'Choose a date. We then define the offer, audience, location, and shot list before filming.',
    },
} as const;

export default function ContentCreationShow({
    variant = 'content_creation',
    price,
    slots,
    servicePortfolio,
    errors,
}: ContentCreationShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale, t } = useTranslations();
    const copySet = variant === 'business_reels' ? BUSINESS_REELS_COPY : COPY;
    const copy = locale === 'en' ? copySet.en : copySet.es;
    const product = useMemo(() => getContentSessionProduct(t), [t]);
    const { showcasePortfolio, supportingPortfolio } = useMemo(
        () => splitPortfolioForSections(servicePortfolio),
        [servicePortfolio],
    );
    const popupPortfolioItems = useMemo(
        () => toPopupPortfolioItems(servicePortfolio),
        [servicePortfolio],
    );
    const whatsappHref = buildWhatsAppHref(site.whatsapp, copy.whatsappMessage);
    const analyticsPrefix = variant === 'business_reels' ? 'business_reels' : 'content_creation';
    const analyticsPayload = useMemo(
        () => ({
            content_name: copy.title,
            content_category: variant === 'business_reels' ? 'business_reels' : 'social_media_content_creation',
            service_name: variant,
            service_type: 'content_session',
            currency: 'MXN',
            value: price,
        }),
        [copy.title, price, variant],
    );

    useEffect(() => {
        trackBookingEvent(`${analyticsPrefix}_page_viewed`, {
            ...analyticsPayload,
            landing: window.location.pathname,
            source: `${analyticsPrefix}_landing`,
        });
    }, [analyticsPayload, analyticsPrefix]);

    const trackWhatsApp = (source: 'hero' | 'final') => {
        trackBookingEvent(`${analyticsPrefix}_whatsapp_cta_clicked`, {
            ...analyticsPayload,
            source: `${analyticsPrefix}_${source}`,
            target: 'whatsapp',
            contact_channel: 'whatsapp',
        });
    };

    return (
        <SiteLayout>
            <SeoHead />

            <ServiceFunnelHero
                eyebrow={copy.heroEyebrow}
                title={copy.title}
                description={copy.intro}
                locations={copy.location}
                price={price}
                priceLabel={copy.from}
                primaryAction={(
                    <BookingCtaButton
                        opensBookingModal
                        bookingSource={`${analyticsPrefix}_hero`}
                        bookingAnalytics={{
                            analyticsEvent: `${analyticsPrefix}_booking_cta_clicked`,
                            analyticsPayload,
                        }}
                        className={serviceFunnelPrimaryActionClass}
                    >
                        <CalendarDays className="size-5" aria-hidden />
                        {copy.book}
                        <ArrowRight className="size-4" aria-hidden />
                    </BookingCtaButton>
                )}
                secondaryAction={(
                    <ServiceWhatsAppButton
                        href={whatsappHref}
                        label={copy.whatsapp}
                        onClick={() => trackWhatsApp('hero')}
                    />
                )}
                media={(
                    <ServiceHeroMedia portfolio={servicePortfolio} />
                )}
                mediaLabel={servicePortfolio.hero.projectLabel}
                mediaCaption={[
                    servicePortfolio.hero.sessionLabel,
                    servicePortfolio.hero.location,
                ].filter(Boolean).join(' · ')}
            />

            <ServiceFunnelSection innerClassName="py-0 sm:py-0 lg:py-0">
                <ServiceProofBand
                    portfolio={servicePortfolio}
                    eyebrow={copy.proofEyebrow}
                    title={copy.proofBandTitle}
                    description={copy.proofBandCopy}
                />
            </ServiceFunnelSection>

            <ServicePortfolioShowcase
                portfolio={showcasePortfolio}
                eyebrow={copy.proofEyebrow}
                title={copy.proofTitle}
                description={copy.proofCopy}
                action={(
                    <BookingCtaButton
                        opensBookingModal
                        bookingSource={`${analyticsPrefix}_portfolio`}
                        bookingAnalytics={{
                            analyticsEvent: `${analyticsPrefix}_booking_cta_clicked`,
                            analyticsPayload: {
                                ...analyticsPayload,
                                source: `${analyticsPrefix}_portfolio`,
                            },
                        }}
                        className={`${serviceFunnelPrimaryActionClass} sm:w-auto`}
                    >
                        {copy.proofCta}
                        <ArrowRight className="size-4" aria-hidden />
                    </BookingCtaButton>
                )}
            />

            <ServiceFunnelSection tone="dark">
                <div className="grid gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                    <ServiceFunnelHeading
                        eyebrow={copy.systemEyebrow}
                        title={copy.systemTitle}
                        description={copy.systemCopy}
                        inverse
                    />
                    <ServiceFunnelDeliverables
                        items={copy.systemItems.map((item) => ({
                            title: item.title,
                            description: item.copy,
                        }))}
                        inverse
                    />
                </div>
                {supportingPortfolio.projects.length > 0 ? (
                    <div className="mt-12 space-y-12">
                        {supportingPortfolio.projects.map((project) => (
                            <PortfolioMediaRail
                                key={project.key}
                                portfolio={supportingPortfolio}
                                projectKey={project.key}
                                ariaLabel={`${copy.railLabel}: ${project.label}`}
                            />
                        ))}
                    </div>
                ) : null}
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <div className="grid gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                    <ServiceFunnelHeading title={copy.deliverablesTitle} />
                    <ServiceFunnelDeliverables
                        items={copy.deliverables.map((item) => ({
                            title: item.title,
                            description: item.copy,
                        }))}
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <ServiceFunnelHeading title={copy.processTitle} />
                <div className="mt-10">
                    <ServiceFunnelProcess
                        items={copy.process.map(([, title, description]) => ({
                            title,
                            description,
                        }))}
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection id="request" tone="soft" className="scroll-mt-24">
                <ServiceFunnelHeading
                    title={copy.bookingTitle}
                    description={copy.bookingCopy}
                />
                <div className="mt-10">
                    <BookingWidget
                        slots={slots}
                        price={price}
                        whatsapp={site.whatsapp}
                        errors={errors}
                        checkoutRoute="booking.checkout"
                        paymentProvider="stripe"
                        product={product}
                        popupVariant="home"
                        popupPortfolioItems={popupPortfolioItems}
                        highlight
                        analyticsPayload={analyticsPayload}
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection>
                <div className="grid gap-10 lg:grid-cols-[0.72fr_1.28fr]">
                    <ServiceFunnelHeading title={copy.faqTitle} />
                    <ServiceFunnelFaq
                        items={copy.faqs.map(([question, answer]) => ({ question, answer }))}
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelFinalCta
                eyebrow="Lapsique Media"
                title={copy.bookingTitle}
                description={copy.bookingCopy}
                primaryAction={(
                    <BookingCtaButton
                        opensBookingModal
                        bookingSource={`${analyticsPrefix}_final`}
                        bookingAnalytics={{
                            analyticsEvent: `${analyticsPrefix}_booking_cta_clicked`,
                            analyticsPayload,
                        }}
                        className={serviceFunnelPrimaryActionClass}
                    >
                        <CalendarDays className="size-5" aria-hidden />
                        {copy.book}
                    </BookingCtaButton>
                )}
                secondaryAction={(
                    <ServiceWhatsAppButton
                        href={whatsappHref}
                        label={copy.whatsapp}
                        onClick={() => trackWhatsApp('final')}
                    />
                )}
            />
        </SiteLayout>
    );
}

function buildWhatsAppHref(phone: string | undefined, message: string): string {
    const cleanedPhone = (phone ?? '').replace(/[^\d]/g, '');
    const base = cleanedPhone ? `https://wa.me/${cleanedPhone}` : 'https://wa.me/';

    return `${base}?text=${encodeURIComponent(message)}`;
}

function ServiceHeroMedia({ portfolio }: { portfolio: ServicePortfolioBundle }) {
    const hero = portfolio.hero;

    if (hero.kind === 'video') {
        return (
            <AutoplayVideo
                src={hero.src}
                poster={hero.poster}
                title={hero.alt}
                className="h-full w-full"
                eager
                preload="metadata"
            />
        );
    }

    return (
        <img
            src={hero.src}
            alt={hero.alt}
            fetchPriority="high"
            loading="eager"
            className="h-full w-full object-cover"
        />
    );
}

function splitPortfolioForSections(portfolio: ServicePortfolioBundle): {
    showcasePortfolio: ServicePortfolioBundle;
    supportingPortfolio: ServicePortfolioBundle;
} {
    const projectsWithoutHero = portfolio.projects
        .map((project) => ({
            ...project,
            media: project.media.filter((media) => (
                media.id !== portfolio.hero.id
                && media.src !== portfolio.hero.src
            )),
        }))
        .filter((project) => project.media.length > 0);
    const showcaseProjectCount = Math.min(3, projectsWithoutHero.length);

    return {
        showcasePortfolio: portfolioWithProjects(
            portfolio,
            projectsWithoutHero.slice(0, showcaseProjectCount),
        ),
        supportingPortfolio: portfolioWithProjects(
            portfolio,
            projectsWithoutHero.slice(showcaseProjectCount),
        ),
    };
}

function portfolioWithProjects(
    portfolio: ServicePortfolioBundle,
    projects: ServicePortfolioBundle['projects'],
): ServicePortfolioBundle {
    return {
        ...portfolio,
        projects,
        stats: {
            projectCount: projects.length,
            mediaCount: projects.reduce((total, project) => total + project.media.length, 0),
        },
    };
}

function toPopupPortfolioItems(portfolio: ServicePortfolioBundle): PortfolioItemData[] {
    const seen = new Set<string>();
    const media = [portfolio.hero, ...portfolio.projects.flatMap((project) => project.media)]
        .filter((item) => {
            if (seen.has(item.id) || seen.has(item.src)) {
                return false;
            }

            seen.add(item.id);
            seen.add(item.src);
            return true;
        });

    return media.map((item, index) => ({
        id: index + 1,
        title: item.projectLabel,
        slug: null,
        type: 'service_portfolio',
        source: 'service-curation',
        caption: item.sessionLabel ?? item.location ?? null,
        tags: [item.projectKey, portfolio.serviceKey],
        asset_url: item.kind === 'image' ? item.src : null,
        poster_url: item.poster ?? (item.kind === 'image' ? item.src : null),
        playback_url: item.kind === 'video' ? item.src : null,
        embed_url: null,
        youtube_id: null,
        youtube_url: null,
        media_type: item.kind,
        is_featured: index === 0,
        orientation: item.orientation,
    }));
}
