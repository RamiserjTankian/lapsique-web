import { useState, type FormEvent, type ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Loader2,
    Plus,
    Send,
} from 'lucide-react';
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
    compact?: boolean;
    showOutcomes?: boolean;
    emphasizeForm?: boolean;
    faqAccordion?: boolean;
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
    className,
    compact = false,
    showOutcomes = true,
    emphasizeForm = false,
    faqAccordion = false,
}: ServiceLandingSectionsProps) {
    const { locale, ziggy } = usePage<PageProps>().props;
    const config = SERVICE_LANDING_CONFIGS[serviceKey];
    const primaryAudience = config.audience.slice(0, 4).map((item) => localized(item, locale));
    const primaryDeliverables = config.deliverables.slice(0, 4).map((item) => localized(item, locale));

    return (
        <div className={cn('mx-auto flex max-w-6xl flex-col gap-12 px-4 py-12 sm:px-6', className)}>
            <Breadcrumbs config={config} locale={locale} />

            {!compact ? <section className="grid gap-8 border-y border-border/70 py-8 lg:grid-cols-2 lg:gap-14">
                <div>
                    <h2 className="max-w-2xl font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                        {localized(config.problemTitle, locale)}
                    </h2>
                    <div className="mt-5 text-base leading-relaxed text-muted-foreground">
                        {config.problem.slice(0, 1).map((item) => (
                            <p key={localized(item, locale)}>{localized(item, locale)}</p>
                        ))}
                    </div>
                </div>

                <div>
                    <h3 className="font-display text-2xl font-bold leading-tight text-foreground">
                        {localized(config.solutionTitle, locale)}
                    </h3>
                    <div className="mt-4 text-base leading-relaxed text-muted-foreground">
                        {config.solution.slice(0, 1).map((item) => (
                            <p key={localized(item, locale)}>{localized(item, locale)}</p>
                        ))}
                    </div>
                </div>
            </section> : null}

            {showOutcomes ? <section className="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                <div>
                    <SectionTitle
                        title={localized(config.outcomesTitle, locale)}
                    />
                </div>
                <div className="grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    {config.outcomes.slice(0, 3).map((item) => (
                        <p
                            key={localized(item, locale)}
                            className="border-t border-border/70 pt-3 text-base font-medium leading-relaxed text-foreground"
                        >
                            {localized(item, locale)}
                        </p>
                    ))}
                </div>
            </section> : null}

            {!compact ? <section className="grid gap-8 lg:grid-cols-3">
                <EditorialList
                    title={localized(config.audienceTitle, locale)}
                    items={primaryAudience}
                />
                <EditorialList
                    title={localized(config.deliverablesTitle, locale)}
                    items={primaryDeliverables}
                />
                <EditorialList
                    title={locale === 'en' ? 'Coverage' : 'Cobertura'}
                    items={SERVICE_AREAS.slice(0, 4)}
                />
            </section> : null}

            <section className="grid gap-6 lg:grid-cols-[0.88fr_1.12fr] lg:items-start">
                <LandingLeadForm
                    config={config}
                    locale={locale}
                    postUrl={route('leads.capture', undefined, false, ziggy)}
                    emphasized={emphasizeForm}
                />
                <FaqSection config={config} locale={locale} defaultOpen={faqAccordion} />
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

function EditorialList({ title, items }: { title: string; items: string[] }) {
    return (
        <div className="border-t border-border/70 pt-4">
            <h2 className="font-display text-2xl font-bold text-foreground">{title}</h2>
            <ul className="mt-4 space-y-2">
                {items.map((item) => (
                    <li key={item} className="text-sm leading-relaxed text-muted-foreground">
                        {item}
                    </li>
                ))}
            </ul>
        </div>
    );
}

function LandingLeadForm({
    config,
    locale,
    postUrl,
    emphasized = false,
}: {
    config: LandingConfig;
    locale: string;
    postUrl: string;
    emphasized?: boolean;
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
        <section className={cn(
            'border bg-card p-5 md:p-7',
            emphasized
                ? 'border-primary border-l-4'
                : 'border-border/75',
        )} data-lead-capture="true">
            <h2 className="font-display text-3xl font-bold leading-tight text-foreground">
                {localized(config.leadForm.title, locale)}
            </h2>
            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                {localized(config.leadForm.description, locale)}
            </p>
            {emphasized ? (
                <p className="mt-3 border-l-2 border-primary pl-3 text-xs font-semibold text-foreground">
                    {locale === 'en' ? 'We reply with the right scope and next available date.' : 'Te respondemos con el alcance adecuado y la siguiente fecha disponible.'}
                </p>
            ) : null}

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

                <Button type="submit" className="h-12 w-full rounded-none" disabled={disabled}>
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

function FaqSection({ config, locale, defaultOpen = false }: { config: LandingConfig; locale: string; defaultOpen?: boolean }) {
    return (
        <section className="border border-foreground/15 bg-secondary/40 p-5 md:p-6">
            <h2 className="font-display text-3xl font-bold leading-tight text-foreground">
                {locale === 'en' ? 'FAQ' : 'Preguntas frecuentes'}
            </h2>
            <div className="mt-5 space-y-2">
                {config.faqs.map((faq, index) => (
                    <details key={localized(faq.question, locale)} className="group border border-foreground/15 bg-background open:border-primary/55" open={defaultOpen && index === 0}>
                        <summary className="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-4 font-display text-base font-bold text-foreground">
                            {localized(faq.question, locale)}
                            <Plus className="size-4 shrink-0 text-primary transition group-open:rotate-45" />
                        </summary>
                        <p className="border-t border-foreground/10 px-4 py-4 text-sm leading-relaxed text-muted-foreground">
                            {localized(faq.answer, locale)}
                        </p>
                    </details>
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
