import { useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Camera, Film, MessageCircle } from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { getContentSessionProduct } from '@/lib/bookingProducts';
import { formatMxn } from '@/lib/utils';
import type { BookingSlot, PageProps, PortfolioItemData } from '@/types';

interface ContentCreationShowProps {
    price: number;
    slots: BookingSlot[];
    portfolioItems: PortfolioItemData[];
    errors?: Record<string, string>;
}

const COPY = {
    es: {
        title: 'Creación de contenido para redes sociales en Riviera Maya',
        intro: 'Producimos reels y fotografías para negocios que necesitan verse claros, actuales y listos para vender en Instagram, TikTok y campañas de Meta.',
        location: 'Playa del Carmen · Tulum · Cancún',
        book: 'Agendar sesión',
        whatsapp: 'Hablar por WhatsApp',
        whatsappMessage: 'Hola, quiero crear contenido para las redes sociales de mi negocio en Riviera Maya.',
        from: 'Sesiones desde',
        proofTitle: 'Trabajo real, producido por Lapsique',
        proofCopy: 'Dirección, cámara y edición aplicadas a gastronomía, hospitalidad, propiedades, eventos y marcas locales.',
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
        title: 'Social media content creation in Riviera Maya',
        intro: 'We produce reels and photography for businesses that need clear, current material built to sell on Instagram, TikTok, and Meta campaigns.',
        location: 'Playa del Carmen · Tulum · Cancun',
        book: 'Book a session',
        whatsapp: 'Talk on WhatsApp',
        whatsappMessage: 'Hi, I want to create social media content for my business in Riviera Maya.',
        from: 'Sessions from',
        proofTitle: 'Real work produced by Lapsique',
        proofCopy: 'Direction, camera, and editing for food, hospitality, properties, events, and local brands.',
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

const FALLBACK_IMAGES = [
    '/images/portfolio/photos/011-juanis-barber-shop-abril-21-221f6c6aa9.webp',
    '/images/food-reels/food-roof-models-toast-clean.webp',
    '/images/portfolio/photos/050-zal-marina-5399c16416.webp',
    '/images/portfolio/photos/082-proper-collective-cab1bed3f4.webp',
    '/images/food-reels/food-santino-cocktail.webp',
    '/images/portfolio/photos/005-dron-malfa-66a6622e91.webp',
];

export default function ContentCreationShow({
    price,
    slots,
    portfolioItems,
    errors,
}: ContentCreationShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale, t } = useTranslations();
    const copy = locale === 'en' ? COPY.en : COPY.es;
    const product = useMemo(() => getContentSessionProduct(t), [t]);
    const images = useMemo(() => {
        const real = portfolioItems
            .filter((item) => item.media_type === 'image' && Boolean(item.asset_url))
            .map((item) => ({ src: item.asset_url as string, alt: item.title || item.caption || copy.proofTitle }));

        const fallbacks = FALLBACK_IMAGES.map((src) => ({ src, alt: copy.proofTitle }));

        return [...real, ...fallbacks]
            .filter((item, index, all) => all.findIndex((candidate) => candidate.src === item.src) === index)
            .slice(0, 6);
    }, [copy.proofTitle, portfolioItems]);
    const heroImage = images[0]?.src ?? FALLBACK_IMAGES[0];
    const whatsappHref = buildWhatsAppHref(site.whatsapp, copy.whatsappMessage);
    const analyticsPayload = {
        content_name: copy.title,
        content_category: 'social_media_content_creation',
        service_type: 'content_session',
        currency: 'MXN',
        value: price,
    };

    const trackWhatsApp = () => {
        trackBookingEvent('content_creation_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source: 'content_creation_hero',
            target: 'whatsapp',
        });
    };

    return (
        <SiteLayout>
            <SeoHead />

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#070708] text-white">
                <img
                    src={heroImage}
                    alt=""
                    className="absolute inset-0 h-full w-full object-cover opacity-65"
                    fetchPriority="high"
                />
                <div className="absolute inset-0 bg-[linear-gradient(90deg,rgb(5_6_8/0.98)_0%,rgb(5_6_8/0.88)_48%,rgb(5_6_8/0.30)_100%)]" />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,transparent_55%,var(--background)_100%)]" />
                <div className="relative mx-auto flex min-h-[min(760px,84svh)] max-w-6xl items-end px-4 pb-24 pt-24 sm:px-6 lg:pb-28">
                    <div className="max-w-4xl">
                        <p className="font-mono text-xs uppercase tracking-[0.18em] text-primary">{copy.location}</p>
                        <h1 className="mt-5 max-w-4xl font-display text-4xl font-bold leading-[0.94] tracking-tight sm:text-6xl lg:text-7xl">
                            {copy.title}
                        </h1>
                        <p className="mt-6 max-w-2xl text-base leading-relaxed text-white/78 sm:text-xl">
                            {copy.intro}
                        </p>
                        <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                            <BookingCtaButton
                                opensBookingModal
                                bookingSource="content_creation_hero"
                                bookingAnalytics={{
                                    analyticsEvent: 'content_creation_booking_cta_clicked',
                                    analyticsPayload,
                                }}
                                className="rounded-none"
                            >
                                <CalendarDays className="size-5" />
                                {copy.book}
                                <ArrowRight className="size-4" />
                            </BookingCtaButton>
                            <Button variant="outline" size="xl" className="rounded-none border-white/35 bg-black/35 text-white hover:bg-white hover:text-black" asChild>
                                <a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={trackWhatsApp}>
                                    <MessageCircle className="size-5" />
                                    {copy.whatsapp}
                                </a>
                            </Button>
                        </div>
                        <div className="mt-8 border-t border-white/20 pt-5">
                            <p className="text-xs uppercase tracking-[0.16em] text-white/55">{copy.from}</p>
                            <p className="mt-1 font-mono-tabular text-3xl font-bold text-primary">{formatMxn(price)}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section className="py-16 sm:py-20">
                <div className="grid gap-10 lg:grid-cols-[0.72fr_1fr]">
                    <h2 className="max-w-xl font-display text-4xl font-bold leading-none text-foreground sm:text-5xl">
                        {copy.deliverablesTitle}
                    </h2>
                    <div className="grid gap-8 sm:grid-cols-3">
                        {copy.deliverables.map((item, index) => (
                            <article key={item.title} className="border-t border-border pt-5">
                                {index === 0 ? <Film className="size-5 text-primary" /> : index === 1 ? <Camera className="size-5 text-primary" /> : <span className="font-mono text-sm text-primary">03</span>}
                                <h3 className="mt-4 font-display text-2xl font-bold text-foreground">{item.title}</h3>
                                <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{item.copy}</p>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            <section className="relative left-1/2 w-screen -translate-x-1/2 bg-[#090a0c] py-16 text-white sm:py-20">
                <div className="mx-auto max-w-6xl px-4 sm:px-6">
                    <div className="grid gap-5 sm:grid-cols-[0.7fr_1fr] sm:items-end">
                        <h2 className="font-display text-4xl font-bold leading-none sm:text-5xl">{copy.proofTitle}</h2>
                        <p className="max-w-2xl text-sm leading-relaxed text-white/65 sm:text-base">{copy.proofCopy}</p>
                    </div>
                    <div className="mt-10 grid auto-rows-[220px] gap-3 sm:grid-cols-2 sm:auto-rows-[300px] lg:grid-cols-3">
                        {images.map((image, index) => (
                            <figure key={image.src} className={index === 0 ? 'overflow-hidden lg:col-span-2' : 'overflow-hidden'}>
                                <img
                                    src={image.src}
                                    alt={image.alt}
                                    className="h-full w-full object-cover transition duration-500 hover:scale-[1.025]"
                                    loading="lazy"
                                />
                            </figure>
                        ))}
                    </div>
                </div>
            </section>

            <section className="py-16 sm:py-20">
                <h2 className="max-w-3xl font-display text-4xl font-bold leading-none text-foreground sm:text-5xl">{copy.processTitle}</h2>
                <div className="mt-10 grid gap-8 md:grid-cols-3">
                    {copy.process.map(([number, title, description]) => (
                        <article key={number} className="border-t border-border pt-5">
                            <p className="font-mono text-sm text-primary">{number}</p>
                            <h3 className="mt-5 font-display text-2xl font-bold text-foreground">{title}</h3>
                            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{description}</p>
                        </article>
                    ))}
                </div>
            </section>

            <section id="agenda" className="scroll-mt-24 border-t border-border py-16 sm:py-20">
                <div className="mb-8 max-w-3xl">
                    <h2 className="font-display text-4xl font-bold leading-none text-foreground sm:text-5xl">{copy.bookingTitle}</h2>
                    <p className="mt-4 text-base leading-relaxed text-muted-foreground">{copy.bookingCopy}</p>
                </div>
                <BookingWidget
                    slots={slots}
                    price={price}
                    whatsapp={site.whatsapp}
                    errors={errors}
                    checkoutRoute="booking.checkout"
                    paymentProvider="stripe"
                    product={product}
                    popupVariant="home"
                    popupPortfolioItems={portfolioItems}
                    highlight
                    analyticsPayload={analyticsPayload}
                />
            </section>

            <section className="border-t border-border py-16 sm:py-20">
                <h2 className="font-display text-4xl font-bold text-foreground">{copy.faqTitle}</h2>
                <div className="mt-8 divide-y divide-border border-y border-border">
                    {copy.faqs.map(([question, answer]) => (
                        <details key={question} className="group py-5">
                            <summary className="flex cursor-pointer list-none items-center justify-between gap-6 font-display text-xl font-bold text-foreground">
                                {question}
                                <span className="font-mono text-primary transition group-open:rotate-45">+</span>
                            </summary>
                            <p className="max-w-3xl pt-4 text-sm leading-relaxed text-muted-foreground">{answer}</p>
                        </details>
                    ))}
                </div>
            </section>
        </SiteLayout>
    );
}

function buildWhatsAppHref(phone: string | undefined, message: string): string {
    const cleanedPhone = (phone ?? '').replace(/[^\d]/g, '');
    const base = cleanedPhone ? `https://wa.me/${cleanedPhone}` : 'https://wa.me/';

    return `${base}?text=${encodeURIComponent(message)}`;
}
