import { useCallback, useEffect, useRef, useState } from 'react';
import {
    FUNNEL_MODAL_STATE_EVENT,
    getActiveFunnelModal,
    hasSeenNewsletterPopupWithinDays,
    markNewsletterPopupSeen,
    NEWSLETTER_OPEN_EVENT,
    setActiveFunnelModal,
} from '@/lib/funnelModalEvents';

const NEWSLETTER_DELAY_MS = 30_000;
const NEWSLETTER_SCROLL_THRESHOLD = 50;
const NEWSLETTER_SCROLL_DELAY_MS = 1_000;
const BOOKING_CONFLICT_DELAY_MS = 30_000;

export function useNewsletterPopupTrigger({
    enabled,
    skipIfLoggedIn,
}: {
    enabled: boolean;
    skipIfLoggedIn: boolean;
}): {
    open: boolean;
    setOpen: (open: boolean) => void;
    openManually: (source?: string) => void;
    dismiss: () => void;
} {
    const [open, setOpenState] = useState(false);
    const triggeredRef = useRef(false);
    const scrollTriggeredRef = useRef(false);
    const exitTriggeredRef = useRef(false);
    const bookingOpenedEarlyRef = useRef(false);
    const mountTimeRef = useRef(Date.now());
    const pendingTimeoutRef = useRef<number | null>(null);

    const clearPendingOpen = useCallback(() => {
        if (pendingTimeoutRef.current !== null) {
            window.clearTimeout(pendingTimeoutRef.current);
            pendingTimeoutRef.current = null;
        }
    }, []);

    const canShow = useCallback(() => {
        if (!enabled || skipIfLoggedIn || triggeredRef.current) {
            return false;
        }

        if (hasSeenNewsletterPopupWithinDays()) {
            return false;
        }

        if (getActiveFunnelModal() !== null) {
            return false;
        }

        return true;
    }, [enabled, skipIfLoggedIn]);

    const setOpen = useCallback(
        (next: boolean) => {
            setOpenState(next);

            if (!next) {
                if (getActiveFunnelModal() === 'newsletter') {
                    setActiveFunnelModal(null);
                }

                return;
            }

            setActiveFunnelModal('newsletter');
        },
        [],
    );

    const tryOpen = useCallback(
        (source: string) => {
            if (!canShow()) {
                return;
            }

            clearPendingOpen();

            const delay =
                bookingOpenedEarlyRef.current
                && Date.now() - mountTimeRef.current < 15_000
                    ? BOOKING_CONFLICT_DELAY_MS
                    : 0;

            pendingTimeoutRef.current = window.setTimeout(() => {
                pendingTimeoutRef.current = null;

                if (!canShow() || getActiveFunnelModal() !== null) {
                    return;
                }

                triggeredRef.current = true;
                setOpen(true);
            }, delay);
        },
        [canShow, clearPendingOpen, setOpen],
    );

    const openManually = useCallback(
        (source = 'manual') => {
            if (skipIfLoggedIn) {
                return;
            }

            clearPendingOpen();
            triggeredRef.current = true;
            setOpen(true);
        },
        [clearPendingOpen, setOpen, skipIfLoggedIn],
    );

    const dismiss = useCallback(() => {
        clearPendingOpen();
        setOpen(false);
        markNewsletterPopupSeen();
    }, [clearPendingOpen, setOpen]);

    useEffect(() => {
        const onManual = (event: Event) => {
            const source =
                (event as CustomEvent<{ source?: string }>).detail?.source ?? 'manual';
            openManually(source);
        };

        window.addEventListener(NEWSLETTER_OPEN_EVENT, onManual);

        return () => window.removeEventListener(NEWSLETTER_OPEN_EVENT, onManual);
    }, [openManually]);

    useEffect(() => {
        const onModalState = (event: Event) => {
            const type = (event as CustomEvent<{ type: string | null }>).detail?.type;

            if (type === 'booking' && Date.now() - mountTimeRef.current < 15_000) {
                bookingOpenedEarlyRef.current = true;
            }
        };

        window.addEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);

        return () => window.removeEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);
    }, []);

    useEffect(() => {
        if (!enabled || skipIfLoggedIn) {
            return;
        }

        const onScroll = () => {
            const doc = document.documentElement;
            const scrollable = doc.scrollHeight - window.innerHeight;

            if (scrollable <= 0) {
                return;
            }

            const percent = (window.scrollY / scrollable) * 100;

            if (percent >= NEWSLETTER_SCROLL_THRESHOLD && !scrollTriggeredRef.current) {
                scrollTriggeredRef.current = true;
                window.setTimeout(() => tryOpen('scroll_50'), NEWSLETTER_SCROLL_DELAY_MS);
            }
        };

        const onExitIntent = (event: MouseEvent) => {
            if (event.clientY < 0 && !exitTriggeredRef.current) {
                exitTriggeredRef.current = true;
                tryOpen('exit_intent');
            }
        };

        const timer = window.setTimeout(() => tryOpen('timer_30s'), NEWSLETTER_DELAY_MS);

        window.addEventListener('scroll', onScroll, { passive: true });
        document.addEventListener('mouseleave', onExitIntent);

        return () => {
            window.clearTimeout(timer);
            clearPendingOpen();
            window.removeEventListener('scroll', onScroll);
            document.removeEventListener('mouseleave', onExitIntent);
        };
    }, [clearPendingOpen, enabled, skipIfLoggedIn, tryOpen]);

    return { open, setOpen, openManually, dismiss };
}
