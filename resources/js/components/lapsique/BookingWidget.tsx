import { useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { PaymentMethodField } from '@/components/lapsique/PaymentMethodField';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { route } from '@/lib/route';
import { cn, formatMxn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { BookingSlot, PageProps } from '@/types';
import {
    CalendarRange,
    Clock3,
    CalendarDays,
    LockKeyhole,
    MapPin,
    ShieldCheck,
    ShoppingBag,
    Sparkles,
    X,
} from 'lucide-react';

interface BookingWidgetProps {
    slots: BookingSlot[];
    price: number;
    whatsapp?: string;
    errors?: Record<string, string>;
}

export function BookingWidget({ slots, price, whatsapp, errors = {} }: BookingWidgetProps) {
    const { ziggy, booking, site, payments } = usePage<PageProps>().props;
    const isTestMode = booking.skipPayment;
    const sectionRef = useSectionEvent<HTMLElement>('booking_widget_viewed', { section: 'agenda' });
    const calendarRef = useSectionEvent<HTMLDivElement>('booking_calendar_opened', { section: 'calendar' });
    const submittedRef = useRef(false);
    const formStartedRef = useRef(false);
    const selectedSlotRef = useRef<number | null>(null);
    const isTestModeRef = useRef(isTestMode);
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

    const daySlots = selectedDate
        ? slotsByDate.get(format(selectedDate, 'yyyy-MM-dd')) ?? []
        : [];

    const selectedSlot = slots.find((s) => s.id === selectedSlotId);

    const { data, setData, post, processing, transform } = useForm({
        booking_slot_id: '' as string | number,
        client_name: '',
        client_email: '',
        client_phone: '',
        client_instagram: '',
        notes: '',
        payment_provider: 'mercadopago',
        terms_accepted: false,
    });

    const showForm = selectedSlotId !== null;

    useEffect(() => {
        if (!selectedDate && availableDates.length > 0) {
            setSelectedDate(availableDates[0]);
        }
    }, [availableDates, selectedDate]);

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
        const openBooking = () => {
            if (!selectedDate && availableDates.length > 0) {
                setSelectedDate(availableDates[0]);
            }

            setIsBookingModalOpen(true);
            trackBookingEvent('booking_popup_shown', { source: 'global_cta' });
        };

        window.addEventListener('booking:open', openBooking);

        return () => window.removeEventListener('booking:open', openBooking);
    }, [availableDates, selectedDate]);

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

    const handleSlotSelect = (slot: BookingSlot) => {
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

    const handleDateSelect = (date: Date) => {
        const key = format(date, 'yyyy-MM-dd');
        setSelectedDate(date);
        setSelectedSlotId(null);
        submittedRef.current = false;
        setData('booking_slot_id', '');
        setIsBookingModalOpen(true);
        trackBookingEvent('booking_date_selected', { date: key });
    };

    const clearSelection = () => {
        submittedRef.current = false;
        setSelectedSlotId(null);
        setData('booking_slot_id', '');
        trackBookingEvent('booking_slot_cleared');
    };

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
        post(route('booking.checkout', undefined, false, ziggy));
    };

    if (slots.length === 0) {
        return (
            <section
                id="agenda"
                ref={sectionRef}
                className="scroll-mt-20 space-y-6"
                data-analytics-section="booking_widget"
            >
                <BookingHeader isTestMode={isTestMode} />
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
                        <Button variant="cinematic" asChild>
                            <a
                                href={`https://wa.me/${whatsapp}?text=${encodeURIComponent('Hola, me interesa una sesion de contenido y no veo horarios publicados.')}`}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Coordinar por WhatsApp
                            </a>
                        </Button>
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
            <BookingHeader isTestMode={isTestMode} />

            <div className={cn(glassCardVariants({ elevated: true }), 'glass-border-glow overflow-hidden border')}>
                <div className="flex flex-wrap items-center justify-between gap-4 border-b border-border/70 px-5 py-5 md:px-7">
                    <div>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary/80">
                            Agenda ahora
                        </p>
                        <p className="mt-1 max-w-2xl text-base font-medium text-foreground md:text-lg">
                            Elige fecha, toma un horario y deja cerrada tu sesión en este momento.
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

                                    return (
                                        <DateOption
                                            key={key}
                                            date={date}
                                            selected={isSelected}
                                            onClick={() => handleDateSelect(date)}
                                        />
                                    );
                                })}
                            </div>

                            <div className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3">
                                <div>
                                    <p className="text-sm font-semibold text-foreground">Wizard de reserva tipo carrito.</p>
                                    <p className="text-xs text-muted-foreground">Elige fecha, horario y confirma tus datos en un modal de checkout.</p>
                                </div>
                                <Button type="button" variant="cinematic" onClick={() => setIsBookingModalOpen(true)}>
                                    Abrir checkout
                                </Button>
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
                                copy={isTestMode ? 'Flujo de prueba sin cobro real.' : 'Mercado Pago o Stripe según prefieras.'}
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
                    />
                </div>
            </div>

            <Dialog open={isBookingModalOpen} onOpenChange={setIsBookingModalOpen}>
                <DialogContent className="theme-scrollbar max-h-[min(92vh,900px)] max-w-[min(96vw,1120px)] overflow-y-auto border-primary/25 p-0">
                    <div className="grid overflow-hidden rounded-[1.6rem] lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div className="space-y-5 p-5 md:p-7">
                            <div>
                                <span className="inline-flex rounded-full border border-primary/25 bg-primary/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                                    Checkout de sesión
                                </span>
                                <DialogTitle className="mt-3 font-display text-2xl md:text-3xl">
                                    Agenda como carrito
                                </DialogTitle>
                                <DialogDescription className="mt-2 text-sm text-muted-foreground">
                                    Revisa tu fecha, agrega un horario y completa datos para cerrar la reserva.
                                </DialogDescription>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                <WizardStep index="1" title="Fecha" active complete={Boolean(selectedDate)} />
                                <WizardStep index="2" title="Horario" active={Boolean(selectedDate)} complete={Boolean(selectedSlot)} />
                                <WizardStep index="3" title="Datos y pago" active={Boolean(selectedSlot)} complete={false} />
                            </div>

                            <div className="space-y-3 rounded-2xl border border-border/70 bg-muted/40 p-4">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                            Fecha elegida
                                        </p>
                                        <h3 className="mt-1 text-lg font-bold">
                                            {selectedDate
                                                ? format(selectedDate, "EEEE d 'de' MMMM", { locale: es })
                                                : 'Selecciona un día'}
                                        </h3>
                                    </div>
                                    <Button type="button" variant="ghost" size="sm" onClick={() => setIsBookingModalOpen(false)}>
                                        Cambiar fecha
                                    </Button>
                                </div>

                                {!selectedDate && (
                                    <p className="rounded-xl border border-dashed border-primary/25 bg-primary/8 px-4 py-3 text-sm text-muted-foreground">
                                        Cierra este modal y elige una fecha disponible.
                                    </p>
                                )}

                                {selectedDate && daySlots.length === 0 && (
                                    <p className="rounded-xl border border-dashed border-border/70 px-4 py-3 text-sm text-muted-foreground">
                                        Ese día ya no tiene horarios disponibles. Prueba otra fecha.
                                    </p>
                                )}

                                {selectedDate && daySlots.length > 0 && (
                                    <div className="grid gap-2 sm:grid-cols-2">
                                        {daySlots.map((slot) => (
                                            <Button
                                                key={slot.id}
                                                type="button"
                                                variant={selectedSlotId === slot.id ? 'cinematic' : 'outline'}
                                                size="lg"
                                                className={cn(
                                                    'h-12 justify-between rounded-xl px-4 text-base',
                                                    selectedSlotId !== slot.id && 'border-border bg-secondary hover:bg-muted',
                                                )}
                                                onClick={() => handleSlotSelect(slot)}
                                            >
                                                <span>{slot.time_label}</span>
                                                <span className="text-xs opacity-70">
                                                    {selectedSlotId === slot.id ? 'Elegido' : 'Disponible'}
                                                </span>
                                            </Button>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {showForm && selectedSlot && (
                                <BookingForm
                                    data={data}
                                    errors={errors}
                                    isTestMode={isTestMode}
                                    price={price}
                                    processing={processing}
                                    selectedSlot={selectedSlot}
                                    setData={setData}
                                    clearSelection={clearSelection}
                                    submitCheckout={submitCheckout}
                                    openTerms={() => setIsTermsModalOpen(true)}
                                />
                            )}
                        </div>

                        <CartSummaryPanel
                            price={price}
                            selectedDate={selectedDate}
                            selectedSlot={selectedSlot}
                            isTestMode={isTestMode}
                        />
                    </div>
                </DialogContent>
            </Dialog>

            <TermsModal open={isTermsModalOpen} onOpenChange={setIsTermsModalOpen} />

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
    selectedSlot,
    setData,
    clearSelection,
    submitCheckout,
    openTerms,
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
    selectedSlot: BookingSlot;
    setData: (key: keyof typeof data, value: string | number | boolean) => void;
    clearSelection: () => void;
    submitCheckout: (e: React.FormEvent) => void;
    openTerms: () => void;
}) {
    return (
        <div className={cn(glassCardVariants({ elevated: true }), 'space-y-6 border p-5 md:p-6')}>
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                {isTestMode ? 'Paso 2 - datos y confirmacion' : 'Paso 2 - datos y pago'}
                            </p>
                            <h3 className="text-lg font-bold">
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

                        {!isTestMode && (
                            <PaymentMethodField
                                value={data.payment_provider}
                                onChange={(v) => setData('payment_provider', v)}
                                stripeConfigured={payments?.stripeConfigured ?? true}
                                mercadopagoConfigured={payments?.mercadopagoConfigured ?? true}
                            />
                        )}

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
                                    de la sesión, incluyendo política de cambios, entregables y uso de material.
                                </span>
                            </label>
                            {errors.terms_accepted && (
                                <p className="mt-2 text-xs text-destructive">{errors.terms_accepted}</p>
                            )}
                        </div>

                        <div className="flex items-center justify-between border-t border-border pt-5">
                            <div className="text-sm">
                                <p className="font-medium">Sesion de contenido premium</p>
                                <p className="text-xs text-muted-foreground">
                                    2 reels + 20 fotos + direccion + entrega editada
                                </p>
                            </div>
                            <p className="font-mono-tabular text-xl font-bold">{formatMxn(price)}</p>
                        </div>

                        <Button
                            type="submit"
                            variant="cinematic"
                            className="w-full"
                            size="lg"
                            disabled={processing || !data.terms_accepted}
                        >
                            {processing
                                ? 'Procesando...'
                                : isTestMode
                                  ? 'Confirmar reserva de prueba'
                                  : `Pagar ${formatMxn(price)}`}
                        </Button>
                        <p className="text-center text-xs text-muted-foreground">
                            {isTestMode
                                ? 'Se confirmara sin cobro real y podras validar admin, correo y portal.'
                                : 'Pago seguro con Mercado Pago o Stripe.'}
                        </p>
                    </form>
                </div>
    );
}

function BookingHeader({ isTestMode }: { isTestMode: boolean }) {
    return (
        <div className="space-y-3 text-center">
            <span className="inline-block rounded-full border border-primary/30 bg-primary/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-primary">
                Cierra tu cita
            </span>
            <h2 className="font-display text-3xl font-bold md:text-5xl">
                Agenda tu sesión ahora
            </h2>
            <p className="mx-auto max-w-2xl text-sm text-muted-foreground md:text-base">
                Elige un horario real, deja tus datos y aparta producción con <strong className="text-foreground">Sony a7</strong>{' '}
                {isTestMode ? '· confirmacion sin cobro real en este entorno.' : '· pago seguro al confirmar.'}
            </p>
        </div>
    );
}

function BookingSummaryPanel({
    price,
    selectedSlot,
    isTestMode,
}: {
    price: number;
    selectedSlot?: BookingSlot;
    isTestMode: boolean;
}) {
    const perks = [
        '2 reels con edicion profesional',
        '20 fotografias editadas',
        'Sesion de 2 a 3 horas',
        'Captura Sony a7 full frame',
        'Entrega en 5 dias habiles',
    ];

    return (
        <div className="space-y-5 border-t border-border/70 bg-muted/30 p-6 md:order-2 md:border-t-0 md:border-l">
            <div>
                <p className="text-xs uppercase tracking-widest text-muted-foreground">lapsique.media</p>
                <h3 className="text-lg font-bold">Sesion de contenido</h3>
            </div>
            <ul className="space-y-2 text-sm text-muted-foreground">
                {perks.map((perk) => (
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
}: {
    index: string;
    title: string;
    active: boolean;
    complete: boolean;
}) {
    return (
        <div
            className={cn(
                'rounded-2xl border px-4 py-3',
                active ? 'border-primary/30 bg-primary/10' : 'border-border/70 bg-secondary',
            )}
        >
            <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                Paso {index}
            </p>
            <p className="mt-1 font-semibold text-foreground">{title}</p>
            <p className={cn('text-xs', complete ? 'text-primary' : 'text-muted-foreground')}>
                {complete ? 'Listo' : active ? 'En curso' : 'Pendiente'}
            </p>
        </div>
    );
}

function DateOption({
    date,
    selected,
    onClick,
}: {
    date: Date;
    selected: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'h-28 w-full rounded-2xl border p-3 text-left transition duration-200',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background',
                selected
                    ? 'border-primary bg-primary text-primary-foreground shadow-[0_0_40px_oklch(0.78_0.14_75/0.32)]'
                    : 'border-border/70 bg-secondary hover:border-primary/45 hover:bg-muted',
            )}
        >
            <span className={cn('text-xs font-semibold uppercase tracking-[0.2em]', selected ? 'text-primary-foreground/75' : 'text-primary')}>
                {format(date, 'EEE', { locale: es })}
            </span>
            <span className="mt-2 block font-mono-tabular text-3xl font-bold leading-none">
                {format(date, 'd', { locale: es })}
            </span>
            <span className={cn('mt-1 block text-sm capitalize', selected ? 'text-primary-foreground/80' : 'text-muted-foreground')}>
                {format(date, 'MMMM', { locale: es })}
            </span>
        </button>
    );
}

function CartSummaryPanel({
    price,
    selectedDate,
    selectedSlot,
    isTestMode,
}: {
    price: number;
    selectedDate?: Date;
    selectedSlot?: BookingSlot;
    isTestMode: boolean;
}) {
    return (
        <aside className="space-y-5 border-t border-border/70 bg-muted/40 p-6 lg:border-t-0 lg:border-l">
            <div className="flex items-center gap-3">
                <span className="flex h-11 w-11 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary">
                    <ShoppingBag className="h-5 w-5" />
                </span>
                <div>
                    <p className="text-xs uppercase tracking-widest text-muted-foreground">Tu carrito</p>
                    <h3 className="font-display text-xl font-bold">Sesión premium</h3>
                </div>
            </div>

            <div className="space-y-3 rounded-2xl border border-border/70 bg-secondary p-4">
                <CartRow label="Servicio" value="2 reels + 20 fotos" />
                <CartRow label="Duración" value="2 horas" />
                <CartRow
                    label="Fecha"
                    value={selectedDate ? format(selectedDate, 'd MMM yyyy', { locale: es }) : 'Por elegir'}
                />
                <CartRow label="Horario" value={selectedSlot?.time_label || 'Por elegir'} />
            </div>

            <div className="rounded-2xl border border-primary/20 bg-primary/10 p-4">
                <div className="flex items-center justify-between gap-3">
                    <span className="text-sm text-muted-foreground">Total</span>
                    <span className="font-mono-tabular text-3xl font-bold text-foreground">{formatMxn(price)}</span>
                </div>
                <p className="mt-2 text-xs text-muted-foreground">
                    {isTestMode ? 'Modo prueba activo: no se cobra.' : 'Pago seguro al confirmar.'}
                </p>
            </div>

            <div className="space-y-2 text-xs text-muted-foreground">
                <p>Incluye dirección en set, captura con Sony a7, edición y entrega digital.</p>
                <p>Meta Pixel recibe ViewContent, Lead, Schedule, AddToCart, InitiateCheckout y Purchase.</p>
            </div>
        </aside>
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
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const terms = [
        'La reserva queda sujeta a disponibilidad real del horario elegido y a la confirmación del flujo de pago o modo prueba.',
        'La sesión estándar tiene duración de 2 horas. Tiempo adicional, locaciones extra o cambios de alcance pueden cotizarse aparte.',
        'Incluye 2 reels editados y 20 fotografías editadas. Material bruto, versiones adicionales o entregas urgentes no están incluidos salvo acuerdo escrito.',
        'Puedes solicitar cambios de fecha con mínimo 24 horas de anticipación. Cambios tardíos o inasistencias pueden perder el horario reservado.',
        'Autorizas el uso del material producido para portafolio de lapsique.media salvo que se acuerde confidencialidad antes de la sesión.',
    ];

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
                        {terms.map((term, index) => (
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
