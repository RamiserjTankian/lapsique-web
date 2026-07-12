import { useEffect, useRef } from 'react';
import {
    BOOKING_AUTO_OPEN_EVENT,
    canOpenAutomatedFunnelPopup,
    getActiveFunnelModal,
    markBookingAutoShown,
    requestBookingAutoOpen,
} from '@/lib/funnelModalEvents';
import { attachScrollDepthTrigger } from '@/lib/funnelScrollTrigger';

const BOOKING_AUTO_DELAY_MS = 25_000;
const BOOKING_SCROLL_THRESHOLD_PERCENT = 58;
const BOOKING_MIN_TIME_ON_PAGE_MS = 18_000;

export function useBookingPopupTrigger(enabled: boolean): void {
    const triggeredRef = useRef(false);
    const mountTimeRef = useRef(Date.now());

    useEffect(() => {
        if (!enabled) {
            return;
        }

        const tryOpen = (source: string) => {
            if (triggeredRef.current) {
                return;
            }

            if (getActiveFunnelModal() !== null) {
                return;
            }

            if (
                !canOpenAutomatedFunnelPopup({
                    bookingIntentMs: 90_000,
                    modalQuietMs: 12_000,
                })
            ) {
                return;
            }

            if (!markBookingAutoShown()) {
                triggeredRef.current = true;

                return;
            }

            triggeredRef.current = true;
            requestBookingAutoOpen(source);
        };

        const detachScroll = attachScrollDepthTrigger(() => tryOpen('scroll_depth_58'), {
            mountTime: mountTimeRef.current,
            thresholdPercent: BOOKING_SCROLL_THRESHOLD_PERCENT,
            minTimeOnPageMs: BOOKING_MIN_TIME_ON_PAGE_MS,
        });

        const timer = window.setTimeout(() => tryOpen('engaged_timer_25s'), BOOKING_AUTO_DELAY_MS);

        return () => {
            window.clearTimeout(timer);
            detachScroll();
        };
    }, [enabled]);
}

export function useBookingAutoOpenListener(onOpen: (source: string) => void): void {
    useEffect(() => {
        const handler = (event: Event) => onOpen(
            (event as CustomEvent<{ source?: string }>).detail?.source ?? 'auto_trigger',
        );

        window.addEventListener(BOOKING_AUTO_OPEN_EVENT, handler);

        return () => window.removeEventListener(BOOKING_AUTO_OPEN_EVENT, handler);
    }, [onOpen]);
}
