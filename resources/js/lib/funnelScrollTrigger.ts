const DEFAULT_SETTLE_MS = 2_000;
const DEFAULT_MIN_TIME_ON_PAGE_MS = 20_000;

export interface ScrollDepthTriggerOptions {
    thresholdPercent?: number;
    settleMs?: number;
    minTimeOnPageMs?: number;
    mountTime?: number;
}

function getScrollDepthPercent(): number {
    const doc = document.documentElement;
    const scrollable = doc.scrollHeight - window.innerHeight;

    if (scrollable <= 0) {
        return 0;
    }

    return (window.scrollY / scrollable) * 100;
}

/**
 * Fires once after the user stops scrolling and has passed a depth threshold.
 * Avoids opening modals mid-swipe or from mobile viewport chrome resizing.
 */
export function attachScrollDepthTrigger(
    onTrigger: () => void,
    options: ScrollDepthTriggerOptions = {},
): () => void {
    const threshold = options.thresholdPercent ?? 60;
    const settleMs = options.settleMs ?? DEFAULT_SETTLE_MS;
    const minTimeOnPageMs = options.minTimeOnPageMs ?? DEFAULT_MIN_TIME_ON_PAGE_MS;
    const mountTime = options.mountTime ?? Date.now();

    let triggered = false;
    let settleTimer: number | null = null;

    const maybeTrigger = () => {
        if (triggered) {
            return;
        }

        if (Date.now() - mountTime < minTimeOnPageMs) {
            return;
        }

        if (getScrollDepthPercent() < threshold) {
            return;
        }

        triggered = true;
        onTrigger();
    };

    const scheduleCheck = () => {
        if (triggered) {
            return;
        }

        if (settleTimer !== null) {
            window.clearTimeout(settleTimer);
        }

        settleTimer = window.setTimeout(maybeTrigger, settleMs);
    };

    window.addEventListener('scroll', scheduleCheck, { passive: true });
    window.addEventListener('scrollend', maybeTrigger, { passive: true });

    return () => {
        if (settleTimer !== null) {
            window.clearTimeout(settleTimer);
        }

        window.removeEventListener('scroll', scheduleCheck);
        window.removeEventListener('scrollend', maybeTrigger);
    };
}

/** Exit intent only makes sense on desktop pointers; touch scroll must not trigger it. */
export function supportsExitIntent(): boolean {
    return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
}
