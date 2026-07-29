import { useEffect, useRef, useState } from 'react';
import { Maximize2, Pause, Play, Volume2, VolumeX } from 'lucide-react';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';

interface EditorialVideoPlayerProps {
    src: string;
    poster?: string | null;
    title: string;
    className?: string;
    videoClassName?: string;
    preload?: 'none' | 'metadata' | 'auto';
    autoPlay?: boolean;
    muted?: boolean;
    hasAudio?: boolean;
    onReady?: () => void;
    onPlay?: () => void;
}

function formatTime(value: number): string {
    if (!Number.isFinite(value) || value < 0) {
        return '0:00';
    }

    const minutes = Math.floor(value / 60);
    const seconds = Math.floor(value % 60);

    return `${minutes}:${String(seconds).padStart(2, '0')}`;
}

export function EditorialVideoPlayer({
    src,
    poster,
    title,
    className,
    videoClassName,
    preload = 'none',
    autoPlay = false,
    muted = false,
    hasAudio = true,
    onReady,
    onPlay,
}: EditorialVideoPlayerProps) {
    const { locale } = useTranslations();
    const videoRef = useRef<HTMLVideoElement>(null);
    const frameRef = useRef<HTMLDivElement>(null);
    const [playing, setPlaying] = useState(false);
    const [isMuted, setIsMuted] = useState(muted || !hasAudio);
    const [currentTime, setCurrentTime] = useState(0);
    const [duration, setDuration] = useState(0);
    const en = locale === 'en';

    useEffect(() => {
        const video = videoRef.current;
        const shouldMute = muted || !hasAudio;

        if (video) {
            video.muted = shouldMute;
        }

        setIsMuted(shouldMute);
    }, [hasAudio, muted, src]);

    useEffect(() => {
        const video = videoRef.current;

        if (!video || !autoPlay) {
            return;
        }

        void video.play().catch(() => setPlaying(false));
    }, [autoPlay, src]);

    const togglePlayback = async () => {
        const video = videoRef.current;

        if (!video) {
            return;
        }

        if (video.paused) {
            await video.play().catch(() => undefined);
        } else {
            video.pause();
        }
    };

    const toggleMute = () => {
        const video = videoRef.current;

        if (!video) {
            return;
        }

        video.muted = !video.muted;
        setIsMuted(video.muted);
    };

    const seek = (value: number) => {
        const video = videoRef.current;

        if (!video) {
            return;
        }

        video.currentTime = value;
        setCurrentTime(value);
    };

    const toggleFullscreen = async () => {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
            return;
        }

        await frameRef.current?.requestFullscreen();
    };

    return (
        <div
            ref={frameRef}
            className={cn(
                'group/player relative overflow-hidden bg-black text-white outline outline-1 -outline-offset-1 outline-white/15',
                className,
            )}
            data-editorial-video-player="true"
        >
            <video
                ref={videoRef}
                src={src}
                poster={poster ?? undefined}
                preload={preload}
                playsInline
                muted={muted || !hasAudio}
                controls={false}
                aria-label={title}
                onClick={() => void togglePlayback()}
                onLoadedMetadata={(event) => {
                    setDuration(event.currentTarget.duration);
                    setIsMuted(event.currentTarget.muted);
                    onReady?.();
                }}
                onCanPlay={onReady}
                onPlay={() => {
                    setPlaying(true);
                    onPlay?.();
                }}
                onPause={() => setPlaying(false)}
                onEnded={() => setPlaying(false)}
                onTimeUpdate={(event) => setCurrentTime(event.currentTarget.currentTime)}
                className={cn('aspect-video h-full w-full cursor-pointer object-cover', videoClassName)}
            />

            {!playing ? (
                <button
                    type="button"
                    onClick={() => void togglePlayback()}
                    className="absolute inset-0 flex items-center justify-center bg-black/15 text-white transition-[background-color] duration-200 hover:bg-black/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary"
                    aria-label={en ? `Play ${title}` : `Reproducir ${title}`}
                >
                    <span className="flex size-14 items-center justify-center border border-white/60 bg-black/65 transition-[background-color,border-color,transform] duration-150 group-hover/player:border-primary group-hover/player:bg-primary active:scale-[0.96] motion-reduce:transition-none">
                        <Play className="ml-0.5 size-5 fill-current" aria-hidden="true" />
                    </span>
                </button>
            ) : null}

            <div
                className={cn(
                    'absolute inset-x-0 bottom-0 z-10 bg-gradient-to-t from-black via-black/75 to-transparent px-3 pb-3 pt-10 transition-[opacity] duration-200 sm:px-4',
                    playing || currentTime > 0
                        ? 'pointer-events-auto opacity-100'
                        : 'pointer-events-none opacity-0 group-hover/player:pointer-events-auto group-hover/player:opacity-100 group-focus-within/player:pointer-events-auto group-focus-within/player:opacity-100',
                )}
            >
                <input
                    type="range"
                    min={0}
                    max={duration || 0}
                    step={0.05}
                    value={Math.min(currentTime, duration || 0)}
                    onChange={(event) => seek(Number(event.target.value))}
                    className="editorial-video-range block h-6 w-full cursor-pointer accent-primary"
                    aria-label={en ? 'Video progress' : 'Progreso del video'}
                />

                <div className="mt-1 flex items-center gap-1.5">
                    <button
                        type="button"
                        onClick={() => void togglePlayback()}
                        className="flex size-11 items-center justify-center border border-white/25 bg-black/55 text-white transition-[background-color,border-color,transform] duration-150 hover:border-primary hover:bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.96] motion-reduce:transition-none"
                        aria-label={playing ? (en ? 'Pause' : 'Pausar') : (en ? 'Play' : 'Reproducir')}
                    >
                        {playing ? <Pause className="size-4" aria-hidden="true" /> : <Play className="size-4 fill-current" aria-hidden="true" />}
                    </button>
                    {hasAudio ? (
                        <button
                            type="button"
                            onClick={toggleMute}
                            className="flex size-11 items-center justify-center border border-white/25 bg-black/55 text-white transition-[background-color,border-color,transform] duration-150 hover:border-primary hover:bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.96] motion-reduce:transition-none"
                            aria-label={isMuted ? (en ? 'Unmute' : 'Activar audio') : (en ? 'Mute' : 'Silenciar')}
                        >
                            {isMuted ? <VolumeX className="size-4" aria-hidden="true" /> : <Volume2 className="size-4" aria-hidden="true" />}
                        </button>
                    ) : null}
                    <span className="ml-1 font-mono text-[0.65rem] tabular-nums tracking-[0.08em] text-white/75">
                        {formatTime(currentTime)} / {formatTime(duration)}
                    </span>
                    <button
                        type="button"
                        onClick={() => void toggleFullscreen()}
                        className="ml-auto flex size-11 items-center justify-center border border-white/25 bg-black/55 text-white transition-[background-color,border-color,transform] duration-150 hover:border-primary hover:bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.96] motion-reduce:transition-none"
                        aria-label={en ? 'Enter full screen' : 'Ver en pantalla completa'}
                    >
                        <Maximize2 className="size-4" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    );
}
