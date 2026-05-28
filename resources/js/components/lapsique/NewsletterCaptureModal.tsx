import { useEffect, useMemo, useState, type FormEvent } from 'react';
import { usePage } from '@inertiajs/react';
import { Check, Loader2, Mail } from 'lucide-react';
import { PremiumSplitDialog } from '@/components/lapsique/PremiumSplitDialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { trackNewsletterEvent } from '@/hooks/useNewsletterAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { markNewsletterPopupSeen } from '@/lib/funnelModalEvents';
import {
    getPopupVisualCopy,
    resolvePopupImage,
    type PopupVariant,
} from '@/lib/popupMedia';
import { route } from '@/lib/route';
import type { HeroProofVideoData, PageProps, PortfolioItemData, VideoItem } from '@/types';

const INTEREST_OPTION_KEYS = [
    { id: 'events', labelKey: 'funnel.newsletter.interest_events' },
    { id: 'djs', labelKey: 'funnel.newsletter.interest_djs' },
    { id: 'production', labelKey: 'funnel.newsletter.interest_production' },
    { id: 'business', labelKey: 'funnel.newsletter.interest_business' },
] as const;

interface NewsletterCaptureModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    variant: PopupVariant;
    portfolioItems?: PortfolioItemData[];
    heroProofVideo?: HeroProofVideoData | null;
    originals?: VideoItem[];
    source?: string;
}

