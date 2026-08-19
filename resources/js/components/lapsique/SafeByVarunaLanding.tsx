import { Link, usePage } from '@inertiajs/react';
import { CalendarDays, Check, Clock3, MapPin, Play, ShieldCheck, Ticket, Users } from 'lucide-react';
import { FormEvent, useEffect, useMemo, useRef, useState } from 'react';
import { NewsletterCaptureModal } from '@/components/lapsique/NewsletterCaptureModal';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { route } from '@/lib/route';
import type { EventItem, EventTicketProduct, PageProps } from '@/types';

interface SafeByVarunaLandingProps {
    event: EventItem;
    viewContentEventId: string;
}

export function SafeByVarunaLanding({ event, viewContentEventId }: SafeByVarunaLandingProps) {
    const { ziggy, errors, site } = usePage<PageProps>().props;
    const en = document.documentElement.lang === 'en';
    const product = event.ticket_products?.[0] ?? null;
    const checkoutReady = Boolean(product?.embedded_checkout_ready);
    const [quantity, setQuantity] = useState(1);
    const [submitting, setSubmitting] = useState(false);
    const [interestOpen, setInterestOpen] = useState(false);
    const [checkoutOpen, setCheckoutOpen] = useState(false);
    const [paymentUnavailable, setPaymentUnavailable] = useState(false);
    const viewTracked = useRef(false);
    const checkoutEventId = useRef(createCheckoutEventId());

    const totals = useMemo(() => commerceTotals(product, quantity), [product, quantity]);
    const contentPayload = useMemo(() => product ? {
        content_type: 'product',
        content_ids: [String(product.id)],
        contents: [{ id: String(product.id), quantity, item_price: product.total }],
        content_name: event.title,
        content_category: 'event_ticket',
        currency: product.currency,
        value: totals.total,
    } : null, [event.title, product, quantity, totals.total]);

    useEffect(() => {
        if (viewTracked.current || !product) return;
        viewTracked.current = true;

        window.trackMetaPixel?.('ViewContent', {
            content_type: 'product',
            content_ids: [String(product.id)],
            content_name: event.title,
            content_category: 'event_ticket',
            currency: product.currency,
            value: product.total,
        }, { eventID: viewContentEventId });
        window.SiteTracker?.track('event_view_content', {
            event_id: event.id,
            event_slug: event.slug,
            sales_mode: product.sales_mode,
        });
    }, [event.id, event.slug, event.title, product, viewContentEventId]);

    const submit = (submitEvent: FormEvent<HTMLFormElement>) => {
        if (!product || !contentPayload) {
            submitEvent.preventDefault();
            setInterestOpen(true);
            return;
        }

        if (!checkoutReady) {
            submitEvent.preventDefault();
            setPaymentUnavailable(true);
            return;
        }

        const form = submitEvent.currentTarget;
        const eventIdInput = form.elements.namedItem('checkout_event_id') as HTMLInputElement | null;
        if (eventIdInput) eventIdInput.value = checkoutEventId.current;
        setSubmitting(true);
    };

    const openCheckout = () => {
        if (!product || !contentPayload) {
            setInterestOpen(true);
            return;
        }

        checkoutEventId.current = createCheckoutEventId();
        window.trackMetaPixel?.('InitiateCheckout', contentPayload, { eventID: checkoutEventId.current });
        window.SiteTracker?.track('checkout_started', {
            event_id: event.id,
            event_slug: event.slug,
            value: totals.total,
            currency: product.currency,
            quantity,
            checkout_event_id: checkoutEventId.current,
        });
        setPaymentUnavailable(false);
        setCheckoutOpen(true);
    };

    const whatsapp = `https://wa.me/${site.whatsapp}?text=${encodeURIComponent(en
        ? 'Hi, I have a question about Safe by Varuna 1st Edition.'
        : 'Hola, tengo una pregunta sobre Safe by Varuna 1 edition.')}`;

    return (
        <article className="text-foreground">
            <header className="border-b border-foreground/20 py-8 sm:py-12 lg:py-16">
                <div className="grid gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(22rem,0.74fr)] lg:items-center lg:gap-12">
                    <div className="order-2 lg:order-1">
                        <h1 className="max-w-[9ch] text-balance font-ui-display text-[clamp(3.1rem,7vw,5.75rem)] font-bold uppercase leading-[0.88] tracking-[-0.045em]">
                            Safe 1 edition
                        </h1>
                        <p className="mt-6 max-w-xl text-pretty text-lg leading-8 text-muted-foreground">
                            {en
                                ? 'A limited minimal-house night in Roma Norte. One artist, one room and 350 places.'
                                : 'Una noche limitada de minimal house en Roma Norte. Un artista, una sala y 350 lugares.'}
                        </p>

                        <div className="mt-9 divide-y divide-foreground/20 border-y border-foreground/20 sm:grid sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                            <Fact icon={<CalendarDays />} label={en ? 'Date' : 'Fecha'} value={en ? 'August 27, 2026' : '27 de agosto de 2026'} />
                            <Fact icon={<Clock3 />} label={en ? 'Doors' : 'Inicio'} value="10:00 p. m. CDMX" />
                            <Fact icon={<MapPin />} label={en ? 'Venue' : 'Lugar'} value="Casa Luma · Tonalá 145" />
                        </div>

                        <button
                            type="button"
                            onClick={openCheckout}
                            className="mt-8 inline-flex min-h-12 w-full items-center justify-center gap-3 bg-primary px-6 font-ui-display text-sm font-bold uppercase tracking-[0.1em] text-primary-foreground focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary motion-safe:transition-colors hover:bg-foreground hover:text-background sm:w-fit"
                        >
                            <Ticket aria-hidden="true" className="size-5" />
                            {en ? 'Buy ticket · $105 MXN' : 'Comprar boleto · $105 MXN'}
                        </button>
                    </div>

                    <div className="order-1 overflow-hidden bg-muted lg:order-2">
                        {event.cover_url ? (
                            <img
                                src={event.cover_url}
                                alt={en ? 'Safe by Varuna 1st Edition poster featuring KAPI' : 'Cartel de Safe by Varuna 1 edition con KAPI'}
                                className="aspect-square w-full object-contain"
                            />
                        ) : null}
                    </div>
                </div>
            </header>

            <div>
                <section className="grid gap-10 border-b border-foreground/20 py-14 md:py-20 lg:grid-cols-[0.7fr_1.3fr]">
                    <div>
                        <p className="alpha-kicker text-primary">{en ? 'The night' : 'La noche'}</p>
                        <h2 className="mt-4 text-balance font-ui-display text-4xl font-bold uppercase leading-[0.94] tracking-[-0.025em] sm:text-5xl">
                            {en ? 'Minimal house, close and direct.' : 'Minimal house, cerca y directo.'}
                        </h2>
                    </div>
                    <div className="max-w-3xl space-y-6 text-lg leading-8 text-muted-foreground">
                        <p>{en
                            ? 'KAPI brings the Tulum sound to Mexico City for Safe by Varuna’s first edition: a focused session built for the room, not for a giant stage.'
                            : 'KAPI lleva el sonido de Tulum a Ciudad de México para la primera edición de Safe by Varuna: una sesión enfocada en la sala, no en un escenario gigante.'}</p>
                        <div className="grid gap-3 text-sm sm:grid-cols-2">
                            <Policy icon={<Users />} text={en ? 'Admission strictly for ages 18+' : 'Acceso únicamente para mayores de 18 años'} />
                            <Policy icon={<ShieldCheck />} text={en ? 'Limited capacity · no refunds' : 'Cupo limitado · sin reembolsos'} />
                            <Policy icon={<Check />} text={en ? 'Black dress code' : 'Dress code negro'} />
                            <Policy icon={<MapPin />} text="Casa Luma Cultural Space · Roma Norte" />
                        </div>
                    </div>
                </section>

                <VenueGallery en={en} />
                <KapiSetCarousel en={en} />
            </div>

            <Dialog open={checkoutOpen} onOpenChange={setCheckoutOpen}>
                <DialogContent className="max-h-[calc(100svh-1.5rem)] w-[min(calc(100vw-1.5rem),64rem)] max-w-none gap-0 rounded-none border-black/15 bg-[#efeadf] p-0 text-[#151713] sm:max-w-none">
                    <div className="grid lg:grid-cols-[0.72fr_1.28fr]">
                        <div className="border-b border-black/10 p-6 sm:p-8 lg:border-r lg:border-b-0">
                            <DialogTitle className="font-ui-display text-4xl font-bold uppercase leading-[0.92] tracking-[-0.035em] sm:text-5xl">
                                {en ? 'Buy your ticket.' : 'Compra tu boleto.'}
                            </DialogTitle>
                            <DialogDescription className="mt-4 text-base leading-7 text-[#4f514b]">
                                {en ? 'Complete your details and continue to secure payment with Mercado Pago.' : 'Completa tus datos y continúa al pago seguro con Mercado Pago.'}
                            </DialogDescription>
                            <p className="mt-8 text-sm leading-6 text-[#62645e]">18+ · {en ? 'No refunds' : 'Sin reembolsos'} · Casa Luma</p>
                        </div>

                        {product ? (
                            <form
                                method="POST"
                                action={route('tickets.checkout.store', { event: event.slug }, false, ziggy)}
                                onSubmit={submit}
                                className="bg-white p-6 sm:p-8"
                                aria-labelledby="ticket-form-title"
                            >
                                <input type="hidden" name="_token" value={csrfToken()} />
                                <input type="hidden" name={`items[${product.id}]`} value={quantity} />
                                <input type="hidden" name="payment_provider" value="mercadopago" />
                                <input type="hidden" name="checkout_event_id" value={checkoutEventId.current} readOnly />
                                <input type="hidden" name="landing_page" value="safe_by_varuna_ads" />
                                <input type="hidden" name="landing_url" value={typeof window === 'undefined' ? '' : window.location.href} />
                                <input type="hidden" name="page_type" value="event_sales_landing" />
                                <input type="hidden" name="page_name" value="safe_by_varuna_1_edition" />
                                <input type="hidden" name="referrer" value={typeof document === 'undefined' ? '' : document.referrer} />
                                <input type="hidden" name="fbp" value={browserCookie('_fbp')} />
                                <input type="hidden" name="fbc" value={browserCookie('_fbc')} />
                                {trackingQueryFields().map(([name, value]) => <input key={name} type="hidden" name={name} value={value} />)}

                                <div className="flex flex-col gap-4 border-b border-black/10 pb-6 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h3 id="ticket-form-title" className="text-2xl font-semibold">{en ? 'General admission' : 'Acceso general'}</h3>
                                        <p className="mt-2 text-sm leading-6 text-[#62645e]">{product.available ?? 350} {en ? 'available' : 'disponibles'}</p>
                                    </div>
                                    <p className="text-4xl font-bold tabular-nums">${formatMoney(product.base_price)} <span className="text-sm font-medium text-[#62645e]">MXN</span></p>
                                </div>

                                {Object.keys(errors ?? {}).length > 0 || paymentUnavailable ? (
                                    <div className="mt-5 border border-amber-300 bg-amber-50 p-4 text-sm leading-6 text-amber-950" role="alert">
                                        {paymentUnavailable
                                            ? (en ? 'Payment is temporarily unavailable. No charge was made.' : 'El pago está temporalmente no disponible. No se realizó ningún cargo.')
                                            : firstError(errors, en ? 'Check your information and try again.' : 'Revisa tus datos e inténtalo de nuevo.')}
                                    </div>
                                ) : null}

                                <div className="mt-6 grid gap-5 sm:grid-cols-2">
                                    <Field label={en ? 'Full name' : 'Nombre completo'} name="buyer_name" type="text" autoComplete="name" placeholder={en ? 'Your full name' : 'Tu nombre completo'} />
                                    <Field label="Email" name="buyer_email" type="email" autoComplete="email" placeholder="nombre@correo.com" />
                                    <Field label={en ? 'WhatsApp phone' : 'Teléfono con WhatsApp'} name="buyer_whatsapp" type="tel" autoComplete="tel" placeholder="+52 55 0000 0000" />
                                    <Field label={en ? 'Instagram (optional)' : 'Instagram (opcional)'} name="buyer_instagram" type="text" autoComplete="off" placeholder="@usuario" required={false} />
                                </div>

                                <div className="mt-6 grid gap-5 sm:grid-cols-[0.55fr_1.45fr] sm:items-end">
                                    <label className="grid gap-2 text-sm font-semibold" htmlFor="safe-ticket-quantity">
                                        {en ? 'Quantity' : 'Cantidad'}
                                        <select id="safe-ticket-quantity" value={quantity} onChange={(changeEvent) => setQuantity(Number(changeEvent.target.value))} className="min-h-12 border border-black/25 bg-white px-4 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                                            {Array.from({ length: Math.min(product.max_per_order ?? 6, product.available ?? 6) }, (_, index) => index + 1).map((option) => <option key={option} value={option}>{option}</option>)}
                                        </select>
                                    </label>
                                    <dl className="border border-black/10 bg-[#f4f0e7] p-5 text-sm">
                                        <SummaryRow label={en ? 'Ticket subtotal' : 'Subtotal de boletos'} value={totals.subtotal} currency={product.currency} />
                                        <SummaryRow label={`${en ? 'Service charge' : 'Cargo de servicio'} (${product.service_charge_pct}%)`} value={totals.fee} currency={product.currency} />
                                        <SummaryRow label="Total" value={totals.total} currency={product.currency} strong />
                                    </dl>
                                </div>

                                <label className="mt-6 flex cursor-pointer items-start gap-3 border border-black/10 bg-[#f4f0e7] p-4 text-sm leading-6 text-[#454741]">
                                    <input type="checkbox" name="consent_terms" value="1" required className="mt-1 size-5 shrink-0 accent-black" />
                                    <span>
                                        {en ? 'I confirm that I am 18 or older and accept the ' : 'Confirmo que tengo 18 años o más y acepto los '}
                                        <Link href={route('legal.terms', undefined, false, ziggy)} target="_blank" className="font-semibold underline underline-offset-4">{en ? 'purchase terms' : 'términos de compra'}</Link>.{' '}
                                        {en ? 'I understand that this event has no refunds.' : 'Entiendo que este evento no admite reembolsos.'}
                                    </span>
                                </label>

                                <button type="submit" disabled={submitting} className="mt-6 inline-flex min-h-14 w-full items-center justify-center gap-3 bg-primary px-6 font-ui-display text-sm font-bold uppercase tracking-[0.1em] text-primary-foreground focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary disabled:cursor-wait disabled:opacity-60 hover:bg-black hover:text-white">
                                    <ShieldCheck aria-hidden="true" className="size-5" />
                                    {submitting ? (en ? 'Opening secure payment…' : 'Abriendo pago seguro…') : (en ? 'Pay with Mercado Pago · $' : 'Pagar con Mercado Pago · $') + formatMoney(totals.total)}
                                </button>
                                <p className="mt-4 text-center text-xs leading-5 text-[#676963]">{en ? 'Mercado Pago securely tokenizes your card. Lapsique does not store card data.' : 'Mercado Pago tokeniza tu tarjeta de forma segura. Lapsique no almacena datos de tarjeta.'}</p>
                            </form>
                        ) : (
                            <div className="bg-white p-8">
                                <p className="text-lg">{en ? 'Tickets will be available soon.' : 'Los boletos estarán disponibles pronto.'}</p>
                                <a href={whatsapp} target="_blank" rel="noopener noreferrer" className="mt-6 inline-flex min-h-12 items-center bg-black px-5 font-semibold text-white">WhatsApp</a>
                            </div>
                        )}
                    </div>
                </DialogContent>
            </Dialog>

            <NewsletterCaptureModal
                open={interestOpen}
                onOpenChange={setInterestOpen}
                variant="eventCoverage"
                source={`event:${event.slug}`}
                imageUrl={event.cover_url}
                imageAlt={event.title}
            />
        </article>
    );
}

