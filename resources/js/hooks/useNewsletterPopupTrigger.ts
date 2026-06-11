import { useCallback, useEffect, useRef, useState } from 'react';
import {
    canOpenAutomatedFunnelPopup,
    getActiveFunnelModal,
    hasSeenNewsletterPopupWithinDays,
    markNewsletterPopupSeen,
    NEWSLETTER_OPEN_EVENT,
    setActiveFunnelModal,
} from '@/lib/funnelModalEvents';
import {
    attachScrollDepthTrigger,
    supportsExitIntent,
} from '@/lib/funnelScrollTrigger';

const NEWSLETTER_DELAY_MS = 90_000;
const NEWSLETTER_SCROLL_THRESHOLD_PERCENT = 84;
const NEWSLETTER_MIN_TIME_ON_PAGE_MS = 70_000;
const NEWSLETTER_EXIT_MIN_TIME_MS = 45_000;

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
    const exitTriggeredRef = useRef(false);
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

        if (
            !canOpenAutomatedFunnelPopup({
                bookingIntentMs: 180_000,
                modalQuietMs: 30_000,
            })
        ) {
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
            pendingTimeoutRef.current = window.setTimeout(() => {
                pendingTimeoutRef.current = null;

                if (!canShow() || getActiveFunnelModal() !== null) {
                    return;
                }

                triggeredRef.current = true;
                setOpen(true);
            }, 0);
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
        if (!enabled || skipIfLoggedIn) {
            return;
        }

        const params = new URLSearchParams(window.location.search);

        if (params.get('book') === '1') {
            return;
        }

        const detachScroll = attachScrollDepthTrigger(
            () => tryOpen('scroll_depth'),
            {
                mountTime: mountTimeRef.current,
                thresholdPercent: NEWSLETTER_SCROLL_THRESHOLD_PERCENT,
                minTimeOnPageMs: NEWSLETTER_MIN_TIME_ON_PAGE_MS,
            },
        );

        const onExitIntent = (event: MouseEvent) => {
            if (
                event.clientY < 0
                && !exitTriggeredRef.current
                && Date.now() - mountTimeRef.current >= NEWSLETTER_EXIT_MIN_TIME_MS
            ) {
                exitTriggeredRef.current = true;
                tryOpen('exit_intent');
            }
        };

        const timer = window.setTimeout(() => tryOpen('timer_90s'), NEWSLETTER_DELAY_MS);

        const exitIntentEnabled = supportsExitIntent();

        if (exitIntentEnabled) {
            document.addEventListener('mouseleave', onExitIntent);
        }

        return () => {
            window.clearTimeout(timer);
            clearPendingOpen();
            detachScroll();

            if (exitIntentEnabled) {
                document.removeEventListener('mouseleave', onExitIntent);
            }
        };
    }, [clearPendingOpen, enabled, skipIfLoggedIn, tryOpen]);

    return { open, setOpen, openManually, dismiss };
}
