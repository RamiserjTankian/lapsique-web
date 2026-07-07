import { useMemo, useState, type FormEvent, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Check,
    CheckCircle2,
    Loader2,
    MapPin,
    MessageCircle,
    Send,
} from 'lucide-react';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { route } from '@/lib/route';
import { cn } from '@/lib/utils';
import {
    localized,
    SERVICE_AREAS,
    SERVICE_LANDING_CONFIGS,
    type LandingConfig,
    type LandingServiceKey,
} from '@/data/serviceLandingPages';
import type { PageProps } from '@/types';

interface ServiceLandingSectionsProps {
    serviceKey: LandingServiceKey;
    onBook: (source: string) => void;
    className?: string;
}

type LeadState = 'idle' | 'submitting' | 'success' | 'error';

function trackingPayload(): Record<string, string | null> {
    const context = window.SiteTracker?.getContext?.() ?? {};

    return {
        current_page: window.location.href,
        landing_page: String(context.landing_page ?? window.location.pathname),
        landing_url: String(context.landing_url ?? window.location.href),
        page_type: context.page_type ? String(context.page_type) : null,
        page_name: context.page_name ? String(context.page_name) : null,
        referrer: String(context.referrer ?? document.referrer ?? ''),
        utm_source: context.utm_source ? String(context.utm_source) : null,
        utm_medium: context.utm_medium ? String(context.utm_medium) : null,
        utm_campaign: context.utm_campaign ? String(context.utm_campaign) : null,
        utm_term: context.utm_term ? String(context.utm_term) : null,
        utm_content: context.utm_content ? String(context.utm_content) : null,
        analytics_visitor_id: context.visitor_id ? String(context.visitor_id) : null,
        analytics_session_id: context.session_id ? String(context.session_id) : null,
        fbp: context.fbp ? String(context.fbp) : null,
        fbc: context.fbc ? String(context.fbc) : null,
    };
}