function Fact({ icon, label, value }: { icon: React.ReactNode; label: string; value: string }) {
    return <div className="py-4 sm:px-4 sm:first:pl-0">
        <div className="flex items-center gap-2 text-primary [&_svg]:size-4 [&_svg]:stroke-[1.5]" aria-hidden="true">{icon}<span className="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-muted-foreground">{label}</span></div>
        <p className="mt-2 text-sm font-semibold leading-5 text-foreground">{value}</p>
    </div>;
}

function VenueGallery({ en }: { en: boolean }) {
    const images = [
        { src: '/images/events/safe-by-varuna/venue/tonala-145-entrance.webp', alt: en ? 'Entrance to Casa Luma at Tonalá 145' : 'Entrada de Casa Luma en Tonalá 145' },
        { src: '/images/events/safe-by-varuna/venue/casa-luma-stair.webp', alt: en ? 'Interior stairway at Casa Luma' : 'Escalera interior de Casa Luma' },
        { src: '/images/events/safe-by-varuna/venue/casa-luma-room.webp', alt: en ? 'Casa Luma room during a music night' : 'Sala de Casa Luma durante una noche de música' },
    ];

    return (
        <section className="border-b border-foreground/20 py-14 md:py-20" aria-labelledby="safe-venue-title">
            <div className="grid gap-8 md:grid-cols-[0.34fr_0.66fr] md:items-end">
                <div>
                    <p className="alpha-kicker text-primary">Casa Luma / Roma Norte</p>
                    <h2 id="safe-venue-title" className="mt-4 text-balance font-ui-display text-4xl font-bold uppercase leading-[0.94] tracking-[-0.025em] sm:text-5xl">
                        {en ? 'The room for this edition.' : 'La sala de esta edición.'}
                    </h2>
                </div>
                <p className="max-w-xl text-base leading-7 text-muted-foreground">{en ? 'Tonalá 145, an intimate cultural space prepared for a limited-capacity night.' : 'Tonalá 145, un espacio cultural íntimo preparado para una noche de cupo limitado.'}</p>
            </div>
            <div className="mt-8 grid gap-2 sm:grid-cols-3">
                {images.map((image, index) => (
                    <img key={image.src} src={image.src} alt={image.alt} loading="lazy" className={`w-full object-cover ${index === 1 ? 'aspect-[4/5]' : 'aspect-[4/5] sm:aspect-auto sm:h-full'}`} />
                ))}
            </div>
        </section>
    );
}

