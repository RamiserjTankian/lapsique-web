import { Head, router, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { glassCardVariants } from '@/lib/variants';
import { cn, formatMxn } from '@/lib/utils';
import { route } from '@/lib/route';
import type { ContentBookingData, PageProps } from '@/types';
import { XCircle } from 'lucide-react';
import { useEffect } from 'react';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';

interface BookingFailureProps {
    booking: ContentBookingData;
}

export default function BookingFailure({ booking }: BookingFailureProps) {
    const { ziggy } = usePage<PageProps>().props;

    useEffect(() => {
        trackBookingEvent('booking_payment_failed', {
            booking_id: booking.public_id,
            payment_provider: booking.payment_provider,
            value: booking.amount,
            currency: booking.currency,
        });
    }, [booking.amount, booking.currency, booking.payment_provider, booking.public_id]);

    return (
        <SiteLayout>
            <Head title="Pago no completado" />
            <div className={cn(glassCardVariants({ elevated: true }), 'mx-auto mt-16 max-w-lg p-8')}>
                <XCircle className="mx-auto h-12 w-12 text-destructive" />
                <h1 className="font-display mt-6 text-center text-2xl font-bold">
                    Pago no completado
                </h1>
                <Alert className="mt-6">
                    <AlertTitle>Tu horario fue liberado</AlertTitle>
                    <AlertDescription>
                        El pago no se procesó. Puedes elegir otro horario o reintentar.
                    </AlertDescription>
                </Alert>
                <p className="mt-4 text-center font-mono-tabular">{formatMxn(booking.amount)}</p>
                <div className="mt-8 flex flex-col gap-3">
                    <Button
                        variant="cinematic"
                        onClick={() =>
                            router.post(
                                route('booking.retry', { publicId: booking.public_id }, false, ziggy),
                            )
                        }
                    >
                        Reintentar pago
                    </Button>
                    <Button variant="glass" asChild>
                        <a href={(booking.service_type === 'dj_set'
                            ? route('djset.show', undefined, false, ziggy)
                            : route('home', undefined, false, ziggy)) + '#agenda'}>
                            Elegir otro horario
                        </a>
                    </Button>
                </div>
            </div>
        </SiteLayout>
    );
}
