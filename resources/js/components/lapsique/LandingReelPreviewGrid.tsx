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
                    key={video.src}
                    src={video.src}
                    poster={video.poster}
                    title={video.title ?? undefined}
                    bookingSource={bookingSource}
                    videoClassName="group-hover:scale-[1.04]"
                />
            ))}
        </div>
    );
}