function getTrackingPayload(): Record<string, string | null> {
    const context = window.LapsiqueTracker?.getContext?.() ?? {};

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

export function NewsletterCaptureModal({
    open,
    onOpenChange,
    variant,
    portfolioItems = [],
    heroProofVideo = null,
    originals = [],
    source = 'auto',
}: NewsletterCaptureModalProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const visual = useMemo(() => getPopupVisualCopy(t, variant, 'newsletter'), [t, variant]);
    const image = useMemo(
        () =>
            resolvePopupImage(t, {
                variant,
                purpose: 'newsletter',
                portfolioItems,
                heroProofVideo,
                originals,
            }),
        [t, variant, portfolioItems, heroProofVideo, originals],
    );

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [instagramHandle, setInstagramHandle] = useState('');
    const [interests, setInterests] = useState<string[]>([]);
    const [loading, setLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');
    const [submitted, setSubmitted] = useState(false);
    const [successMessage, setSuccessMessage] = useState('');

    useEffect(() => {
        if (!open) {
            return;
        }

        trackNewsletterEvent('newsletter_popup_shown', { variant, source });
    }, [open, variant, source]);

    const handleOpenChange = (next: boolean) => {
        if (!next) {
            markNewsletterPopupSeen();
            trackNewsletterEvent('newsletter_popup_dismissed', { variant, source });
        }

        onOpenChange(next);
    };

    const toggleInterest = (id: string) => {
        setInterests((current) =>
            current.includes(id) ? current.filter((item) => item !== id) : [...current, id],
        );
    };

    const submit = async (event: FormEvent) => {
        event.preventDefault();
        setLoading(true);
        setErrorMessage('');

        try {
            const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;

            if (!token) {
                setErrorMessage(t('common.error.session_invalid'));

                return;
            }

            const response = await fetch(route('leads.capture', undefined, false, ziggy), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    name,
                    email,
                    phone: phone || null,
                    instagram_handle: instagramHandle || null,
                    interests,
                    ...getTrackingPayload(),
                }),
            });

            const data = await parseLeadCaptureResponse(response);

            if (!response.ok || !data.success) {
                setErrorMessage(
                    data.message
                    ?? formatValidationMessage(data.errors)
                    ?? t('common.error.generic'),
                );

                return;
            }

            setSubmitted(true);
            setSuccessMessage(
                data.message ?? t('funnel.newsletter.success_default'),
            );
            markNewsletterPopupSeen();
            trackNewsletterEvent('newsletter_form_submitted', { variant, source });
        } catch {
            setErrorMessage(t('common.error.generic'));
        } finally {
            setLoading(false);
        }
    };

    return (
        <PremiumSplitDialog
            open={open}
            onOpenChange={handleOpenChange}
            layout="promo"
            imageUrl={image.url}
            imageAlt={image.alt}
            badge={visual.badge}
            title={visual.title}
            description={visual.description}
            caption={visual.caption}
            contentClassName="px-4 py-4 sm:px-5 sm:py-5"
        >
            {submitted ? (
                <div className="flex flex-col items-center py-8 text-center">
                    <span className="inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/25 bg-primary/10 text-primary">
                        <Check className="h-8 w-8" />
                    </span>
                    <h2 className="mt-6 font-display text-2xl font-bold">{successMessage}</h2>
                    <p className="mt-2 text-sm text-muted-foreground">{t('funnel.newsletter.success_subtitle')}</p>
                    <Button
                        type="button"
                        variant="cinematic"
                        className="mt-8 w-full max-w-xs"
                        onClick={() => handleOpenChange(false)}
                    >
                        {t('common.actions.close')}
                    </Button>
                </div>
            ) : (
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="newsletter-name">{t('common.form.name')}</Label>
                        <Input
                            id="newsletter-name"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            required
                            className="bg-input/50"
                            placeholder={t('common.form.placeholder_name')}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="newsletter-email">{t('common.form.email')} *</Label>
                        <Input
                            id="newsletter-email"
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            className="bg-input/50"
                            placeholder={t('common.form.placeholder_email')}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="newsletter-phone">{t('funnel.newsletter.phone_optional')}</Label>
                            <Input
                                id="newsletter-phone"
                                type="tel"
                                value={phone}
                                onChange={(e) => setPhone(e.target.value)}
                                className="bg-input/50"
                                placeholder={t('common.form.placeholder_phone')}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="newsletter-ig">{t('funnel.newsletter.instagram_optional')}</Label>
                            <Input
                                id="newsletter-ig"
                                value={instagramHandle}
                                onChange={(e) => setInstagramHandle(e.target.value)}
                                className="bg-input/50"
                                placeholder={t('common.form.placeholder_instagram')}
                            />
                        </div>
                    </div>

                    <fieldset className="space-y-2">
                        <legend className="text-sm font-medium">{t('funnel.newsletter.interests_label')}</legend>
                        <div className="grid grid-cols-2 gap-2">
                            {INTEREST_OPTION_KEYS.map((option) => (
                                <label
                                    key={option.id}
                                    className="flex cursor-pointer items-center gap-2 rounded-xl border border-border/70 bg-secondary/60 px-3 py-2.5 text-sm transition hover:border-primary/30"
                                >
                                    <Checkbox
                                        checked={interests.includes(option.id)}
                                        onCheckedChange={() => toggleInterest(option.id)}
                                    />
                                    {t(option.labelKey)}
                                </label>
                            ))}
                        </div>
                    </fieldset>

                    {errorMessage && (
                        <p className="rounded-xl border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                            {errorMessage}
                        </p>
                    )}

                    <Button type="submit" variant="cinematic" className="w-full" disabled={loading}>
                        {loading ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                {t('common.loading.sending')}
                            </>
                        ) : (
                            <>
                                <Mail className="h-4 w-4" />
                                {t('funnel.newsletter.submit')}
                            </>
                        )}
                    </Button>

                    <p className="text-center text-xs text-muted-foreground">
                        {t('funnel.newsletter.consent')}
                    </p>
                </form>
            )}
        </PremiumSplitDialog>
    );
}

async function parseLeadCaptureResponse(response: Response): Promise<{
    success?: boolean;
    message?: string;
    errors?: Record<string, string[]>;
}> {
    const contentType = response.headers.get('content-type') ?? '';

    if (!contentType.includes('application/json')) {
        return { success: false };
    }

    try {
        return (await response.json()) as {
            success?: boolean;
            message?: string;
            errors?: Record<string, string[]>;
        };
    } catch {
        return { success: false };
    }
}

function formatValidationMessage(errors?: Record<string, string[]>): string | null {
    if (!errors) {
        return null;
    }

    const first = Object.values(errors).flat()[0];

    return first ?? null;
}
