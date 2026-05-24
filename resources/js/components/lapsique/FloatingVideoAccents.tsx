import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import type { LandingVideoEntry } from '@/types';
import { cn } from '@/lib/utils';

interface FloatingVideoAccentsProps {
    videos: LandingVideoEntry[];
    className?: string;
}

export function FloatingVideoAccents({ videos, className }: FloatingVideoAccentsProps) {
    if (videos.length === 0) {
        return null;
    }

    const positions = [
        'right-2 top-3 hidden w-[min(18vw,156px)] rotate-3 xl:block',
        'left-2 top-32 hidden w-[min(16vw,140px)] -rotate-2 xl:top-40 xl:block',
    ] as const;

    return (
        <div
            className={cn('pointer-events-none absolute inset-0 z-0 overflow-hidden', className)}
            aria-hidden
        >
            {videos.slice(0, 2).map((video, index) => (
                <div
                    key={video.src}
                    className={cn(
                        'absolute aspect-[9/16] overflow-hidden rounded-2xl border border-white/10 shadow-[0_24px_80px_rgb(0_0_0/0.45)]',
                        positions[index] ?? positions[0],
                    )}
                >
                    <AutoplayVideo
                        src={video.src}
                        poster={video.poster}
                        className="h-full w-full"
                        videoClassName="object-cover"
                        pauseWhenOffscreen
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                </div>
            ))}
        </div>
    );
}
