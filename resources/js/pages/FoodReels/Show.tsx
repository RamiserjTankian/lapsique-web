import { useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays } from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget, type BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import { EditorialVideoPlayer } from '@/components/lapsique/EditorialVideoPlayer';
import { PortfolioLightbox } from '@/components/lapsique/PortfolioLightbox';
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
import { localized, SERVICE_LANDING_CONFIGS } from '@/data/serviceLandingPages';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { openBookingModal } from '@/lib/openBookingModal';
import { cn } from '@/lib/utils';
import type {
    BookingSlot,
    PageProps,
    PortfolioItemData,
    ServicePortfolioBundle,
    ServicePortfolioMedia,
} from '@/types';

interface FoodReelsShowProps {
    price: number;
    slots: BookingSlot[];
    servicePortfolio: ServicePortfolioBundle;
    errors?: Record<string, string>;
}

const FOOD_PAGE_COPY = {
    es: {
        analyticsName: 'Landing de reels de comida',
        heroEyebrow: 'Contenido gastronómico / Riviera Maya',
        heroTitle: 'Reels de comida para restaurantes en Riviera Maya',
        heroDescription: 'Reels y fotografías que muestran platillos, servicio y ambiente como una experiencia que vale la pena reservar.',
        heroPrimary: 'Reservar sesión',
        whatsappCta: 'Cotizar por WhatsApp',
        proofTitle: 'Restaurantes reales. Piezas listas para publicar y pautar.',
        proofDescription: 'La evidencia se organiza por restaurante para que puedas revisar variedad, acabado y consistencia antes de reservar.',
        reelEyebrow: 'Reels / SushiClub',
        reelTitle: 'Movimiento, servicio y producto en cuatro cortes reales.',
        reelDescription: 'Cada reel conserva el ritmo del restaurante y deja claro qué se sirve, cómo llega a la mesa y qué ambiente acompaña la visita.',
        productEyebrow: 'Fotografía de producto',
        productTitle: 'Platillos que se entienden antes de leer el menú.',
        productDescription: 'Santino, SushiClub y Tanuki muestran producto, textura y presentación sin convertir la página en una cuadrícula interminable.',
        activationEyebrow: 'Experiencia / The Roof',
        activationTitle: 'Personas, mesa y ambiente completan la decisión.',
        activationDescription: 'Cuando el restaurante también vende una experiencia, dirigimos escenas de consumo que se sienten naturales y habitadas.',
        deliverablesEyebrow: 'Una sesión, un sistema',
        deliverablesTitle: 'Contenido útil para cada punto de venta.',
        deliverablesDescription: 'Definimos un shot list breve y salimos con piezas que pueden convivir en redes, anuncios, menú digital y Google.',
        deliverables: [
            { title: 'Reels verticales', description: 'Preparación, servicio, barra y platillos editados para captar atención desde el primer segundo.' },
            { title: 'Fotografía de producto', description: 'Imágenes limpias para menú, redes, campañas y actualizaciones de temporada.' },
            { title: 'Experiencia con personas', description: 'Escenas dirigidas para mostrar la mesa, el ambiente y el tipo de visita que ofreces.' },
            { title: 'Recursos del espacio', description: 'Entrada, barra, terraza y tomas de contexto para ubicar la experiencia completa.' },
        ],
        bookingEyebrow: 'Elige una fecha',
        bookingTitle: 'Agenda la sesión y definimos el shot list.',
        bookingDescription: 'Antes de grabar confirmamos platillos, horario, espacios y piezas prioritarias. Llegas a la sesión con un plan claro.',
        finalTitle: 'Haz que tu restaurante provoque una visita.',
        finalDescription: 'Comparte tu concepto y la fecha tentativa. Te respondemos con disponibilidad y un alcance claro.',
        finalPrimary: 'Reservar sesión',
        finalWhatsApp: 'Preguntar por WhatsApp',
        bookingProduct: {
            checkoutLabel: 'Producción gastronómica',
            headerTitle: 'Agenda tu sesión de comida',
            headerDescription: 'Selecciona fecha, comparte el concepto del restaurante y confirma la producción.',
            summaryTitle: 'Sesión de contenido para restaurante',
            summaryDescription: 'Reels y fotografías dirigidos para mostrar producto, servicio y ambiente.',
            lines: ['Platillos y espacios prioritarios', 'Contenido vertical para redes y pauta', 'Entrega lista para publicar'],
            cartService: 'Reels y fotos de comida',
            cartDuration: 'Sesión dirigida en restaurante',
            perks: ['Reels de producto y servicio', 'Fotografías editadas', 'Shot list previo', 'Uso comercial', 'Pago seguro con tarjeta'],
            terms: ['La fecha queda sujeta a disponibilidad.', 'El alcance final se confirma según platillos, locación y necesidades de producción.', 'Puedes reprogramar con aviso previo según disponibilidad.', 'El material puede usarse en portafolio salvo acuerdo distinto.'],
            unavailableWhatsApp: 'Hola, quiero una sesión de reels y fotos para mi restaurante.',
        },
        whatsappPrefill: 'Hola, quiero cotizar reels y fotografías para mi restaurante.',
    },
    en: {
        analyticsName: 'Food reels landing page',
        heroEyebrow: 'Food content / Riviera Maya',
        heroTitle: 'Food reels for restaurants in Riviera Maya',
        heroDescription: 'Reels and photography that present dishes, service, and atmosphere as an experience worth booking.',
        heroPrimary: 'Book session',
        whatsappCta: 'Quote on WhatsApp',
        proofTitle: 'Real restaurants. Assets ready to publish and promote.',
        proofDescription: 'Evidence is grouped by restaurant so you can review variety, finish, and consistency before booking.',
        reelEyebrow: 'Reels / SushiClub',
        reelTitle: 'Movement, service, and product across four real edits.',
        reelDescription: 'Each reel keeps the restaurant rhythm clear: what is served, how it reaches the table, and the atmosphere around the visit.',
        productEyebrow: 'Product photography',
        productTitle: 'Dishes people understand before reading the menu.',
        productDescription: 'Santino, SushiClub, and Tanuki show product, texture, and presentation without turning the page into an endless grid.',
        activationEyebrow: 'Experience / The Roof',
        activationTitle: 'People, table, and atmosphere complete the decision.',
        activationDescription: 'When the restaurant also sells an experience, we direct natural scenes that feel lived in.',
        deliverablesEyebrow: 'One session, one system',
        deliverablesTitle: 'Useful content for every sales surface.',
        deliverablesDescription: 'We define a concise shot list and leave with assets that work across social, ads, digital menus, and Google.',
        deliverables: [
            { title: 'Vertical reels', description: 'Preparation, service, bar, and dishes edited to earn attention from the first second.' },
            { title: 'Product photography', description: 'Clean images for menus, social media, campaigns, and seasonal updates.' },
            { title: 'Experience with people', description: 'Directed scenes that show the table, atmosphere, and kind of visit you offer.' },
            { title: 'Space details', description: 'Entrance, bar, terrace, and context shots that locate the complete experience.' },
        ],
        bookingEyebrow: 'Choose a date',
        bookingTitle: 'Book the session and define the shot list.',
        bookingDescription: 'Before production, we confirm dishes, timing, spaces, and priority pieces. You arrive with a clear plan.',
        finalTitle: 'Make your restaurant worth the visit.',
        finalDescription: 'Share your concept and tentative date. We will reply with availability and a clear scope.',
        finalPrimary: 'Book session',
        finalWhatsApp: 'Ask on WhatsApp',
        bookingProduct: {
            checkoutLabel: 'Food production',
            headerTitle: 'Book your food session',
            headerDescription: 'Choose a date, share the restaurant concept, and confirm production.',
            summaryTitle: 'Restaurant content session',
            summaryDescription: 'Directed reels and photography built to show product, service, and atmosphere.',
            lines: ['Priority dishes and spaces', 'Vertical content for social and ads', 'Delivery ready to publish'],
            cartService: 'Food reels and photos',
            cartDuration: 'Directed restaurant session',
            perks: ['Product and service reels', 'Edited photography', 'Preproduction shot list', 'Commercial use', 'Secure card payment'],
            terms: ['Dates depend on availability.', 'Final scope is confirmed according to dishes, location, and production needs.', 'You can reschedule with prior notice depending on availability.', 'Material may be used in portfolio unless agreed otherwise.'],
            unavailableWhatsApp: 'Hi, I want a food reels and photography session for my restaurant.',
        },
        whatsappPrefill: 'Hi, I want to quote reels and photography for my restaurant.',
    },
} as const;