export function ServiceLandingSections({
    serviceKey,
    onBook,
    className,
}: ServiceLandingSectionsProps) {
    const { site, locale, ziggy } = usePage<PageProps>().props;
    const config = SERVICE_LANDING_CONFIGS[serviceKey];
    const whatsappHref = useMemo(
        () => buildWhatsAppHref(site.whatsapp, localized(config.whatsappMessage, locale)),
        [config.whatsappMessage, locale, site.whatsapp],
    );

    const trackWhatsApp = (source: string) => {
        trackBookingEvent('service_landing_whatsapp_clicked', {
            service: config.serviceKey,
            service_name: config.trackingService,
            service_area: 'riviera_maya',
            city_focus: 'playa_del_carmen_tulum_cancun',
            landing: config.path,
            source,
            target: 'whatsapp',
        });
    };

    return (
        <div className={cn('mx-auto flex max-w-6xl flex-col gap-10 px-4 py-12 sm:px-6', className)}>
            <Breadcrumbs config={config} locale={locale} />

            <section className="grid gap-5 lg:grid-cols-[0.92fr_1.08fr] lg:gap-8">
                <StoryPanel
                    label={locale === 'en' ? 'Problem' : 'Problema'}
                    title={localized(config.problemTitle, locale)}
                    body={config.problem.map((item) => localized(item, locale))}
                    tone="dark"
                />
                <StoryPanel
                    label={locale === 'en' ? 'Solution' : 'Solucion'}
                    title={localized(config.solutionTitle, locale)}
                    body={config.solution.map((item) => localized(item, locale))}
                />
            </section>

            <section className="grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                <div>
                    <SectionTitle
                        title={localized(config.outcomesTitle, locale)}
                        description={localized(config.intro, locale)}
                    />
                    <div className="mt-5 flex flex-wrap gap-2">
                        {config.audience.map((item) => (
                            <span
                                key={localized(item, locale)}
                                className="rounded-full border border-primary/25 bg-primary/10 px-3 py-1.5 text-xs font-semibold text-foreground"
                            >
                                {localized(item, locale)}
                            </span>
                        ))}
                    </div>
                </div>
                <div className="grid gap-3 sm:grid-cols-2">
                    {config.outcomes.map((item) => (
                        <div
                            key={localized(item, locale)}
                            className="flex gap-3 rounded-lg border border-border/70 bg-card p-4 shadow-sm"
                        >
                            <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-primary" />
                            <p className="text-sm font-medium leading-relaxed text-foreground">
                                {localized(item, locale)}
                            </p>
                        </div>
                    ))}
                </div>
            </section>

            <section className="grid gap-6 rounded-xl border border-border/75 bg-background/75 p-5 shadow-sm backdrop-blur md:p-6 lg:grid-cols-2">
                <Checklist
                    title={localized(config.audienceTitle, locale)}
                    items={config.audience.map((item) => localized(item, locale))}
                />
                <Checklist
                    title={localized(config.deliverablesTitle, locale)}
                    items={config.deliverables.map((item) => localized(item, locale))}
                />
            </section>

            <section>
                <SectionTitle
                    title={locale === 'en' ? 'Packages and scope' : 'Paquetes y alcance'}
                    description={locale === 'en'
                        ? 'Clear entry points for leads from Meta Ads without inventing testimonials or fake results.'
                        : 'Puntos de entrada claros para leads de Meta Ads sin inventar testimonios ni resultados falsos.'}
                />
                <div className="mt-6 grid gap-4 lg:grid-cols-3">
                    {config.packages.map((item, index) => (
                        <article
                            key={localized(item.name, locale)}
                            className={cn(
                                'flex min-h-[310px] flex-col rounded-lg border bg-card p-5 shadow-sm',
                                index === 1 && 'border-primary/55 shadow-[0_22px_70px_oklch(0.72_0.14_75/0.14)]',
                            )}
                        >
                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                                {localized(item.priceLabel, locale)}
                            </p>
                            <h3 className="mt-3 font-display text-2xl font-bold text-foreground">
                                {localized(item.name, locale)}
                            </h3>
                            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                                {localized(item.description, locale)}
                            </p>
                            <ul className="mt-5 flex flex-col gap-3">
                                {item.includes.map((include) => (
                                    <li key={localized(include, locale)} className="flex gap-2 text-sm text-foreground">
                                        <Check className="mt-0.5 size-4 shrink-0 text-primary" />
                                        <span>{localized(include, locale)}</span>
                                    </li>
                                ))}
                            </ul>
                        </article>
                    ))}
                </div>
            </section>

            <section>
                <SectionTitle
                    title={locale === 'en' ? 'Work process' : 'Proceso de trabajo'}
                    description={locale === 'en'
                        ? 'A simple flow from quote to delivery.'
                        : 'Un flujo simple desde cotizacion hasta entrega.'}
                />
                <div className="mt-6 grid gap-4 md:grid-cols-4">
                    {config.process.map((step, index) => (
                        <article key={localized(step.title, locale)} className="rounded-lg border border-border/70 bg-card p-4 shadow-sm">
                            <p className="font-mono-tabular text-sm font-bold text-primary">
                                {String(index + 1).padStart(2, '0')}
                            </p>
                            <h3 className="mt-3 font-display text-xl font-bold text-foreground">
                                {localized(step.title, locale)}
                            </h3>
                            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                                {localized(step.description, locale)}
                            </p>
                        </article>
                    ))}
                </div>
            </section>

            <ServiceAreas locale={locale} />

            <section className="grid gap-6 lg:grid-cols-[0.88fr_1.12fr] lg:items-start">
                <LandingLeadForm
                    config={config}
                    locale={locale}
                    postUrl={route('leads.capture', undefined, false, ziggy)}
                />
                <FaqSection config={config} locale={locale} />
            </section>

            <section className="rounded-xl border border-primary/25 bg-[linear-gradient(135deg,oklch(0.14_0.04_250),oklch(0.18_0.05_210))] p-6 text-white shadow-xl shadow-black/20 md:p-8">
                <div className="grid gap-5 md:grid-cols-[1fr_auto] md:items-center">
                    <div>
                        <h2 className="font-display text-3xl font-bold leading-tight text-white">
                            {localized(config.headline, locale)}
                        </h2>
                        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-white/72 md:text-base">
                            {locale === 'en'
                                ? 'Tell us the project and we will recommend the right production path.'
                                : 'Cuéntanos el proyecto y te recomendamos el camino de producción correcto.'}
                        </p>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 md:min-w-[360px]">
                        <Button className="h-12 rounded-lg bg-[#25D366] text-white hover:bg-[#1EBE5D]" asChild>
                            <a
                                href={whatsappHref}
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label={localized(config.primaryCta, locale)}
                                onClick={() => trackWhatsApp('final_story_cta')}
                            >
                                <MessageCircle className="size-4" />
                                {localized(config.finalCta, locale)}
                            </a>
                        </Button>
                        <BookingCtaButton type="button" className="h-12 rounded-lg" onClick={() => onBook('final_story_booking')}>
                            {locale === 'en' ? 'Schedule' : 'Agendar'}
                            <ArrowRight className="size-4" />
                        </BookingCtaButton>
                    </div>
                </div>
            </section>
        </div>
    );
}

