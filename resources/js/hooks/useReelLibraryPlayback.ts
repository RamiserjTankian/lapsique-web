import { useCallback, useEffect, useRef, useState } from 'react';

const DEFAULT_OVERLAY_MS = 3500;

export function useReelLibraryPlayback(
    clipCount: number,
    overlayDurationMs: number = DEFAULT_OVERLAY_MS,
) {
    const [activeIndex, setActiveIndex] = useState(0);
    const [showOverlay, setShowOverlay] = useState(false);
    const inViewIndicesRef = useRef<Set<number>>(new Set());
    const [inViewVersion, setInViewVersion] = useState(0);

    const reportInView = useCallback((index: number, inView: boolean) => {
        const next = inViewIndicesRef.current;
        const had = next.has(index);

        if (inView) {
            next.add(index);
        } else {
            next.delete(index);
        }

        if (had !== inView) {
            setInViewVersion((current) => current + 1);
        }
    }, []);

    const handleLoopComplete = useCallback(
        (index: number) => {
            if (clipCount === 0 || index !== activeIndex || showOverlay) {
                return;
            }

            setShowOverlay(true);
        },
        [activeIndex, clipCount, showOverlay],
    );

    const dismissOverlay = useCallback(() => {
        setShowOverlay(false);

        if (clipCount > 0) {
            setActiveIndex((current) => (current + 1) % clipCount);
        }
    }, [clipCount]);

    const handleBook = useCallback(() => {
        dismissOverlay();
    }, [dismissOverlay]);

    useEffect(() => {
        if (!showOverlay || overlayDurationMs <= 0) {
            return;
        }

        const timeoutId = window.setTimeout(dismissOverlay, overlayDurationMs);

        return () => window.clearTimeout(timeoutId);
    }, [dismissOverlay, overlayDurationMs, showOverlay]);

    useEffect(() => {
        if (showOverlay || clipCount === 0 || inViewIndicesRef.current.has(activeIndex)) {
            return;
        }

        let next = (activeIndex + 1) % clipCount;
        let attempts = 0;

        while (!inViewIndicesRef.current.has(next) && attempts < clipCount - 1) {
            next = (next + 1) % clipCount;
            attempts += 1;
        }

        if (inViewIndicesRef.current.has(next) && next !== activeIndex) {
            setActiveIndex(next);
        }
    }, [activeIndex, clipCount, inViewVersion, showOverlay]);

    return {
        activeIndex,
        showOverlay,
        handleLoopComplete,
        dismissOverlay,
        handleBook,
        reportInView,
    };
}
