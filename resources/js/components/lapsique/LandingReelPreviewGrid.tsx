import { Play } from 'lucide-react';
import { ReelLoopCard } from '@/components/lapsique/ReelLoopCard';
import { cn } from '@/lib/utils';
import type { LandingVideoEntry } from '@/types';

function isPlayableLandingVideo(
    video: LandingVideoEntry | null | undefined,
): video is LandingVideoEntry {
    return Boolean(video?.src);
}

export function LandingReelPreviewGrid({
    videos,
    className,
    bookingSource = 'reel_preview',
}: {
    videos: Array<LandingVideoEntry | null | undefined>;
    className?: string;
    bookingSource?: string;
}) {
    const reelPreviews = videos.filter(isPlayableLandingVideo);

    if (reelPreviews.length === 0) {
        return null;
    }

    return (
        <div
            className={cn(
                'grid gap-3',
                reelPreviews.length > 1 ? 'sm:grid-cols-2' : 'max-w-xs mx-auto',
                className,
            )}
        >
            {reelPreviews.map((video) => (
                <ReelLoopCard
                    key={video.id ?? video.src}
                    src={video.src}
                    poster={video.poster}
                    title={video.title ?? undefined}
                    bookingSource={bookingSource}
                    articleClassName="rounded-xl border border-border/70 bg-black shadow-lg"
                    videoClassName="group-hover:scale-[1.04]"
                    footer={
                        <div className="pointer-events-none absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 p-3">
                            {video.title ? (
                                <span className="max-w-[70%] truncate text-xs font-medium text-white/90">
                                    {video.title}
                                </span>
                            ) : (
                                <span />
                            )}
                            <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/20 bg-black/35 text-primary backdrop-blur">
                                <Play className="h-3 w-3 fill-current" />
                            </span>
                        </div>
                    }
                />
            ))}
        </div>
    );
}
