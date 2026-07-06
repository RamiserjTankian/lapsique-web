import { cn } from '@/lib/utils';

interface VideoLoadingCoverProps {
    poster?: string | null;
    title?: string | null;
    className?: string;
    mediaClassName?: string;
    eager?: boolean;
}

export function VideoLoadingCover({
    poster,
    title,
    className,
    mediaClassName,
    eager = false,
}: VideoLoadingCoverProps) {
    return (
        <div
            className={cn(
                'pointer-events-none absolute inset-0 z-[1] overflow-hidden bg-black',
                className,
            )}
            data-video-loading-cover=""
            aria-hidden
        >
            {poster ? (
                <img
                    src={poster}
                    alt={title ?? ''}
                    className={cn('h-full w-full object-cover object-center', mediaClassName)}
                    loading={eager ? 'eager' : 'lazy'}
                    fetchPriority={eager ? 'high' : undefined}
                    decoding="async"
                />
            ) : (
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_34%_22%,oklch(0.74_0.13_165/0.24),transparent_36%),linear-gradient(145deg,oklch(0.13_0.02_260),oklch(0.05_0.01_260))]" />
            )}

            <div className="absolute inset-0 bg-[linear-gradient(110deg,transparent_0%,transparent_34%,oklch(1_0_0/0.18)_48%,transparent_62%,transparent_100%)] animate-[portfolio-shimmer_1.35s_ease-in-out_infinite]" />
            <div className="absolute bottom-3 left-3 flex items-center gap-2 rounded-full border border-white/14 bg-black/42 px-2.5 py-1.5 backdrop-blur">
                <span className="h-2 w-2 rounded-full bg-primary shadow-[0_0_16px_rgb(16_185_129/0.85)]" />
                <span className="h-1.5 w-10 rounded-full bg-white/22" />
            </div>
        </div>
    );
}
