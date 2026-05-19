import { Head, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { CinematicHero } from '@/components/lapsique/CinematicHero';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
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

    return (
        <SiteLayout>
            <Head title="Sesión de contenido" />
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
