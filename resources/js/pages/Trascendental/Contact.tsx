import { usePage } from '@inertiajs/react';
import { Mail } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';
import { PageShell } from './Partials';

type FormState = {
    service_type: 'booking' | 'production';
    city: string;
    event_date: string;
    budget: string;
    name: string;
    email: string;
    phone: string;
    message: string;
    captcha_answer: string;
    privacy_accepted: boolean;
    company_website: string;
};

const initialState: FormState = {
    service_type: 'production',
    city: '',
    event_date: '',
    budget: '',
    name: '',
    email: '',
    phone: '',
    message: '',
    captcha_answer: '',
    privacy_accepted: false,
    company_website: '',
};

export default function Contact() {
    const { ziggy, locale, site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const [form, setForm] = useState<FormState>(initialState);
    const [status, setStatus] = useState<'idle' | 'sending' | 'success' | 'error'>('idle');

    const update = (key: keyof FormState, value: string | boolean) => {
        setForm((current) => ({ ...current, [key]: value }));
    };

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setStatus('sending');

        const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
        const response = await fetch(route('trascendental.leads.store', undefined, false, ziggy), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({
                ...form,
                locale,
                current_url: window.location.href,
            }),
        });

        if (response.ok) {
            setStatus('success');
            setForm(initialState);
            return;
        }

        setStatus('error');
    };

    return (
        <TrascendentalLayout>
            <PageShell title={t('trascendental.contact.title')} intro={t('trascendental.contact.intro')}>
                <div className="mb-8 grid gap-4 border-y border-black/15 py-6 text-lg leading-relaxed text-black/70 lg:grid-cols-[0.8fr_1.2fr]">
                    <p className="text-2xl font-black uppercase leading-none text-black">
                        {t('trascendental.contact.prompt_title')}
                    </p>
                    <div>
                        <p>{t('trascendental.contact.prompt_body')}</p>
                        {site.email ? (
                            <a
                                href={`mailto:${site.email}`}
                                className="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-black px-5 text-xs font-bold uppercase text-black sm:text-sm"
                            >
                                {t('trascendental.contact.email_direct_cta')}
                                <Mail className="h-4 w-4" />
                            </a>
                        ) : null}
                    </div>
                </div>
                <form onSubmit={submit} className="grid gap-8 border-y border-black/15 py-10 lg:grid-cols-2">
                    <div className="grid gap-6">
                        <input
                            type="text"
                            name="company_website"
                            value={form.company_website}
                            onChange={(event) => update('company_website', event.target.value)}
                            className="hidden"
                            tabIndex={-1}
                            autoComplete="off"
                        />
                        <Field label={t('trascendental.contact.service_type')}>
                            <select
                                value={form.service_type}
                                onChange={(event) => update('service_type', event.target.value as FormState['service_type'])}
                                className="contact-input"
                                required
                            >
                                <option value="production">{t('trascendental.contact.production')}</option>
                                <option value="booking">{t('trascendental.contact.booking')}</option>
                            </select>
                        </Field>
                        <Field label={t('trascendental.contact.city')}>
                            <input value={form.city} onChange={(event) => update('city', event.target.value)} className="contact-input" required />
                        </Field>
                        <Field label={t('trascendental.contact.event_date')}>
                            <input type="date" value={form.event_date} onChange={(event) => update('event_date', event.target.value)} className="contact-input" />
                        </Field>
                        <Field label={t('trascendental.contact.budget')}>
                            <input value={form.budget} onChange={(event) => update('budget', event.target.value)} className="contact-input" required />
                        </Field>
                    </div>

                    <div className="grid gap-6">
                        <Field label={t('trascendental.contact.name')}>
                            <input value={form.name} onChange={(event) => update('name', event.target.value)} className="contact-input" required />
                        </Field>
                        <Field label={t('trascendental.contact.email')}>
                            <input type="email" value={form.email} onChange={(event) => update('email', event.target.value)} className="contact-input" required />
                        </Field>
                        <Field label={t('trascendental.contact.phone')}>
                            <input value={form.phone} onChange={(event) => update('phone', event.target.value)} className="contact-input" />
                        </Field>
                        <Field label={t('trascendental.contact.message')}>
                            <textarea value={form.message} onChange={(event) => update('message', event.target.value)} className="contact-input min-h-32 resize-y" />
                        </Field>
                        <Field label={t('trascendental.contact.captcha_label')}>
                            <input
                                value={form.captcha_answer}
                                onChange={(event) => update('captcha_answer', event.target.value)}
                                className="contact-input"
                                inputMode="numeric"
                                required
                            />
                        </Field>
                        <label className="grid grid-cols-[1.1rem_1fr] gap-3 border-t border-black/15 pt-4 text-sm leading-relaxed text-black/65">
                            <input
                                type="checkbox"
                                checked={form.privacy_accepted}
                                onChange={(event) => update('privacy_accepted', event.target.checked)}
                                className="mt-1 h-4 w-4 rounded-none border-black accent-black"
                                required
                            />
                            <span>
                                <span className="font-bold text-black">{t('trascendental.contact.privacy_accept')}</span>
                                {' '}
                                {t('trascendental.contact.privacy_notice')}
                            </span>
                        </label>
                        <Button type="submit" disabled={status === 'sending'} className="h-12 rounded-full bg-black text-sm font-bold uppercase text-white hover:bg-black/80">
                            {t('trascendental.contact.submit')}
                        </Button>
                        {status === 'success' ? <p className="text-sm font-bold text-black">{t('trascendental.contact.success')}</p> : null}
                        {status === 'error' ? <p className="text-sm font-bold text-red-700">{t('trascendental.contact.error')}</p> : null}
                    </div>
                </form>
            </PageShell>
        </TrascendentalLayout>
    );
}

function Field({ label, children }: { label: string; children: ReactNode }) {
    return (
        <label className="grid gap-2">
            <span className="text-xs font-bold uppercase text-black/45">{label}</span>
            {children}
        </label>
    );
}
