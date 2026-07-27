import { useEffect, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Check, Film, Headphones, MessageCircle } from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget, type BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { formatMxn } from '@/lib/utils';
import type { BookingSlot, PageProps, PortfolioItemData } from '@/types';

type MultiCameraDrop = {
    id: string;
    title: string;
    project: string;
    kind: 'video' | 'image';
    src: string;
    poster: string | null;
};

interface MultiCameraShowProps {
    price: number;
    slots: BookingSlot[];
    drops: MultiCameraDrop[];
    photos: PortfolioItemData[];
    errors?: Record<string, string>;
}

const COPY = {
    es: {
        eyebrow: 'Lapsique Originals · Sony Alpha',
        title: '10 drops para que tu DJ set siga sonando después del evento.',
        intro: 'Producción multicámara para sets continuos, campañas y archivo editorial. Grabamos la energía completa y la dejamos lista para editar.',
        location: 'Playa del Carmen · Tulum · Cancún · Mérida · Riviera Maya',
        book: 'Reservar producción',
        whatsapp: 'Consultar por WhatsApp',
        whatsappMessage: 'Hola, quiero cotizar la producción multicámara de mi DJ set o evento.',
        priceLabel: 'Producción completa',
        priceNote: 'Precio fijo · agenda online · pago seguro con tarjeta.',
        specs: [
            ['10', 'drops multicámara'],
            ['1:30 h', 'video continuo en Log'],
            ['3', 'cámaras Sony'],
            ['32-bit', 'audio Zoom H4'],
            ['15', 'fotos editadas'],
        ],
        dropsEyebrow: 'Archivo real · MTRX + Karen Echev',
        dropsTitle: 'Cortes, frames y pruebas de una noche completa.',
        dropsCopy: 'Los primeros cuatro drops son videos reales. Los siguientes muestran frames de la misma cobertura para que veas cómo se construye el paquete completo.',
        photosEyebrow: '15 fotografías incluidas',
        photosTitle: 'DJ, público, escenario y la atmósfera que hace reconocible tu fecha.',
        packageEyebrow: 'El paquete',
        packageTitle: 'Un master continuo para editar y piezas cortas para publicar.',
        packageCopy: 'Grabamos el set completo en Log con tres cámaras Sony, sincronizamos audio Zoom H4 a 32 bits y entregamos quince fotografías editadas con dirección editorial.',
        includes: ['10 drops multicámara listos para redes', '1:30 horas de video continuo en Log', 'Audio Zoom H4 a 32 bits', '15 fotografías editadas del evento', 'Preproducción de venue, accesos y shot list'],
        faqTitle: 'Preguntas directas',
        faqs: [
            ['¿Qué son los 10 drops?', 'Son diez piezas cortas extraídas de la cobertura multicámara, pensadas para publicar durante y después de tu campaña.'],
            ['¿Puedo editar el video continuo?', 'Sí. Entregamos el master de 1:30 horas en Log para que puedas hacer tu propio corte o pedir una edición adicional.'],
            ['¿Cómo se sincroniza el audio?', 'Grabamos la mezcla y ambiente con Zoom H4 a 32 bits y lo sincronizamos con las tres cámaras Sony.'],
            ['¿Dónde trabajan?', 'Playa del Carmen, Tulum, Cancún y toda la Riviera Maya.'],
        ],
        finalTitle: 'Tu set merece una pieza que dure más que la noche.',
        finalCopy: 'Comparte fecha, venue y horario. Te respondemos con disponibilidad y una propuesta clara.',
        booking: {
            checkoutLabel: 'Reservar multicámara',
            headerTitle: 'Agenda tu producción multicámara',
            headerDescription: 'Elige fecha y horario. Después alineamos venue, accesos, lineup y shot list.',
            summaryTitle: 'Producción multicámara',
            summaryDescription: '10 drops, video continuo Log, audio Zoom H4 32-bit y 15 fotos editadas.',
            cartService: 'Producción multicámara',
            cartDuration: 'Cobertura de 1:30 h',
            perks: ['10 drops multicámara', '1:30 h de video continuo en Log', '3 cámaras Sony', 'Audio Zoom H4 a 32 bits', '15 fotos editadas'],
            terms: ['La fecha queda sujeta a disponibilidad.', 'Venue, accesos y horario se confirman antes de grabar.', 'El material se entrega para edición y publicación comercial.', 'Cambios de fecha se coordinan según disponibilidad.'],
            paymentCopy: 'Pago seguro con tarjeta procesado por Stripe.',
            unavailableWhatsApp: 'Hola, quiero revisar disponibilidad para una producción multicámara.',
        },
    },
    en: {
        eyebrow: 'Lapsique Originals · Sony Alpha',
        title: '10 drops so your DJ set keeps playing after the event.',
        intro: 'Multicamera production for continuous sets, campaigns, and editorial archives. We capture the full energy and leave it ready to edit.',
        location: 'Playa del Carmen · Tulum · Cancun · Merida · Riviera Maya',
        book: 'Reserve production',
        whatsapp: 'Ask on WhatsApp',
        whatsappMessage: 'Hi, I want to quote multicamera production for my DJ set or event.',
        priceLabel: 'Complete production',
        priceNote: 'Fixed price · online booking · secure card payment.',
        specs: [['10', 'multicamera drops'], ['1:30 h', 'continuous Log video'], ['3', 'Sony cameras'], ['32-bit', 'Zoom H4 audio'], ['15', 'edited photos']],
        dropsEyebrow: 'Real archive · MTRX + Karen Echev',
        dropsTitle: 'Cuts, frames, and proof of a complete night.',
        dropsCopy: 'The first four drops are real videos. The next ones show frames from the same coverage so you can see how the full package is built.',
        photosEyebrow: '15 photos included',
        photosTitle: 'DJ, crowd, stage, and the atmosphere that makes your date recognizable.',
        packageEyebrow: 'The package',
        packageTitle: 'One continuous master to edit and short pieces to publish.',
        packageCopy: 'We record the complete set in Log with three Sony cameras, sync Zoom H4 32-bit audio, and deliver fifteen edited photographs with editorial direction.',
        includes: ['10 multicamera drops ready for social', '1:30 hours of continuous Log video', 'Zoom H4 32-bit audio', '15 edited event photos', 'Venue, access, and shot-list preproduction'],
        faqTitle: 'Straight answers',
        faqs: [['What are the 10 drops?', 'Ten short pieces extracted from the multicamera coverage, designed to publish during and after your campaign.'], ['Can I edit the continuous video?', 'Yes. We deliver the 1:30-hour Log master so you can create your own cut or request an additional edit.'], ['How is audio synced?', 'We record mixer and room audio with a Zoom H4 in 32-bit and sync it with the three Sony cameras.'], ['Where do you work?', 'Playa del Carmen, Tulum, Cancun, and the Riviera Maya.']],
        finalTitle: 'Your set deserves a piece that lasts beyond the night.',
        finalCopy: 'Share the date, venue, and schedule. We will reply with availability and a clear proposal.',
        booking: {
            checkoutLabel: 'Reserve multicamera', headerTitle: 'Book your multicamera production', headerDescription: 'Choose a date and time. Then we align venue, access, lineup, and shot list.', summaryTitle: 'Multicamera production', summaryDescription: '10 drops, continuous Log video, Zoom H4 32-bit audio, and 15 edited photos.', cartService: 'Multicamera production', cartDuration: '1:30 h coverage', perks: ['10 multicamera drops', '1:30 h continuous Log video', '3 Sony cameras', 'Zoom H4 32-bit audio', '15 edited photos'], terms: ['Date is subject to availability.', 'Venue, access, and schedule are confirmed before filming.', 'Material is delivered for commercial editing and publishing.', 'Date changes are coordinated according to availability.'], paymentCopy: 'Secure card payment processed by Stripe.', unavailableWhatsApp: 'Hi, I want to check availability for multicamera production.',
        },
    },
} as const;

