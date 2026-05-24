import { useEffect, useRef, useState } from 'react';
import { useReducedMotion } from 'framer-motion';
import { useSaveDataConnection } from '@/hooks/useSaveDataConnection';
import { cn } from '@/lib/utils';

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
    const showPosterOnly = prefersReducedMotion || saveDataMode;
    const resolvedPreload = preload ?? (eager ? 'auto' : 'none');
    const mediaClassName = cn('h-full w-full object-cover object-center', videoClassName);

    useEffect(() => {
        onLoopSegmentCompleteRef.current = onLoopSegmentComplete;
    }, [onLoopSegmentComplete]);

    useEffect(() => {
        if (eager || showPosterOnly || !pauseWhenOffscreen) {
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
            { rootMargin: offscreenRootMargin, threshold: 0.15 },
        );

        observer.observe(node);

        return () => observer.disconnect();
    }, [eager, offscreenRootMargin, pauseWhenOffscreen, showPosterOnly]);

    useEffect(() => {
        onInViewChange?.(isInView);
    }, [isInView, onInViewChange]);

    useEffect(() => {
        if (showPosterOnly || !pauseWhenOffscreen) {
            return;
        }

        const video = videoRef.current;

        if (!video) {
            return;
        }

        if (isInView && playbackEnabled) {
            void video.play().catch(() => undefined);
        } else {
            video.pause();
        }
    }, [isInView, pauseWhenOffscreen, playbackEnabled, showPosterOnly, src]);

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

    if (showPosterOnly && poster) {
        return (
            <div ref={containerRef} className={cn('relative overflow-hidden bg-black', className)}>
                <img
                    src={poster}
                    alt={title ?? ''}
                    className={mediaClassName}
                    loading={eager ? 'eager' : 'lazy'}
                />
            </div>
        );
    }

    return (
        <div ref={containerRef} className={cn('relative overflow-hidden bg-black', className)}>
            {isInView ? (
                <video
                    ref={videoRef}
                    src={src}
                    poster={poster ?? undefined}
                    className={mediaClassName}
                    autoPlay
                    loop
                    muted
                    playsInline
                    preload={resolvedPreload}
                    aria-hidden={title ? undefined : true}
                    title={title}
                />
            ) : poster ? (
                <img
                    src={poster}
                    alt={title ?? ''}
                    className={mediaClassName}
                    loading="lazy"
                />
            ) : (
                <div className={cn('h-full w-full bg-black', videoClassName)} aria-hidden />
            )}
        </div>
    );
}
