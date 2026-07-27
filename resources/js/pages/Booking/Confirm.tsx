import { Head, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { glassCardVariants } from '@/lib/variants';
import { cn, formatMxn } from '@/lib/utils';
import { CheckCircle2 } from 'lucide-react';
import { useEffect } from 'react';
import { bookingMetaEventId, trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { getDateFnsLocale } from '@/lib/dateLocale';
import type { ContentBookingData, PageProps } from '@/types';
import { format, parseISO } from 'date-fns';

interface BookingConfirmProps {
    booking: ContentBookingData;
    paymentVerified: boolean;
    isTestBooking?: boolean;
}

export default function BookingConfirm({ booking, paymentVerified, isTestBooking = false }: BookingConfirmProps) {
    const { flash } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const dateLocale = getDateFnsLocale(locale);
    const slotLabel = booking.slot
        ? `${format(parseISO(booking.slot.date), 'd MMM yyyy', { locale: dateLocale })} · ${booking.slot.time_label}`
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
            content_category: bookingContentCategory(booking.service_type),
            service_type: booking.service_type,
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
            const purchaseStorageKey = `lapsique_booking_purchase_tracked_${booking.public_id}`;

            if (hasStoredEvent(purchaseStorageKey)) {
                return;
            }

            trackBookingEvent('booking_confirmed', {
                ...purchasePayload,
                payment_provider: booking.payment_provider,
            });
            storeEvent(purchaseStorageKey);
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

    const title = isTestBooking
        ? t('booking.confirm.test_title')
        : paymentVerified
          ? t('booking.confirm.title_service', { service: booking.service_short_name })
          : t('booking.confirm.title_processing');

    const body = isTestBooking
        ? t('booking.confirm.test_body')
        : paymentVerified
          ? t('booking.confirm.verified_body', { service: booking.service_short_name.toLowerCase() })
          : booking.payment_provider === 'stripe'
            ? t('booking.confirm.stripe_pending')
            : t('booking.confirm.generic_pending');

    return (
        <SiteLayout>
            <Head title={paymentVerified ? t('booking.confirm.title_confirmed') : t('booking.confirm.title_pending')} />
            <div className={cn(glassCardVariants({ elevated: true }), 'mx-auto mt-16 max-w-lg p-8 text-center')}>
                <CheckCircle2
                    className={cn(
                        'mx-auto h-16 w-16',
                        paymentVerified ? 'text-primary' : 'text-muted-foreground',
                    )}
                />
                <h1 className="font-display mt-6 text-2xl font-bold">{title}</h1>
                <p className="mt-3 text-muted-foreground">{body}</p>
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

function bookingContentCategory(serviceType: ContentBookingData['service_type']): string {
    if (serviceType === 'dj_set') {
        return 'dj_set_booking';
    }

    if (serviceType === 'drone_session') {
        return 'drone_session_booking';
    }

    if (serviceType === 'construction_progress') {
        return 'construction_progress_booking';
    }

    if (serviceType === 'electronic_event_coverage') {
        return 'electronic_event_coverage_booking';
    }

    if (serviceType === 'multi_camera') {
        return 'multi_camera_booking';
    }

    return 'content_booking';
}

function hasStoredEvent(key: string): boolean {
    try {
        return window.localStorage.getItem(key) === '1';
    } catch {
        return false;
    }
}

function storeEvent(key: string): void {
    try {
        window.localStorage.setItem(key, '1');
    } catch {
        // Browser storage can be unavailable in private modes.
    }
}