export default function FoodReelsShow({
    price,
    slots,
    servicePortfolio,
    errors,
}: FoodReelsShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';
    const copy = FOOD_PAGE_COPY[en ? 'en' : 'es'];
    const landingConfig = SERVICE_LANDING_CONFIGS.reels_de_comida;
    const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);
    const whatsappHref = useMemo(
        () => buildWhatsAppHref(site.whatsapp, copy.whatsappPrefill),
        [copy.whatsappPrefill, site.whatsapp],
    );
    const product = useMemo<BookingWidgetProduct>(
        () => ({
            checkoutLabel: copy.bookingProduct.checkoutLabel,
            headerTitle: copy.bookingProduct.headerTitle,
            headerDescription: copy.bookingProduct.headerDescription,
            summaryTitle: copy.bookingProduct.summaryTitle,
            summaryDescription: copy.bookingProduct.summaryDescription,
            summaryDescriptionLines: [...copy.bookingProduct.lines],
            cartService: copy.bookingProduct.cartService,
            cartDuration: copy.bookingProduct.cartDuration,
            summaryPerks: [...copy.bookingProduct.perks],
            terms: [...copy.bookingProduct.terms],
            paymentCopy: en
                ? 'Secure card payment powered by Stripe.'
                : 'Pago seguro con tarjeta procesado por Stripe.',
            unavailableWhatsApp: copy.bookingProduct.unavailableWhatsApp,
        }),
        [copy, en],
    );
    const analyticsPayload = useMemo(
        () => ({
            content_name: copy.analyticsName,
            content_category: 'food_reels_booking',
            service_type: 'food_reels',
            currency: 'MXN',
            value: price,
        }),
        [copy.analyticsName, price],
    );
    const foodVideos = useMemo(
        () => filterPortfolio(
            servicePortfolio,
            (media) => media.kind === 'video' && media.id !== servicePortfolio.hero.id,
        ),
        [servicePortfolio],
    );
    const popupProofMedia = useMemo(
        () => pickDistinctPopupMedia(servicePortfolio),
        [servicePortfolio],
    );
    const selectedPhotos = useMemo(() => {
        const photos = uniqueMedia(servicePortfolio.projects.flatMap((project) => project.media))
            .filter((media) => media.kind === 'image' && media.id !== servicePortfolio.hero.id);
        const activations = photos.filter(isActivationMedia).slice(0, 4);
        const activationIds = new Set(activations.map((media) => media.id));
        const products = photos.filter((media) => !activationIds.has(media.id)).slice(0, 12);

        return {
            products,
            activations,
            all: [...products, ...activations].slice(0, 16),
        };
    }, [servicePortfolio]);
    const lightboxItems = useMemo(
        () => selectedPhotos.all.map(toPortfolioItem),
        [selectedPhotos.all],
    );
    const productPhotoCount = selectedPhotos.products.length;

    useEffect(() => {
        trackBookingEvent('food_reels_page_viewed', {
            ...analyticsPayload,
            section: 'food_reels',
        });
    }, [analyticsPayload]);

    const openBooking = (source: string) => {
        openBookingModal({
            source,
            analyticsEvent: 'food_reels_booking_cta_clicked',
            analyticsPayload: { ...analyticsPayload, source },
        });
    };

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('food_reels_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };

    const primaryAction = (source: string, compact = false) => (
        <BookingCtaButton
            type="button"
            className={cn(serviceFunnelPrimaryActionClass, compact && 'sm:w-auto')}
            onClick={() => openBooking(source)}
        >
            <CalendarDays className="size-5" aria-hidden="true" />
            {source === 'final' ? copy.finalPrimary : copy.heroPrimary}
            <ArrowRight className="size-4" aria-hidden="true" />
        </BookingCtaButton>
    );
    const whatsappAction = (source: string, compact = false) => (
        <ServiceWhatsAppButton
            href={whatsappHref}
            label={source === 'final' ? copy.finalWhatsApp : copy.whatsappCta}
            onClick={() => trackWhatsApp(source)}
            className={compact ? 'sm:w-auto' : undefined}
        />
    );

    return (
        <SiteLayout>
            <SeoHead />

            <ServiceFunnelHero
                eyebrow={copy.heroEyebrow}
                title={copy.heroTitle}
                description={copy.heroDescription}
                locations="Playa del Carmen · Tulum · Cancún · Riviera Maya"
                price={price}
                priceLabel={en ? 'From' : 'Desde'}
                priceNote={en ? 'Directed production for restaurants.' : 'Producción dirigida para restaurantes.'}
                primaryAction={primaryAction('hero')}
                secondaryAction={whatsappAction('hero')}
                media={<HeroMedia media={servicePortfolio.hero} />}
                mediaLabel={servicePortfolio.hero.projectLabel}
                mediaCaption={servicePortfolio.hero.sessionLabel ?? copy.reelEyebrow}
            />

            <ServiceFunnelSection innerClassName="py-0 sm:py-0 lg:py-0">
                <ServiceProofBand
                    portfolio={servicePortfolio}
                    eyebrow={en ? 'Verified food portfolio' : 'Portafolio gastronómico verificado'}
                    title={copy.proofTitle}
                    description={copy.proofDescription}
                />
            </ServiceFunnelSection>

            {foodVideos.stats.mediaCount > 0 ? (
                <ServicePortfolioShowcase
                    portfolio={foodVideos}
                    eyebrow={copy.reelEyebrow}
                    title={copy.reelTitle}
                    description={copy.reelDescription}
                    action={(
                        <>
                            {primaryAction('food_portfolio', true)}
                            {whatsappAction('food_portfolio', true)}
                        </>
                    )}
                />
            ) : null}

            {selectedPhotos.products.length > 0 ? (
                <ServiceFunnelSection id="fotografia-producto">
                    <ServiceFunnelHeading
                        eyebrow={copy.productEyebrow}
                        title={copy.productTitle}
                        description={copy.productDescription}
                    />
                    <PhotoMosaic
                        items={selectedPhotos.products}
                        onOpen={(index) => setLightboxIndex(index)}
                    />
                </ServiceFunnelSection>
            ) : null}

            {selectedPhotos.activations.length > 0 ? (
                <ServiceFunnelSection tone="dark" id="experiencia-restaurante">
                    <ServiceFunnelHeading
                        eyebrow={copy.activationEyebrow}
                        title={copy.activationTitle}
                        description={copy.activationDescription}
                        inverse
                    />
                    <PhotoStrip
                        items={selectedPhotos.activations}
                        onOpen={(index) => setLightboxIndex(productPhotoCount + index)}
                    />
                </ServiceFunnelSection>
            ) : null}

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.deliverablesEyebrow}
                    title={copy.deliverablesTitle}
                    description={copy.deliverablesDescription}
                />
                <div className="mt-10">
                    <ServiceFunnelDeliverables items={[...copy.deliverables]} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft">
                <ServiceFunnelHeading
                    eyebrow={en ? 'Production flow' : 'Proceso de producción'}
                    title={en ? 'A clear route from shot list to delivery.' : 'Una ruta clara del shot list a la entrega.'}
                    description={en
                        ? 'Three decisions keep the session focused; the rest is production.'
                        : 'Tres decisiones mantienen la sesión enfocada; el resto es producción.'}
                />
                <div className="mt-10">
                    <ServiceFunnelProcess
                        items={landingConfig.process.slice(1, 4).map((step) => ({
                            title: localized(step.title, locale),
                            description: localized(step.description, locale),
                        }))}
                    />
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
                    checkoutRoute="booking.checkout"
                    paymentProvider="stripe"
                    product={product}
                    popupVariant="foodReels"
                    popupHeroProofVideo={popupProofMedia ? toHeroProofVideo(popupProofMedia) : null}
                    highlight
                    analyticsPayload={analyticsPayload}
                />
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft">
                <ServiceFunnelHeading
                    eyebrow={en ? 'Before booking' : 'Antes de reservar'}
                    title={en ? 'Direct answers for the production.' : 'Respuestas directas para la producción.'}
                    description={en
                        ? 'Scope, timing, and final formats are confirmed before the session.'
                        : 'El alcance, el horario y los formatos finales se confirman antes de la sesión.'}
                />
                <div className="mt-10">
                    <ServiceFunnelFaq
                        items={landingConfig.faqs.map((faq) => ({
                            question: localized(faq.question, locale),
                            answer: localized(faq.answer, locale),
                        }))}
                    />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelFinalCta
                eyebrow={en ? 'Lapsique Media / Food' : 'Lapsique Media / Gastronomía'}
                title={copy.finalTitle}
                description={copy.finalDescription}
                primaryAction={primaryAction('final')}
                secondaryAction={whatsappAction('final')}
            />

            <PortfolioLightbox
                items={lightboxItems}
                activeIndex={lightboxIndex}
                onClose={() => setLightboxIndex(null)}
                onNavigate={setLightboxIndex}
            />
        </SiteLayout>
    );
}

