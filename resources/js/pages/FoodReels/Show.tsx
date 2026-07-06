import { useEffect, useMemo, useState, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Check,
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
import { useIsMobileViewport } from '@/hooks/useMediaQuery';
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
    },
    {
        id: 'night-food',
        title: 'SushiClub noche',
        caption: 'Servicio nocturno, drinks y platos entrando a mesa.',
        src: '/videos/food-reels/sushiclub-night-food.mp4',
        poster: '/images/food-reels/sushiclub-night-food-poster.jpg',
    },
    {
        id: 'day-plates',
        title: 'Platos de día',
        caption: 'Producto claro para menú, stories y anuncios.',
        src: '/videos/food-reels/sushiclub-day-plates.mp4',
        poster: '/images/food-reels/sushiclub-day-plates-poster.jpg',
    },
    {
        id: 'destilados',
        title: 'Drinks y destilados',
        caption: 'Movimiento de barra para consumo y nightlife.',
        src: '/videos/food-reels/sushiclub-destilados.mp4',
        poster: '/images/food-reels/sushiclub-destilados-poster.jpg',
    },
];

const PRODUCT_PHOTOS: FoodPhoto[] = [
    {
        id: 'sushiclub-table-reels',
        title: 'Mesa de sushi',
        caption: 'Plato, manos y mesa con lectura clara de experiencia.',
        src: '/images/food-reels/sushiclub-table-reels.webp',
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
        id: 'roof-loaded-potatoes-close',
        title: 'Platillo cargado',
        caption: 'Close-up de comida con contraste, textura y color.',
        src: '/images/food-reels/food-roof-loaded-potatoes-close.webp',
        orientation: 'portrait',
        layout: 'tall',
    },
    {
        id: 'tanuki-shrimp-rice',
        title: 'Arroz con camarón',
        caption: 'Producto vertical con proteína, color y foco claro.',
        src: '/images/food-reels/food-tanuki-shrimp-rice.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-burger-fries',
        title: 'Hamburguesa con papas',
        caption: 'Burger nocturna con textura, salsa y antojo inmediato.',
        src: '/images/food-reels/food-santino-burger-fries.webp',
        orientation: 'portrait',
    },
    {
        id: 'sushiclub-salmon-plate',
        title: 'Plato principal',
        caption: 'Producto servido con luz cálida y acabado editorial.',
        src: '/images/food-reels/sushiclub-salmon-plate.webp',
        orientation: 'portrait',
    },
    {
        id: 'roof-sauce-pour',
        title: 'Salsa en acción',
        caption: 'Movimiento de producto con buen contraste y foco.',
        src: '/images/food-reels/food-roof-sauce-pour.webp',
        orientation: 'portrait',
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
        id: 'tanuki-octopus-rice',
        title: 'Arroz con pulpo',
        caption: 'Close-up de producto para menú, pauta e historias.',
        src: '/images/food-reels/food-tanuki-octopus-rice.webp',
        orientation: 'portrait',
    },
    {
        id: 'sushiclub-drinks',
        title: 'Cócteles en mesa',
        caption: 'Bebidas con atmósfera para elevar ticket promedio.',
        src: '/images/food-reels/sushiclub-drinks.webp',
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
        id: 'roof-caesar',
        title: 'Ensalada de menú',
        caption: 'Foto limpia de platillo para menú y redes.',
        src: '/images/food-reels/food-roof-caesar.webp',
        orientation: 'portrait',
    },
    {
        id: 'tanuki-grill-rice',
        title: 'Arroz con proteína',
        caption: 'Foto cálida y directa para menú digital.',
        src: '/images/food-reels/food-tanuki-grill-rice.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-cocktail',
        title: 'Cóctel de barra',
        caption: 'Bebida con textura y luz nocturna para elevar ticket.',
        src: '/images/food-reels/food-santino-cocktail.webp',
        orientation: 'portrait',
    },
    {
        id: 'roof-fork-action',
        title: 'Mesa en movimiento',
        caption: 'Manos, cubiertos y comida en una escena activa.',
        src: '/images/food-reels/food-roof-fork-action.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-pancakes',
        title: 'Pancakes con berries',
        caption: 'Desayuno dulce con syrup, color y fondo cálido.',
        src: '/images/food-reels/food-santino-pancakes.webp',
        orientation: 'portrait',
    },
];

