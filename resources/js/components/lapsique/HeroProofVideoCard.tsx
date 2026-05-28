import { ReelLoopCard } from '@/components/lapsique/ReelLoopCard';
import { videoSurfaceFrameClass } from '@/lib/videoSurface';
import { useTranslations } from '@/hooks/useTranslations';
import type { HeroProofVideoData } from '@/types';

interface HeroProofVideoCardProps {
    video: HeroProofVideoData;
    className?: string;
    eager?: boolean;
}

export function HeroProofVideoCard({ video, className = '', eager = false }: HeroProofVideoCardProps) {
    const { t } = useTranslations();
    const portfolioVideoTitle = t('common.alt.portfolio_video');
    const portfolioVisualAlt = t('common.alt.portfolio_visual');
    const isLocalVideo = video.media_type === 'video' && Boolean(video.playback_url);
    const isYoutube = video.media_type === 'youtube' && Boolean(video.embed_url);
    const aspectClass = isYoutube ? 'aspect-video' : 'aspect-[9/16]';

    return (
        <figure
            className={`group relative w-full ${videoSurfaceFrameClass} ${aspectClass} ${className}`}
        >
            {isLocalVideo && video.playback_url ? (
                <ReelLoopCard
                    src={video.playback_url}
                    poster={video.poster_url}
                    title={portfolioVideoTitle}
                    bookingSource="hero_proof_reel"
                    articleClassName="absolute inset-0 h-full w-full rounded-none border-0"
                    fillContainer
                    eager={eager}
                    pauseWhenOffscreen={!eager}
                />
            ) : isYoutube && video.embed_url ? (
                <iframe
                    title={portfolioVideoTitle}
                    src={video.embed_url}
                    className="absolute inset-0 h-full w-full"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    loading={eager ? 'eager' : 'lazy'}
                />
            ) : video.poster_url ? (
                <img
                    src={video.poster_url}
                    alt={portfolioVisualAlt}
                    className="absolute inset-0 h-full w-full object-cover object-center transition duration-700 group-hover:scale-[1.03]"
                    loading={eager ? 'eager' : 'lazy'}
                />
            ) : null}
            {!isLocalVideo ? (
                <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/78 via-black/12 to-transparent" />
            ) : null}
        </figure>
    );
}
