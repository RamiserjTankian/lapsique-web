import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { cn } from '@/lib/utils';

export function HeroBackgroundOverlays({ className }: { className?: string }) {
    return (
        <>
            <div
                className={cn(
                    'absolute inset-0 bg-[linear-gradient(90deg,oklch(0.11_0.02_280/0.97)_0%,oklch(0.11_0.02_280/0.84)_50%,oklch(0.11_0.02_280/0.46)_100%)]',
                    className,
                )}
            />
            <div className="absolute inset-0 bg-[linear-gradient(180deg,oklch(0.08_0.01_280/0.25)_0%,oklch(0.08_0.01_280/0.38)_54%,var(--background)_100%)]" />
        </>
    );
}

interface LoopingVideoBackgroundProps {
    src: string;
    poster?: string | null;
    className?: string;
    overlayClassName?: string;
    eager?: boolean;
    loopSegmentSeconds?: number;
}

export function LoopingVideoBackground({
    src,
    poster,
    className,
    overlayClassName,
    eager = false,
    loopSegmentSeconds,
}: LoopingVideoBackgroundProps) {
    return (
        <div className={cn('absolute inset-0 overflow-hidden', className)} aria-hidden>
            <AutoplayVideo
                src={src}
                poster={poster}
                className="absolute inset-0 h-full w-full"
                videoClassName="object-cover object-center"
                eager={eager}
                pauseWhenOffscreen={!eager}
                loopSegmentSeconds={loopSegmentSeconds}
            />
            <HeroBackgroundOverlays className={overlayClassName} />
        </div>
    );
}

interface PortfolioPhotoBackgroundProps {
    src: string;
    alt?: string | null;
    className?: string;
    eager?: boolean;
}

export function PortfolioPhotoBackground({
    src,
    alt = '',
    className,
    eager = false,
}: PortfolioPhotoBackgroundProps) {
    return (
        <div className={cn('absolute inset-0 overflow-hidden', className)} aria-hidden>
            <img
                src={src}
                alt={alt ?? ''}
                className="absolute inset-0 h-full w-full object-cover object-center"
                loading={eager ? 'eager' : 'lazy'}
                fetchPriority={eager ? 'high' : undefined}
                decoding="async"
            />
            <HeroBackgroundOverlays />
        </div>
    );
}
