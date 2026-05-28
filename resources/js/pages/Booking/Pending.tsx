import { Head, router, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { glassCardVariants } from '@/lib/variants';
import { cn, formatMxn } from '@/lib/utils';
import { route } from '@/lib/route';
import { useTranslations } from '@/hooks/useTranslations';
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
    const { t } = useTranslations();

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
            <Head title={t('booking.pending.title')} />
            <div className={cn(glassCardVariants({ elevated: true }), 'mx-auto mt-16 max-w-lg p-8')}>
                <Clock className="mx-auto h-12 w-12 text-primary" />
                <h1 className="font-display mt-6 text-center text-2xl font-bold">{t('booking.pending.title')}</h1>
                <p className="mt-3 text-center text-muted-foreground">
                    {t('booking.pending.body', { service: booking.service_short_name.toLowerCase() })}
                    {booking.payment_provider === 'stripe' && (
                        <span className="mt-2 block text-xs">
                            {t('booking.pending.stripe_note')}
                        </span>
                    )}
                </p>
                <p className="mt-4 text-center font-mono-tabular text-lg">
                    {formatMxn(booking.amount)}
                </p>

                {errors?.payment && (
                    <Alert variant="destructive" className="mt-6">
                        <AlertTitle>{t('common.error.title')}</AlertTitle>
                        <AlertDescription>{errors.payment}</AlertDescription>
                    </Alert>
                )}

                <Button variant="cinematic" className="mt-8 w-full" onClick={retry}>
                    {t('booking.failure.retry')}
                </Button>
            </div>
        </SiteLayout>
    );
}