const SESSION_PHOTOS: FoodPhoto[] = [
    {
        id: 'santino-brunch-table',
        title: 'Brunch completo',
        caption: 'Mesa cenital con fruta, waffles, bowls y platos servidos.',
        src: '/images/food-reels/food-santino-brunch-table.webp',
        orientation: 'landscape',
        layout: 'wide',
    },
    {
        id: 'santino-charcuterie-table',
        title: 'Tabla de charcutería',
        caption: 'Mesa abundante con quesos, carnes frias y platillos al fondo.',
        src: '/images/food-reels/food-santino-charcuterie-table.webp',
        orientation: 'portrait',
        layout: 'tall',
    },
    PRODUCT_PHOTOS[0],
    {
        id: 'santino-waffle-table',
        title: 'Mesa con fruta',
        caption: 'Tabla con fruta, pan y platos para comunicar abundancia.',
        src: '/images/food-reels/food-santino-waffle-table.webp',
        orientation: 'landscape',
        layout: 'wide',
    },
    {
        id: 'roof-models-toast',
        title: 'Activación en mesa',
        caption: 'Personas, drinks y comida para reforzar la experiencia.',
        src: '/images/food-reels/food-roof-models-toast-clean.webp',
        orientation: 'portrait',
    },
];

const ACTIVATION_PHOTOS: FoodPhoto[] = [
    {
        id: 'roof-models-toast',
        title: 'Mesa con modelos',
        caption: 'Comida, drinks y personas con luz cálida.',
        src: '/images/food-reels/food-roof-models-toast-clean.webp',
        orientation: 'portrait',
    },
    {
        id: 'santino-couple-burger',
        title: 'Hamburguesa con modelos',
        caption: 'Escena social con comida al centro y gesto natural.',
        src: '/images/food-reels/food-santino-couple-burger.webp',
        orientation: 'portrait',
    },
    {
        id: 'roof-guacamole-models',
        title: 'Mesa social',
        caption: 'Escena de consumo con modelos, comida y color.',
        src: '/images/food-reels/food-roof-guacamole-models.webp',
        orientation: 'portrait',
    },
];