function Breadcrumbs({ config, locale }: { config: LandingConfig; locale: string }) {
    return (
        <nav aria-label={locale === 'en' ? 'Breadcrumb' : 'Migas de pan'} className="text-sm text-muted-foreground">
            <ol className="flex flex-wrap items-center gap-2">
                <li>
                    <a href="/" className="transition hover:text-foreground">
                        {locale === 'en' ? 'Home' : 'Inicio'}
                    </a>
                </li>
                <li aria-hidden>/</li>
                <li className="font-medium text-foreground">{localized(config.headline, locale)}</li>
            </ol>
        </nav>
    );
}

function SectionTitle({ title, description }: { title: string; description?: string }) {
    return (
        <div className="max-w-3xl">
            <h2 className="font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                {title}
            </h2>
            {description ? (
                <p className="mt-3 text-sm leading-relaxed text-muted-foreground md:text-base">
                    {description}
                </p>
            ) : null}
        </div>
    );
}

function StoryPanel({
    label,
    title,
    body,
    tone = 'light',
}: {
    label: string;
    title: string;
    body: string[];
    tone?: 'light' | 'dark';
}) {
    const dark = tone === 'dark';

    return (
        <article
            className={cn(
                'rounded-xl border p-5 shadow-sm md:p-6',
                dark
                    ? 'border-white/12 bg-[linear-gradient(135deg,oklch(0.13_0.03_250),oklch(0.18_0.04_230))] text-white shadow-black/20'
                    : 'border-border/75 bg-card text-foreground',
            )}
        >
            <p className={cn('text-xs font-semibold uppercase tracking-[0.18em]', dark ? 'text-primary' : 'text-primary')}>
                {label}
            </p>
            <h2 className={cn('mt-3 font-display text-2xl font-bold leading-tight md:text-3xl', dark ? 'text-white' : 'text-foreground')}>
                {title}
            </h2>
            <div className="mt-4 space-y-3">
                {body.map((paragraph) => (
                    <p key={paragraph} className={cn('text-sm leading-relaxed md:text-base', dark ? 'text-white/72' : 'text-muted-foreground')}>
                        {paragraph}
                    </p>
                ))}
            </div>
        </article>
    );
}