export default function MultiCameraShow({ price, slots, drops, photos, errors }: MultiCameraShowProps) {
    const { site } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const copy = COPY[locale === 'en' ? 'en' : 'es'];
    const whatsappHref = useMemo(() => buildWhatsAppHref(site.whatsapp, copy.whatsappMessage), [copy.whatsappMessage, site.whatsapp]);
    const analyticsPayload = useMemo(() => ({ content_name: copy.title, content_category: 'multi_camera_booking', service_type: 'multi_camera', currency: 'MXN', value: price }), [copy.title, price]);
    const bookingProduct = useMemo<BookingWidgetProduct>(() => ({ checkoutLabel: copy.booking.checkoutLabel, headerTitle: copy.booking.headerTitle, headerDescription: copy.booking.headerDescription, summaryTitle: copy.booking.summaryTitle, summaryDescription: copy.booking.summaryDescription, cartService: copy.booking.cartService, cartDuration: copy.booking.cartDuration, summaryPerks: [...copy.booking.perks], terms: [...copy.booking.terms], paymentCopy: copy.booking.paymentCopy, unavailableWhatsApp: copy.booking.unavailableWhatsApp }), [copy]);
    const videoDrops = useMemo(() => drops.filter((drop) => drop.kind === 'video'), [drops]);

    useEffect(() => {
        trackBookingEvent('multi_camera_page_viewed', { ...analyticsPayload, section: 'multi_camera' });
    }, [analyticsPayload]);

    const trackWhatsApp = (source: string) => trackBookingEvent('multi_camera_whatsapp_cta_clicked', { ...analyticsPayload, source, target: 'whatsapp' });

    return (
        <SiteLayout>
            <SeoHead />
            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#050607] text-white">
                <div className="absolute inset-0 bg-cover bg-center opacity-55" style={{ backgroundImage: "url('/images/portfolio/photos/2026-07-11-mtrx-pista-58db81e520.webp')" }} />
                <div className="absolute inset-0 bg-[linear-gradient(90deg,#050607_0%,rgba(5,6,7,.92)_42%,rgba(5,6,7,.48)_100%)]" />
                <div className="absolute inset-0 bg-black/25" />
                <div className="relative mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:items-end lg:py-24">
                    <div>
                        <p className="alpha-kicker text-primary">{copy.eyebrow}</p>
                        <h1 className="mt-5 max-w-3xl font-display text-5xl font-bold leading-[.9] tracking-tight md:text-7xl">{copy.title}</h1>
                        <p className="mt-6 max-w-2xl text-base leading-relaxed text-white/70 md:text-lg">{copy.intro}</p>
                        <p className="mt-4 font-mono text-[11px] uppercase tracking-[.18em] text-primary">{copy.location}</p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            <BookingCtaButton size="lg" opensBookingModal bookingSource="multi_camera_hero" bookingAnalytics={{ analyticsEvent: 'multi_camera_booking_cta_clicked', analyticsPayload }} className="w-fit rounded-none border border-primary bg-primary px-6 text-primary-foreground shadow-[0_12px_30px_rgba(232,92,21,.22)] hover:bg-primary/90"><CalendarDays className="size-5" />{copy.book}<ArrowRight className="size-5" /></BookingCtaButton>
                            <Button variant="outline" size="xl" className="rounded-none border-white/25 bg-white/5 text-white hover:bg-white hover:text-black" asChild><a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={() => trackWhatsApp('hero')}><MessageCircle className="size-5" />{copy.whatsapp}</a></Button>
                        </div>
                    </div>
                    <div className="border border-white/20 bg-black/30 p-3 backdrop-blur-sm">
                        <div className="aspect-video overflow-hidden border border-white/10 bg-black">
                            {drops[0]?.kind === 'video' ? <video className="h-full w-full object-cover" src={drops[0].src} poster={drops[0].poster ?? undefined} muted autoPlay loop playsInline /> : null}
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-6xl py-16 sm:px-0 md:py-24">
                <p className="alpha-kicker text-primary">{copy.dropsEyebrow}</p>
                <h2 className="mt-4 max-w-3xl font-display text-4xl font-bold leading-[.94] md:text-6xl">{copy.dropsTitle}</h2>
                <p className="mt-5 max-w-2xl text-muted-foreground">{copy.dropsCopy}</p>
                <div className="multicam-marquee mt-10 overflow-hidden border-y border-border py-4">
                    <div className="multicam-marquee__track">
                        {[...videoDrops, ...videoDrops].map((drop, index) => <div key={`${drop.id}-${index}`} className="multicam-marquee__item"><video className="h-full w-full object-cover" src={drop.src} poster={drop.poster ?? undefined} muted autoPlay loop playsInline preload="metadata" /></div>)}
                    </div>
                </div>
            </section>

            <section className="relative left-1/2 w-screen -translate-x-1/2 bg-[#070809] py-16 text-white md:py-24">
                <div className="mx-auto max-w-6xl px-4 sm:px-6"><p className="alpha-kicker text-primary">{copy.photosEyebrow}</p><h2 className="mt-4 max-w-4xl font-display text-4xl font-bold leading-[.94] md:text-6xl">{copy.photosTitle}</h2><div className="mt-10 columns-2 gap-4 md:columns-3 lg:columns-5">{photos.map((photo) => <button key={photo.id} type="button" className="group mb-4 block w-full overflow-hidden border border-white/10 text-left" onClick={() => trackBookingEvent('multi_camera_portfolio_engaged', { ...analyticsPayload, asset: photo.slug })}><img src={photo.asset_url ?? photo.poster_url ?? ''} alt={photo.title ?? 'Fotografía de evento'} className="h-auto w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" /></button>)}</div></div>
            </section>

            <section className="mx-auto grid max-w-6xl gap-10 py-16 md:grid-cols-[.9fr_1.1fr] md:py-24"><div><p className="alpha-kicker text-primary">{copy.packageEyebrow}</p><h2 className="mt-4 font-display text-4xl font-bold leading-[.94] md:text-6xl">{copy.packageTitle}</h2><p className="mt-5 text-muted-foreground">{copy.packageCopy}</p><div className="mt-7 grid gap-3">{copy.includes.map((item) => <div key={item} className="flex gap-3 border-t border-border pt-3 text-sm"><Check className="mt-0.5 size-4 shrink-0 text-primary" />{item}</div>)}</div></div><div className="border border-[#26292d] bg-[#08090b] p-6 text-white md:p-8"><p className="font-mono text-[10px] uppercase tracking-[.16em] text-primary">{copy.priceLabel}</p><p className="mt-3 font-display text-5xl font-bold">{formatMxn(price)}</p><p className="mt-1 text-sm text-white/55">{copy.priceNote}</p><div className="mt-8 divide-y divide-white/10 border-y border-white/10"><div className="grid grid-cols-[auto_1fr] gap-4 py-4"><Film className="mt-1 size-5 text-primary" /><div><p className="font-display text-2xl font-bold">1:30 h · Log</p><p className="mt-1 text-sm text-white/60">Master continuo listo para abrir en DaVinci Resolve.</p></div></div><div className="grid grid-cols-[auto_1fr] gap-4 py-4"><Headphones className="mt-1 size-5 text-primary" /><div><p className="font-display text-2xl font-bold">Zoom H4 · 32-bit</p><p className="mt-1 text-sm text-white/60">Audio de mixer y ambiente sincronizado con tres cámaras.</p></div></div></div></div></section>

            <BookingWidget slots={slots} price={price} whatsapp={site.whatsapp} errors={errors} className="mx-auto max-w-6xl" checkoutRoute="multi-camera.checkout" paymentProvider="stripe" product={bookingProduct} popupVariant="multiCamera" popupPortfolioItems={photos} popupHeroProofVideo={drops[0]?.kind === 'video' ? { title: drops[0].title, media_type: 'video', embed_url: null, playback_url: drops[0].src, poster_url: drops[0].poster } : null} highlight analyticsPayload={analyticsPayload} analyticsOpenEvent="multi_camera_booking_opened" />

            <section className="mx-auto max-w-6xl border border-primary/30 bg-primary/5 px-6 py-8 md:px-10"><p className="alpha-kicker text-primary">Proyecto editable</p><div className="mt-4 grid gap-6 md:grid-cols-[1fr_auto] md:items-end"><div><h2 className="font-display text-3xl font-bold md:text-5xl">Entrega para DaVinci Resolve 21.</h2><p className="mt-3 max-w-2xl text-muted-foreground">Recibes el video continuo en Log, audio sincronizado y una estructura de proyecto pensada para continuar la edición en DaVinci Resolve 21.</p></div><div className="border border-border bg-background px-5 py-4 font-mono text-xs uppercase tracking-[.14em]">Timeline · Log · Audio 32-bit</div></div></section>

            <section className="mx-auto grid max-w-6xl gap-8 py-16 md:grid-cols-[.72fr_1.28fr] md:py-24"><div><p className="alpha-kicker text-primary">{copy.faqTitle}</p></div><div className="divide-y divide-border border-y border-border">{copy.faqs.map(([question, answer]) => <details key={question} className="group py-5"><summary className="cursor-pointer list-none pr-8 font-display text-xl font-bold marker:hidden">{question}<span className="float-right text-primary">+</span></summary><p className="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">{answer}</p></details>)}</div></section>

            <section className="relative left-1/2 mb-0 w-screen -translate-x-1/2 overflow-hidden bg-primary py-14 text-white md:py-20"><div className="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-end"><div className="max-w-3xl"><h2 className="font-display text-4xl font-bold leading-[.94] md:text-6xl">{copy.finalTitle}</h2><p className="mt-5 text-base leading-relaxed text-white/85 md:text-lg">{copy.finalCopy}</p></div><div className="grid gap-3 sm:grid-cols-2 lg:min-w-[440px]"><BookingCtaButton opensBookingModal bookingSource="multi_camera_final" bookingAnalytics={{ analyticsEvent: 'multi_camera_booking_cta_clicked', analyticsPayload }} className="w-full rounded-none border-white/40 bg-black/15 text-white hover:bg-white hover:text-black"><CalendarDays className="size-5" />{copy.book}</BookingCtaButton><Button size="xl" className="w-full rounded-none border border-[#25D366] bg-[#25D366] text-white hover:bg-[#1ebe5d]" asChild><a href={whatsappHref} target="_blank" rel="noopener noreferrer" onClick={() => trackWhatsApp('final')}><MessageCircle className="size-5" />{copy.whatsapp}</a></Button></div></div></section>

            <section className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#050607] py-14 text-white"><div className="mx-auto max-w-6xl px-4 sm:px-6"><p className="alpha-kicker text-primary">Psique Sessions · Horizontal</p><h2 className="mt-4 font-display text-4xl font-bold leading-[.94] md:text-6xl">El set completo también se ve así.</h2><div className="multicam-marquee mt-8 overflow-hidden border-y border-white/10 py-4"><div className="multicam-marquee__track">{[...videoDrops, ...videoDrops].map((drop, index) => <div key={`horizontal-${drop.id}-${index}`} className="multicam-marquee__item multicam-marquee__item--wide"><video className="h-full w-full object-cover" src={drop.src} poster={drop.poster ?? undefined} muted autoPlay loop playsInline preload="metadata" /></div>)}</div></div></div></section>
        </SiteLayout>
    );
}

function buildWhatsAppHref(phone: string | null | undefined, text: string): string {
    const digits = (phone ?? '').replace(/\D/g, '');
    return `https://wa.me/${digits}?text=${encodeURIComponent(text)}`;
}
