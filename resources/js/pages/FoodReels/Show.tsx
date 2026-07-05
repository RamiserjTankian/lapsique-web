import { useEffect, useMemo, useState, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Camera,
    Check,
    Clapperboard,
    Flame,
    Images,
    MessageCircle,
    Play,
    Sparkles,
    UtensilsCrossed,
    Video,
} from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget, type BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import { PaymentTrustOrTestMode } from '@/components/lapsique/PaymentTrustPanel';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { openBookingModal } from '@/lib/openBookingModal';
import { cn, formatMxn } from '@/lib/utils';
import type { BookingSlot, PageProps } from '@/types';

interface FoodReelsShowProps {
    price: number;
    slots: BookingSlot[];
    errors?: Record<string, string>;
}

type FoodReel = {
    id: string;
    title: string;
    caption: string;
    src: string;
    poster: string;
    duration: string;
};

type FoodPhoto = {
    id: string;
    title: string;
    caption: string;
    src: string;
    orientation: 'portrait' | 'square' | 'landscape';
    layout?: 'large' | 'wide' | 'tall';
};

const FOOD_REELS: FoodReel[] = [
    {
        id: 'day-sushi',
        title: 'SushiClub día',
        caption: 'Mesa, rollos y manos en acción para promo vertical.',
        src: '/videos/food-reels/sushiclub-day-sushi-promo.mp4',
        poster: '/images/food-reels/sushiclub-day-sushi-promo-poster.jpg',
        duration: '0:12',
    },
    {
        id: 'night-food',
        title: 'SushiClub noche',
        caption: 'Servicio nocturno, drinks y platos entrando a mesa.',
        src: '/videos/food-reels/sushiclub-night-food.mp4',
        poster: '/images/food-reels/sushiclub-night-food-poster.jpg',
        duration: '0:12',
    },
    {
        id: 'day-plates',
        title: 'Platos de día',
        caption: 'Producto claro para menú, stories y anuncios.',
        src: '/videos/food-reels/sushiclub-day-plates.mp4',
        poster: '/images/food-reels/sushiclub-day-plates-poster.jpg',
        duration: '0:12',
    },
    {
        id: 'destilados',
        title: 'Drinks y destilados',
        caption: 'Movimiento de barra para consumo y nightlife.',
        src: '/videos/food-reels/sushiclub-destilados.mp4',
        poster: '/images/food-reels/sushiclub-destilados-poster.jpg',
        duration: '0:12',
    },
];

