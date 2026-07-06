import { useEffect, useRef, useState, type RefObject } from 'react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { VideoLoadingCover } from '@/components/lapsique/VideoLoadingCover';
import { REEL_PLAYER_CTA_DELAY_SECONDS } from '@/data/contentOffer';
import { useSaveDataConnection } from '@/hooks/useSaveDataConnection';
import { useReelPlayerModal } from '@/hooks/useReelPlayerModal';
import { useReelWatchMilestones } from '@/hooks/useReelWatchMilestones';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { buildReelAnalyticsPayload } from '@/lib/reelAnalytics';
import { modalOverlayMedia, modalShellMediaReel } from '@/lib/modalLayout';
import { bookingConfirmButtonClasses } from '@/lib/bookingSelectionStyles';
import { useTranslations } from '@/hooks/useTranslations';
import { openBookingModal } from '@/lib/openBookingModal';
import { cn } from '@/lib/utils';

export function ReelPlayerModal() {
    const { t } = useTranslations();
    const { activeReel, closeReelPlayer } = useReelPlayerModal();
    const prefersReducedMotion = useReducedMotion();
    const saveDataMode = useSaveDataConnection();
    const videoRef = useRef<HTMLVideoElement>(null);
    const ctaTimeoutRef = useRef<number | null>(null);
    const [showCta, setShowCta] = useState(false);

    const isOpen = activeReel !== null;
    const showPosterOnly = prefersReducedMotion || saveDataMode;

    useReelWatchMilestones(videoRef, isOpen && !showPosterOnly, activeReel);

    useEffect(() => {
        if (!isOpen || !activeReel) {
            setShowCta(false);

            if (ctaTimeoutRef.current !== null) {
                window.clearTimeout(ctaTimeoutRef.current);
                ctaTimeoutRef.current = null;
            }

            return;
        }

        trackBookingEvent(
            'reel_player_opened',
            buildReelAnalyticsPayload({
                src: activeReel.src,
                bookingSource: activeReel.bookingSource,
                title: activeReel.title,
            }),
        );

        const delayMs = REEL_PLAYER_CTA_DELAY_SECONDS * 1000;

        const scheduleCta = () => {
            if (ctaTimeoutRef.current !== null) {
                window.clearTimeout(ctaTimeoutRef.current);
            }

            ctaTimeoutRef.current = window.setTimeout(() => {
                setShowCta(true);
            }, delayMs);
        };

        if (showPosterOnly) {
            scheduleCta();

            return () => {
                if (ctaTimeoutRef.current !== null) {
                    window.clearTimeout(ctaTimeoutRef.current);
                }
            };
        }

        const video = videoRef.current;

        if (!video) {
            scheduleCta();

            return () => {
                if (ctaTimeoutRef.current !== null) {
                    window.clearTimeout(ctaTimeoutRef.current);
                }
            };
        }

        const onPlaying = () => scheduleCta();

        video.addEventListener('playing', onPlaying, { once: true });
        void video.play().catch(() => scheduleCta());

        return () => {
            video.removeEventListener('playing', onPlaying);

            if (ctaTimeoutRef.current !== null) {
                window.clearTimeout(ctaTimeoutRef.current);
            }
        };
    }, [activeReel, isOpen, showPosterOnly]);

    const handleAgendar = () => {
        if (!activeReel) {
            return;
        }

        const { bookingSource } = activeReel;
        closeReelPlayer();
        openBookingModal({
            source: bookingSource,
            analyticsEvent: 'reel_player_agendar_clicked',
        });
    };

    return (
        <Dialog
            open={isOpen}
            onOpenChange={(open) => {
                if (!open) {
                    closeReelPlayer();
                }
            }}
        >
            <DialogContent
                className={modalShellMediaReel}
                overlayClassName={modalOverlayMedia}
            >
                <DialogTitle className="sr-only">
                    {activeReel?.title ?? t('funnel.reel_player.title_fallback')}
                </DialogTitle>

                {activeReel ? (
                    <ReelPlayerFrame
                        src={activeReel.src}
                        poster={activeReel.poster}
                        showPosterOnly={showPosterOnly}
                        showCta={showCta}
                        videoRef={videoRef}
                        onAgendar={handleAgendar}
                        bookLabel={t('common.cta.book')}
                    />
                ) : null}
            </DialogContent>
        </Dialog>
    );
}

function ReelPlayerFrame({
    src,
    poster,
    showPosterOnly,
    showCta,
    videoRef,
    onAgendar,
    bookLabel,
}: {
    src: string;
    poster?: string | null;
    showPosterOnly: boolean;
    showCta: boolean;
    videoRef: RefObject<HTMLVideoElement | null>;
    onAgendar: () => void;
    bookLabel: string;
}) {
    const prefersReducedMotion = useReducedMotion();
    const [isReady, setIsReady] = useState(false);

    useEffect(() => {
        setIsReady(false);
    }, [src]);

    return (
        <motion.div
            className="relative h-full w-full bg-black"
            initial={false}
            animate={{ opacity: 1 }}
        >
            {showPosterOnly ? (
                <VideoLoadingCover
                    poster={poster}
                    className="z-0"
                    mediaClassName="h-full w-full object-cover"
                    eager
                />
            ) : (
                <video
                    ref={videoRef}
                    src={src}
                    poster={poster ?? undefined}
                    className="absolute inset-0 h-full w-full object-cover"
                    autoPlay
                    muted
                    playsInline
                    loop
                    controls={false}
                    preload="metadata"
                    onLoadedData={() => setIsReady(true)}
                    onCanPlay={() => setIsReady(true)}
                    onPlaying={() => setIsReady(true)}
                />
            )}

            {!showPosterOnly && !isReady ? (
                <VideoLoadingCover
                    poster={poster}
                    mediaClassName="h-full w-full object-cover"
                    eager
                />
            ) : null}

            <AnimatePresence>
                {showCta ? (
                    <motion.div
                        key="reel-player-cta"
                        initial={prefersReducedMotion ? { opacity: 1 } : { opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={prefersReducedMotion ? { opacity: 0 } : { opacity: 0 }}
                        transition={{ duration: prefersReducedMotion ? 0 : 0.35, ease: [0.22, 1, 0.36, 1] }}
                        className="pointer-events-none absolute inset-0 flex items-center justify-center bg-gradient-to-t from-black/90 via-black/55 to-black/20"
                    >
                        <motion.div
                            initial={
                                prefersReducedMotion
                                    ? { opacity: 1, y: 0, scale: 1 }
                                    : { opacity: 0, y: 18, scale: 0.92 }
                            }
                            animate={{ opacity: 1, y: 0, scale: 1 }}
                            exit={
                                prefersReducedMotion
                                    ? { opacity: 0 }
                                    : { opacity: 0, y: 10, scale: 0.96 }
                            }
                            transition={{
                                duration: prefersReducedMotion ? 0 : 0.42,
                                ease: [0.22, 1, 0.36, 1],
                                delay: prefersReducedMotion ? 0 : 0.08,
                            }}
                            className="pointer-events-auto px-6"
                        >
                            <Button
                                type="button"
                                variant="ghost"
                                className={cn(bookingConfirmButtonClasses, 'max-w-[240px]')}
                                onClick={onAgendar}
                            >
                                {bookLabel}
                            </Button>
                        </motion.div>
                    </motion.div>
                ) : null}
            </AnimatePresence>
        </motion.div>
    );
}
