import { Head, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { glassCardVariants } from '@/lib/variants';
import { cn, formatMxn } from '@/lib/utils';
import { CheckCircle2 } from 'lucide-react';
import { useEffect } from 'react';
import { bookingMetaEventId, trackBookingEvent } from '@/hooks/useBookingAnalytics';
import type { ContentBookingData, PageProps } from '@/types';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';

interface BookingConfirmProps {
    booking: ContentBookingData;
    paymentVerified: boolean;
    isTestBooking?: boolean;
}

export default function BookingConfirm({ booking, paymentVerified, isTestBooking = false }: BookingConfirmProps) {
    const { flash } = usePage<PageProps>().props;
    const slotLabel = booking.slot
        ? `${format(parseISO(booking.slot.date), 'd MMM yyyy', { locale: es })} · ${booking.slot.time_label}`
        : null;

    useEffect(() => {
        const purchaseEventId = bookingMetaEventId('purchase', booking.public_id);

        const purchasePayload = {
            booking_id: booking.public_id,
            event_id: purchaseEventId,
            value: booking.amount,
            amount: booking.amount,
            currency: booking.currency,
            content_name: booking.service_name,
            content_category: 'content_booking',
            customer_id: booking.customer_id ?? undefined,
            customer_name: booking.client_name,
            customer_email: booking.client_email,
            customer_phone: booking.client_phone,
        };

        if (isTestBooking) {
            trackBookingEvent('booking_test_confirmed', purchasePayload);
            return;
        }

        if (paymentVerified) {
            trackBookingEvent('booking_confirmed', {
                ...purchasePayload,
                payment_provider: booking.payment_provider,
            });
        }
    }, [
        booking.amount,
        booking.client_email,
        booking.client_name,
        booking.client_phone,
        booking.currency,
        booking.customer_id,
        booking.payment_provider,
        booking.public_id,
        booking.service_name,
        isTestBooking,
        paymentVerified,
    ]);

    return (
        <SiteLayout>
            <Head title={paymentVerified ? 'Reserva confirmada' : 'Pago en revisión'} />
            <div className={cn(glassCardVariants({ elevated: true }), 'mx-auto mt-16 max-w-lg p-8 text-center')}>
                <CheckCircle2
                    className={cn(
                        'mx-auto h-16 w-16',
                        paymentVerified ? 'text-primary' : 'text-muted-foreground',
                    )}
                />
                <h1 className="font-display mt-6 text-2xl font-bold">
                    {isTestBooking
                      ? 'Reserva de prueba confirmada'
                      : paymentVerified
                          ? `${booking.service_short_name} confirmado`
                          : 'Pago en proceso'}
                </h1>
                <p className="mt-3 text-muted-foreground">
                    {isTestBooking
                        ? 'La sesión quedó apartada en modo prueba, sin cobro real. Revisa tu correo o logs según tu configuración.'
                        : paymentVerified
                          ? `Tu ${booking.service_short_name.toLowerCase()} está agendado. Revisa tu correo con la confirmación y los detalles.`
                          : booking.payment_provider === 'stripe'
                            ? 'Stripe está procesando tu pago. Recibirás un correo en cuanto se confirme.'
                            : 'Estamos verificando tu pago. Te notificaremos en cuanto se confirme.'}
                </p>
                {flash.success && (
                    <p className="mt-4 rounded-xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm text-primary">
                        {flash.success}
                    </p>
                )}
                {slotLabel && (
                    <p className="mt-4 font-mono text-sm text-primary">{slotLabel}</p>
                )}
                <p className="mt-2 font-mono-tabular text-lg">{formatMxn(booking.amount)}</p>
                <p className="mt-1 text-sm text-muted-foreground">{booking.service_name}</p>
                <p className="mt-6 text-sm text-muted-foreground">
                    {booking.client_name} · {booking.client_email}
                </p>
            </div>
        </SiteLayout>
    );
}
