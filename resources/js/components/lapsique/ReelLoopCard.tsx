import { type KeyboardEvent, type MouseEvent, type ReactNode, useCallback, useEffect, useId, useState } from 'react';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { LANDING_VIDEO_LOOP_SECONDS } from '@/data/contentOffer';
import { useOptionalReelPlayerModal } from '@/hooks/useReelPlayerModal';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { buildReelAnalyticsPayload } from '@/lib/reelAnalytics';
import { openBookingModal } from '@/lib/openBookingModal';
import { cn } from '@/lib/utils';

const DEFAULT_OVERLAY_MS = 3500;

export interface ReelLoopCardProps {
    src: string;
    poster?: string | null;
    title?: string;
    articleClassName?: string;
    videoClassName?: string;
    bookingSource?: string;
    eager?: boolean;
    pauseWhenOffscreen?: boolean;
    preload?: 'none' | 'metadata' | 'auto';
    playbackEnabled?: boolean;
    showBookingOverlay?: boolean;
    onLoopSegmentComplete?: () => void;
    onInViewChange?: (inView: boolean) => void;
    onBook?: () => void;
    overlayAutoHideMs?: number;
    footer?: ReactNode;
    fillContainer?: boolean;
    /** When false, card is not clickable (e.g. outside ReelPlayerProvider). */
    openPlayerOnClick?: boolean;
}

export function ReelLoopCard({
    src,
    poster,
    title,
    articleClassName,
    videoClassName,
    bookingSource = 'reel_loop',
    eager = false,
    pauseWhenOffscreen = true,
    preload = 'none',
    playbackEnabled = true,
    showBookingOverlay,
    onLoopSegmentComplete,
    onInViewChange,
    onBook,
    overlayAutoHideMs = DEFAULT_OVERLAY_MS,
    footer,
    fillContainer = false,
    openPlayerOnClick = true,
}: ReelLoopCardProps) {
    const reelId = useId();
    const reelPlayer = useOptionalReelPlayerModal();
    const [localOverlay, setLocalOverlay] = useState(false);
    const overlayVisible = showBookingOverlay ?? localOverlay;
    const isControlledOverlay = showBookingOverlay !== undefined;

    const dismissOverlay = useCallback(() => {
        if (!isControlledOverlay) {
            setLocalOverlay(false);
        }
    }, [isControlledOverlay]);

    const handleLoopComplete = useCallback(() => {
        onLoopSegmentComplete?.();

        if (!isControlledOverlay) {
            setLocalOverlay(true);
        }
    }, [isControlledOverlay, onLoopSegmentComplete]);

    const handleBook = useCallback(
        (event: MouseEvent<HTMLButtonElement>) => {
            event.stopPropagation();

            trackBookingEvent(
                'reel_overlay_cta_clicked',
                buildReelAnalyticsPayload({ src, bookingSource, title }),
            );

            if (onBook) {
                onBook();
            } else {
                openBookingModal({ source: bookingSource, skipAnalytics: true });
            }

            dismissOverlay();
        },
        [bookingSource, dismissOverlay, onBook, src, title],
    );

    const handleOpenPlayer = useCallback(() => {
        if (!openPlayerOnClick || !reelPlayer) {
            return;
        }

        trackBookingEvent(
            'reel_card_clicked',
            buildReelAnalyticsPayload({ src, bookingSource, title }),
        );

        reelPlayer.openReelPlayer({
            src,
            poster,
            title: title ?? null,
            bookingSource,
        });
    }, [bookingSource, openPlayerOnClick, poster, reelPlayer, src, title]);

    const handleCardKeyDown = useCallback(
        (event: KeyboardEvent<HTMLElement>) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            handleOpenPlayer();
        },
        [handleOpenPlayer],
    );

    const isClickable = openPlayerOnClick && reelPlayer !== null;

    useEffect(() => {
        if (!localOverlay || isControlledOverlay || overlayAutoHideMs <= 0) {
            return;
        }

        const timeoutId = window.setTimeout(dismissOverlay, overlayAutoHideMs);

        return () => window.clearTimeout(timeoutId);
    }, [dismissOverlay, isControlledOverlay, localOverlay, overlayAutoHideMs]);

    return (
        <article
            className={cn(
                'group relative overflow-hidden rounded-lg border border-white/10 bg-background',
                !fillContainer && 'aspect-[9/16]',
                isClickable && 'cursor-pointer',
                articleClassName,
            )}
            data-reel-id={reelId}
            role={isClickable ? 'button' : undefined}
            tabIndex={isClickable ? 0 : undefined}
            onClick={isClickable ? handleOpenPlayer : undefined}
            onKeyDown={isClickable ? handleCardKeyDown : undefined}
        >
            <AutoplayVideo
                src={src}
                poster={poster}
                title={title}
                className="absolute inset-0 h-full w-full"
                videoClassName={cn(
                    'object-cover object-center transition duration-700 group-hover:scale-[1.04]',
                    videoClassName,
                )}
                eager={eager}
                pauseWhenOffscreen={pauseWhenOffscreen}
                preload={preload}
                loopSegmentSeconds={LANDING_VIDEO_LOOP_SECONDS}
                playbackEnabled={playbackEnabled && !overlayVisible}
                onLoopSegmentComplete={handleLoopComplete}
                onInViewChange={onInViewChange}
            />

            <div
                className={cn(
                    'pointer-events-none absolute inset-0 transition-opacity duration-500',
                    overlayVisible
                        ? 'bg-gradient-to-t from-black/90 via-black/65 to-black/25 opacity-100'
                        : 'bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-100',
                )}
            />

            {overlayVisible ? (
                <div className="absolute inset-0 z-10 flex items-end justify-center p-4 sm:p-5">
                    <BookingCtaButton
                        type="button"
                        compact
                        bookingSource={bookingSource}
                        onClick={handleBook}
                        className="w-full max-w-[220px] shadow-[0_12px_36px_rgb(16_185_129/0.35)]"
                    >
                        Agendar fecha
                    </BookingCtaButton>
                </div>
            ) : (
                footer
            )}
        </article>
    );
}
