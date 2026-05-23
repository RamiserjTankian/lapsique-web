import { useEffect, useState } from 'react';

interface UseTypingCycleOptions {
    texts: string[];
    typingIntervalMs?: number;
    deletingIntervalMs?: number;
    pauseAfterCompleteMs?: number;
    pauseBetweenTextsMs?: number;
    enabled?: boolean;
}

export function useTypingCycle({
    texts,
    typingIntervalMs = 45,
    deletingIntervalMs = 28,
    pauseAfterCompleteMs = 2200,
    pauseBetweenTextsMs = 400,
    enabled = true,
}: UseTypingCycleOptions) {
    const [textIndex, setTextIndex] = useState(0);
    const [displayed, setDisplayed] = useState('');
    const [isDeleting, setIsDeleting] = useState(false);

    const activeText = texts[textIndex] ?? '';

    useEffect(() => {
        if (!enabled || texts.length === 0) {
            return;
        }

        const isComplete = !isDeleting && displayed.length === activeText.length;
        const isEmpty = isDeleting && displayed.length === 0;

        let delay = isDeleting ? deletingIntervalMs : typingIntervalMs;

        if (isComplete) {
            delay = pauseAfterCompleteMs;
        } else if (isEmpty) {
            delay = pauseBetweenTextsMs;
        }

        const timer = window.setTimeout(() => {
            if (isComplete) {
                setIsDeleting(true);

                return;
            }

            if (isEmpty) {
                setIsDeleting(false);
                setTextIndex((current) => (current + 1) % texts.length);

                return;
            }

            const nextLength = isDeleting
                ? Math.max(0, displayed.length - 1)
                : Math.min(activeText.length, displayed.length + 1);

            setDisplayed(activeText.slice(0, nextLength));
        }, delay);

        return () => window.clearTimeout(timer);
    }, [
        activeText,
        deletingIntervalMs,
        displayed,
        enabled,
        isDeleting,
        pauseAfterCompleteMs,
        pauseBetweenTextsMs,
        texts.length,
        typingIntervalMs,
    ]);

    useEffect(() => {
        if (!enabled) {
            setDisplayed('');
            setIsDeleting(false);
            setTextIndex(0);
        }
    }, [enabled]);

    return {
        displayed,
        isDeleting,
        showCursor: enabled,
    };
}
