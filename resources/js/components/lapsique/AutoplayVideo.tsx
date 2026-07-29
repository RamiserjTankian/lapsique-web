import { useCallback, useEffect, useRef, useState } from 'react';
import { useReducedMotion } from 'framer-motion';
import { VideoLoadingCover } from '@/components/lapsique/VideoLoadingCover';
import { useSaveDataConnection } from '@/hooks/useSaveDataConnection';
import { cn } from '@/lib/utils';
import { registerMediaAutoplaySync } from '@/lib/mediaAutoplayRegistry';
import {
    attemptVideoAutoplay,
    isIosLikeDevice,
    prepareVideoForAutoplay,
    resolveVideoPreload,
} from '@/lib/videoAutoplay';

export interface AutoplayVideoProps {
    src: string;
    poster?: string | null;
    title?: string;
    className?: string;
    videoClassName?: string;
    eager?: boolean;
    pauseWhenOffscreen?: boolean;
    offscreenRootMargin?: string;
    preload?: 'none' | 'metadata' | 'auto';
    /** When set, restart playback after this many seconds (landing previews). */
    loopSegmentSeconds?: number;
    /** When false, video stays paused even if in view (sequential reel grids). */
    playbackEnabled?: boolean;
    /** Fired when a loop segment reaches its duration, before restarting. */
    onLoopSegmentComplete?: () => void;
    /** Notifies parent when the clip enters or leaves the viewport. */
    onInViewChange?: (inView: boolean) => void;
}