function KapiSetCarousel({ en }: { en: boolean }) {
    return (
        <section className="border-b border-foreground/20 py-14 md:py-20" aria-labelledby="kapi-set-title">
            <div className="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p className="alpha-kicker text-primary">KAPI / DJ set</p>
                    <h2 id="kapi-set-title" className="mt-4 text-balance font-ui-display text-4xl font-bold uppercase leading-[0.94] tracking-[-0.025em] sm:text-5xl">
                        {en ? 'Listen before the night.' : 'Escúchalo antes de la noche.'}
                    </h2>
                </div>
                <Link href="/djs/kapi" className="inline-flex min-h-11 items-center gap-2 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-primary underline decoration-2 underline-offset-4">
                    {en ? 'Open KAPI profile' : 'Ver perfil de KAPI'}
                </Link>
            </div>
            <div className="mt-8 overflow-x-auto pb-3 [scrollbar-width:thin]" aria-label={en ? 'KAPI DJ set carousel' : 'Carrusel de DJ sets de KAPI'}>
                <article className="grid min-w-[min(100%,52rem)] border border-foreground/20 bg-[#07090b] text-white md:grid-cols-[0.42fr_0.58fr]">
                    <video controls preload="metadata" playsInline poster="/images/portfolio/video-posters/080-mac-proyectos-kapi-at-umi-reel.jpg" className="aspect-[4/5] h-full w-full bg-black object-cover">
                        <source src="/videos/reels/080-mac-proyectos-kapi-at-umi-reel.mp4" type="video/mp4" />
                    </video>
                    <div className="flex flex-col justify-between p-6 sm:p-8">
                        <div>
                            <p className="alpha-kicker text-primary">Psique Session / UMI</p>
                            <h3 className="mt-4 text-3xl font-semibold uppercase leading-none sm:text-4xl">KAPI</h3>
                            <p className="mt-4 max-w-md text-sm leading-6 text-white/60">{en ? 'A reel from a previous session produced by Lapsique Media.' : 'Un reel de una sesión anterior producida por Lapsique Media.'}</p>
                        </div>
                        <span className="mt-8 inline-flex items-center gap-2 text-sm text-white/70"><Play className="size-4 text-primary" aria-hidden="true" /> {en ? 'Press play' : 'Reproducir'}</span>
                    </div>
                </article>
            </div>
        </section>
    );
}

