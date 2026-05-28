import { useEffect, useRef } from 'react';
import {
    BOOKING_AUTO_OPEN_EVENT,
    getActiveFunnelModal,
    markBookingAutoShown,
    requestBookingAutoOpen,
} from '@/lib/funnelModalEvents';
import { attachScrollDepthTrigger } from '@/lib/funnelScrollTrigger';

const BOOKING_AUTO_DELAY_MS = 22_000;

export function useBookingPopupTrigger(enabled: boolean): void {
    const triggeredRef = useRef(false);
    const mountTimeRef = useRef(Date.now());

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

        const detachScroll = attachScrollDepthTrigger(() => tryOpen(), {
            mountTime: mountTimeRef.current,
        });

        const timer = window.setTimeout(tryOpen, BOOKING_AUTO_DELAY_MS);

        return () => {
            window.clearTimeout(timer);
            detachScroll();
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