export function AutoplayVideo({
    src,
    poster,
    title,
    className,
    videoClassName,
    eager = false,
    pauseWhenOffscreen = true,
    offscreenRootMargin = '120px',
    preload,
    loopSegmentSeconds,
    playbackEnabled = true,
    onLoopSegmentComplete,
    onInViewChange,
}: AutoplayVideoProps) {
    const prefersReducedMotion = useReducedMotion();
    const saveDataMode = useSaveDataConnection();
    const containerRef = useRef<HTMLDivElement>(null);
    const videoRef = useRef<HTMLVideoElement>(null);
    const onLoopSegmentCompleteRef = useRef(onLoopSegmentComplete);
    const [isInView, setIsInView] = useState(eager);
    const [isReady, setIsReady] = useState(false);
    const [isPlaying, setIsPlaying] = useState(false);
    const showPosterOnly = prefersReducedMotion || saveDataMode;
    const resolvedPreload = resolveVideoPreload(eager, preload);
    const mediaClassName = cn('h-full w-full object-cover object-center', videoClassName);
    const shouldPlay = !showPosterOnly && playbackEnabled && (isInView || !pauseWhenOffscreen);
    const shouldAttachSource = !showPosterOnly && (eager || isInView || !pauseWhenOffscreen);
    const posterLoading = eager || isInView ? 'eager' : 'lazy';
    const showLoadingCover =
        showPosterOnly
        || !shouldAttachSource
        || !isReady
        || (shouldPlay && !isPlaying);

    useEffect(() => {
        onLoopSegmentCompleteRef.current = onLoopSegmentComplete;
    }, [onLoopSegmentComplete]);

    useEffect(() => {
        setIsReady(false);
        setIsPlaying(false);
    }, [src]);

    useEffect(() => {
        if (showPosterOnly || !pauseWhenOffscreen) {
            setIsInView(true);

            return;
        }

        const node = containerRef.current;

        if (!node) {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                setIsInView(entry.isIntersecting);
            },
            { rootMargin: offscreenRootMargin, threshold: 0.01 },
        );

        observer.observe(node);

        return () => observer.disconnect();
    }, [offscreenRootMargin, pauseWhenOffscreen, showPosterOnly]);

    useEffect(() => {
        onInViewChange?.(isInView);
    }, [isInView, onInViewChange]);

    const syncPlayback = useCallback(async () => {
        const video = videoRef.current;

        if (!video || showPosterOnly) {
            return;
        }

        if (shouldPlay) {
            await attemptVideoAutoplay(video);
        } else if (pauseWhenOffscreen) {
            video.pause();
        }
    }, [pauseWhenOffscreen, shouldPlay, showPosterOnly]);

    useEffect(() => registerMediaAutoplaySync(syncPlayback), [syncPlayback]);

    useEffect(() => {
        if (showPosterOnly) {
            return;
        }

        const video = videoRef.current;

        if (!video) {
            return;
        }

        prepareVideoForAutoplay(video);
        void syncPlayback();

        const onReady = () => {
            setIsReady(true);
            void syncPlayback();
        };

        const onPlaying = () => {
            setIsReady(true);
            setIsPlaying(true);
        };
        const onPause = () => setIsPlaying(false);

        video.addEventListener('loadeddata', onReady);
        video.addEventListener('canplay', onReady);
        video.addEventListener('playing', onPlaying);
        video.addEventListener('pause', onPause);

        const onVisibilityChange = () => {
            if (document.visibilityState === 'visible') {
                void syncPlayback();
            }
        };

        document.addEventListener('visibilitychange', onVisibilityChange);

        return () => {
            video.removeEventListener('loadeddata', onReady);
            video.removeEventListener('canplay', onReady);
            video.removeEventListener('playing', onPlaying);
            video.removeEventListener('pause', onPause);
            document.removeEventListener('visibilitychange', onVisibilityChange);
        };
    }, [shouldPlay, showPosterOnly, src, syncPlayback]);

    useEffect(() => {
        if (showPosterOnly || !isIosLikeDevice()) {
            return;
        }

        let attempts = 0;
        const maxAttempts = 12;
        const intervalId = window.setInterval(() => {
            attempts += 1;
            void syncPlayback();

            if (attempts >= maxAttempts) {
                window.clearInterval(intervalId);
            }
        }, 400);

        return () => window.clearInterval(intervalId);
    }, [shouldPlay, showPosterOnly, src, syncPlayback]);

    useEffect(() => {
        if (showPosterOnly || !loopSegmentSeconds || loopSegmentSeconds <= 0) {
            return;
        }

        const video = videoRef.current;

        if (!video) {
            return;
        }

        let firedForSegment = false;

        const onTimeUpdate = () => {
            if (video.currentTime < 0.2) {
                firedForSegment = false;
            }

            if (!firedForSegment && video.currentTime >= loopSegmentSeconds - 0.08) {
                firedForSegment = true;
                onLoopSegmentCompleteRef.current?.();
                video.currentTime = 0;
            }
        };

        video.addEventListener('timeupdate', onTimeUpdate);

        return () => video.removeEventListener('timeupdate', onTimeUpdate);
    }, [loopSegmentSeconds, showPosterOnly, src]);

    if (showPosterOnly) {
        return (
            <div ref={containerRef} className={cn('relative overflow-hidden bg-black', className)}>
                <VideoLoadingCover
                    poster={poster}
                    title={title}
                    className="relative z-0 h-full w-full"
                    mediaClassName={mediaClassName}
                    eager={posterLoading === 'eager'}
                />
            </div>
        );
    }

    return (
        <div ref={containerRef} className={cn('relative overflow-hidden bg-black', className)}>
            <video
                ref={videoRef}
                src={shouldAttachSource ? src : undefined}
                className={cn('pointer-events-none lapsique-autoplay-video', mediaClassName)}
                data-lapsique-autoplay=""
                autoPlay
                loop
                muted
                poster={poster ?? undefined}
                playsInline
                controls={false}
                disablePictureInPicture
                disableRemotePlayback
                preload={shouldAttachSource ? resolvedPreload : 'none'}
                aria-hidden={title ? undefined : true}
                title={title}
            />
            {showLoadingCover ? (
                <VideoLoadingCover
                    poster={poster}
                    title={title}
                    mediaClassName={mediaClassName}
                    eager={posterLoading === 'eager'}
                />
            ) : null}
        </div>
    );
}