const FOOD_PAGE_COPY = {
    es: {
        analyticsName: 'Landing de reels de comida',
        heroTitle: 'Reels de comida que hacen que el antojo se vuelva reserva.',
        heroDescription: 'Producción de reels, fotos y activaciones para restaurantes que necesitan verse irresistibles en Instagram, anuncios y menú digital.',
        heroPrimary: 'Agendar sesión',
        heroSecondary: 'Ver prueba real',
        whatsappCta: 'Cotizar por WhatsApp',
        reelSectionTitle: 'Reels para restaurantes',
        sessionSectionTitle: 'Vende la experiencia de estar en tu restaurante',
        photoSectionTitle: 'Foto de producto para utilizar en tus menús o redes',
        activationTitle: 'Activaciones con modelos',
        activationDescription: 'Cuando el restaurante vende experiencia, sumamos personas, drinks, mesa y ambiente para crear contenido que convierte.',
        packageTitle: 'Paquete de producción gastronómica',
        packageDescription: 'Incluye 10 fotos de producto, 2 reels de activación sin modelos, máximo 5 platillos y 3 tomas de dron del espacio del restaurante con DJI Air 3 en vertical 4K.',
        packageCta: 'Reservar fecha',
        packageCards: [
            {
                title: '10 fotos',
                copy: 'Producto limpio para menú, redes y anuncios.',
                items: ['Máximo 5 platillos', 'Fotos editadas', 'Uso comercial'],
            },
            {
                title: '2 reels',
                copy: 'Activación de comida sin modelos, enfocada en producto.',
                items: ['Movimiento de platos', 'Drinks y servicio', 'Formato vertical'],
            },
            {
                title: '3 tomas de dron',
                copy: 'Recursos del espacio para elevar la percepción del restaurante.',
                items: ['DJI Air 3', 'Vertical 4K', 'Exterior o interior viable'],
            },
        ],
        bookingTitle: 'Agenda la sesión y armamos el shot list de comida.',
        bookingDescription: 'Al reservar definimos hasta 5 platillos prioritarios, horario, mood, tomas de dron y piezas finales para que el contenido salga con objetivo comercial.',
        finalWhatsApp: 'Preguntar por WhatsApp',
        bookingProduct: {
            checkoutLabel: 'Producción gastronómica',
            headerTitle: 'Agenda tu sesión de comida',
            headerDescription: 'Selecciona fecha, comparte el concepto del restaurante y confirma la producción.',
            summaryTitle: 'Sesión de contenido para restaurante',
            summaryDescription: '10 fotos, 2 reels sin modelos y 3 tomas verticales de dron para vender comida.',
            lines: ['Máximo 5 platillos prioritarios', 'Contenido vertical para redes y pauta', 'Entrega lista para publicar'],
            cartService: 'Reels y fotos de comida',
            cartDuration: 'Sesión dirigida en restaurante',
            perks: ['10 fotos de producto', '2 reels sin modelos', '3 tomas de dron DJI Air 3', 'Uso para anuncios y menú digital', 'Pago seguro con tarjeta'],
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
        whatsappCta: 'Quote on WhatsApp',
        reelSectionTitle: 'Restaurant reels',
        sessionSectionTitle: 'Sell the experience of being in your restaurant',
        photoSectionTitle: 'Product photography for your menus or social channels',
        activationTitle: 'Model activations',
        activationDescription: 'When the restaurant sells experience, we add people, drinks, table, and mood to create content that converts.',
        packageTitle: 'Food production package',
        packageDescription: 'Includes 10 product photos, 2 activation reels without models, up to 5 dishes, and 3 vertical 4K drone shots of the restaurant space with DJI Air 3.',
        packageCta: 'Reserve date',
        packageCards: [
            {
                title: '10 photos',
                copy: 'Clean product imagery for menus, social, and ads.',
                items: ['Up to 5 dishes', 'Edited photos', 'Commercial use'],
            },
            {
                title: '2 reels',
                copy: 'Food activation without models, focused on product.',
                items: ['Plates in motion', 'Drinks and service', 'Vertical format'],
            },
            {
                title: '3 drone shots',
                copy: 'Space footage that raises the perceived value of the restaurant.',
                items: ['DJI Air 3', 'Vertical 4K', 'Viable exterior or interior'],
            },
        ],
        bookingTitle: 'Book the session and we build the food shot list.',
        bookingDescription: 'After booking, we define up to 5 priority dishes, timing, mood, drone shots, and final pieces so the content has a clear sales goal.',
        finalWhatsApp: 'Ask on WhatsApp',
        bookingProduct: {
            checkoutLabel: 'Food production',
            headerTitle: 'Book your food session',
            headerDescription: 'Choose a date, share the restaurant concept, and confirm production.',
            summaryTitle: 'Restaurant content session',
            summaryDescription: '10 photos, 2 model-free reels, and 3 vertical drone shots built to sell food.',
            lines: ['Up to 5 priority dishes', 'Vertical content for social and ads', 'Delivery ready to publish'],
            cartService: 'Food reels and photos',
            cartDuration: 'Directed restaurant session',
            perks: ['10 product photos', '2 model-free reels', '3 DJI Air 3 drone shots', 'Use for ads and digital menus', 'Secure card payment'],
            terms: ['Dates depend on availability.', 'Final scope is confirmed by dishes, location, and model needs.', 'You can reschedule with prior notice depending on availability.', 'Material may be used in portfolio unless agreed otherwise.'],
            unavailableWhatsApp: 'Hi, I want a food reels session for my restaurant.',
        },
        whatsappPrefill: 'Hi, I want to quote food reels and photos for my restaurant.',
    },
} as const;

export default function FoodReelsShow({ price, slots, errors }: FoodReelsShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const isMobileViewport = useIsMobileViewport();
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
                <div className="relative mx-auto grid min-h-[min(760px,82svh)] max-w-6xl gap-8 px-4 pb-10 pt-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.58fr)] lg:items-center lg:gap-10 lg:pb-12">
                    <div className="flex max-w-3xl flex-col gap-6">
                        <div className="flex flex-col gap-4">
                            <h1 className="max-w-4xl text-wrap font-display text-[2.2rem] font-bold leading-[0.98] tracking-tight text-white drop-shadow-[0_10px_34px_rgb(0_0_0/0.45)] sm:text-5xl sm:leading-[0.96] lg:text-6xl xl:text-7xl">
                                {copy.heroTitle}
                            </h1>
                            <p className="max-w-2xl text-wrap text-base leading-relaxed text-white/78 sm:text-lg">
                                {copy.heroDescription}
                            </p>
                        </div>

                        <div className="grid w-full max-w-xl gap-3 sm:grid-cols-2">
                            <BookingCtaButton
                                type="button"
                                className="min-h-12 w-full min-w-0 rounded-lg px-4 text-sm whitespace-normal sm:text-base"
                                onClick={() => openBooking('hero')}
                            >
                                <CalendarDays data-icon="inline-start" />
                                {copy.heroPrimary}
                            </BookingCtaButton>
                            <Button
                                type="button"
                                variant="outline"
                                size="xl"
                                className="min-h-12 w-full min-w-0 rounded-lg border-white/22 bg-white/5 px-4 text-sm text-white whitespace-normal hover:bg-white/12 hover:text-white sm:text-base"
                                asChild
                            >
                                <a href="#prueba-real">
                                    <Play data-icon="inline-start" />
                                    {copy.heroSecondary}
                                </a>
                            </Button>
                        </div>
                    </div>

                    <div className="relative mx-auto w-full max-w-[370px]">
                        <div className="relative overflow-hidden rounded-[2rem] border border-white/18 bg-black p-2 shadow-[0_32px_90px_rgb(0_0_0/0.55)]">
                            <AutoplayVideo
                                key={activeReel.id}
                                src={activeReel.src}
                                poster={activeReel.poster}
                                title={activeReel.title}
                                eager={!isMobileViewport}
                                pauseWhenOffscreen={isMobileViewport}
                                className="aspect-[9/16] rounded-[1.45rem]"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section id="prueba-real" className="mx-auto flex max-w-6xl flex-col gap-5 px-4 pt-10 sm:px-6 lg:pt-12">
                <SectionHeader
                    icon={<Video className="size-5" />}
                    title={copy.reelSectionTitle}
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
                <SectionHeader
                    icon={<Sparkles className="size-5" />}
                    title={copy.sessionSectionTitle}
                    className="max-w-3xl"
                />
                <div className="grid auto-rows-[210px] grid-flow-dense gap-3 sm:grid-cols-2 sm:auto-rows-[250px] lg:grid-cols-4 lg:auto-rows-[245px]">
                    {SESSION_PHOTOS.map((photo) => (
                        <MediaPhoto key={photo.id} photo={photo} mosaic />
                    ))}
                </div>
            </section>

            <section className="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-12 sm:px-6">
                <SectionHeader
                    icon={<Images className="size-5" />}
                    title={copy.photoSectionTitle}
                    className="max-w-3xl"
                />
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {PRODUCT_PHOTOS.map((photo) => (
                        <MediaPhoto key={photo.id} photo={photo} dense />
                    ))}
                </div>
            </section>

            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
                className="mx-auto mt-4 w-full max-w-6xl"
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

            <section className="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-12 sm:px-6">
                <div className="grid gap-6 lg:grid-cols-[0.54fr_1fr] lg:items-end">
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

                <div className="flex flex-col gap-4 rounded-xl border border-border/70 bg-background/82 p-5 shadow-sm backdrop-blur-md lg:flex-row lg:items-center lg:justify-between">
                    <div className="max-w-2xl min-w-0">
                        <h2 className="font-display text-2xl font-bold text-foreground">
                            {copy.bookingTitle}
                        </h2>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {copy.bookingDescription}
                        </p>
                    </div>
                    <div className="grid w-full min-w-0 gap-2 sm:grid-cols-2 lg:w-auto lg:min-w-[360px]">
                        <Button
                            variant="outline"
                            className="w-full rounded-lg"
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
                            className="w-full rounded-lg"
                            onClick={() => openBooking('package')}
                        >
                            {copy.packageCta}
                            <ArrowRight data-icon="inline-end" />
                        </BookingCtaButton>
                    </div>
                </div>
            </section>
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
    description?: string;
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
                {description ? (
                    <p className={cn(
                        'mt-3 text-sm leading-relaxed md:text-base',
                        inverted ? 'text-white/72' : 'text-muted-foreground',
                    )}>
                        {description}
                    </p>
                ) : null}
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
                <img
                    src={reel.poster}
                    alt={reel.title}
                    className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    loading="lazy"
                    decoding="async"
                />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,transparent_52%,rgb(0_0_0/0.45)_100%)]" />
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
                    loading={compact || mosaic || dense ? 'eager' : 'lazy'}
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
