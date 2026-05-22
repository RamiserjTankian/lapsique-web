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
import { cn, formatMxn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { BookingSlot, HeroProofVideoData, PageProps, PortfolioItemData, VideoItem } from '@/types';
import {
    CalendarRange,
    Check,
    Clock3,
    CalendarDays,
    LockKeyhole,
    MapPin,
    ShieldCheck,
    Sparkles,
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
    cartService: string;
    cartDuration: string;
    summaryPerks: string[];
    terms: string[];
    paymentCopy: string;
    unavailableWhatsApp: string;
}

const contentSessionProduct: BookingWidgetProduct = {
    checkoutLabel: 'Checkout de sesión',
    headerTitle: 'Agenda tu sesión ahora',
    headerDescription: 'Elige un horario real, deja tus datos y aparta producción con Sony a7',
    summaryTitle: 'Sesión de contenido',
    summaryDescription: '1 reel + 10 fotos editadas + dirección + entrega editada',
    cartService: '1 reel + 10 fotos editadas',
    cartDuration: '2 horas',
    summaryPerks: [
        '1 reel con edición profesional',
        '10 fotografías editadas',
        'Sesión de 2 a 3 horas',
        'Captura Sony a7 full frame',
        'Entrega en 5 días hábiles',
    ],
    terms: [
        'La reserva queda sujeta a disponibilidad real del horario elegido y a la confirmación del flujo de pago o modo prueba.',
        'La sesión estándar tiene duración de 2 horas. Tiempo adicional, locaciones extra o cambios de alcance pueden cotizarse aparte.',
        'Incluye 1 reel editado y 10 fotografías editadas. Material bruto, versiones adicionales o entregas urgentes no están incluidos salvo acuerdo escrito.',
        'Puedes solicitar cambios de fecha con mínimo 24 horas de anticipación. Cambios tardíos o inasistencias pueden perder el horario reservado.',
        'Autorizas el uso del material producido para portafolio de lapsique.media salvo que se acuerde confidencialidad antes de la sesión.',
    ],
    paymentCopy: 'Pago seguro con Mercado Pago o Stripe.',
    unavailableWhatsApp: 'Hola, me interesa una sesión de contenido y no veo horarios publicados.',
};

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
    const { ziggy, booking, site, payments } = usePage<PageProps>().props;
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
    const [selectedDate, setSelectedDate] = useState<Date | undefined>();
    const [selectedSlotId, setSelectedSlotId] = useState<number | null>(null);
    const [isBookingModalOpen, setIsBookingModalOpen] = useState(false);
    const [isTrustModalOpen, setIsTrustModalOpen] = useState(false);
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

    const availableDates = useMemo(
        () => Array.from(slotsByDate.keys()).sort().map((d) => parseISO(d)),
        [slotsByDate],
    );

    const suggestedDate = useMemo(() => {
        if (availableDates.length === 0) {
            return undefined;
        }

        const today = startOfDay(new Date());
        return availableDates.find((date) => startOfDay(date) >= today) ?? availableDates[0];
    }, [availableDates]);

    const daySlots = useMemo(() => {
        if (!selectedDate) {
            return [];
        }

        return slotsByDate.get(format(selectedDate, 'yyyy-MM-dd')) ?? [];
    }, [selectedDate, slotsByDate]);

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
        setSelectedDate(undefined);
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

    const openBookingModalFresh = useCallback(
        (source: string) => {
            resetBookingWizard();
            setIsBookingModalOpen(true);
            trackBookingEvent('booking_popup_shown', { source });
        },
        [resetBookingWizard],
    );

    useEffect(() => {
        setActiveFunnelModal(isBookingModalOpen ? 'booking' : null);
    }, [isBookingModalOpen]);

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
            window.location.hash === '#agenda' || consumeBookingModalPending();

        if (!shouldOpen) {
            return;
        }

        handledDeepLinkOpenRef.current = true;
        openBookingModalFresh(
            window.location.hash === '#agenda' ? 'hash_agenda' : 'pending_navigation',
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

    const applyDateSelection = (date: Date) => {
        const key = format(date, 'yyyy-MM-dd');
        const slotsForDay = slotsByDate.get(key) ?? [];
        skipSlotAutoSelectRef.current = false;
        setSelectedDate(date);
        submittedRef.current = false;
        if (slotsForDay.length > 0) {
            applySlotSelection(slotsForDay[0]);
        } else {
            setSelectedSlotId(null);
            setData('booking_slot_id', '');
        }
        trackBookingEvent('booking_date_selected', { date: key });
    };

    const handleInlineDateSelect = (date: Date) => {
        applyDateSelection(date);
        setIsBookingModalOpen(true);
        pendingScrollStepRef.current = 'horario';
    };

    const handleModalDateSelect = (date: Date) => {
        applyDateSelection(date);
        pendingScrollStepRef.current = 'horario';
    };

    const clearSelection = () => {
        submittedRef.current = false;
        skipSlotAutoSelectRef.current = true;
        setSelectedSlotId(null);
        setData('booking_slot_id', '');
        trackBookingEvent('booking_slot_cleared');
        if (isBookingModalOpen && selectedDate) {
            requestAnimationFrame(() => scrollToBookingStep('horario'));
        }
    };

    useEffect(() => {
        if (!selectedDate || daySlots.length === 0) {
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
    }, [selectedDate, daySlots, selectedSlotId]);

    useEffect(() => {
        if (!isBookingModalOpen || !pendingScrollStepRef.current) {
            return;
        }

        const step = pendingScrollStepRef.current;

        if (step === 'horario' && !selectedDate) {
            return;
        }

        if (step === 'datos' && !selectedSlotId) {
            return;
        }

        pendingScrollStepRef.current = null;
        requestAnimationFrame(() => scrollToBookingStep(step));
    }, [isBookingModalOpen, selectedDate, selectedSlotId, daySlots.length, scrollToBookingStep]);

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
                <div className="flex flex-wrap items-center justify-between gap-4 border-b border-border/70 px-5 py-5 md:px-7">
                    <div>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary/80">
                            Agenda ahora
                        </p>
                        <p className="mt-1 max-w-2xl text-base font-medium text-foreground md:text-lg">
                            Elige fecha, toma un horario y deja cerrada tu reserva en este momento.
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="glass"
                        size="lg"
                        onClick={() => setIsTrustModalOpen(true)}
                    >
                        <ShieldCheck className="h-4 w-4" />
                        Reserva protegida
                    </Button>
                </div>

                <div className="grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div
                        ref={calendarRef}
                        className="order-1 space-y-5 p-5 md:p-6"
                    >
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
                                {availableDates.slice(0, 12).map((date) => {
                                    const key = format(date, 'yyyy-MM-dd');
                                    const isSelected = selectedDate
                                        ? format(selectedDate, 'yyyy-MM-dd') === key
                                        : false;
                                    const isSuggested =
                                        !isSelected &&
                                        suggestedDate !== undefined &&
                                        format(suggestedDate, 'yyyy-MM-dd') === key;

                                    return (
                                        <DateOption
                                            key={key}
                                            date={date}
                                            selected={isSelected}
                                            suggested={isSuggested}
                                            onClick={() => handleInlineDateSelect(date)}
                                        />
                                    );
                                })}
                            </div>

                            <div className="rounded-2xl border border-primary/20 bg-primary/10 px-4 py-4">
                                <p className="text-sm font-semibold text-foreground">Wizard de reserva tipo carrito.</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Elige fecha, horario y confirma tus datos en un modal de checkout.
                                </p>
                                <BookingCtaSection className="py-4 pb-0">
                                    <BookingCtaButton
                                        type="button"
                                        onClick={() => openBookingModalFresh('widget_cta')}
                                    >
                                        Seleccionar fecha
                                    </BookingCtaButton>
                                </BookingCtaSection>
                            </div>
                        </div>

                        <div className="grid gap-3 border-t border-border/70 pt-5 sm:grid-cols-3">
                            <TrustChip
                                icon={<Sparkles className="h-4 w-4" />}
                                title="Respuesta boutique"
                                copy="Producción dirigida y atención uno a uno."
                            />
                            <TrustChip
                                icon={<LockKeyhole className="h-4 w-4" />}
                                title="Pago protegido"
                                copy={isTestMode ? 'Flujo de prueba sin cobro real.' : product.paymentCopy}
                            />
                            <TrustChip
                                icon={<Clock3 className="h-4 w-4" />}
                                title="Cierre rápido"
                                copy="Elige fecha, comparte datos y confirma en minutos."
                            />
                        </div>
                    </div>

                    <BookingSummaryPanel
                        price={price}
                        selectedSlot={selectedSlot}
                        isTestMode={isTestMode}
                        product={product}
                    />
                </div>
            </div>

            <PremiumSplitDialog
                open={isBookingModalOpen}
                onOpenChange={setIsBookingModalOpen}
                imageUrl={bookingPopupImage.url}
                imageAlt={bookingPopupImage.alt}
                badge={bookingPopupVisual.badge}
                title={bookingPopupVisual.title}
                description={bookingPopupVisual.description}
                caption={bookingPopupVisual.caption}
                imageOverlay={
                    <div className="mt-5 space-y-2 rounded-2xl border border-white/15 bg-black/35 p-4 backdrop-blur-md">
                        <p className="font-mono-tabular text-2xl font-bold text-white">{formatMxn(price)}</p>
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
                contentClassName="p-5 md:p-7"
            >
                <div className="space-y-5">
                    <div className="lg:hidden">
                        <span className="inline-flex rounded-full border border-primary/25 bg-primary/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            {product.checkoutLabel}
                        </span>
                        <h2 className="mt-3 font-display text-2xl font-bold">Agendar tu sesión</h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Elige fecha y horario, completa tus datos y confirma con pago seguro.
                        </p>
                    </div>

                    <div className="grid w-full grid-cols-3 gap-2">
                                <WizardStep
                                    index="1"
                                    title="Fecha"
                                    active={!selectedDate}
                                    complete={Boolean(selectedDate)}
                                    onClick={() => scrollToBookingStep('fecha')}
                                />
                                <WizardStep
                                    index="2"
                                    title="Horario"
                                    active={Boolean(selectedDate) && !selectedSlot}
                                    complete={Boolean(selectedSlot)}
                                    disabled={!selectedDate}
                                    onClick={() => scrollToBookingStep('horario')}
                                />
                                <WizardStep
                                    index="3"
                                    title="Datos y pago"
                                    active={Boolean(selectedSlot)}
                                    complete={false}
                                    disabled={!selectedSlot}
                                    onClick={() => scrollToBookingStep('datos')}
                                />
                            </div>

                            <div className="space-y-4">
                                <section
                                    ref={dateSectionRef}
                                    id="booking-step-fecha"
                                    className={cn(
                                        'scroll-mt-4 space-y-4 rounded-2xl border bg-muted/40 p-4 transition-shadow',
                                        !selectedDate
                                            ? 'border-primary/50 ring-2 ring-primary'
                                            : 'border-primary/40',
                                    )}
                                >
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                            1. Escoge día
                                        </p>
                                        <h3
                                            data-step-focus
                                            tabIndex={-1}
                                            className="mt-1 text-lg font-bold outline-none"
                                        >
                                            Fechas disponibles
                                        </h3>
                                        <p className="mt-2 text-sm text-muted-foreground">
                                            Elige tu día — puedes cambiar la fecha cuando quieras.
                                        </p>
                                    </div>
                                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                        {availableDates.slice(0, 12).map((date) => {
                                            const key = format(date, 'yyyy-MM-dd');
                                            const isSelected =
                                                selectedDate !== undefined &&
                                                format(selectedDate, 'yyyy-MM-dd') === key;
                                            const isSuggested =
                                                !isSelected &&
                                                suggestedDate !== undefined &&
                                                format(suggestedDate, 'yyyy-MM-dd') === key;

                                            return (
                                                <DateOption
                                                    key={key}
                                                    date={date}
                                                    selected={isSelected}
                                                    suggested={isSuggested}
                                                    onClick={() => handleModalDateSelect(date)}
                                                />
                                            );
                                        })}
                                    </div>
                                </section>

                                {selectedDate && (
                                    <section
                                        ref={timeSectionRef}
                                        id="booking-step-horario"
                                        className={cn(
                                            'scroll-mt-4 space-y-4 rounded-2xl border bg-muted/40 p-4 transition-shadow',
                                            Boolean(selectedDate) && !selectedSlotId
                                                ? 'border-primary/50 ring-2 ring-primary'
                                                : selectedSlotId
                                                  ? 'border-primary/40'
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
                                                            variant={isSelected ? 'cinematic' : 'outline'}
                                                            size="lg"
                                                            aria-pressed={isSelected}
                                                            className={cn(
                                                                'h-12 justify-between rounded-xl px-4 text-base transition-all',
                                                                isSelected
                                                                    ? 'border-primary bg-primary text-primary-foreground shadow-[0_0_24px_oklch(0.78_0.14_75/0.28)] ring-2 ring-primary ring-offset-2 ring-offset-background'
                                                                    : 'border-border/70 bg-secondary/80 text-foreground opacity-90 hover:border-primary/40 hover:bg-muted hover:opacity-100',
                                                            )}
                                                            onClick={() => handleSlotSelect(slot)}
                                                        >
                                                            <span className="flex items-center gap-2 font-semibold">
                                                                {isSelected && (
                                                                    <Check
                                                                        className="h-4 w-4 shrink-0"
                                                                        aria-hidden
                                                                    />
                                                                )}
                                                                {slot.time_label}
                                                            </span>
                                                            <span
                                                                className={cn(
                                                                    'text-xs font-semibold uppercase tracking-[0.12em]',
                                                                    isSelected
                                                                        ? 'text-primary-foreground'
                                                                        : 'text-muted-foreground',
                                                                )}
                                                            >
                                                                {isSelected ? 'Elegido' : 'Disponible'}
                                                            </span>
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
                                                ? 'border-primary/50 ring-2 ring-primary'
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
                                            fixedPaymentProvider={
                                                paymentProvider === 'stripe' ? 'stripe' : undefined
                                            }
                                        />
                                    </section>
                                )}
                            </div>
                </div>
            </PremiumSplitDialog>

            <TermsModal open={isTermsModalOpen} onOpenChange={setIsTermsModalOpen} product={product} />

            <ReservationTrustModal
                open={isTrustModalOpen}
                onOpenChange={setIsTrustModalOpen}
                isTestMode={isTestMode}
                teamName={site.bookingTeamName}
                teamBio={site.bookingTeamBio}
                studioLocation={site.studioLocation}
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
    fixedPaymentProvider,
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
    fixedPaymentProvider?: 'stripe';
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
                            <p className="text-sm font-medium text-primary">
                                {format(parseISO(selectedSlot.date), 'd MMM yyyy', { locale: es })} ·{' '}
                                {selectedSlot.time_label}
                            </p>
                        </div>
                        <Button type="button" variant="ghost" size="sm" onClick={clearSelection}>
                            <X className="h-4 w-4" />
                            Cambiar
                        </Button>
                    </div>

                    {isTestMode && (
                        <div className="flex items-start gap-3 rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm text-primary">
                            <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />
                            <p>
                                Modo prueba activo. Esta reserva no genera cobro real y se confirmara
                                directamente para validacion del flujo.
                            </p>
                        </div>
                    )}

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
                                stripeConfigured={payments?.stripeConfigured ?? true}
                                mercadopagoConfigured={payments?.mercadopagoConfigured ?? true}
                            />
                        )}
                        {!isTestMode && fixedPaymentProvider === 'stripe' && (
                            <div className="rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm">
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
                            isTestMode={isTestMode}
                            product={product}
                        />

                        <div className="rounded-2xl border border-border/70 bg-secondary p-4">
                            <label className="flex items-start gap-3 text-sm leading-relaxed">
                                <Checkbox
                                    checked={data.terms_accepted}
                                    onCheckedChange={(checked) => setData('terms_accepted', checked === true)}
                                    className="mt-0.5"
                                    required
                                />
                                <span className="text-muted-foreground">
                                    Acepto los{' '}
                                    <button
                                        type="button"
                                        className="font-semibold text-primary underline-offset-4 hover:underline"
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

                        <BookingCtaButton
                            type="submit"
                            className="w-full"
                            size="lg"
                            disabled={processing || !data.terms_accepted}
                        >
                            {processing
                                ? 'Procesando...'
                                : isTestMode
                                  ? 'Confirmar reserva de prueba'
                                  : `Pagar ${formatMxn(price)}`}
                        </BookingCtaButton>
                        <p className="text-center text-xs text-muted-foreground">
                            {isTestMode
                                ? 'Se confirmara sin cobro real y podras validar admin, correo y portal.'
                                : product.paymentCopy}
                        </p>
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

function BookingSummaryPanel({
    price,
    selectedSlot,
    isTestMode,
    product,
}: {
    price: number;
    selectedSlot?: BookingSlot;
    isTestMode: boolean;
    product: BookingWidgetProduct;
}) {
    return (
        <div className="space-y-5 border-t border-border/70 bg-muted/30 p-6 md:order-2 md:border-t-0 md:border-l">
            <div>
                <p className="text-xs uppercase tracking-widest text-muted-foreground">lapsique.media</p>
                <h3 className="text-lg font-bold">{product.summaryTitle}</h3>
            </div>
            <ul className="space-y-2 text-sm text-muted-foreground">
                {product.summaryPerks.map((perk) => (
                    <li key={perk}>{perk}</li>
                ))}
            </ul>
            <div className="border-t border-border pt-4">
                <p className="text-xs uppercase tracking-wider text-muted-foreground">Precio</p>
                <p className="font-mono-tabular text-3xl font-bold">{formatMxn(price)}</p>
                <p className="text-xs text-muted-foreground">
                    {isTestMode ? 'Modo prueba sin cobro real' : 'MXN · todo incluido'}
                </p>
            </div>
            {selectedSlot && (
                <div className="rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3">
                    <p className="text-xs uppercase tracking-wider text-muted-foreground">Sesion elegida</p>
                    <p className="text-sm font-medium text-primary">
                        {format(parseISO(selectedSlot.date), 'd MMM yyyy', { locale: es })} ·{' '}
                        {selectedSlot.time_label}
                    </p>
                </div>
            )}
        </div>
    );
}

function TrustChip({
    icon,
    title,
    copy,
}: {
    icon: ReactNode;
    title: string;
    copy: string;
}) {
    return (
        <div className="rounded-2xl border border-border/70 bg-secondary p-3">
            <div className="flex items-center gap-2 text-primary">
                {icon}
                <p className="text-xs font-semibold uppercase tracking-[0.2em]">{title}</p>
            </div>
            <p className="mt-2 text-sm text-muted-foreground">{copy}</p>
        </div>
    );
}

function WizardStep({
    index,
    title,
    active,
    complete,
    disabled = false,
    onClick,
}: {
    index: string;
    title: string;
    active: boolean;
    complete: boolean;
    disabled?: boolean;
    onClick?: () => void;
}) {
    const statusLabel = complete ? 'Listo' : active ? 'En curso' : 'Pendiente';
    const isInteractive = Boolean(onClick) && !disabled;

    return (
        <div
            role={isInteractive ? 'button' : undefined}
            tabIndex={isInteractive ? 0 : undefined}
            onClick={isInteractive ? onClick : undefined}
            onKeyDown={
                isInteractive
                    ? (event) => {
                          if (event.key === 'Enter' || event.key === ' ') {
                              event.preventDefault();
                              onClick?.();
                          }
                      }
                    : undefined
            }
            className={cn(
                'min-w-0 rounded-xl border px-2 py-2 sm:rounded-2xl sm:px-3 sm:py-2.5 md:px-4 md:py-3',
                complete && 'border-primary/45 bg-primary/15',
                active && !complete && 'border-primary ring-2 ring-primary bg-primary/10',
                !active && !complete && 'border-border/70 bg-secondary',
                isInteractive &&
                    'cursor-pointer transition hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                disabled && 'cursor-not-allowed opacity-50',
            )}
        >
            <div className="flex items-center justify-between gap-1">
                <p className="truncate text-[9px] font-semibold uppercase tracking-[0.16em] text-muted-foreground sm:text-[10px] sm:tracking-[0.2em]">
                    {index}
                </p>
                {complete && <Check className="h-3.5 w-3.5 shrink-0 text-primary" aria-hidden />}
            </div>
            <p className="mt-0.5 truncate text-xs font-semibold text-foreground sm:mt-1 sm:text-sm md:text-base">
                {title}
            </p>
            <p
                className={cn(
                    'mt-0.5 truncate text-[10px] sm:text-xs',
                    complete ? 'text-primary' : 'text-muted-foreground',
                )}
                title={statusLabel}
            >
                {statusLabel}
            </p>
        </div>
    );
}

function DateOption({
    date,
    selected,
    suggested = false,
    onClick,
}: {
    date: Date;
    selected: boolean;
    suggested?: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={selected}
            className={cn(
                'flex min-h-[7.5rem] w-full flex-col rounded-2xl border-2 p-3.5 text-left transition duration-200 sm:p-4',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background',
                'active:scale-[0.98]',
                selected
                    ? 'border-primary bg-primary text-primary-foreground shadow-[0_0_40px_oklch(0.78_0.14_75/0.32)] ring-2 ring-primary/50 ring-offset-2 ring-offset-background'
                    : suggested
                      ? 'border-primary/55 border-dashed bg-primary/12 text-foreground hover:border-primary hover:bg-primary/18'
                      : 'border-border/70 bg-secondary hover:border-primary/45 hover:bg-muted',
            )}
        >
            <div className="flex min-h-[1.375rem] items-start justify-between gap-2">
                <span
                    className={cn(
                        'truncate text-[11px] font-semibold uppercase tracking-[0.14em] sm:text-xs sm:tracking-[0.18em]',
                        selected ? 'text-primary-foreground/80' : suggested ? 'text-primary' : 'text-muted-foreground',
                    )}
                >
                    {format(date, 'EEE', { locale: es })}
                </span>
                {selected && (
                    <span
                        aria-label="Fecha seleccionada"
                        className="inline-flex shrink-0 items-center gap-1 rounded-full border border-primary-foreground/30 bg-primary-foreground/15 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.08em] text-primary-foreground sm:px-2 sm:text-[10px]"
                    >
                        <Check className="h-3 w-3" aria-hidden />
                        Elegido
                    </span>
                )}
                {suggested && !selected && (
                    <span className="shrink-0 rounded-full border border-primary/35 bg-primary/15 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.1em] text-primary sm:px-2 sm:text-[10px]">
                        Sugerido
                    </span>
                )}
            </div>
            <span className="mt-3 block font-mono-tabular text-[2rem] font-bold leading-none sm:mt-3.5 sm:text-3xl md:text-4xl">
                {format(date, 'd', { locale: es })}
            </span>
            <span
                className={cn(
                    'mt-auto block pt-2 text-sm capitalize leading-snug',
                    selected ? 'text-primary-foreground/85' : 'text-muted-foreground',
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
    isTestMode,
    product,
}: {
    price: number;
    selectedDate?: Date;
    selectedSlot: BookingSlot;
    isTestMode: boolean;
    product: BookingWidgetProduct;
}) {
    return (
        <div className="space-y-3 border-t border-border/70 pt-5">
            <div>
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                    Resumen de tu reserva
                </p>
                <p className="mt-1 font-display text-lg font-bold">{product.summaryTitle}</p>
                <p className="mt-1 text-xs text-muted-foreground">{product.summaryDescription}</p>
            </div>

            <div className="space-y-2 rounded-2xl border border-border/70 bg-secondary/80 p-4">
                <CartRow label="Servicio" value={product.cartService} />
                <CartRow label="Duración" value={product.cartDuration} />
                <CartRow
                    label="Fecha"
                    value={
                        selectedDate
                            ? format(selectedDate, 'd MMM yyyy', { locale: es })
                            : format(parseISO(selectedSlot.date), 'd MMM yyyy', { locale: es })
                    }
                />
                <CartRow label="Horario" value={selectedSlot.time_label} />
            </div>

            <div className="rounded-2xl border border-primary/20 bg-primary/10 p-4">
                <div className="flex items-center justify-between gap-3">
                    <span className="text-sm font-medium text-muted-foreground">Total</span>
                    <span className="font-mono-tabular text-2xl font-bold text-foreground md:text-3xl">
                        {formatMxn(price)}
                    </span>
                </div>
                <p className="mt-2 text-xs text-muted-foreground">
                    {isTestMode ? 'Modo prueba activo: no se cobra.' : 'Pago seguro al confirmar.'}
                </p>
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
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    product: BookingWidgetProduct;
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="theme-scrollbar max-h-[min(90vh,760px)] max-w-[min(94vw,680px)] overflow-y-auto border-primary/25 p-0">
                <div className="space-y-6 p-6 md:p-8">
                    <div className="space-y-3">
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

function ReservationTrustModal({
    open,
    onOpenChange,
    isTestMode,
    teamName,
    teamBio,
    studioLocation,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    isTestMode: boolean;
    teamName?: string | null;
    teamBio?: string | null;
    studioLocation?: string | null;
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="theme-scrollbar max-h-[min(92vh,860px)] max-w-[min(94vw,680px)] overflow-y-auto border-primary/25 p-0">
                <div className="relative overflow-hidden rounded-[1.6rem]">
                    <div className="absolute inset-x-0 top-0 h-28 bg-[radial-gradient(circle_at_top,var(--hero-radial-glow),transparent_72%)]" />
                    <div className="space-y-6 p-6 md:p-8">
                        <div className="space-y-3">
                            <span className="inline-flex rounded-full border border-primary/25 bg-primary/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                                Reserva con confianza
                            </span>
                            <DialogTitle className="font-display text-2xl md:text-3xl">
                                Todo está pensado para que agendes con claridad y sin fricción
                            </DialogTitle>
                            <DialogDescription className="max-w-xl text-sm leading-relaxed text-muted-foreground md:text-base">
                                Confirmas tu fecha desde el home, tu espacio queda registrado y el
                                siguiente paso queda claro desde el primer minuto.
                            </DialogDescription>
                        </div>

                        <div className="grid gap-3 md:grid-cols-2">
                            <TrustModalCard
                                icon={<ShieldCheck className="h-5 w-5" />}
                                title="Reserva respaldada"
                                copy={isTestMode ? 'Este entorno confirma sin cobro real para validar el flujo completo.' : 'Puedes pagar con Mercado Pago o Stripe dentro de un checkout protegido.'}
                            />
                            <TrustModalCard
                                icon={<Clock3 className="h-5 w-5" />}
                                title="Proceso rápido"
                                copy="Seleccionas horario, dejas tus datos y quedas encaminado sin tener que esperar una cotización manual."
                            />
                            <TrustModalCard
                                icon={<MapPin className="h-5 w-5" />}
                                title="Operación clara"
                                copy={studioLocation || 'La locación y la logística se confirman contigo una vez apartada la sesión.'}
                            />
                            <TrustModalCard
                                icon={<Sparkles className="h-5 w-5" />}
                                title="Dirección premium"
                                copy={teamBio || `La sesión se trabaja con criterio visual, dirección en set y foco comercial${teamName ? ` por ${teamName}` : ''}.`}
                            />
                        </div>

                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-border/70 bg-muted/30 px-4 py-3">
                            <p className="text-sm text-foreground">
                                Si ya viste una fecha disponible, lo ideal es apartarla ahora.
                            </p>
                            <Button type="button" variant="cinematic" onClick={() => onOpenChange(false)}>
                                Volver al calendario
                            </Button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}

function TrustModalCard({
    icon,
    title,
    copy,
}: {
    icon: ReactNode;
    title: string;
    copy: string;
}) {
    return (
        <div className="rounded-[1.4rem] border border-border/70 bg-secondary p-4">
            <div className="flex items-center gap-3 text-primary">
                <span className="flex h-10 w-10 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10">
                    {icon}
                </span>
                <p className="font-semibold text-foreground">{title}</p>
            </div>
            <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{copy}</p>
        </div>
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