function Checklist({ title, items }: { title: string; items: string[] }) {
    return (
        <div>
            <h2 className="font-display text-2xl font-bold text-foreground">{title}</h2>
            <ul className="mt-4 grid gap-3 sm:grid-cols-2">
                {items.map((item) => (
                    <li key={item} className="flex items-start gap-2 text-sm text-foreground">
                        <Check className="mt-0.5 size-4 shrink-0 text-primary" />
                        <span>{item}</span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

function ServiceAreas({ locale }: { locale: string }) {
    return (
        <section className="rounded-xl border border-border/75 bg-card p-5 shadow-sm md:p-6">
            <div className="grid gap-6 lg:grid-cols-[0.75fr_1.25fr] lg:items-start">
                <SectionTitle
                    title={locale === 'en' ? 'Audiovisual services in Riviera Maya and Cancun' : 'Servicios audiovisuales en Riviera Maya y Cancun'}
                    description={locale === 'en'
                        ? 'We cover projects in the main commercial and tourism zones of the region.'
                        : 'Atendemos proyectos en las principales zonas comerciales y turisticas de la region.'}
                />
                <div className="grid gap-3 sm:grid-cols-2">
                    {SERVICE_AREAS.map((area) => (
                        <div key={area} className="flex gap-2 rounded-lg border border-border/65 bg-background/70 p-3">
                            <MapPin className="mt-0.5 size-4 shrink-0 text-primary" />
                            <p className="text-sm font-medium text-foreground">{area}</p>
                        </div>
                    ))}
                </div>
            </div>
            <p className="mt-5 text-sm leading-relaxed text-muted-foreground">
                {locale === 'en'
                    ? 'Projects outside Riviera Maya can be quoted with logistics and travel costs depending on location, calendar, and production type.'
                    : 'Tambien podemos cotizar proyectos fuera de Riviera Maya con logistica y viaticos segun ubicacion, calendario y tipo de produccion.'}
            </p>
        </section>
    );
}

function LandingLeadForm({
    config,
    locale,
    postUrl,
}: {
    config: LandingConfig;
    locale: string;
    postUrl: string;
}) {
    const [state, setState] = useState<LeadState>('idle');
    const [message, setMessage] = useState('');
    const [form, setForm] = useState({
        name: '',
        email: '',
        phone: '',
        instagram: '',
        need: localized(config.leadForm.needOptions[0], locale),
        city: '',
        notes: '',
    });

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setState('submitting');
        setMessage('');

        const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        if (!token) {
            setState('error');
            setMessage(locale === 'en' ? 'Session token unavailable.' : 'No se pudo validar la sesion.');
            return;
        }

        try {
            const response = await fetch(postUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    name: form.name,
                    email: form.email,
                    phone: form.phone || null,
                    instagram_handle: form.instagram || null,
                    message: form.notes || null,
                    interests: [
                        config.serviceKey,
                        config.leadType,
                        form.need,
                        form.city,
                    ].filter(Boolean),
                    ...trackingPayload(),
                }),
            });

            const payload = await parseResponse(response);

            if (!response.ok || !payload.success) {
                throw new Error(payload.message ?? validationMessage(payload.errors) ?? 'Lead failed');
            }

            trackBookingEvent('service_landing_lead_form_submitted', {
                service: config.serviceKey,
                service_name: config.trackingService,
                service_area: 'riviera_maya',
                city_focus: form.city || 'playa_del_carmen_tulum_cancun',
                landing: config.path,
                form_name: 'service_lead_form',
                lead_type: config.leadType,
                event_id: payload.meta_event_id,
            });

            setState('success');
            setMessage(locale === 'en' ? 'Thanks. We received your request.' : 'Gracias. Recibimos tu solicitud.');
        } catch (error) {
            setState('error');
            setMessage(error instanceof Error ? error.message : (locale === 'en' ? 'Something went wrong.' : 'Ocurrio un error.'));
        }
    };

    const disabled = state === 'submitting' || state === 'success';

    return (
        <section className="rounded-xl border border-border/75 bg-card p-5 shadow-sm md:p-6">
            <h2 className="font-display text-3xl font-bold leading-tight text-foreground">
                {localized(config.leadForm.title, locale)}
            </h2>
            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                {localized(config.leadForm.description, locale)}
            </p>

            <form onSubmit={submit} className="mt-6 space-y-4" data-service-type={config.serviceKey}>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field id={`${config.serviceKey}-name`} label={locale === 'en' ? 'Name' : 'Nombre'}>
                        <Input id={`${config.serviceKey}-name`} required value={form.name} onChange={(event) => setForm({ ...form, name: event.target.value })} disabled={disabled} />
                    </Field>
                    <Field id={`${config.serviceKey}-email`} label="Email">
                        <Input id={`${config.serviceKey}-email`} type="email" required value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} disabled={disabled} />
                    </Field>
                    <Field id={`${config.serviceKey}-phone`} label="WhatsApp">
                        <Input id={`${config.serviceKey}-phone`} type="tel" value={form.phone} onChange={(event) => setForm({ ...form, phone: event.target.value })} disabled={disabled} />
                    </Field>
                    <Field id={`${config.serviceKey}-city`} label={locale === 'en' ? 'City / area' : 'Ciudad / zona'}>
                        <Input id={`${config.serviceKey}-city`} value={form.city} onChange={(event) => setForm({ ...form, city: event.target.value })} disabled={disabled} placeholder="Playa del Carmen, Tulum, Cancun" />
                    </Field>
                </div>

                <Field id={`${config.serviceKey}-need`} label={locale === 'en' ? 'What do you need?' : 'Que necesitas?'}>
                    <select
                        id={`${config.serviceKey}-need`}
                        value={form.need}
                        onChange={(event) => setForm({ ...form, need: event.target.value })}
                        disabled={disabled}
                        className="h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    >
                        {config.leadForm.needOptions.map((option) => (
                            <option key={localized(option, locale)} value={localized(option, locale)}>
                                {localized(option, locale)}
                            </option>
                        ))}
                    </select>
                </Field>

                <Field id={`${config.serviceKey}-notes`} label={locale === 'en' ? 'Additional message' : 'Mensaje adicional'}>
                    <Textarea
                        id={`${config.serviceKey}-notes`}
                        value={form.notes}
                        onChange={(event) => setForm({ ...form, notes: event.target.value })}
                        disabled={disabled}
                        placeholder={locale === 'en' ? 'Project, tentative date, or context.' : 'Proyecto, fecha tentativa o contexto.'}
                    />
                </Field>

                {message ? (
                    <p className={cn('text-sm font-medium', state === 'error' ? 'text-destructive' : 'text-primary')}>
                        {message}
                    </p>
                ) : null}

                <Button type="submit" className="h-12 w-full rounded-lg" disabled={disabled}>
                    {state === 'submitting' ? <Loader2 className="size-4 animate-spin" /> : <Send className="size-4" />}
                    {state === 'success'
                        ? (locale === 'en' ? 'Request sent' : 'Solicitud enviada')
                        : (locale === 'en' ? 'Send request' : 'Enviar solicitud')}
                </Button>
            </form>
        </section>
    );
}

