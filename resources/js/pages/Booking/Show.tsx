import { Head, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import SiteLayout from '@/layouts/SiteLayout';
import { CinematicHero } from '@/components/lapsique/CinematicHero';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import type { BookingSlot, PageProps } from '@/types';

interface BookingShowProps {
    title: string;
    subtitle: string;
    price: number;
    slots: BookingSlot[];
    errors?: Record<string, string>;
}

export default function BookingShow({
    title,
    subtitle,
    price,
    slots,
    errors,
}: BookingShowProps) {
    const { site } = usePage<PageProps>().props;
    const { t } = useTranslations();

    useEffect(() => {
        trackBookingEvent('booking_page_viewed', {
            section: 'booking_show',
            content_name: title,
            content_category: 'content_booking',
            value: price,
            currency: 'MXN',
        });
    }, [title, price]);

    return (
        <SiteLayout>
            <Head title={t('booking.show.head_title')} />
            <CinematicHero title={title} subtitle={subtitle} price={price} />
            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
            />
        </SiteLayout>
    );
}
