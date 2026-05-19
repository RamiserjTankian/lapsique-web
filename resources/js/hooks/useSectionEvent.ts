import { useEffect, useRef } from 'react';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';

export function useSectionEvent<T extends HTMLElement = HTMLElement>(
    eventName: string,
    metadata?: Record<string, unknown>,
) {
    const ref = useRef<T | null>(null);
    const hasSentRef = useRef(false);
    const metadataKey = JSON.stringify(metadata ?? {});

    useEffect(() => {
        const element = ref.current;

        if (!element || typeof IntersectionObserver === 'undefined' || hasSentRef.current) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const entry = entries[0];

                if (!entry?.isIntersecting || hasSentRef.current) {
                    return;
                }

                hasSentRef.current = true;
                trackBookingEvent(eventName, metadata);
                observer.disconnect();
            },
            {
                threshold: 0.35,
            },
        );

        observer.observe(element);

        return () => observer.disconnect();
    }, [eventName, metadataKey]);

    return ref;
}
