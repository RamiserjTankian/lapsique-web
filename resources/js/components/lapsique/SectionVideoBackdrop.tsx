import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import type { LandingVideoEntry } from '@/types';
import { cn } from '@/lib/utils';

interface SectionVideoBackdropProps {
    video: LandingVideoEntry | null | undefined;
    className?: string;
    overlayClassName?: string;
    eager?: boolean;
}

export function SectionVideoBackdrop({
    video,
    className,
    overlayClassName,
    eager = false,
}: SectionVideoBackdropProps) {
    if (!video) {
        return null;
    }

    return (
        <div
            className={cn('pointer-events-none absolute inset-0 overflow-hidden', className)}
            aria-hidden
        >
            <AutoplayVideo
                src={video.src}
                poster={video.poster}
                className="absolute inset-0 h-full w-full"
                videoClassName="h-full w-full object-cover opacity-35"
                eager={eager}
                pauseWhenOffscreen
            />
            <div
                className={cn(
                    'absolute inset-0 bg-gradient-to-b from-background/88 via-background/78 to-background/94',
                    overlayClassName,
                )}
            />
        </div>
    );
}