function Field({ id, label, children }: { id: string; label: string; children: ReactNode }) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            {children}
        </div>
    );
}

function FaqSection({ config, locale }: { config: LandingConfig; locale: string }) {
    return (
        <section className="rounded-xl border border-border/75 bg-card p-5 shadow-sm md:p-6">
            <h2 className="font-display text-3xl font-bold leading-tight text-foreground">
                {locale === 'en' ? 'FAQ' : 'Preguntas frecuentes'}
            </h2>
            <div className="mt-5 divide-y divide-border/70">
                {config.faqs.map((faq) => (
                    <article key={localized(faq.question, locale)} className="py-4 first:pt-0 last:pb-0">
                        <h3 className="text-base font-semibold text-foreground">
                            {localized(faq.question, locale)}
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {localized(faq.answer, locale)}
                        </p>
                    </article>
                ))}
            </div>
        </section>
    );
}

async function parseResponse(response: Response): Promise<{
    success?: boolean;
    message?: string;
    errors?: Record<string, string[]>;
    meta_event_id?: string;
}> {
    if (!response.headers.get('content-type')?.includes('application/json')) {
        return { success: false };
    }

    try {
        return await response.json();
    } catch {
        return { success: false };
    }
}

function validationMessage(errors?: Record<string, string[]>): string | null {
    return errors ? Object.values(errors).flat()[0] ?? null : null;
}

function buildWhatsAppHref(phone: string | undefined, text: string): string {
    const cleanedPhone = (phone ?? '').replace(/[^\d]/g, '');
    const base = cleanedPhone ? `https://wa.me/${cleanedPhone}` : 'https://wa.me/';

    return `${base}?text=${encodeURIComponent(text)}`;
}