const PRODUCT_PHOTOS: FoodPhoto[] = [
    {
        id: 'santino-charcuterie-table',
        title: 'Tabla de charcutería',
        caption: 'Mesa abundante con quesos, carnes frias y platillos al fondo.',
        src: '/images/food-reels/food-santino-charcuterie-table.webp',
        orientation: 'portrait',
        layout: 'large',
    },
    {
        id: 'santino-charcuterie-overhead',
        title: 'Mesa completa',
        caption: 'Composición cenital para enseñar variedad y abundancia.',
        src: '/images/food-reels/food-santino-charcuterie-overhead.webp',
        orientation: 'landscape',
        layout: 'wide',
    },
    {
        id: 'santino-burger-fries',
        title: 'Hamburguesa con papas',
        caption: 'Burger nocturna con textura, salsa y antojo inmediato.',
        src: '/images/food-reels/food-santino-burger-fries.webp',
        orientation: 'portrait',
        layout: 'tall',
    },
    {
        id: 'santino-steak-close',
        title: 'Steak close-up',
        caption: 'Detalle de carne y vegetales con lectura fuerte de producto.',
        src: '/images/food-reels/food-santino-steak-close.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-fried-fish',
        title: 'Pescado en mesa',
        caption: 'Plato principal con guarnición, luz cálida y contexto de mesa.',
        src: '/images/food-reels/food-santino-fried-fish.webp',
        orientation: 'portrait',
        layout: 'tall',
    },
    {
        id: 'santino-brunch-table',
        title: 'Brunch completo',
        caption: 'Mesa cenital con fruta, waffles, bowls y platos servidos.',
        src: '/images/food-reels/food-santino-brunch-table.webp',
        orientation: 'landscape',
        layout: 'wide',
    },
    {
        id: 'santino-shrimp-skewer',
        title: 'Brocheta de camarón',
        caption: 'Producto limpio con salsas, textura y color.',
        src: '/images/food-reels/food-santino-shrimp-skewer.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-shrimp-overhead',
        title: 'Camarón cenital',
        caption: 'Foto superior para menú digital, pauta y carrusel.',
        src: '/images/food-reels/food-santino-shrimp-overhead.webp',
        orientation: 'landscape',
        layout: 'wide',
    },
    {
        id: 'santino-octopus-overhead',
        title: 'Pulpo servido',
        caption: 'Plato cenital con styling de mesa y detalles de servicio.',
        src: '/images/food-reels/food-santino-octopus-overhead.webp',
        orientation: 'landscape',
        layout: 'wide',
    },
    {
        id: 'santino-cocktail',
        title: 'Cóctel de barra',
        caption: 'Bebida con textura y luz nocturna para elevar ticket.',
        src: '/images/food-reels/food-santino-cocktail.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-ravioli-table',
        title: 'Pasta en mesa',
        caption: 'Plato caliente con ambiente, velas y mesa servida.',
        src: '/images/food-reels/food-santino-ravioli-table.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-steak-bowl',
        title: 'Steak con guarnición',
        caption: 'Plato de carne con luz de restaurante y contraste.',
        src: '/images/food-reels/food-santino-steak-bowl.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-pancakes',
        title: 'Pancakes con berries',
        caption: 'Desayuno dulce con syrup, color y fondo cálido.',
        src: '/images/food-reels/food-santino-pancakes.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-ceviche-hand',
        title: 'Ceviche en acción',
        caption: 'Mano entrando al plato para dar escala y movimiento.',
        src: '/images/food-reels/food-santino-ceviche-hand.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-waffle-table',
        title: 'Waffles en mesa',
        caption: 'Mesa de brunch con fruta, bebida y profundidad.',
        src: '/images/food-reels/food-santino-waffle-table.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-omelette-close',
        title: 'Omelette close-up',
        caption: 'Detalle de platillo con salsa y textura clara.',
        src: '/images/food-reels/food-santino-omelette-close.webp',
        orientation: 'portrait',
    },
];

const HERO_PHOTOS: FoodPhoto[] = [
    PRODUCT_PHOTOS[1],
    PRODUCT_PHOTOS[2],
];

const SESSION_PHOTOS: FoodPhoto[] = [
    PRODUCT_PHOTOS[5],
    PRODUCT_PHOTOS[0],
    PRODUCT_PHOTOS[4],
    PRODUCT_PHOTOS[8],
    PRODUCT_PHOTOS[10],
];

const ACTIVATION_PHOTOS: FoodPhoto[] = [
    {
        id: 'santino-couple-bar',
        title: 'Cena con marca',
        caption: 'Personas, plato, drinks y contexto de restaurante.',
        src: '/images/food-reels/food-santino-couple-bar.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-couple-burger',
        title: 'Hamburguesa con modelos',
        caption: 'Escena social con comida al centro y gesto natural.',
        src: '/images/food-reels/food-santino-couple-burger.webp',
        orientation: 'portrait',
    },
    PRODUCT_PHOTOS[13],
];

