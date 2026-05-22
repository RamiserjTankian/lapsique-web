import type { HeroProofVideoData } from '@/types';

interface HeroProofVideoCardProps {
    video: HeroProofVideoData;
    className?: string;
    eager?: boolean;
}

export function HeroProofVideoCard({ video, className = '', eager = false }: HeroProofVideoCardProps) {
    const isPlayable = video.media_type === 'youtube' || video.media_type === 'video';

    return (
        <figure
            className={`group relative min-h-[180px] overflow-hidden rounded-xl border border-border/70 bg-black ${className}`}
        >
            {video.media_type === 'youtube' && video.embed_url ? (
                <iframe
                    title={video.title ?? 'Video de portafolio'}
                    src={video.embed_url}
                    className="absolute inset-0 h-full w-full object-cover"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    loading={eager ? 'eager' : 'lazy'}
                />
            ) : video.media_type === 'video' && video.playback_url ? (
                <video
                    src={video.playback_url}
                    poster={video.poster_url ?? undefined}
                    className="absolute inset-0 h-full w-full object-cover"
                    autoPlay
                    loop
                    muted
                    playsInline
                    preload={eager ? 'auto' : 'metadata'}
                />
            ) : video.poster_url ? (
                <img
                    src={video.poster_url}
                    alt={video.title ?? 'Portafolio visual de Lapsique'}
                    className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                    loading={eager ? 'eager' : 'lazy'}
                />
            ) : null}
            <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/78 via-black/12 to-transparent" />
            {video.title && (
                <figcaption className="absolute inset-x-0 bottom-0 p-3 text-xs font-semibold text-white">
                    {video.title}
                </figcaption>
            )}
        </figure>
    );
}
