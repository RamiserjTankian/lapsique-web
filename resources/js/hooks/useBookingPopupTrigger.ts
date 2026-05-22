import { useEffect, useRef } from 'react';
import {
    BOOKING_AUTO_OPEN_EVENT,
    getActiveFunnelModal,
    markBookingAutoShown,
    requestBookingAutoOpen,
} from '@/lib/funnelModalEvents';

const BOOKING_AUTO_DELAY_MS = 14_000;
const BOOKING_SCROLL_THRESHOLD = 35;

export function useBookingPopupTrigger(enabled: boolean): void {
    const triggeredRef = useRef(false);

    useEffect(() => {
        if (!enabled) {
            return;
        }

        const tryOpen = () => {
            if (triggeredRef.current) {
                return;
            }

            if (getActiveFunnelModal() !== null) {
                return;
            }

            if (!markBookingAutoShown()) {
                triggeredRef.current = true;

                return;
            }

            triggeredRef.current = true;
            requestBookingAutoOpen();
        };

        const onScroll = () => {
            const doc = document.documentElement;
            const scrollable = doc.scrollHeight - window.innerHeight;

            if (scrollable <= 0) {
                return;
            }

            const percent = (window.scrollY / scrollable) * 100;

            if (percent >= BOOKING_SCROLL_THRESHOLD) {
                tryOpen();
            }
        };

        const timer = window.setTimeout(tryOpen, BOOKING_AUTO_DELAY_MS);

        window.addEventListener('scroll', onScroll, { passive: true });

        return () => {
            window.clearTimeout(timer);
            window.removeEventListener('scroll', onScroll);
        };
    }, [enabled]);
}

export function useBookingAutoOpenListener(onOpen: (source: string) => void): void {
    useEffect(() => {
        const handler = () => onOpen('auto_trigger');

        window.addEventListener(BOOKING_AUTO_OPEN_EVENT, handler);

        return () => window.removeEventListener(BOOKING_AUTO_OPEN_EVENT, handler);
    }, [onOpen]);
}