const FOOD_PAGE_COPY = {
    es: {
        analyticsName: 'Landing de reels de comida',
        heroTitle: 'Reels de comida que hacen que el antojo se vuelva reserva.',
        heroDescription: 'Producción de reels, fotos y activaciones para restaurantes que necesitan verse irresistibles en Instagram, anuncios y menú digital.',
        heroPrimary: 'Agendar sesión',
        heroSecondary: 'Ver prueba real',
        proofClient: 'Sesiones reales',
        proofLines: ['comida + drinks', 'menú + redes', 'reels verticales'],
        featureItems: ['Reels 9:16', 'Fotos editadas', 'Activaciones'],
        whatsappCta: 'Cotizar por WhatsApp',
        reelSectionTitle: 'Reels gastronómicos',
        reelSectionDescription: 'Piezas verticales listas para mostrar platos, bebidas, ambiente y manos en acción sin perder ritmo de redes.',
        sessionSectionTitle: 'Contenido para vender una experiencia completa',
        sessionSectionDescription: 'Combinamos mesa completa, platillos estrella, detalles de barra, modelos y escenas de consumo para que la landing se sienta como una producción real.',
        photoSectionTitle: 'Foto de producto',
        photoSectionDescription: 'Una selección más amplia de platillos, drinks, mesa y detalles para mostrar variedad real de menú, no solo fotos aisladas.',
        activationTitle: 'Activaciones con modelos',
        activationDescription: 'Cuando el restaurante vende experiencia, sumamos personas, drinks, mesa y ambiente para crear contenido que convierte.',
        packageTitle: 'Paquete de producción gastronómica',
        packageDescription: 'Una sesión enfocada en contenido de comida: reels verticales, fotos editadas y narrativa para restaurantes.',
        packageCta: 'Reservar fecha',
        packageCards: [
            {
                title: 'Reels',
                copy: 'Piezas verticales para Instagram, anuncios y stories.',
                items: ['Platos en movimiento', 'Drinks y servicio', 'Formato 9:16'],
            },
            {
                title: 'Fotos',
                copy: 'Imágenes útiles para menú, pauta y redes.',
                items: ['Producto limpio', 'Ambiente de mesa', 'Entrega editada'],
            },
            {
                title: 'Activación',
                copy: 'Contenido lifestyle con modelos y experiencia real.',
                items: ['Personas en escena', 'Mood de restaurante', 'Contexto social'],
            },
        ],
        bookingTitle: 'Agenda la sesión y armamos el shot list de comida.',
        bookingDescription: 'Al reservar definimos platos prioritarios, horario, mood, modelos si aplica y piezas finales para que el contenido salga con objetivo comercial.',
        finalWhatsApp: 'Preguntar por WhatsApp',
        bookingProduct: {
            checkoutLabel: 'Producción gastronómica',
            headerTitle: 'Agenda tu sesión de comida',
            headerDescription: 'Selecciona fecha, comparte el concepto del restaurante y confirma la producción.',
            summaryTitle: 'Sesión de contenido para restaurante',
            summaryDescription: 'Reels, fotos editadas y activacion visual pensada para vender comida.',
            lines: ['Shot list por platos prioritarios', 'Contenido vertical para redes y pauta', 'Entrega lista para publicar'],
            cartService: 'Reels y fotos de comida',
            cartDuration: 'Sesión dirigida en restaurante',
            perks: ['Reels verticales', 'Fotos editadas', 'Dirección de comida y mesa', 'Uso para anuncios y menú digital', 'Pago seguro con tarjeta'],
            terms: ['La fecha queda sujeta a disponibilidad.', 'El alcance final se confirma según platos, locación y modelo.', 'Puedes reprogramar con aviso previo según disponibilidad.', 'El material puede usarse en portafolio salvo acuerdo distinto.'],
            unavailableWhatsApp: 'Hola, quiero una sesión de reels de comida para mi restaurante.',
        },
        whatsappPrefill: 'Hola, quiero cotizar reels de comida y fotos para mi restaurante.',
    },
    en: {
        analyticsName: 'Food reels landing page',
        heroTitle: 'Food reels that turn craving into bookings.',
        heroDescription: 'Reel production, food photography, and lifestyle activations for restaurants that need to look irresistible on Instagram, ads, and digital menus.',
        heroPrimary: 'Book session',
        heroSecondary: 'View real proof',
        proofClient: 'Real shoots',
        proofLines: ['food + drinks', 'menus + social', 'vertical reels'],
        featureItems: ['9:16 reels', 'Edited photos', 'Activations'],
        whatsappCta: 'Quote on WhatsApp',
        reelSectionTitle: 'Food reels',
        reelSectionDescription: 'Vertical pieces built to show plates, drinks, atmosphere, and hands in motion without losing social rhythm.',
        sessionSectionTitle: 'Content that sells the full dining experience',
        sessionSectionDescription: 'We combine full tables, hero dishes, bar details, models, and consumption scenes so the landing feels like a real production.',
        photoSectionTitle: 'Product photography',
        photoSectionDescription: 'A wider selection of dishes, drinks, table scenes, and details to show real menu variety, not isolated photos.',
        activationTitle: 'Model activations',
        activationDescription: 'When the restaurant sells experience, we add people, drinks, table, and mood to create content that converts.',
        packageTitle: 'Food production package',
        packageDescription: 'A session focused on restaurant content: vertical reels, edited photos, and a useful food story.',
        packageCta: 'Reserve date',
        packageCards: [
            {
                title: 'Reels',
                copy: 'Vertical pieces for Instagram, ads, and stories.',
                items: ['Plates in motion', 'Drinks and service', '9:16 format'],
            },
            {
                title: 'Photos',
                copy: 'Images ready for menus, ads, and social.',
                items: ['Clean product', 'Table atmosphere', 'Edited delivery'],
            },
            {
                title: 'Activation',
                copy: 'Lifestyle content with models and real experience.',
                items: ['People in scene', 'Restaurant mood', 'Social context'],
            },
        ],
        bookingTitle: 'Book the session and we build the food shot list.',
        bookingDescription: 'After booking, we define priority dishes, timing, mood, models if needed, and final pieces so the content has a clear sales goal.',
        finalWhatsApp: 'Ask on WhatsApp',
        bookingProduct: {
            checkoutLabel: 'Food production',
            headerTitle: 'Book your food session',
            headerDescription: 'Choose a date, share the restaurant concept, and confirm production.',
            summaryTitle: 'Restaurant content session',
            summaryDescription: 'Reels, edited photos, and visual activation built to sell food.',
            lines: ['Shot list by priority dishes', 'Vertical content for social and ads', 'Delivery ready to publish'],
            cartService: 'Food reels and photos',
            cartDuration: 'Directed restaurant session',
            perks: ['Vertical reels', 'Edited photos', 'Food and table direction', 'Use for ads and digital menus', 'Secure card payment'],
            terms: ['Dates depend on availability.', 'Final scope is confirmed by dishes, location, and model needs.', 'You can reschedule with prior notice depending on availability.', 'Material may be used in portfolio unless agreed otherwise.'],
            unavailableWhatsApp: 'Hi, I want a food reels session for my restaurant.',
        },
        whatsappPrefill: 'Hi, I want to quote food reels and photos for my restaurant.',
    },
} as const;

