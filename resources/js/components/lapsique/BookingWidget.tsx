import { useCallback, useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { format, parseISO, startOfDay } from 'date-fns';
import { es } from 'date-fns/locale';
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
import { getPaymentTrustContent, type PaymentTrustVariant } from '@/lib/paymentTrustCopy';
import { setActiveFunnelModal } from '@/lib/funnelModalEvents';
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
import { consumeBookingModalPending } from '@/lib/openBookingModal';
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
import { glassCardVariants } from '@/lib/variants';
import type { BookingSlot, HeroProofVideoData, PageProps, PortfolioItemData, VideoItem } from '@/types';
import {
    CalendarRange,
    Check,
    CalendarDays,
    ShieldCheck,
    X,
} from 'lucide-react';

interface BookingWidgetProps {
    slots: BookingSlot[];
    price: number;
    whatsapp?: string;
    errors?: Record<string, string>;
    checkoutRoute?: string;
    paymentProvider?: 'mercadopago' | 'stripe';
    product?: BookingWidgetProduct;
    popupVariant?: PopupVariant;
    popupPortfolioItems?: PortfolioItemData[];
    popupHeroProofVideo?: HeroProofVideoData | null;
    popupOriginals?: VideoItem[];
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

import {
    CONTENT_DELIVERY_BUSINESS_DAYS,
    CONTENT_DRONE_SHOTS,
    CONTENT_OFFER_CHECKOUT_SUMMARY_LINES,
    CONTENT_OFFER_ONE_LINER,
    CONTENT_OFFER_SHORT,
    CONTENT_PHOTOS_COUNT,
    CONTENT_REEL_DURATION_SECONDS,
    CONTENT_SESSION_DURATION,
} from '@/data/contentOffer';

const contentSessionProduct: BookingWidgetProduct = {
    checkoutLabel: 'Checkout de sesión',
    headerTitle: 'Agenda tu sesión ahora',
    headerDescription: `Reel de ${CONTENT_REEL_DURATION_SECONDS} s con cámara Sony, ${CONTENT_DRONE_SHOTS} tomas de dron DJI y dirección en set`,
    summaryTitle: 'Sesión de contenido',
    summaryDescription: CONTENT_OFFER_ONE_LINER,
    summaryDescriptionLines: CONTENT_OFFER_CHECKOUT_SUMMARY_LINES,
    cartService: CONTENT_OFFER_ONE_LINER,
    cartDuration: CONTENT_SESSION_DURATION,
    summaryPerks: [
        `Reel editado de ${CONTENT_REEL_DURATION_SECONDS} segundos con cámara Sony, listo para Meta Ads`,
        `${CONTENT_DRONE_SHOTS} tomas aéreas con dron DJI integradas al reel`,
        `${CONTENT_PHOTOS_COUNT} fotografías editadas`,
        `Sesión de ${CONTENT_SESSION_DURATION} con dirección en set`,
        'Captura Sony α7 full frame',
        `Entrega en ${CONTENT_DELIVERY_BUSINESS_DAYS} días hábiles`,
    ],
    terms: [
        'La reserva queda sujeta a disponibilidad real del horario elegido y a la confirmación del flujo de pago o modo prueba.',
        `La sesión estándar tiene duración de ${CONTENT_SESSION_DURATION}. Tiempo adicional, locaciones extra o cambios de alcance pueden cotizarse aparte.`,
        `Incluye 1 reel editado de ${CONTENT_REEL_DURATION_SECONDS} segundos con cámara Sony, ${CONTENT_DRONE_SHOTS} tomas aéreas con dron DJI (cuando locación, permisos y condiciones de vuelo lo permiten) y ${CONTENT_PHOTOS_COUNT} fotografías editadas. Material bruto, versiones adicionales o entregas urgentes no están incluidos salvo acuerdo escrito.`,
        'Puedes solicitar cambios de fecha con mínimo 24 horas de anticipación. Cambios tardíos o inasistencias pueden perder el horario reservado.',
        'Autorizas el uso del material producido para portafolio de lapsique.media salvo que se acuerde confidencialidad antes de la sesión.',
    ],
    paymentCopy: 'Pago seguro con tarjeta vía Stripe.',
    unavailableWhatsApp: 'Hola, me interesa una sesión de contenido y no veo horarios publicados.',
};

const BOOKING_FORM_DRAFT_KEY = 'lapsique_booking_form_draft';

export function BookingWidget({
    slots,
    price,
    whatsapp,
    errors = {},
    checkoutRoute = 'booking.checkout',
    paymentProvider = 'mercadopago',
    product = contentSessionProduct,
    popupVariant = 'home',
    popupPortfolioItems = [],
    popupHeroProofVideo = null,
    popupOriginals = [],
}: BookingWidgetProps) {
    const { ziggy, booking, payments } = usePage<PageProps>().props;
    const isTestMode = booking.skipPayment;
    const sectionRef = useSectionEvent<HTMLElement>('booking_widget_viewed', { section: 'agenda' });
    const calendarRef = useSectionEvent<HTMLDivElement>('booking_calendar_opened', { section: 'calendar' });
    const submittedRef = useRef(false);
    const formStartedRef = useRef(false);
    const skipSlotAutoSelectRef = useRef(false);
    const handledDeepLinkOpenRef = useRef(false);
    const selectedSlotRef = useRef<number | null>(null);
    const isTestModeRef = useRef(isTestMode);
    const dateSectionRef = useRef<HTMLElement>(null);
    const timeSectionRef = useRef<HTMLElement>(null);
    const formSectionRef = useRef<HTMLElement>(null);
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
        () => getPaymentTrustContent(trustVariant),
        [trustVariant],
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
        skipSlotAutoSelectRef.current = true;
        setSelectedDateKey(undefined);
        setSelectedSlotId(null);
        setData('booking_slot_id', '');
    }, [setData]);

    const bookingPopupVisual = useMemo(
        () => getPopupVisualCopy(popupVariant, 'booking'),
        [popupVariant],
    );

    const bookingPopupImage = useMemo(
        () =>
            resolvePopupImage({
                variant: popupVariant,
                purpose: 'booking',
                portfolioItems: popupPortfolioItems,
                heroProofVideo: popupHeroProofVideo,
                originals: popupOriginals,
            }),
        [popupVariant, popupPortfolioItems, popupHeroProofVideo, popupOriginals],
    );

    const handleBookingModalOpenChange = useCallback(
        (next: boolean) => {
            setIsBookingModalOpen(next);
            setActiveFunnelModal(next ? 'booking' : null);
        },
        [],
    );

    const openBookingModalFresh = useCallback(
        (source: string) => {
            resetBookingWizard();
            handleBookingModalOpenChange(true);
            trackBookingEvent('booking_popup_shown', { source });
        },
        [handleBookingModalOpenChange, resetBookingWizard],
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
            slot_id: selectedSlot.id,
            skip_payment: isTestMode,
        });
    }, [isTestMode, selectedSlot, showForm]);

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
            slot_id: slot.id,
            value: price,
            currency: 'MXN',
            skip_payment: isTestMode,
        });
    };

    const handleSlotSelect = (slot: BookingSlot) => {
        skipSlotAutoSelectRef.current = false;
        userPickedSlotRef.current = true;
        applySlotSelection(slot);
    };

    const applyDateSelection = (dateKey: string) => {
        const slotsForDay = slotsByDate.get(dateKey) ?? [];
        skipSlotAutoSelectRef.current = false;
        setSelectedDateKey(dateKey);
        submittedRef.current = false;
        if (slotsForDay.length > 0) {
            applySlotSelection(slotsForDay[0]);
        } else {
            setSelectedSlotId(null);
            setData('booking_slot_id', '');
        }
        trackBookingEvent('booking_date_selected', { date: dateKey });
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
        skipSlotAutoSelectRef.current = true;
        setSelectedSlotId(null);
        setData('booking_slot_id', '');
        trackBookingEvent('booking_slot_cleared');
        if (isBookingModalOpen && selectedDateKey) {
            requestAnimationFrame(() => scrollToBookingStep('horario'));
        }
    };

    useEffect(() => {
        if (!selectedDateKey || daySlots.length === 0) {
            return;
        }

        if (skipSlotAutoSelectRef.current) {
            skipSlotAutoSelectRef.current = false;
            return;
        }

        const hasValidSelection =
            selectedSlotId !== null && daySlots.some((slot) => slot.id === selectedSlotId);

        if (hasValidSelection) {
            return;
        }

        applySlotSelection(daySlots[0]);
    }, [selectedDateKey, daySlots, selectedSlotId]);

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
        const trackingContext = window.LapsiqueTracker?.getContext?.() ?? {};

        trackBookingEvent('booking_form_submitted', {
            skip_payment: isTestMode,
            value: price,
            currency: 'MXN',
            client_email: data.client_email,
            client_phone: data.client_phone,
            client_name: data.client_name,
        });
        trackBookingEvent('booking_checkout_started', {
            skip_payment: isTestMode,
            payment_provider: isTestMode ? 'none' : data.payment_provider,
            value: price,
            currency: 'MXN',
            client_email: data.client_email,
            client_phone: data.client_phone,
            client_name: data.client_name,
        });
        transform((current) => ({
            ...current,
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
            <section
                id="agenda"
                ref={sectionRef}
                className="scroll-mt-20 space-y-6"
                data-analytics-section="booking_widget"
            >
                <BookingHeader isTestMode={isTestMode} product={product} />
                <div className={cn(glassCardVariants({ elevated: true }), 'space-y-5 p-8 text-center md:p-10')}>
                    <span className="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary">
                        <CalendarRange className="h-7 w-7" />
                    </span>
                    <div className="space-y-2">
                        <h3 className="text-xl font-semibold text-foreground">No hay horarios publicados</h3>
                        <p className="mx-auto max-w-2xl text-sm leading-relaxed text-muted-foreground">
                            {isTestMode
                                ? 'El calendario todavia no tiene fechas disponibles. Si eres del equipo, publica horarios con php artisan booking:ensure-slots y vuelve a cargar esta pagina.'
                                : 'El calendario todavia no tiene fechas disponibles. Puedes escribirnos para coordinar manualmente una fecha.'}
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
                                    Coordinar por WhatsApp
                                </a>
                            </BookingCtaButton>
                        </BookingCtaSection>
                    )}
                </div>
            </section>
        );
    }

    return (
        <section
            id="agenda"
            ref={sectionRef}
            className="scroll-mt-20 space-y-6 py-4 md:py-6"
            data-analytics-section="booking_widget"
        >
            <BookingHeader isTestMode={isTestMode} product={product} />

            <div className={cn(glassCardVariants({ elevated: true }), 'glass-border-glow overflow-hidden border')}>
                <div
                    ref={calendarRef}
                    className="space-y-5 p-5 md:p-6"
                >
                    <div>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary/80">
                            Agenda ahora
                        </p>
                        <p className="mt-1 max-w-2xl text-base font-medium text-foreground md:text-lg">
                            Elige fecha, toma un horario y deja cerrada tu reserva en este momento.
                        </p>
                    </div>

                    <div className="space-y-4">
                            <div className="flex flex-wrap items-end justify-between gap-3">
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        1. Escoge día
                                    </p>
                                    <h3 className="mt-1 text-xl font-bold md:text-2xl">
                                        Fechas disponibles
                                    </h3>
                                    <p className="mt-2 max-w-xl text-sm text-muted-foreground">
                                        Elige tu día — la fecha resaltada es una sugerencia; confírmala con un toque.
                                    </p>
                                </div>
                                <span className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                    <CalendarDays className="h-4 w-4" />
                                    Próximas fechas
                                </span>
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
                imageUrl={bookingPopupImage.url}
                imageAlt={bookingPopupImage.alt}
                badge={bookingPopupVisual.badge}
                title={bookingPopupVisual.title}
                description={bookingPopupVisual.description}
                caption={bookingPopupVisual.caption}
                imageOverlay={
                    <div className="mt-5 space-y-2 rounded-2xl border border-white/15 bg-black/35 p-4 backdrop-blur-md">
                        <p className="font-mono-tabular text-2xl font-bold text-white">{formatMxn(price)}</p>
                        {showStripePopupOverlay && (
                            <p className="text-xs font-medium text-primary">
                                Compra protegida · reembolso 100% si no hay sesión
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
                    <div className="lg:hidden">
                        <h2 className="font-display text-xl font-bold leading-tight sm:text-2xl">
                            Agendar tu sesión
                        </h2>
                    </div>

                            <div className="space-y-4">
                                <section
                                    ref={dateSectionRef}
                                    id="booking-step-fecha"
                                    className={cn(
                                        'scroll-mt-4 space-y-3 rounded-2xl border bg-muted/40 p-3 transition-shadow sm:space-y-4 sm:p-4',
                                        !selectedDateKey
                                            ? bookingStepActiveSectionClasses
                                            : bookingStepCompleteSectionClasses,
                                    )}
                                >
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                            1. Escoge día
                                        </p>
                                        <h3
                                            data-step-focus
                                            tabIndex={-1}
                                            className="mt-1 text-base font-bold outline-none sm:text-lg"
                                        >
                                            Fechas disponibles
                                        </h3>
                                        <p className="mt-1.5 hidden text-sm text-muted-foreground sm:block">
                                            Elige tu día — puedes cambiar la fecha cuando quieras.
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
                                            'scroll-mt-4 space-y-4 rounded-2xl border bg-muted/40 p-4 transition-shadow',
                                            Boolean(selectedDateKey) && !selectedSlotId
                                                ? bookingStepActiveSectionClasses
                                                : selectedSlotId
                                                  ? bookingStepCompleteSectionClasses
                                                  : 'border-border/70',
                                        )}
                                    >
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                                2. Elige horario
                                            </p>
                                            <h3
                                                data-step-focus
                                                tabIndex={-1}
                                                className="mt-1 text-lg font-bold outline-none"
                                            >
                                                {format(selectedDate, "EEEE d 'de' MMMM", { locale: es })}
                                            </h3>
                                        </div>

                                        {daySlots.length === 0 && (
                                            <p className="rounded-xl border border-dashed border-border/70 px-4 py-3 text-sm text-muted-foreground">
                                                Ese día ya no tiene horarios disponibles. Elige otra fecha arriba.
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
                                                                    : 'border-border/70 bg-secondary/80 text-foreground opacity-90 hover:border-primary/40 hover:bg-muted hover:opacity-100',
                                                            )}
                                                            onClick={() => handleSlotSelect(slot)}
                                                        >
                                                            <span className="font-semibold">{slot.time_label}</span>
                                                            {isSelected ? (
                                                                <span className={bookingOptionSelectedBadgeClasses}>
                                                                    <Check className="h-3 w-3" aria-hidden />
                                                                    Elegido
                                                                </span>
                                                            ) : (
                                                                <span className="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">
                                                                    Disponible
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
                                            'scroll-mt-4 rounded-2xl border bg-muted/40 p-4 transition-shadow',
                                            showForm
                                                ? bookingStepActiveSectionClasses
                                                : 'border-border/70',
                                        )}
                                    >
                                        <p className="mb-4 text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                            3. Datos y pago
                                        </p>
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
                isTestMode={isTestMode}
                usesStripeCheckout={
                    !isTestMode
                    && (paymentProvider === 'stripe' || data.payment_provider === 'stripe')
                }
            />

        </section>
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
    product: BookingWidgetProduct;
    stripeConfigured: boolean;
    mercadopagoConfigured: boolean;
    fixedPaymentProvider?: 'stripe';
    trustBody?: string;
}) {
    return (
        <div className={cn(glassCardVariants({ elevated: true }), 'space-y-6 border p-5 md:p-6')}>
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                {isTestMode ? 'Paso 2 - datos y confirmacion' : 'Paso 2 - datos y pago'}
                            </p>
                            <h3
                                data-step-focus
                                tabIndex={-1}
                                className="text-lg font-bold outline-none"
                            >
                                {isTestMode
                                    ? 'Completa para confirmar tu reserva de prueba'
                                    : 'Completa y paga para confirmar'}
                            </h3>
                            <p className="text-sm font-bold text-foreground">
                                {format(parseISO(selectedSlot.date), 'd MMM yyyy', { locale: es })} ·{' '}
                                {selectedSlot.time_label}
                            </p>
                        </div>
                        <Button type="button" variant="ghost" size="sm" onClick={clearSelection}>
                            <X className="h-4 w-4" />
                            Cambiar
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

                    <form onSubmit={submitCheckout} className="space-y-5">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Nombre completo *">
                                <Input
                                    value={data.client_name}
                                    onChange={(e) => setData('client_name', e.target.value)}
                                    required
                                    className="bg-input/50"
                                />
                            </Field>
                            <Field label="Email *">
                                <Input
                                    type="email"
                                    value={data.client_email}
                                    onChange={(e) => setData('client_email', e.target.value)}
                                    required
                                    className="bg-input/50"
                                />
                            </Field>
                            <Field label="WhatsApp *">
                                <Input
                                    type="tel"
                                    value={data.client_phone}
                                    onChange={(e) => setData('client_phone', e.target.value)}
                                    required
                                    className="bg-input/50"
                                />
                            </Field>
                            <Field label="Instagram">
                                <Input
                                    value={data.client_instagram}
                                    onChange={(e) => setData('client_instagram', e.target.value)}
                                    placeholder="@tuusuario (opcional)"
                                    className="bg-input/50"
                                />
                            </Field>
                        </div>

                        <Field label="Notas (opcional)">
                            <Textarea
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
                                <p className="font-semibold text-foreground">Pago con tarjeta</p>
                                <p className="mt-1 text-muted-foreground">
                                    El cobro se completa en Stripe al confirmar esta reserva.
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
                                    Acepto los{' '}
                                    <button
                                        type="button"
                                        className={bookingCheckoutLinkClasses}
                                        onClick={openTerms}
                                    >
                                        términos y condiciones
                                    </button>{' '}
                                    de la reserva, incluyendo política de cambios, entregables y uso de material.
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
                                'Redirigiendo a pago seguro…'
                            ) : (
                                <>
                                    {!isTestMode ? (
                                        <ShieldCheck className="h-5 w-5 shrink-0" aria-hidden />
                                    ) : null}
                                    {isTestMode
                                        ? 'Confirmar reserva de prueba'
                                        : `Pagar ${formatMxn(price)}`}
                                </>
                            )}
                        </Button>
                    </form>
                </div>
    );
}

