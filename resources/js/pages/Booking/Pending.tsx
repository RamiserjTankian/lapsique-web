import { Head, router, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { glassCardVariants } from '@/lib/variants';
import { cn, formatMxn } from '@/lib/utils';
import { route } from '@/lib/route';
import type { ContentBookingData, PageProps } from '@/types';
import { Clock } from 'lucide-react';
import { useEffect } from 'react';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';

interface BookingPendingProps {
    booking: ContentBookingData;
    errors?: Record<string, string>;
}

export default function BookingPending({ booking, errors }: BookingPendingProps) {
    const { ziggy } = usePage<PageProps>().props;

    useEffect(() => {
        trackBookingEvent('booking_payment_pending', {
            booking_id: booking.public_id,
            payment_provider: booking.payment_provider,
            value: booking.amount,
            currency: booking.currency,
        });
    }, [booking.amount, booking.currency, booking.payment_provider, booking.public_id]);

    const retry = () => {
        router.post(route('booking.retry', { publicId: booking.public_id }, false, ziggy));
    };

    return (
        <SiteLayout>
            <Head title="Pago pendiente" />
            <motionPendingCard booking={booking} errors={errors} onRetry={retry} />
        </SiteLayout>
    );
}

function motionPendingCard({
    booking,
    errors,
    onRetry,
}: {
    booking: ContentBookingData;
    errors?: Record<string, string>;
    onRetry: () => void;
}) {
    return (
        <div className={cn(glassCardVariants({ elevated: true }), 'mx-auto mt-16 max-w-lg p-8')}>
            <Clock className="mx-auto h-12 w-12 text-primary" />
            <h1 className="font-display mt-6 text-center text-2xl font-bold">Pago pendiente</h1>
            <p className="mt-3 text-center text-muted-foreground">
                Tu reserva está guardada. Completa el pago para confirmar tu sesión con Sony α7.
                {booking.payment_provider === 'stripe' && (
                    <span className="mt-2 block text-xs">
                        Si ya pagaste con tarjeta, Stripe puede tardar unos segundos en confirmar.
                    </span>
                )}
            </p>
            <p className="mt-4 text-center font-mono-tabular text-lg">
                {formatMxn(booking.amount)}
            </p>

            {errors?.payment && (
                <Alert variant="destructive" className="mt-6">
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>{errors.payment}</AlertDescription>
                </Alert>
            )}

            <Button variant="cinematic" className="mt-8 w-full" onClick={onRetry}>
                Reintentar pago
            </Button>
        </div>
    );
}
