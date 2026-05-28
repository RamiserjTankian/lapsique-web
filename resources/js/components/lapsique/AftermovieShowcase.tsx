import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { videoSurfaceFrameClass } from '@/lib/videoSurface';
import type { LandingVideoEntry } from '@/types';

interface AftermovieShowcaseProps {
    videos: LandingVideoEntry[];
}

export function AftermovieShowcase({ videos }: AftermovieShowcaseProps) {
    if (videos.length === 0) {
        return null;
    }

    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {videos.map((video) => (
                <figure
                    key={video.src}
                    className={`group relative aspect-[9/16] ${videoSurfaceFrameClass}`}
                >
                    <AutoplayVideo
                        src={video.src}
                        poster={video.poster}
                        className="absolute inset-0 h-full w-full"
                        videoClassName="object-cover transition duration-700 group-hover:scale-[1.03]"
                        pauseWhenOffscreen
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/15 to-transparent" />
                </figure>
            ))}
        </div>
    );
}
