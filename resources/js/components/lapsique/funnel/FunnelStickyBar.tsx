import { usePage } from '@inertiajs/react';
import { useEffect, useState, type MouseEvent } from 'react';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { openBookingModal } from '@/lib/openBookingModal';
import { formatMxn } from '@/lib/utils';
import type { PageProps } from '@/types';

export function FunnelStickyBar({
    price,
    label = 'Sesión premium',
    deferUntilScrolled = false,
}: {
    price: number;
    label?: string;
    deferUntilScrolled?: boolean;
}) {
    const { booking } = usePage<PageProps>().props;
    const [isVisible, setIsVisible] = useState(!deferUntilScrolled);

    useEffect(() => {
        if (!deferUntilScrolled) {
            setIsVisible(true);

            return;
        }

        const syncVisibility = () => {
            setIsVisible(window.scrollY > 360);
        };

        syncVisibility();
        window.addEventListener('scroll', syncVisibility, { passive: true });

        return () => window.removeEventListener('scroll', syncVisibility);
    }, [deferUntilScrolled]);

    const openBookingPopup = (event: MouseEvent<HTMLAnchorElement>) => {
        event.preventDefault();
        openBookingModal({
            source: 'sticky_bar',
            analyticsEvent: 'sticky_cta_clicked',
        });
    };

    if (!isVisible) {
        return null;
    }

    return (
        <div className="pointer-events-none fixed inset-x-0 bottom-4 z-40 px-4">
            <div className="pointer-events-auto mx-auto flex max-w-4xl items-center justify-between gap-4 rounded-2xl border border-primary/25 bg-[var(--sticky-bar-bg)] px-4 py-3 shadow-[0_24px_80px_var(--sticky-bar-shadow)] backdrop-blur-xl">
                <div className="min-w-0">
                    <p className="text-[10px] uppercase tracking-[0.24em] text-primary/80">
                        {label}
                    </p>
                    <p className="font-mono-tabular text-xl font-bold leading-none tracking-tight text-primary motion-safe:animate-sticky-price-glow motion-reduce:animate-none md:text-2xl">
                        {formatMxn(price)}
                    </p>
                    <p className="hidden truncate text-xs text-muted-foreground sm:block">
                        {booking.skipPayment ? 'Modo prueba activo' : 'Reserva directa con checkout seguro'}
                    </p>
                </div>
                <BookingCtaButton asChild compact className="shrink-0">
                    <a href="#agenda" onClick={openBookingPopup}>
                        Agendar
                    </a>
                </BookingCtaButton>
            </div>
        </div>
    );
}
