import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface BookingCtaSectionProps {
    children: ReactNode;
    /** Hero block — full width of parent, extra vertical rhythm */
    hero?: boolean;
    className?: string;
    innerClassName?: string;
}

export function BookingCtaSection({
    children,
    hero = false,
    className,
    innerClassName,
}: BookingCtaSectionProps) {
    return (
        <div
            className={cn('booking-cta-section', hero && 'booking-cta-section--hero', className)}
        >
            <div className={cn('booking-cta-section__inner', innerClassName)}>{children}</div>
        </div>
    );
}