const FEATURE_ICONS = [
    Clapperboard,
    Camera,
    Sparkles,
] as const;

export default function FoodReelsShow({ price, slots, errors }: FoodReelsShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const copy = FOOD_PAGE_COPY[locale === 'en' ? 'en' : 'es'];
    const [activeReelId, setActiveReelId] = useState(FOOD_REELS[0].id);
    const activeReel = FOOD_REELS.find((reel) => reel.id === activeReelId) ?? FOOD_REELS[0];
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
            paymentCopy: locale === 'en'
                ? 'Secure card payment powered by Stripe.'
                : 'Pago seguro con tarjeta procesado por Stripe.',
            unavailableWhatsApp: copy.bookingProduct.unavailableWhatsApp,
        }),
        [copy, locale],
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
            analyticsPayload: {
                ...analyticsPayload,
                source,
            },
        });
    };

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('food_reels_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#070706] text-white">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_74%_20%,rgb(206_58_42/0.22),transparent_30%),linear-gradient(180deg,#070706_0%,#0d0b09_64%,var(--background)_100%)]" />
                <div className="relative mx-auto grid min-h-[min(760px,82svh)] max-w-6xl gap-8 px-4 pb-10 pt-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(300px,0.62fr)_minmax(220px,0.42fr)] lg:items-center lg:gap-6 lg:pb-12">
                    <div className="flex max-w-3xl flex-col gap-6">
                        <div className="flex flex-col gap-4">
                            <h1 className="max-w-4xl font-display text-[2.6rem] font-bold leading-[0.96] tracking-tight text-white drop-shadow-[0_10px_34px_rgb(0_0_0/0.45)] sm:text-5xl lg:text-6xl xl:text-7xl">
                                {copy.heroTitle}
                            </h1>
                            <p className="max-w-2xl text-base leading-relaxed text-white/78 sm:text-lg">
                                {copy.heroDescription}
                            </p>
                        </div>

                        <div className="grid max-w-xl gap-3 sm:grid-cols-2">
                            <BookingCtaButton
                                type="button"
                                className="min-h-12 rounded-lg px-6"
                                onClick={() => openBooking('hero')}
                            >
                                <CalendarDays data-icon="inline-start" />
                                {copy.heroPrimary}
                            </BookingCtaButton>
                            <Button
                                type="button"
                                variant="outline"
                                size="xl"
                                className="min-h-12 rounded-lg border-white/22 bg-white/5 text-white hover:bg-white/12 hover:text-white"
                                asChild
                            >
                                <a href="#prueba-real">
                                    <Play data-icon="inline-start" />
                                    {copy.heroSecondary}
                                </a>
                            </Button>
                        </div>

                        <div className="max-w-xs border-l-2 border-[#e43d30] pl-4">
                            <p className="font-display text-xl font-bold text-white">
                                {copy.proofClient}
                            </p>
                            <div className="mt-1 flex flex-col gap-0.5 text-sm text-white/70">
                                {copy.proofLines.map((line) => (
                                    <span key={line}>{line}</span>
                                ))}
                            </div>
                        </div>

                        <div className="grid max-w-xl gap-2 sm:grid-cols-3">
                            {copy.featureItems.map((label, index) => {
                                const Icon = FEATURE_ICONS[index];

                                return (
                                    <div
                                        key={label}
                                        className="flex min-h-12 items-center gap-2 rounded-lg border border-white/14 bg-white/7 px-3 text-sm font-semibold text-white/84 backdrop-blur-md"
                                    >
                                        <Icon className="size-4 shrink-0 text-[#ff6b55]" />
                                        <span>{label}</span>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div className="relative mx-auto w-full max-w-[330px]">
                        <div className="relative overflow-hidden rounded-[2rem] border border-white/18 bg-black p-2 shadow-[0_32px_90px_rgb(0_0_0/0.55)]">
                            <AutoplayVideo
                                key={activeReel.id}
                                src={activeReel.src}
                                poster={activeReel.poster}
                                title={activeReel.title}
                                eager
                                pauseWhenOffscreen={false}
                                className="aspect-[9/16] rounded-[1.45rem]"
                            />
                        </div>
                    </div>

                    <div className="hidden flex-col gap-3 lg:flex">
                        {HERO_PHOTOS.map((photo) => (
                            <MediaPhoto key={photo.id} photo={photo} compact />
                        ))}
                    </div>
                </div>
            </section>

            <section id="prueba-real" className="mx-auto flex max-w-6xl flex-col gap-5 px-4 pt-10 sm:px-6 lg:pt-12">
                <SectionHeader
                    icon={<Video className="size-5" />}
                    title={copy.reelSectionTitle}
                    description={copy.reelSectionDescription}
                />
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {FOOD_REELS.map((reel) => (
                        <ReelSelector
                            key={reel.id}
                            reel={reel}
                            selected={reel.id === activeReel.id}
                            onSelect={() => setActiveReelId(reel.id)}
                        />
                    ))}
                </div>
            </section>

            <section className="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-12 sm:px-6">
                <SectionHeader
                    icon={<Sparkles className="size-5" />}
                    title={copy.sessionSectionTitle}
                    description={copy.sessionSectionDescription}
                    className="max-w-3xl"
                />
                <div className="grid auto-rows-[210px] gap-3 sm:grid-cols-2 sm:auto-rows-[250px] lg:grid-cols-4 lg:auto-rows-[245px]">
                    {SESSION_PHOTOS.map((photo) => (
                        <MediaPhoto key={photo.id} photo={photo} mosaic />
                    ))}
                </div>
            </section>

            <section className="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-12 sm:px-6">
                <SectionHeader
                    icon={<Images className="size-5" />}
                    title={copy.photoSectionTitle}
                    description={copy.photoSectionDescription}
                    className="max-w-3xl"
                />
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {PRODUCT_PHOTOS.map((photo) => (
                        <MediaPhoto key={photo.id} photo={photo} dense />
                    ))}
                </div>
            </section>

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#0b0908] py-12 text-white">
                <div className="absolute inset-0 bg-[linear-gradient(90deg,rgb(216_60_45/0.12),transparent_38%),radial-gradient(circle_at_78%_34%,rgb(119_146_76/0.18),transparent_30%)]" />
                <div className="relative mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.42fr_1fr] lg:items-center">
                    <SectionHeader
                        icon={<Flame className="size-5" />}
                        title={copy.activationTitle}
                        description={copy.activationDescription}
                        inverted
                    />
                    <div className="grid gap-3 md:grid-cols-3">
                        {ACTIVATION_PHOTOS.map((photo) => (
                            <MediaPhoto key={photo.id} photo={photo} dark />
                        ))}
                    </div>
                </div>
            </section>

            <section className="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-12 sm:px-6">
                <div className="grid gap-6 lg:grid-cols-[0.46fr_1fr] lg:items-end">
                    <SectionHeader
                        icon={<UtensilsCrossed className="size-5" />}
                        title={copy.packageTitle}
                        description={copy.packageDescription}
                    />
                    <div className="rounded-xl border border-primary/30 bg-[linear-gradient(135deg,rgb(255_255_255/0.78),rgb(244_239_232/0.72))] p-5 shadow-[0_24px_80px_rgb(42_23_12/0.10)] backdrop-blur-md dark:bg-[linear-gradient(135deg,rgb(15_12_10/0.86),rgb(24_18_14/0.82))] dark:shadow-black/30">
                        <p className="text-sm font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                            {locale === 'en' ? 'From' : 'Desde'}
                        </p>
                        <div className="mt-2 flex flex-wrap items-end gap-3">
                            <p className="font-mono-tabular text-4xl font-bold text-primary">
                                {formatMxn(price)}
                            </p>
                            <PaymentTrustOrTestMode variant="stripe" layout="compact" className="pb-1" />
                        </div>
                    </div>
                </div>

                <div className="grid gap-3 md:grid-cols-3">
                    {copy.packageCards.map((card, index) => (
                        <PackageCard key={card.title} card={card} featured={index === 1} />
                    ))}
                </div>

                <div className="flex flex-col gap-3 rounded-xl border border-border/70 bg-background/82 p-5 shadow-sm backdrop-blur-md md:flex-row md:items-center md:justify-between">
                    <div className="max-w-2xl">
                        <h2 className="font-display text-2xl font-bold text-foreground">
                            {copy.bookingTitle}
                        </h2>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {copy.bookingDescription}
                        </p>
                    </div>
                    <div className="flex shrink-0 flex-col gap-2 sm:flex-row">
                        <Button
                            variant="outline"
                            className="rounded-lg"
                            asChild
                        >
                            <a
                                href={whatsappHref}
                                target="_blank"
                                rel="noopener noreferrer"
                                onClick={() => trackWhatsApp('package')}
                            >
                                <MessageCircle data-icon="inline-start" />
                                {copy.finalWhatsApp}
                            </a>
                        </Button>
                        <BookingCtaButton
                            type="button"
                            className="rounded-lg"
                            onClick={() => openBooking('package')}
                        >
                            {copy.packageCta}
                            <ArrowRight data-icon="inline-end" />
                        </BookingCtaButton>
                    </div>
                </div>
            </section>

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                className="mt-0"
                checkoutRoute="booking.checkout"
                paymentProvider="stripe"
                product={product}
                popupVariant="home"
                popupHeroProofVideo={{
                    title: activeReel.title,
                    media_type: 'video',
                    embed_url: null,
                    playback_url: activeReel.src,
                    poster_url: activeReel.poster,
                }}
                highlight
                analyticsPayload={analyticsPayload}
            />
        </SiteLayout>
    );
}

function SectionHeader({
    icon,
    title,
    description,
    className,
    inverted = false,
}: {
    icon: ReactNode;
    title: string;
    description: string;
    className?: string;
    inverted?: boolean;
}) {
    return (
        <div className={cn('flex flex-col gap-3', className)}>
            <div className={cn(
                'flex size-10 items-center justify-center rounded-lg border',
                inverted
                    ? 'border-white/18 bg-white/8 text-[#f05a44]'
                    : 'border-primary/25 bg-primary/10 text-primary',
            )}>
                {icon}
            </div>
            <div>
                <h2 className={cn(
                    'font-display text-3xl font-bold leading-tight md:text-4xl',
                    inverted ? 'text-white' : 'text-foreground',
                )}>
                    {title}
                </h2>
                <p className={cn(
                    'mt-3 text-sm leading-relaxed md:text-base',
                    inverted ? 'text-white/72' : 'text-muted-foreground',
                )}>
                    {description}
                </p>
            </div>
        </div>
    );
}

function ReelSelector({
    reel,
    selected,
    onSelect,
}: {
    reel: FoodReel;
    selected: boolean;
    onSelect: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onSelect}
            className={cn(
                'group overflow-hidden rounded-lg border bg-card text-left shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                selected
                    ? 'border-primary shadow-[0_20px_60px_rgb(206_58_42/0.18)]'
                    : 'border-border/70 hover:border-primary/60',
            )}
            aria-pressed={selected}
            aria-label={reel.title}
        >
            <div className="relative aspect-[9/14] overflow-hidden bg-black">
                <AutoplayVideo
                    src={reel.src}
                    poster={reel.poster}
                    title={reel.title}
                    eager
                    pauseWhenOffscreen={false}
                    className="h-full w-full"
                    videoClassName="transition duration-500 group-hover:scale-105"
                    preload="metadata"
                />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,transparent_46%,rgb(0_0_0/0.72)_100%)]" />
                <div className="absolute bottom-3 left-3 right-3 flex items-center justify-between gap-2 text-xs font-semibold text-white">
                    <span className="inline-flex items-center gap-1.5">
                        <Play className="size-3.5 fill-current" />
                        {reel.duration}
                    </span>
                    {selected ? <span className="text-[#ff6b55]">Live</span> : null}
                </div>
            </div>
        </button>
    );
}

