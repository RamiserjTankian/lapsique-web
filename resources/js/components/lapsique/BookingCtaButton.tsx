import { type MouseEvent } from 'react';
import { Button, type ButtonProps } from '@/components/ui/button';
import { openBookingModal, type OpenBookingModalOptions } from '@/lib/openBookingModal';
import { cn } from '@/lib/utils';

export type BookingCtaButtonProps = ButtonProps & {
    /** Hero primary CTA — full width, largest glow on lg */
    hero?: boolean;
    /** Sticky bar / compact toolbar */
    compact?: boolean;
    /** Dispatch booking:open instead of relying on scroll-only handlers */
    opensBookingModal?: boolean;
    /** Passed to openBookingModal analytics */
    bookingSource?: string;
    bookingAnalytics?: OpenBookingModalOptions;
};

export function BookingCtaButton({
    hero = false,
    compact = false,
    opensBookingModal = false,
    bookingSource,
    bookingAnalytics,
    className,
    size,
    variant = 'cinematic',
    onClick,
    ...props
}: BookingCtaButtonProps) {
    const handleClick = (event: MouseEvent<HTMLButtonElement>) => {
        if (opensBookingModal) {
            openBookingModal({
                source: bookingSource,
                ...bookingAnalytics,
            });
        }

        onClick?.(event);
    };

    return (
        <Button
            variant={variant}
            size={size ?? (hero ? 'xl' : compact ? 'lg' : 'xl')}
            className={cn(
                'booking-cta',
                hero && 'booking-cta--hero',
                compact && 'booking-cta--compact',
                className,
            )}
            onClick={opensBookingModal || onClick ? handleClick : onClick}
            {...props}
        />
    );
}