function Policy({ icon, text }: { icon: React.ReactNode; text: string }) {
    return <p className="flex min-h-12 items-center gap-3 border-t border-foreground/15 py-3 text-foreground [&_svg]:size-4 [&_svg]:shrink-0 [&_svg]:stroke-[1.5] [&_svg]:text-primary">{icon}{text}</p>;
}

function Field({ label, name, type, autoComplete, placeholder, required = true }: { label: string; name: string; type: string; autoComplete: string; placeholder: string; required?: boolean }) {
    const id = `safe-${name}`;
    return <label className="grid gap-2 text-sm font-semibold" htmlFor={id}>
        {label}{required ? <span className="sr-only"> (required)</span> : null}
        <input id={id} name={name} type={type} autoComplete={autoComplete} placeholder={placeholder} required={required} className="min-h-12 rounded-xl border border-black/25 bg-white px-4 text-base font-normal focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black" />
    </label>;
}

function SummaryRow({ label, value, currency, strong = false }: { label: string; value: number; currency: string; strong?: boolean }) {
    return <div className={`flex items-center justify-between gap-4 py-1.5 ${strong ? 'mt-2 border-t border-black/10 pt-3 text-base font-bold' : 'text-[#565852]'}`}>
        <dt>{label}</dt><dd className="tabular-nums text-[#151713]">${formatMoney(value)} {currency}</dd>
    </div>;
}