function MediaPhoto({
    photo,
    compact = false,
    dark = false,
    mosaic = false,
    dense = false,
}: {
    photo: FoodPhoto;
    compact?: boolean;
    dark?: boolean;
    mosaic?: boolean;
    dense?: boolean;
}) {
    const aspectClass = mosaic
        ? 'h-full'
        : dense
          ? 'aspect-[4/5]'
        : compact
        ? photo.orientation === 'landscape'
          ? 'aspect-[4/3]'
          : 'aspect-[3/4]'
        : photo.orientation === 'landscape'
          ? 'aspect-[16/10]'
          : photo.orientation === 'square'
            ? 'aspect-square'
            : 'aspect-[4/5]';

    return (
        <figure className={cn(
            'group overflow-hidden rounded-lg border shadow-sm',
            dark ? 'border-white/14 bg-white/6' : 'border-border/70 bg-card',
            mosaic && 'min-h-0',
            mosaic && photo.layout === 'large' && 'sm:col-span-2 sm:row-span-2',
            mosaic && photo.layout === 'wide' && 'sm:col-span-2',
            mosaic && photo.layout === 'tall' && 'sm:row-span-2',
        )}>
            <div className={cn('overflow-hidden bg-black', aspectClass)}>
                <img
                    src={photo.src}
                    alt={photo.title}
                    className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    loading={compact ? 'eager' : 'lazy'}
                />
            </div>
        </figure>
    );
}