function HeroMedia({ media }: { media: ServicePortfolioMedia }) {
    if (media.kind === 'video') {
        return (
            <EditorialVideoPlayer
                src={media.src}
                poster={media.poster}
                title={media.alt}
                preload="metadata"
                autoPlay={false}
                muted={false}
                hasAudio={media.hasAudio ?? false}
                className="h-full w-full"
                videoClassName="h-full w-full object-cover"
            />
        );
    }

    return (
        <img
            src={media.src}
            alt={media.alt}
            className="h-full w-full object-cover"
            loading="eager"
            decoding="async"
            fetchPriority="high"
        />
    );
}

function PhotoMosaic({
    items,
    onOpen,
}: {
    items: ServicePortfolioMedia[];
    onOpen: (index: number) => void;
}) {
    return (
        <div className="mt-10 grid auto-rows-[11rem] grid-cols-2 gap-3 sm:auto-rows-[14rem] md:grid-cols-4">
            {items.map((item, index) => (
                <PhotoButton
                    key={item.id}
                    item={item}
                    onClick={() => onOpen(index)}
                    className={cn(
                        index === 0 && 'col-span-2 row-span-2',
                        index === 3 && 'md:col-span-2',
                        index === 6 && 'md:row-span-2',
                    )}
                />
            ))}
        </div>
    );
}