function BookingHeader({ isTestMode, product }: { isTestMode: boolean; product: BookingWidgetProduct }) {
    return (
        <div className="space-y-3 text-center">
            <span className="inline-block rounded-full border border-primary/30 bg-primary/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-primary">
                Cierra tu cita
            </span>
            <h2 className="font-display text-3xl font-bold md:text-5xl">
                {product.headerTitle}
            </h2>
            <p className="mx-auto max-w-2xl text-sm text-muted-foreground md:text-base">
                {product.headerDescription}{' '}
                {isTestMode ? '· confirmacion sin cobro real en este entorno.' : '· pago seguro al confirmar.'}
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
    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={selected}
            className={cn(
                'flex w-full flex-col rounded-2xl border-2 text-left transition duration-200',
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
                    {format(date, 'EEEE', { locale: es })}
                </span>
                {selected ? (
                    <span aria-label="Fecha seleccionada" className={bookingOptionSelectedBadgeClasses}>
                        <Check className="h-3 w-3" aria-hidden />
                        <span className="hidden min-[380px]:inline">Elegido</span>
                    </span>
                ) : null}
                {suggested && !selected ? (
                    <span
                        className={cn(
                            'shrink-0 rounded-full border px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.08em] sm:px-2 sm:text-[10px]',
                            bookingOptionSuggestedBadgeClasses,
                        )}
                    >
                        Sugerido
                    </span>
                ) : null}
            </div>
            <span
                className={cn(
                    'mt-1.5 block font-mono-tabular font-bold leading-none sm:mt-2',
                    compact ? 'text-2xl sm:text-3xl' : 'text-[2rem] sm:text-3xl md:text-4xl',
                )}
            >
                {format(date, 'd', { locale: es })}
            </span>
            <span
                className={cn(
                    'mt-auto block pt-1 text-xs capitalize leading-snug sm:pt-1.5 sm:text-sm',
                    selected ? bookingOptionSelectedMonthClasses : 'text-muted-foreground',
                )}
            >
                {format(date, 'MMMM', { locale: es })}
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
    return (
        <div className="space-y-3 border-t border-border/70 pt-5">
            <div>
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                    Resumen de tu reserva
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
                <CartRow label="Servicio" value={product.cartService} />
                <CartRow label="Duración de la sesión" value={product.cartDuration} />
                <CartRow
                    label="Fecha"
                    value={
                        selectedDate
                            ? format(selectedDate, 'd MMM yyyy', { locale: es })
                            : format(parseISO(selectedSlot.date), 'd MMM yyyy', { locale: es })
                    }
                />
                <CartRow label="Horario" value={selectedSlot.time_label} />
                <div className="flex items-center justify-between gap-3 border-t border-border/70 pt-3">
                    <span className="text-sm font-semibold text-foreground">Total</span>
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
    isTestMode,
    usesStripeCheckout = false,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    product: BookingWidgetProduct;
    isTestMode: boolean;
    usesStripeCheckout?: boolean;
}) {
    const showStripeProtection = usesStripeCheckout;
    const stripeTrust = getPaymentTrustContent('stripe');

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                layer="stacked"
                className="theme-scrollbar max-h-[min(90vh,760px)] max-w-[min(94vw,680px)] overflow-y-auto border-primary/25 p-0 shadow-[0_24px_80px_oklch(0_0_0/0.55)]"
            >
                <div className="space-y-5 p-5 md:p-7">
                    <div className="space-y-2">
                        <span className="inline-flex rounded-full border border-primary/25 bg-primary/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            Términos de reserva
                        </span>
                        <DialogTitle className="font-display text-2xl md:text-3xl">
                            Condiciones claras antes de cerrar tu cita
                        </DialogTitle>
                        <DialogDescription className="text-sm leading-relaxed text-muted-foreground">
                            Estos puntos protegen el tiempo de producción y evitan confusiones sobre alcance, cambios y entrega.
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
                        Entendido
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}

function Field({
    label,
    children,
}: {
    label: string;
    children: ReactNode;
}) {
    return (
        <div>
            <Label className="mb-1.5 text-xs uppercase tracking-wider text-muted-foreground">
                {label}
            </Label>
            {children}
        </div>
    );
}
