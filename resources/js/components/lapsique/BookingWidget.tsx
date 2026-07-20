import { useCallback, useEffect, useMemo, useRef, useState, type ReactNode, type RefObject } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { format, parseISO, startOfDay } from 'date-fns';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { PremiumSplitDialog } from '@/components/lapsique/PremiumSplitDialog';
import { FunnelPopups } from '@/components/lapsique/FunnelPopups';
import { getPaymentTrustContent, type PaymentTrustVariant } from '@/lib/paymentTrustCopy';
import { setActiveFunnelModal, BOOKING_MODAL_CLOSED_EVENT } from '@/lib/funnelModalEvents';
import {
    getPopupVisualCopy,
    resolvePopupImage,
    type PopupVariant,
} from '@/lib/popupMedia';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { PaymentMethodField } from '@/components/lapsique/PaymentMethodField';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { getContentSessionProduct } from '@/lib/bookingProducts';
import { getDateFnsLocale } from '@/lib/dateLocale';
import { consumeBookingModalPending } from '@/lib/openBookingModal';
import { modalShellDocument } from '@/lib/modalLayout';
import { route } from '@/lib/route';
import { cn, formatMxn, parseSlotDate } from '@/lib/utils';
import {
    bookingCheckboxSelectedClasses,
    bookingCheckoutLinkClasses,
    bookingCheckoutPanelClasses,
    bookingCheckoutPriceClasses,
    bookingConfirmButtonClasses,
    bookingOptionSelectedBadgeClasses,
    bookingOptionSelectedClasses,
    bookingOptionSelectedDayClasses,
    bookingOptionSelectedMonthClasses,
    bookingOptionSuggestedBadgeClasses,
    bookingOptionSuggestedClasses,
    bookingOptionSuggestedLabelClasses,
    bookingSlotSelectedClasses,
    bookingStepActiveSectionClasses,
    bookingStepCompleteSectionClasses,
} from '@/lib/bookingSelectionStyles';
import { LandingPageSection } from '@/components/lapsique/LandingPageSection';
import { glassCardVariants } from '@/lib/variants';
import type { BookingSlot, HeroProofVideoData, PageProps, PortfolioItemData, VideoItem } from '@/types';
import {
    CalendarRange,
    Check,
    ShieldCheck,
    X,
} from 'lucide-react';

interface BookingWidgetProps {
    slots: BookingSlot[];
    price: number;
    whatsapp?: string;
    errors?: Record<string, string>;
    className?: string;
    checkoutRoute?: string;
    paymentProvider?: 'mercadopago' | 'stripe';
    product?: BookingWidgetProduct;
    popupVariant?: PopupVariant;
    popupPortfolioItems?: PortfolioItemData[];
    popupHeroProofVideo?: HeroProofVideoData | null;
    popupOriginals?: VideoItem[];
    highlight?: boolean;
    analyticsPayload?: Record<string, unknown>;
    /** Optional landing-specific event emitted only after the booking modal actually opens. */
    analyticsOpenEvent?: string;
}

export interface BookingWidgetProduct {
    checkoutLabel: string;
    headerTitle: string;
    headerDescription: string;
    summaryTitle: string;
    summaryDescription: string;
    summaryDescriptionLines?: string[];
    cartService: string;
    cartDuration: string;
    summaryPerks: string[];
    terms: string[];
    paymentCopy: string;
    unavailableWhatsApp: string;
}

const BOOKING_FORM_DRAFT_KEY = 'lapsique_booking_form_draft';
const DEFAULT_ANALYTICS_PAYLOAD: Record<string, unknown> = {};

function generateCheckoutEventId(): string {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    return `chk_${Date.now()}_${Math.random().toString(36).slice(2)}`;
}