function PackageCard({
    card,
    featured,
}: {
    card: {
        title: string;
        copy: string;
        items: readonly string[];
    };
    featured: boolean;
}) {
    return (
        <article className={cn(
            'flex min-h-[260px] flex-col gap-5 rounded-lg border bg-card p-5 shadow-sm',
            featured && 'border-primary/60 shadow-[0_22px_70px_rgb(206_58_42/0.16)]',
        )}>
            <div className="flex flex-col gap-2">
                <p className="font-display text-2xl font-bold text-foreground">
                    {card.title}
                </p>
                <p className="text-sm leading-relaxed text-muted-foreground">
                    {card.copy}
                </p>
            </div>
            <ul className="mt-auto flex flex-col gap-3">
                {card.items.map((item) => (
                    <li key={item} className="flex items-start gap-2 text-sm text-foreground">
                        <Check className="mt-0.5 size-4 shrink-0 text-primary" />
                        <span>{item}</span>
                    </li>
                ))}
            </ul>
        </article>
    );
}

function buildWhatsAppHref(phone: string | undefined, text: string): string {
    const cleanedPhone = (phone ?? '').replace(/[^\d]/g, '');
    const base = cleanedPhone ? `https://wa.me/${cleanedPhone}` : 'https://wa.me/';

    return `${base}?text=${encodeURIComponent(text)}`;
}