function PhotoStrip({
    items,
    onOpen,
}: {
    items: ServicePortfolioMedia[];
    onOpen: (index: number) => void;
}) {
    return (
        <div className="mt-10 grid grid-cols-2 gap-3 md:grid-cols-4">
            {items.map((item, index) => (
                <PhotoButton
                    key={item.id}
                    item={item}
                    onClick={() => onOpen(index)}
                    className="aspect-[4/5]"
                    inverse
                />
            ))}
        </div>
    );
}

function PhotoButton({
    item,
    onClick,
    className,
    inverse = false,
}: {
    item: ServicePortfolioMedia;
    onClick: () => void;
    className?: string;
    inverse?: boolean;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'group relative min-h-0 overflow-hidden bg-black text-start outline outline-1 -outline-offset-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
                inverse ? 'outline-white/15' : 'outline-black/10',
                className,
            )}
            aria-label={item.alt}
        >
            <img
                src={item.src}
                alt={item.alt}
                loading="lazy"
                decoding="async"
                className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02] motion-reduce:transition-none"
            />
            <span className="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/78 to-transparent px-3 pb-3 pt-10 font-mono text-[0.62rem] uppercase tracking-[0.12em] text-white/82">
                {item.projectLabel}
            </span>
        </button>
    );
}

function filterPortfolio(
    portfolio: ServicePortfolioBundle,
    predicate: (media: ServicePortfolioMedia) => boolean,
): ServicePortfolioBundle {
    const projects = portfolio.projects
        .map((project) => ({
            ...project,
            media: uniqueMedia(project.media.filter(predicate)),
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

function uniqueMedia(items: ServicePortfolioMedia[]): ServicePortfolioMedia[] {
    const seen = new Set<string>();

    return items.filter((item) => {
        const key = `${item.id}:${item.src}`;
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    });
}

function isActivationMedia(media: ServicePortfolioMedia): boolean {
    const haystack = `${media.projectKey} ${media.projectLabel} ${media.sessionLabel ?? ''} ${media.alt}`.toLowerCase();

    return ['the-roof', 'roof', 'activation', 'model', 'couple', 'toast', 'experience']
        .some((keyword) => haystack.includes(keyword));
}

function toPortfolioItem(media: ServicePortfolioMedia, index: number): PortfolioItemData {
    return {
        id: index + 1,
        title: media.alt,
        slug: null,
        type: 'food_reels',
        source: 'service-curation',
        caption: media.sessionLabel ?? media.location ?? null,
        tags: [media.projectKey],
        asset_url: media.src,
        poster_url: null,
        playback_url: null,
        embed_url: null,
        youtube_id: null,
        youtube_url: null,
        media_type: 'image',
        is_featured: index === 0,
        orientation: media.orientation,
    };
}

function toHeroProofVideo(media: ServicePortfolioMedia) {
    return {
        title: media.alt,
        media_type: media.kind,
        embed_url: null,
        playback_url: media.kind === 'video' ? media.src : null,
        poster_url: media.poster ?? (media.kind === 'image' ? media.src : null),
    } as const;
}

function pickDistinctPopupMedia(
    portfolio: ServicePortfolioBundle,
): ServicePortfolioMedia | null {
    const media = uniqueMedia(
        portfolio.projects.flatMap((project) => project.media),
    ).filter(
        (item) =>
            item.id !== portfolio.hero.id
            && item.src !== portfolio.hero.src,
    );

    return media.find(
        (item) => item.kind === 'video' && Boolean(item.poster),
    )
        ?? media.find((item) => item.kind === 'image')
        ?? media[0]
        ?? null;
}

function buildWhatsAppHref(phone: string | undefined, text: string): string {
    const cleanedPhone = (phone ?? '').replace(/[^\d]/g, '');
    const base = cleanedPhone ? `https://wa.me/${cleanedPhone}` : 'https://wa.me/';

    return `${base}?text=${encodeURIComponent(text)}`;
}
