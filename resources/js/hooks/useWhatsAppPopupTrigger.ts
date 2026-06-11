import { useCallback, useEffect, useRef, useState } from 'react';
import {
    FUNNEL_CONTENT_ENGAGEMENT_EVENT,
    getFunnelContentEngagementCount,
} from '@/lib/funnelEngagement';
import {
    BOOKING_MODAL_CLOSED_EVENT,
    canOpenAutomatedFunnelPopup,
    FUNNEL_MODAL_STATE_EVENT,
    getActiveFunnelModal,
    hasSeenWhatsAppPopupWithinDays,
    hasWhatsAppPopupShownThisSession,
    markWhatsAppPopupShownThisSession,
    setActiveFunnelModal,
} from '@/lib/funnelModalEvents';
import { attachScrollDepthTrigger } from '@/lib/funnelScrollTrigger';

const OPEN_COOLDOWN_MS = 5_000;
const BOOKING_CLOSE_DELAY_MS = 90_000;

const DESKTOP = {
    settledDelayMinMs: 150_000,
    settledDelayRangeMs: 60_000,
    engagementMinSections: 4,
    engagementMinTimeMs: 90_000,
    scrollThresholdPercent: 82,
} as const;

const MOBILE = {
    settledDelayMinMs: 120_000,
    settledDelayRangeMs: 45_000,
    engagementMinSections: 3,
    engagementMinTimeMs: 70_000,
    scrollThresholdPercent: 76,
} as const;

function isMobileViewport(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(max-width: 767px)').matches;
}

function getTriggerProfile() {
    return isMobileViewport() ? MOBILE : DESKTOP;
}

export function useWhatsAppPopupTrigger({
    enabled,
    whatsapp,
}: {
    enabled: boolean;
    whatsapp: string | null | undefined;
}): {
    open: boolean;
    setOpen: (open: boolean) => void;
    dismiss: () => void;
    source: string;
} {
    const [open, setOpenState] = useState(false);
    const [source, setSource] = useState('auto');
    const triggeredRef = useRef(false);
    const mountTimeRef = useRef(Date.now());
    const pendingTimeoutRef = useRef<number | null>(null);
    const engagementTriggeredRef = useRef(false);
    const deferredSourceRef = useRef<string | null>(null);

    const clearPendingOpen = useCallback(() => {
        if (pendingTimeoutRef.current !== null) {
            window.clearTimeout(pendingTimeoutRef.current);
            pendingTimeoutRef.current = null;
        }
    }, []);

    const canShow = useCallback(() => {
        if (!enabled || !whatsapp?.trim() || triggeredRef.current) {
            return false;
        }

        if (hasSeenWhatsAppPopupWithinDays()) {
            return false;
        }

        if (hasWhatsAppPopupShownThisSession()) {
            return false;
        }

        if (getActiveFunnelModal() !== null) {
            return false;
        }

        if (
            !canOpenAutomatedFunnelPopup({
                bookingIntentMs: 180_000,
                modalQuietMs: 35_000,
            })
        ) {
            return false;
        }

        return true;
    }, [enabled, whatsapp]);

    const setOpen = useCallback((next: boolean) => {
        setOpenState(next);

        if (!next) {
            if (getActiveFunnelModal() === 'whatsapp') {
                setActiveFunnelModal(null);
            }

            return;
        }

        markWhatsAppPopupShownThisSession();
        setActiveFunnelModal('whatsapp');
    }, []);

    const attemptOpen = useCallback(
        (triggerSource: string): boolean => {
            if (triggeredRef.current) {
                return true;
            }

            if (getActiveFunnelModal() !== null) {
                deferredSourceRef.current = triggerSource;

                return false;
            }

            if (!canShow()) {
                return false;
            }

            triggeredRef.current = true;
            deferredSourceRef.current = null;
            setSource(triggerSource);
            setOpen(true);

            return true;
        },
        [canShow, setOpen],
    );

    const tryOpen = useCallback(
        (triggerSource: string, delayMs = OPEN_COOLDOWN_MS) => {
            if (triggeredRef.current) {
                return;
            }

            clearPendingOpen();

            pendingTimeoutRef.current = window.setTimeout(() => {
                pendingTimeoutRef.current = null;
                attemptOpen(triggerSource);
            }, delayMs);
        },
        [attemptOpen, clearPendingOpen],
    );

    const retryDeferred = useCallback(() => {
        const deferred = deferredSourceRef.current;

        if (!deferred || triggeredRef.current) {
            return;
        }

        tryOpen(deferred, OPEN_COOLDOWN_MS);
    }, [tryOpen]);

    const dismiss = useCallback(() => {
        clearPendingOpen();
        setOpen(false);
    }, [clearPendingOpen, setOpen]);

    useEffect(() => {
        if (!enabled || !whatsapp?.trim()) {
            return;
        }

        const profile = getTriggerProfile();

        const settledDelay =
            profile.settledDelayMinMs
            + Math.floor(Math.random() * profile.settledDelayRangeMs);
        const settledTimer = window.setTimeout(
            () => tryOpen('settled_timer'),
            settledDelay,
        );

        const detachScroll = attachScrollDepthTrigger(
            () => tryOpen('scroll_depth'),
            {
                mountTime: mountTimeRef.current,
                thresholdPercent: profile.scrollThresholdPercent,
                minTimeOnPageMs: profile.engagementMinTimeMs,
            },
        );

        const onEngagement = (event: Event) => {
            if (engagementTriggeredRef.current) {
                return;
            }

            const count =
                (event as CustomEvent<{ count: number }>).detail?.count
                ?? getFunnelContentEngagementCount();

            if (count < profile.engagementMinSections) {
                return;
            }

            if (Date.now() - mountTimeRef.current < profile.engagementMinTimeMs) {
                return;
            }

            engagementTriggeredRef.current = true;
            tryOpen('high_engagement');
        };

        const onBookingClosed = () => {
            tryOpen('booking_dismissed', BOOKING_CLOSE_DELAY_MS);
        };

        const onModalState = (event: Event) => {
            const type = (event as CustomEvent<{ type: string | null }>).detail?.type;

            if (type === null) {
                retryDeferred();
            }
        };

        window.addEventListener(FUNNEL_CONTENT_ENGAGEMENT_EVENT, onEngagement);
        window.addEventListener(BOOKING_MODAL_CLOSED_EVENT, onBookingClosed);
        window.addEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);

        return () => {
            window.clearTimeout(settledTimer);
            clearPendingOpen();
            detachScroll();
            window.removeEventListener(FUNNEL_CONTENT_ENGAGEMENT_EVENT, onEngagement);
            window.removeEventListener(BOOKING_MODAL_CLOSED_EVENT, onBookingClosed);
            window.removeEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);
        };
    }, [clearPendingOpen, enabled, retryDeferred, tryOpen, whatsapp]);

    return { open, setOpen, dismiss, source };
}
