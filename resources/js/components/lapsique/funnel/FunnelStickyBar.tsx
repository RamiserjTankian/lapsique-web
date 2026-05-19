import { usePage } from '@inertiajs/react';
import type { MouseEvent } from 'react';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { formatMxn } from '@/lib/utils';
import type { PageProps } from '@/types';

export function FunnelStickyBar({ price }: { price: number }) {
    const { booking } = usePage<PageProps>().props;
    const openBookingPopup = (event: MouseEvent<HTMLAnchorElement>) => {
        event.preventDefault();
        trackBookingEvent('sticky_cta_clicked', { target: 'booking_popup' });
        window.dispatchEvent(new CustomEvent('booking:open'));
    };

    return (
        <div className="pointer-events-none fixed inset-x-0 bottom-4 z-40 px-4">
            <div className="pointer-events-auto mx-auto flex max-w-4xl items-center justify-between gap-4 rounded-2xl border border-primary/25 bg-[var(--sticky-bar-bg)] px-4 py-3 shadow-[0_24px_80px_var(--sticky-bar-shadow)] backdrop-blur-xl">
                <div className="min-w-0">
                    <p className="text-[10px] uppercase tracking-[0.24em] text-primary/80">
                        Sesión premium
                    </p>
                    <p className="font-mono-tabular text-sm font-semibold text-foreground md:text-base">
                        {formatMxn(price)}
                    </p>
                    <p className="truncate text-xs text-muted-foreground">
                        {booking.skipPayment ? 'Modo prueba activo' : 'Reserva directa con checkout seguro'}
                    </p>
                </div>
                <Button
                    asChild
                    variant="cinematic"
                    size="lg"
                    className="h-12 shrink-0 rounded-xl px-6 font-bold"
                >
                    <a href="#agenda" onClick={openBookingPopup}>Agendar</a>
                </Button>
            </div>
        </div>
    );
}