export function BookingWidget({
    slots,
    price,
    whatsapp,
    errors = {},
    className,
    checkoutRoute = 'booking.checkout',
    paymentProvider = 'mercadopago',
    product: productProp,
    popupVariant = 'home',
    popupPortfolioItems = [],
    popupHeroProofVideo = null,
    popupOriginals = [],
    highlight = false,
    analyticsPayload = DEFAULT_ANALYTICS_PAYLOAD,
    analyticsOpenEvent,
}: BookingWidgetProps) {
    const { t, locale } = useTranslations();
    const product = useMemo(
        () => productProp ?? getContentSessionProduct(t),
        [productProp, t],
    );
    const { ziggy, booking, payments } = usePage<PageProps>().props;
    const isTestMode = booking.skipPayment;
    const sectionRef = useSectionEvent<HTMLElement>('booking_widget_viewed', { section: 'agenda' });
    const calendarRef = useSectionEvent<HTMLDivElement>('booking_calendar_opened', { section: 'calendar' });
    const sectionSurfaceClassName = cn(
        highlight
            && 'relative overflow-hidden !border-2 !border-primary/55 !bg-[radial-gradient(circle_at_top,oklch(0.88_0.13_80/0.28),transparent_34%),linear-gradient(135deg,oklch(1_0_0/0.88),oklch(0.95_0.06_80/0.28))] !shadow-[0_28px_110px_oklch(0.78_0.14_75/0.28)]',
    );
    const sectionInnerClassName = cn('space-y-6', highlight && 'relative z-10');
    const calendarPanelClassName = cn(
        glassCardVariants({ elevated: true }),
        'glass-border-glow overflow-hidden border',
        highlight && '!border-primary/45 !bg-background/88 !shadow-[0_22px_80px_oklch(0.12_0.04_260/0.16)]',
    );
    const submittedRef = useRef(false);
    const formStartedRef = useRef(false);
    const handledDeepLinkOpenRef = useRef(false);
    const selectedSlotRef = useRef<number | null>(null);
    const isTestModeRef = useRef(isTestMode);
    const dateSectionRef = useRef<HTMLElement>(null);
    const timeSectionRef = useRef<HTMLElement>(null);
    const formSectionRef = useRef<HTMLElement>(null);
    const termsTriggerRef = useRef<HTMLButtonElement>(null);
    const pendingScrollStepRef = useRef<'horario' | 'datos' | null>(null);
    const userPickedSlotRef = useRef(false);
    const [selectedDateKey, setSelectedDateKey] = useState<string | undefined>();
    const [selectedSlotId, setSelectedSlotId] = useState<number | null>(null);
    const [isBookingModalOpen, setIsBookingModalOpen] = useState(false);
    const [isTermsModalOpen, setIsTermsModalOpen] = useState(false);

    const slotsByDate = useMemo(() => {
        const map = new Map<string, BookingSlot[]>();
        for (const slot of slots) {
            const list = map.get(slot.date) ?? [];
            list.push(slot);
            map.set(slot.date, list);
        }
        return map;
    }, [slots]);

    const availableDateKeys = useMemo(
        () => Array.from(slotsByDate.keys()).sort(),
        [slotsByDate],
    );

    const selectedDate = useMemo(
        () => (selectedDateKey ? parseSlotDate(selectedDateKey) : undefined),
        [selectedDateKey],
    );

    const suggestedDateKey = useMemo(() => {
        if (availableDateKeys.length === 0) {
            return undefined;
        }

        const todayKey = format(startOfDay(new Date()), 'yyyy-MM-dd');

        return availableDateKeys.find((key) => key >= todayKey) ?? availableDateKeys[0];
    }, [availableDateKeys]);

    const daySlots = useMemo(() => {
        if (!selectedDateKey) {
            return [];
        }

        return slotsByDate.get(selectedDateKey) ?? [];
    }, [selectedDateKey, slotsByDate]);

    const selectedSlot = slots.find((s) => s.id === selectedSlotId);

    const { data, setData, post, processing, transform } = useForm({
        booking_slot_id: '' as string | number,
        client_name: '',
        client_email: '',
        client_phone: '',
        client_instagram: '',
        notes: '',
        payment_provider: paymentProvider,
        terms_accepted: false,
    });

    const showForm = selectedSlotId !== null;

    const trustVariant: PaymentTrustVariant = useMemo(() => {
        if (popupVariant === 'djset' || paymentProvider === 'stripe') {
            return 'stripe';
        }

        if (data.payment_provider === 'stripe') {
            return 'stripe';
        }

        return 'dual';
    }, [popupVariant, paymentProvider, data.payment_provider]);

    const trustContent = useMemo(
        () => getPaymentTrustContent(t, trustVariant),
        [t, trustVariant],
    );

    const showStripePopupOverlay =
        !isTestMode && (popupVariant === 'djset' || trustVariant === 'stripe');

    const scrollToBookingStep = useCallback((step: 'fecha' | 'horario' | 'datos') => {
        const target =
            step === 'fecha'
                ? dateSectionRef.current
                : step === 'horario'
                  ? timeSectionRef.current
                  : formSectionRef.current;

        if (!target) {
            return;
        }

        target.scrollIntoView({ behavior: 'smooth', block: 'start' });

        const focusTarget = target.querySelector<HTMLElement>('[data-step-focus]');
        focusTarget?.focus({ preventScroll: true });
    }, []);

    const resetBookingWizard = useCallback(() => {
        submittedRef.current = false;
        setSelectedDateKey(undefined);
        setSelectedSlotId(null);
        setData('booking_slot_id', '');
    }, [setData]);

    const bookingPopupVisual = useMemo(
        () => getPopupVisualCopy(t, popupVariant, 'booking'),
        [t, popupVariant],
    );

    const bookingPopupImage = useMemo(
        () =>
            resolvePopupImage(t, {
                variant: popupVariant,
                purpose: 'booking',
                portfolioItems: popupPortfolioItems,
                heroProofVideo: popupHeroProofVideo,
                originals: popupOriginals,
            }),
        [t, popupVariant, popupPortfolioItems, popupHeroProofVideo, popupOriginals],
    );

    const handleBookingModalOpenChange = useCallback(
        (next: boolean) => {
            setIsBookingModalOpen((prev) => {
                if (prev && !next && !submittedRef.current) {
                    window.dispatchEvent(
                        new CustomEvent(BOOKING_MODAL_CLOSED_EVENT, {
                            detail: {
                                hadSlot: selectedSlotRef.current !== null,
                                hadFormProgress: formStartedRef.current,
                            },
                        }),
                    );
                }

                return next;
            });
            setActiveFunnelModal(next ? 'booking' : null);
        },
        [],
    );

    const openBookingModalFresh = useCallback(
        (source: string) => {
            resetBookingWizard();
            handleBookingModalOpenChange(true);
            const openPayload = { ...analyticsPayload, source, target: 'booking_popup' };

            trackBookingEvent('booking_popup_shown', openPayload);

            if (analyticsOpenEvent) {
                trackBookingEvent(analyticsOpenEvent, openPayload);
            }
        },
        [analyticsOpenEvent, analyticsPayload, handleBookingModalOpenChange, resetBookingWizard],
    );

    useEffect(() => {
        try {
            const raw = sessionStorage.getItem(BOOKING_FORM_DRAFT_KEY);

            if (!raw) {
                return;
            }

            const draft = JSON.parse(raw) as Partial<typeof data>;

            if (draft.client_name) {
                setData('client_name', draft.client_name);
            }

            if (draft.client_email) {
                setData('client_email', draft.client_email);
            }

            if (draft.client_phone) {
                setData('client_phone', draft.client_phone);
            }

            if (draft.client_instagram) {
                setData('client_instagram', draft.client_instagram);
            }

            if (draft.notes) {
                setData('notes', draft.notes);
            }
        } catch {
            // sessionStorage unavailable or invalid JSON
        }
    }, [setData]);

    useEffect(() => {
        try {
            sessionStorage.setItem(
                BOOKING_FORM_DRAFT_KEY,
                JSON.stringify({
                    client_name: data.client_name,
                    client_email: data.client_email,
                    client_phone: data.client_phone,
                    client_instagram: data.client_instagram,
                    notes: data.notes,
                }),
            );
        } catch {
            // sessionStorage unavailable
        }
    }, [
        data.client_email,
        data.client_instagram,
        data.client_name,
        data.client_phone,
        data.notes,
    ]);

    useEffect(() => {
        if (!showForm || !selectedSlot || formStartedRef.current) {
            return;
        }

        formStartedRef.current = true;
        trackBookingEvent('booking_form_started', {
            ...analyticsPayload,
            slot_id: selectedSlot.id,
            skip_payment: isTestMode,
        });
    }, [analyticsPayload, isTestMode, selectedSlot, showForm]);

    useEffect(() => {
        if (!showForm) {
            formStartedRef.current = false;
        }
    }, [showForm]);

    useEffect(() => {
        const openBooking = (event: Event) => {
            const source =
                (event as CustomEvent<{ source?: string }>).detail?.source ?? 'global_cta';
            openBookingModalFresh(source);
        };

        window.addEventListener('booking:open', openBooking);

        return () => window.removeEventListener('booking:open', openBooking);
    }, [openBookingModalFresh]);

    useEffect(() => {
        if (slots.length === 0 || handledDeepLinkOpenRef.current) {
            return;
        }

        const shouldOpen =
            window.location.hash === '#agenda'
            || new URLSearchParams(window.location.search).get('book') === '1'
            || consumeBookingModalPending();

        if (!shouldOpen) {
            return;
        }

        handledDeepLinkOpenRef.current = true;
        openBookingModalFresh(
            window.location.hash === '#agenda'
                ? 'hash_agenda'
                : new URLSearchParams(window.location.search).get('book') === '1'
                  ? 'query_book'
                  : 'pending_navigation',
        );
    }, [openBookingModalFresh, slots.length]);

    useEffect(() => {
        selectedSlotRef.current = selectedSlotId;
    }, [selectedSlotId]);

    useEffect(() => {
        isTestModeRef.current = isTestMode;
    }, [isTestMode]);

    useEffect(() => {
        return () => {
            if (submittedRef.current) {
                return;
            }

            const activeSlotId = selectedSlotRef.current;

            if (activeSlotId !== null) {
                trackBookingEvent('booking_abandoned', {
                    ...analyticsPayload,
                    stage: 'form_open',
                    skip_payment: isTestModeRef.current,
                });
            }
        };
    }, []);

    const applySlotSelection = (slot: BookingSlot) => {
        submittedRef.current = false;
        setSelectedSlotId(slot.id);
        setData('booking_slot_id', slot.id);
        trackBookingEvent('booking_slot_selected', {
            ...analyticsPayload,
            slot_id: slot.id,
            value: price,
            currency: 'MXN',
            skip_payment: isTestMode,
        });
    };

    const handleSlotSelect = (slot: BookingSlot) => {
        userPickedSlotRef.current = true;
        applySlotSelection(slot);
    };

    const applyDateSelection = (dateKey: string) => {
        setSelectedDateKey(dateKey);
        submittedRef.current = false;
        setSelectedSlotId(null);
        setData('booking_slot_id', '');
        trackBookingEvent('booking_date_selected', { ...analyticsPayload, date: dateKey });
    };

    const handleInlineDateSelect = (dateKey: string) => {
        applyDateSelection(dateKey);
        handleBookingModalOpenChange(true);
        pendingScrollStepRef.current = 'horario';
    };

    const handleModalDateSelect = (dateKey: string) => {
        applyDateSelection(dateKey);
        pendingScrollStepRef.current = 'horario';
    };

    const clearSelection = () => {
        submittedRef.current = false;
        setSelectedSlotId(null);
        setData('booking_slot_id', '');
        trackBookingEvent('booking_slot_cleared', analyticsPayload);
        if (isBookingModalOpen && selectedDateKey) {
            requestAnimationFrame(() => scrollToBookingStep('horario'));
        }
    };

    useEffect(() => {
        if (!isBookingModalOpen || !pendingScrollStepRef.current) {
            return;
        }

        const step = pendingScrollStepRef.current;

        if (step === 'horario' && !selectedDateKey) {
            return;
        }

        if (step === 'datos' && !selectedSlotId) {
            return;
        }

        pendingScrollStepRef.current = null;
        requestAnimationFrame(() => scrollToBookingStep(step));
    }, [isBookingModalOpen, selectedDateKey, selectedSlotId, daySlots.length, scrollToBookingStep]);

    useEffect(() => {
        if (!isBookingModalOpen || !userPickedSlotRef.current || selectedSlotId === null) {
            return;
        }

        userPickedSlotRef.current = false;
        requestAnimationFrame(() => scrollToBookingStep('datos'));
    }, [isBookingModalOpen, selectedSlotId, scrollToBookingStep]);

    const submitCheckout = (e: React.FormEvent) => {
        e.preventDefault();
        submittedRef.current = true;
        const trackingContext = window.SiteTracker?.getContext?.() ?? {};
        // event_id compartido entre el pixel (browser) y CAPI (servidor) para deduplicar checkout.
        const checkoutEventId = generateCheckoutEventId();
        const paymentInfoEventId = `${checkoutEventId}_payment_info`;

        trackBookingEvent('booking_form_submitted', {
            ...analyticsPayload,
            skip_payment: isTestMode,
            value: price,
            currency: 'MXN',
            client_email: data.client_email,
            client_phone: data.client_phone,
            client_name: data.client_name,
        });
        trackBookingEvent('booking_payment_info_added', {
            ...analyticsPayload,
            skip_payment: isTestMode,
            payment_provider: isTestMode ? 'none' : data.payment_provider,
            event_id: paymentInfoEventId,
            value: price,
            currency: 'MXN',
            client_email: data.client_email,
            client_phone: data.client_phone,
            client_name: data.client_name,
        });
        trackBookingEvent('booking_checkout_started', {
            ...analyticsPayload,
            skip_payment: isTestMode,
            payment_provider: isTestMode ? 'none' : data.payment_provider,
            event_id: checkoutEventId,
            value: price,
            currency: 'MXN',
            client_email: data.client_email,
            client_phone: data.client_phone,
            client_name: data.client_name,
        });
        transform((current) => ({
            ...current,
            checkout_event_id: checkoutEventId,
            payment_info_event_id: paymentInfoEventId,
            analytics_visitor_id: trackingContext.visitor_id,
            analytics_session_id: trackingContext.session_id,
            utm_source: trackingContext.utm_source,
            utm_medium: trackingContext.utm_medium,
            utm_campaign: trackingContext.utm_campaign,
            utm_content: trackingContext.utm_content,
            utm_term: trackingContext.utm_term,
            fbp: trackingContext.fbp,
            fbc: trackingContext.fbc,
            referrer: trackingContext.referrer,
            landing_url: trackingContext.landing_url,
        }));
        post(route(checkoutRoute, undefined, false, ziggy));
    };

    if (slots.length === 0) {
        return (
            <LandingPageSection
                id="agenda"
                ref={sectionRef}
                data-analytics-section="booking_widget"
                className={className}
                surfaceClassName={sectionSurfaceClassName}
                innerClassName={sectionInnerClassName}
            >
                <BookingHeader isTestMode={isTestMode} product={product} />
                <div className={cn(glassCardVariants({ elevated: true }), 'space-y-5 p-8 text-center md:p-10')}>
                    <span className="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary">
                        <CalendarRange className="h-7 w-7" />
                    </span>
                    <div className="space-y-2">
                        <h3 className="text-xl font-semibold text-foreground">{t('booking.empty.title')}</h3>
                        <p className="mx-auto max-w-2xl text-sm leading-relaxed text-muted-foreground">
                            {isTestMode ? t('booking.empty.message_test') : t('booking.empty.message')}
                        </p>
                    </div>
                    {whatsapp && (
                        <BookingCtaSection className="py-0">
                            <BookingCtaButton asChild>
                                <a
                                    href={`https://wa.me/${whatsapp}?text=${encodeURIComponent(product.unavailableWhatsApp)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {t('booking.empty.cta_whatsapp')}
                                </a>
                            </BookingCtaButton>
                        </BookingCtaSection>
                    )}
                </div>
            </LandingPageSection>
        );
    }

    const isTimeStepAwaitingSelection = Boolean(selectedDateKey) && selectedSlotId === null;

    return (
        <LandingPageSection
            id="agenda"
            ref={sectionRef}
            data-analytics-section="booking_widget"
            className={className}
            surfaceClassName={sectionSurfaceClassName}
            innerClassName={sectionInnerClassName}
        >
            <BookingHeader isTestMode={isTestMode} product={product} />

            <div className={calendarPanelClassName}>
                <div
                    ref={calendarRef}
                    className="space-y-5 p-5 md:p-6"
                >
                    <div className="space-y-4">
                        <div className="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <h3 className="mt-1 text-xl font-bold md:text-2xl">
                                    {t('booking.step.date_title')}
                                </h3>
                                <p className="mt-2 max-w-xl text-sm text-muted-foreground">
                                    {t('booking.step.date_hint')}
                                </p>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                            {availableDateKeys.slice(0, 12).map((dateKey) => {
                                const date = parseSlotDate(dateKey);
                                const isSelected = selectedDateKey === dateKey;
                                const isSuggested =
                                    !selectedDateKey && suggestedDateKey === dateKey;

                                return (
                                    <DateOption
                                        key={dateKey}
                                        date={date}
                                        selected={isSelected}
                                        suggested={isSuggested}
                                        onClick={() => handleInlineDateSelect(dateKey)}
                                    />
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>

            <PremiumSplitDialog
                open={isBookingModalOpen}
                onOpenChange={handleBookingModalOpenChange}
                layout="form"
                imageUrl={bookingPopupImage.url}
                imageAlt={bookingPopupImage.alt}
                title={bookingPopupVisual.title}
                description={bookingPopupVisual.description}
                caption={bookingPopupVisual.caption}
                imageOverlay={
                    <div className="mt-5 space-y-2 border border-white/20 bg-black/60 p-4 backdrop-blur-md">
                        <p className="font-mono-tabular text-2xl font-bold text-white">{formatMxn(price)}</p>
                        {showStripePopupOverlay && (
                            <p className="text-xs font-medium text-primary">
                                {trustContent.protectedPaymentChip}
                            </p>
                        )}
                        <ul className="space-y-1 text-xs text-white/75">
                            {product.summaryPerks.slice(0, 3).map((perk) => (
                                <li key={perk} className="flex items-start gap-2">
                                    <Check className="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary" />
                                    <span>{perk}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                }
                contentClassName="px-4 py-4 sm:px-5 sm:py-5 md:px-7 md:py-7"
            >
                <div className="space-y-4 sm:space-y-5">
                    <div className="flex items-start justify-between gap-4 border-b border-foreground/15 pb-4">
                        <div>
                            <p className="font-mono text-[10px] font-semibold uppercase tracking-[0.18em] text-primary">
                                {locale === 'en' ? 'Guided booking · 3 steps' : 'Reserva guiada · 3 pasos'}
                            </p>
                            <h2 className="mt-1 font-display text-xl font-bold leading-tight sm:text-2xl">
                                {t('booking.modal.title')}
                            </h2>
                        </div>
                        <p className="hidden max-w-[14rem] text-right text-xs leading-relaxed text-muted-foreground sm:block">
                            {locale === 'en' ? 'Your progress is saved while you choose.' : 'Tu avance se guarda mientras eliges.'}
                        </p>
                    </div>

                            <div className="space-y-4">
                                <section
                                    ref={dateSectionRef}
                                    id="booking-step-fecha"
                                    className={cn(
                                        'scroll-mt-4 space-y-3 border bg-muted/40 p-3 transition-shadow sm:space-y-4 sm:p-4',
                                        !selectedDateKey
                                            ? bookingStepActiveSectionClasses
                                            : bookingStepCompleteSectionClasses,
                                    )}
                                >
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                            {t('booking.step.date_label')}
                                        </p>
                                        <h3
                                            data-step-focus
                                            tabIndex={-1}
                                            className="mt-1 text-base font-bold outline-none sm:text-lg"
                                        >
                                            {t('booking.step.date_title')}
                                        </h3>
                                        <p className="mt-1.5 hidden text-sm text-muted-foreground sm:block">
                                            {t('booking.step.date_hint_modal')}
                                        </p>
                                    </div>
                                    <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3">
                                        {availableDateKeys.slice(0, 12).map((dateKey) => {
                                            const date = parseSlotDate(dateKey);
                                            const isSelected = selectedDateKey === dateKey;
                                            const isSuggested =
                                                !selectedDateKey && suggestedDateKey === dateKey;

                                            return (
                                                <DateOption
                                                    key={dateKey}
                                                    date={date}
                                                    selected={isSelected}
                                                    suggested={isSuggested}
                                                    compact
                                                    onClick={() => handleModalDateSelect(dateKey)}
                                                />
                                            );
                                        })}
                                    </div>
                                </section>

                                {selectedDateKey && selectedDate && (
                                    <section
                                        ref={timeSectionRef}
                                        id="booking-step-horario"
                                        className={cn(
                                            'scroll-mt-4 space-y-4 border bg-muted/40 p-4 transition-all',
                                            isTimeStepAwaitingSelection
                                                ? [
                                                    bookingStepActiveSectionClasses,
                                                    'border-primary/80 bg-[radial-gradient(circle_at_top_left,oklch(0.86_0.15_78/0.28),transparent_42%),linear-gradient(135deg,oklch(1_0_0/0.9),oklch(0.94_0.07_78/0.32))] shadow-[0_0_0_1px_oklch(0.78_0.14_75/0.24),0_22px_70px_oklch(0.78_0.14_75/0.26)] ring-primary/65',
                                                ]
                                                : selectedSlotId
                                                  ? bookingStepCompleteSectionClasses
                                                  : 'border-border/70',
                                        )}
                                    >
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                                {t('booking.step.time_label')}
                                            </p>
                                            <h3
                                                data-step-focus
                                                tabIndex={-1}
                                                className="mt-1 text-lg font-bold outline-none"
                                            >
                                                {format(selectedDate, "EEEE d 'de' MMMM", { locale: getDateFnsLocale(locale) })}
                                            </h3>
                                        </div>

                                        {daySlots.length === 0 && (
                                            <p className="rounded-xl border border-dashed border-border/70 px-4 py-3 text-sm text-muted-foreground">
                                                {t('booking.step.no_slots')}
                                            </p>
                                        )}

                                        {daySlots.length > 0 && (
                                            <div className="grid gap-2 sm:grid-cols-2">
                                                {daySlots.map((slot) => {
                                                    const isSelected = selectedSlotId === slot.id;

                                                    return (
                                                        <Button
                                                            key={slot.id}
                                                            type="button"
                                                            variant="outline"
                                                            size="lg"
                                                            aria-pressed={isSelected}
                                                            className={cn(
                                                                'h-12 justify-between rounded-xl px-4 text-base transition-all',
                                                                isSelected
                                                                    ? bookingSlotSelectedClasses
                                                                    : isTimeStepAwaitingSelection
                                                                      ? 'border-primary/45 bg-primary/10 text-foreground shadow-[0_12px_36px_oklch(0.78_0.14_75/0.18)] opacity-100 ring-1 ring-primary/20 hover:border-primary/75 hover:bg-primary/15 hover:shadow-[0_16px_46px_oklch(0.78_0.14_75/0.28)]'
                                                                      : 'border-border/70 bg-secondary/80 text-foreground opacity-90 hover:border-primary/40 hover:bg-muted hover:opacity-100',
                                                            )}
                                                            onClick={() => handleSlotSelect(slot)}
                                                        >
                                                            <span className="font-semibold">{slot.time_label}</span>
                                                            {isSelected ? (
                                                                <span className={bookingOptionSelectedBadgeClasses}>
                                                                    <Check className="h-3 w-3" aria-hidden />
                                                                    {t('booking.step.selected')}
                                                                </span>
                                                            ) : (
                                                                <span className="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">
                                                                    {t('booking.step.available')}
                                                                </span>
                                                            )}
                                                        </Button>
                                                    );
                                                })}
                                            </div>
                                        )}
                                    </section>
                                )}

                                {showForm && selectedSlot && (
                                    <section
                                        ref={formSectionRef}
                                        id="booking-step-datos"
                                        className={cn(
                                            'scroll-mt-4 border bg-muted/40 p-4 transition-shadow',
                                            showForm
                                                ? bookingStepActiveSectionClasses
                                                : 'border-border/70',
                                        )}
                                    >
                                        <BookingForm
                                            data={data}
                                            errors={errors}
                                            isTestMode={isTestMode}
                                            price={price}
                                            processing={processing}
                                            selectedDate={selectedDate}
                                            selectedSlot={selectedSlot}
                                            setData={setData}
                                            clearSelection={clearSelection}
                                            submitCheckout={submitCheckout}
                                            openTerms={() => setIsTermsModalOpen(true)}
                                            termsTriggerRef={termsTriggerRef}
                                            product={product}
                                            stripeConfigured={payments?.stripeConfigured ?? true}
                                            mercadopagoConfigured={payments?.mercadopagoConfigured ?? true}
                                            fixedPaymentProvider={
                                                paymentProvider === 'stripe' ? 'stripe' : undefined
                                            }
                                            trustBody={trustContent.body}
                                        />
                                    </section>
                                )}
                            </div>
                </div>
            </PremiumSplitDialog>

            <TermsModal
                open={isTermsModalOpen}
                onOpenChange={setIsTermsModalOpen}
                product={product}
                returnFocusRef={termsTriggerRef}
                usesStripeCheckout={
                    !isTestMode
                    && (paymentProvider === 'stripe' || data.payment_provider === 'stripe')
                }
            />

            <FunnelPopups
                variant={popupVariant}
                slotsCount={slots.length}
                portfolioItems={popupPortfolioItems}
                heroProofVideo={popupHeroProofVideo}
                originals={popupOriginals}
            />

        </LandingPageSection>
    );
}

function BookingForm({
    data,
    errors,
    isTestMode,
    price,
    processing,
    selectedDate,
    selectedSlot,
    setData,
    clearSelection,
    submitCheckout,
    openTerms,
    termsTriggerRef,
    product,
    stripeConfigured,
    mercadopagoConfigured,
    fixedPaymentProvider,
    trustBody,
}: {
    data: {
        booking_slot_id: string | number;
        client_name: string;
        client_email: string;
        client_phone: string;
        client_instagram: string;
        notes: string;
        payment_provider: string;
        terms_accepted: boolean;
    };
    errors: Record<string, string>;
    isTestMode: boolean;
    price: number;
    processing: boolean;
    selectedDate?: Date;
    selectedSlot: BookingSlot;
    setData: (key: keyof typeof data, value: string | number | boolean) => void;
    clearSelection: () => void;
    submitCheckout: (e: React.FormEvent) => void;
    openTerms: () => void;
    termsTriggerRef: RefObject<HTMLButtonElement | null>;
    product: BookingWidgetProduct;
    stripeConfigured: boolean;
    mercadopagoConfigured: boolean;
    fixedPaymentProvider?: 'stripe';
    trustBody?: string;
}) {
    const { t, locale } = useTranslations();

    return (
        <div className={cn(glassCardVariants({ elevated: true }), 'space-y-6 border p-5 md:p-6')}>
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                {isTestMode ? t('booking.form.step_test') : t('booking.form.step_live')}
                            </p>
                            <h3
                                data-step-focus
                                tabIndex={-1}
                                className="text-lg font-bold outline-none"
                            >
                                {isTestMode
                                    ? t('booking.form.title_test')
                                    : t('booking.form.title_live')}
                            </h3>
                            <p className="text-sm font-bold text-foreground">
                                {format(parseISO(selectedSlot.date), 'd MMM yyyy', { locale: getDateFnsLocale(locale) })} ·{' '}
                                {selectedSlot.time_label}
                            </p>
                        </div>
                        <Button type="button" variant="ghost" size="sm" onClick={clearSelection}>
                            <X className="h-4 w-4" />
                            {t('booking.form.change')}
                        </Button>
                    </div>

                    {Object.keys(errors).length > 0 && (
                        <Alert variant="destructive">
                            <AlertDescription>
                                {Object.values(errors).map((msg) => (
                                    <p key={msg}>{msg}</p>
                                ))}
                            </AlertDescription>
                        </Alert>
                    )}

                    <form
                        onSubmit={submitCheckout}
                        className="space-y-5"
                        data-analytics-label="booking_form"
                        data-service-type="content_booking"
                        data-checkout-stage="customer_details"
                    >
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label={t('booking.form.full_name')} htmlFor="booking-client-name">
                                <Input
                                    id="booking-client-name"
                                    name="client_name"
                                    autoComplete="name"
                                    value={data.client_name}
                                    onChange={(e) => setData('client_name', e.target.value)}
                                    required
                                    className="bg-input/50"
                                />
                            </Field>
                            <Field label={`${t('common.form.email')} *`} htmlFor="booking-client-email">
                                <Input
                                    id="booking-client-email"
                                    name="client_email"
                                    autoComplete="email"
                                    type="email"
                                    value={data.client_email}
                                    onChange={(e) => setData('client_email', e.target.value)}
                                    required
                                    className="bg-input/50"
                                />
                            </Field>
                            <Field label={t('booking.form.whatsapp')} htmlFor="booking-client-phone">
                                <Input
                                    id="booking-client-phone"
                                    name="client_phone"
                                    autoComplete="tel"
                                    type="tel"
                                    value={data.client_phone}
                                    onChange={(e) => setData('client_phone', e.target.value)}
                                    required
                                    className="bg-input/50"
                                />
                            </Field>
                            <Field label={t('booking.form.instagram')} htmlFor="booking-client-instagram">
                                <Input
                                    id="booking-client-instagram"
                                    name="client_instagram"
                                    value={data.client_instagram}
                                    onChange={(e) => setData('client_instagram', e.target.value)}
                                    placeholder={t('common.form.placeholder_instagram_optional')}
                                    className="bg-input/50"
                                />
                            </Field>
                        </div>

                        <Field label={t('booking.form.notes')} htmlFor="booking-notes">
                            <Textarea
                                id="booking-notes"
                                name="notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                rows={3}
                                className="bg-input/50"
                            />
                        </Field>

                        {!isTestMode && !fixedPaymentProvider && (
                            <PaymentMethodField
                                value={data.payment_provider}
                                onChange={(v) => setData('payment_provider', v)}
                                stripeConfigured={stripeConfigured}
                                mercadopagoConfigured={mercadopagoConfigured}
                            />
                        )}
                        {!isTestMode && fixedPaymentProvider === 'stripe' && (
                            <div className={cn(bookingCheckoutPanelClasses, 'px-4 py-3 text-sm')}>
                                <p className="font-semibold text-foreground">{t('booking.payment.card_title')}</p>
                                <p className="mt-1 text-muted-foreground">
                                    {t('booking.payment.card_body')}
                                </p>
                            </div>
                        )}

                        <BookingCheckoutSummary
                            price={price}
                            selectedDate={selectedDate}
                            selectedSlot={selectedSlot}
                            product={product}
                        />

                        <div className={cn(bookingCheckoutPanelClasses, 'p-4')}>
                            <label className="flex items-start gap-3 text-sm leading-relaxed">
                                <Checkbox
                                    checked={data.terms_accepted}
                                    onCheckedChange={(checked) => setData('terms_accepted', checked === true)}
                                    className={cn('mt-0.5', bookingCheckboxSelectedClasses)}
                                    required
                                />
                                <span className="text-muted-foreground">
                                    {t('booking.terms.accept_lead')}{' '}
                                    <button
                                        ref={termsTriggerRef}
                                        type="button"
                                        aria-haspopup="dialog"
                                        data-booking-terms-trigger
                                        className={bookingCheckoutLinkClasses}
                                        onClick={openTerms}
                                    >
                                        {t('booking.terms.link')}
                                    </button>{' '}
                                    {t('booking.terms.accept_rest')}
                                </span>
                            </label>
                            {errors.terms_accepted && (
                                <p className="mt-2 text-xs text-destructive">{errors.terms_accepted}</p>
                            )}
                        </div>

                        {!isTestMode && fixedPaymentProvider === 'stripe' && trustBody ? (
                            <p className={cn(bookingCheckoutPanelClasses, 'px-4 py-3 text-sm text-muted-foreground lg:hidden')}>
                                {trustBody}
                            </p>
                        ) : null}

                        <Button
                            type="submit"
                            variant="ghost"
                            className={bookingConfirmButtonClasses}
                            disabled={processing || !data.terms_accepted}
                        >
                            {processing ? (
                                t('booking.submit.redirecting')
                            ) : (
                                <>
                                    {!isTestMode ? (
                                        <ShieldCheck className="h-5 w-5 shrink-0" aria-hidden />
                                    ) : null}
                                    {isTestMode
                                        ? t('booking.submit.test')
                                        : t('booking.submit.pay', { amount: formatMxn(price) })}
                                </>
                            )}
                        </Button>
                    </form>
                </div>
    );
}

function BookingHeader({ isTestMode, product }: { isTestMode: boolean; product: BookingWidgetProduct }) {
    const { t } = useTranslations();

    return (
        <div className="space-y-3 text-center">
            <h2 className="font-display text-3xl font-bold md:text-5xl">
                {product.headerTitle}
            </h2>
            <p className="mx-auto max-w-2xl text-sm text-muted-foreground md:text-base">
                {product.headerDescription}
                {isTestMode ? ` ${t('booking.header.test_suffix')}` : ''}
            </p>
        </div>
    );
}

function DateOption({
    date,
    selected,
    suggested = false,
    compact = false,
    onClick,
}: {
    date: Date;
    selected: boolean;
    suggested?: boolean;
    compact?: boolean;
    onClick: () => void;
}) {
    const { t, locale } = useTranslations();
    const dateLocale = getDateFnsLocale(locale);

    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={selected}
            className={cn(
                'flex w-full flex-col border-2 text-left transition duration-200',
                compact ? 'min-h-[6.75rem] p-3 sm:min-h-[7.5rem] sm:p-4' : 'min-h-[7.5rem] p-3.5 sm:p-4',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background',
                'active:scale-[0.98]',
                selected
                    ? bookingOptionSelectedClasses
                    : suggested
                      ? bookingOptionSuggestedClasses
                      : 'border-border/70 bg-secondary hover:border-primary/45 hover:bg-muted',
            )}
        >
            <div className="flex items-start justify-between gap-1.5">
                <span
                    className={cn(
                        'min-w-0 flex-1 text-[10px] font-semibold uppercase leading-snug tracking-[0.06em] sm:text-[11px]',
                        selected
                            ? bookingOptionSelectedDayClasses
                            : suggested
                              ? bookingOptionSuggestedLabelClasses
                              : 'text-muted-foreground',
                    )}
                >
                    {format(date, 'EEEE', { locale: dateLocale })}
                </span>
                {selected ? (
                    <span aria-label={t('booking.step.date_title')} className={bookingOptionSelectedBadgeClasses}>
                        <Check className="h-3 w-3" aria-hidden />
                        <span className="hidden min-[380px]:inline">{t('booking.step.selected')}</span>
                    </span>
                ) : null}
                {suggested && !selected ? (
                    <span
                        className={cn(
                            'shrink-0 rounded-full border px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.08em] sm:px-2 sm:text-[10px]',
                            bookingOptionSuggestedBadgeClasses,
                        )}
                    >
                        {t('booking.step.suggested')}
                    </span>
                ) : null}
            </div>
            <span
                className={cn(
                    'mt-1.5 block font-mono-tabular font-bold leading-none sm:mt-2',
                    compact ? 'text-2xl sm:text-3xl' : 'text-[2rem] sm:text-3xl md:text-4xl',
                )}
            >
                {format(date, 'd', { locale: dateLocale })}
            </span>
            <span
                className={cn(
                    'mt-auto block pt-1 text-xs capitalize leading-snug sm:pt-1.5 sm:text-sm',
                    selected ? bookingOptionSelectedMonthClasses : 'text-muted-foreground',
                )}
            >
                {format(date, 'MMMM', { locale: dateLocale })}
            </span>
        </button>
    );
}

function BookingCheckoutSummary({
    price,
    selectedDate,
    selectedSlot,
    product,
}: {
    price: number;
    selectedDate?: Date;
    selectedSlot: BookingSlot;
    product: BookingWidgetProduct;
}) {
    const { t, locale } = useTranslations();
    const dateLocale = getDateFnsLocale(locale);

    return (
        <div className="space-y-3 border-t border-border/70 pt-5">
            <div>
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                    {t('booking.summary.title')}
                </p>
                <p className="mt-1 font-display text-lg font-bold">{product.summaryTitle}</p>
                {product.summaryDescriptionLines?.length ? (
                    <ul className="mt-2 space-y-1.5 text-xs leading-relaxed text-muted-foreground">
                        {product.summaryDescriptionLines.map((line) => (
                            <li key={line} className="flex gap-2">
                                <span className="mt-0.5 shrink-0 text-muted-foreground/50" aria-hidden>
                                    ·
                                </span>
                                <span>{line}</span>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p className="mt-1 text-xs text-muted-foreground">{product.summaryDescription}</p>
                )}
            </div>

            <div className={cn(bookingCheckoutPanelClasses, 'space-y-2 p-4')}>
                <CartRow label={t('booking.summary.service')} value={product.cartService} />
                <CartRow label={t('booking.summary.duration')} value={product.cartDuration} />
                <CartRow
                    label={t('booking.summary.date')}
                    value={
                        selectedDate
                            ? format(selectedDate, 'd MMM yyyy', { locale: dateLocale })
                            : format(parseISO(selectedSlot.date), 'd MMM yyyy', { locale: dateLocale })
                    }
                />
                <CartRow label={t('booking.summary.time')} value={selectedSlot.time_label} />
                <div className="flex items-center justify-between gap-3 border-t border-border/70 pt-3">
                    <span className="text-sm font-semibold text-foreground">{t('booking.summary.total')}</span>
                    <span className={bookingCheckoutPriceClasses}>
                        {formatMxn(price)}
                    </span>
                </div>
            </div>
        </div>
    );
}

function CartRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-3 text-sm">
            <span className="text-muted-foreground">{label}</span>
            <span className="text-right font-medium text-foreground">{value}</span>
        </div>
    );
}

function TermsModal({
    open,
    onOpenChange,
    product,
    returnFocusRef,
    usesStripeCheckout = false,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    product: BookingWidgetProduct;
    returnFocusRef?: RefObject<HTMLElement | null>;
    usesStripeCheckout?: boolean;
}) {
    const { t } = useTranslations();
    const titleRef = useRef<HTMLHeadingElement>(null);
    const showStripeProtection = usesStripeCheckout;
    const stripeTrust = getPaymentTrustContent(t, 'stripe');

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                layer="stacked"
                className={modalShellDocument}
                onOpenAutoFocus={(event) => {
                    event.preventDefault();
                    titleRef.current?.focus({ preventScroll: true });
                }}
                onCloseAutoFocus={(event) => {
                    event.preventDefault();
                    returnFocusRef?.current?.focus({ preventScroll: true });
                }}
            >
                <div className="space-y-5 p-5 md:p-7">
                    <div className="space-y-2">
                        <DialogTitle
                            ref={titleRef}
                            tabIndex={-1}
                            className="font-display text-2xl outline-none md:text-3xl"
                        >
                            {t('booking.terms.modal_title')}
                        </DialogTitle>
                        <DialogDescription className="text-sm leading-relaxed text-muted-foreground">
                            {t('booking.terms.modal_description')}
                        </DialogDescription>
                    </div>

                    {showStripeProtection && stripeTrust.headline && (
                        <div className="flex gap-3 rounded-2xl border border-primary/30 bg-primary/10 p-4">
                            <ShieldCheck className="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                            <div className="space-y-1.5 text-sm leading-relaxed">
                                <p className="font-semibold text-foreground">
                                    {stripeTrust.headline}
                                </p>
                                {stripeTrust.body && (
                                    <p className="text-muted-foreground">{stripeTrust.body}</p>
                                )}
                            </div>
                        </div>
                    )}

                    <div className="space-y-3">
                        {product.terms.map((term, index) => (
                            <div key={term} className="rounded-2xl border border-border/70 bg-secondary p-4">
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                                    {String(index + 1).padStart(2, '0')}
                                </p>
                                <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{term}</p>
                            </div>
                        ))}
                    </div>

                    <Button type="button" variant="cinematic" className="w-full" onClick={() => onOpenChange(false)}>
                        {t('booking.terms.understood')}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}

function Field({
    label,
    htmlFor,
    children,
}: {
    label: string;
    htmlFor: string;
    children: ReactNode;
}) {
    return (
        <div>
            <Label htmlFor={htmlFor} className="mb-1.5 text-xs uppercase tracking-wider text-muted-foreground">
                {label}
            </Label>
            {children}
        </div>
    );
}
