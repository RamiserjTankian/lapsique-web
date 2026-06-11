import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { markBookingIntent } from '@/lib/funnelModalEvents';

export const BOOKING_OPEN_EVENT = 'booking:open';
export const BOOKING_OPEN_PENDING_KEY = 'booking:open';

export type OpenBookingModalOptions = {
    /** Analytics label for the CTA source (hero, header, sticky, etc.) */
    source?: string;
    /** Custom analytics event name; defaults to booking_cta_clicked */
    analyticsEvent?: string;
    /** Extra analytics payload merged into the event */
    analyticsPayload?: Record<string, unknown>;
    /** Skip analytics when the caller already tracked a dedicated event */
    skipAnalytics?: boolean;
};

/**
 * Opens the booking checkout modal when `#agenda` / BookingWidget is on the page.
 * Use `markBookingModalPending()` before navigating to home#agenda from other routes.
 */
export function openBookingModal({
    source = 'cta',
    analyticsEvent = 'booking_cta_clicked',
    analyticsPayload,
    skipAnalytics = false,
}: OpenBookingModalOptions = {}): void {
    markBookingIntent();

    if (!skipAnalytics) {
        trackBookingEvent(analyticsEvent, {
            target: 'booking_popup',
            source,
            ...analyticsPayload,
        });
    }

    const agenda = document.getElementById('agenda');

    if (agenda) {
        window.dispatchEvent(
            new CustomEvent(BOOKING_OPEN_EVENT, { detail: { source } }),
        );
    }
}

/** Call before navigating to home#agenda from a page without BookingWidget. */
export function markBookingModalPending(): void {
    try {
        sessionStorage.setItem(BOOKING_OPEN_PENDING_KEY, '1');
    } catch {
        // Private browsing / blocked storage
    }
}

export function consumeBookingModalPending(): boolean {
    try {
        if (sessionStorage.getItem(BOOKING_OPEN_PENDING_KEY) !== '1') {
            return false;
        }

        sessionStorage.removeItem(BOOKING_OPEN_PENDING_KEY);

        return true;
    } catch {
        return false;
    }
}