function commerceTotals(product: EventTicketProduct | null, quantity: number) {
    return {
        subtotal: Number(((product?.base_price ?? 0) * quantity).toFixed(2)),
        fee: Number(((product?.service_charge_amount ?? 0) * quantity).toFixed(2)),
        total: Number(((product?.total ?? 0) * quantity).toFixed(2)),
    };
}

function formatMoney(value: number) {
    return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
}

function csrfToken() {
    return typeof document === 'undefined' ? '' : (document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '');
}

function browserCookie(name: string) {
    if (typeof document === 'undefined') return '';
    return document.cookie.split('; ').find((entry) => entry.startsWith(`${name}=`))?.split('=').slice(1).join('=') ?? '';
}

function trackingQueryFields(): Array<[string, string]> {
    if (typeof window === 'undefined') return [];
    const query = new URLSearchParams(window.location.search);
    return ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']
        .map((name) => [name, query.get(name) ?? ''] as [string, string]);
}

function createCheckoutEventId() {
    if (typeof window !== 'undefined' && window.crypto?.randomUUID) return `ticket_checkout_${window.crypto.randomUUID()}`;
    return `ticket_checkout_${Date.now()}_${Math.random().toString(36).slice(2)}`;
}

function firstError(errors: Record<string, string>, fallback: string) {
    return Object.values(errors).find((value) => typeof value === 'string') ?? fallback;
}
