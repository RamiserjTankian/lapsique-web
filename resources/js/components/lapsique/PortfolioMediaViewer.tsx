import { useState } from 'react';
import { motion } from 'framer-motion';
import { cn } from '@/lib/utils';
import { useTranslations } from '@/hooks/useTranslations';
import type { PortfolioItemData } from '@/types';

interface PortfolioMediaViewerProps {
    item: PortfolioItemData;
    className?: string;
}

export function PortfolioMediaViewer({ item, className }: PortfolioMediaViewerProps) {
    const { t } = useTranslations();
    const [isLoaded, setIsLoaded] = useState(false);
    const portfolioVideoTitle = t('common.alt.portfolio_video');

    if (item.media_type === 'youtube' && item.embed_url) {
        return (
            <div className="relative w-full overflow-hidden rounded-xl bg-muted/40">
                {!isLoaded && <MediaViewerSkeleton />}
                <iframe
                    title={item.title ?? portfolioVideoTitle}
                    src={item.embed_url}
                    onLoad={() => setIsLoaded(true)}
                    className={cn(
                        className ?? 'aspect-video w-full rounded-xl border border-border/60',
                        isLoaded ? 'opacity-100' : 'opacity-0',
                    )}
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowFullScreen
                />
            </div>
        );
    }

    if (item.media_type === 'video' && item.playback_url) {
        return (
            <div className="relative w-full overflow-hidden rounded-xl bg-black">
                {!isLoaded && <MediaViewerSkeleton />}
                <video
                    key={item.id}
                    src={item.playback_url}
                    poster={item.poster_url ?? undefined}
                    controls
                    autoPlay
                    muted
                    playsInline
                    preload="metadata"
                    onLoadedData={() => setIsLoaded(true)}
                    onCanPlay={() => setIsLoaded(true)}
                    className={cn(
                        className ?? 'max-h-[75vh] w-full rounded-xl bg-black',
                        isLoaded ? 'opacity-100' : 'opacity-0',
                    )}
                />
            </div>
        );
    }

    if (item.asset_url) {
        return (
            <div className="relative flex min-h-[50vh] w-full items-center justify-center overflow-hidden bg-muted/30">
                {!isLoaded && <MediaViewerSkeleton />}
                <motion.img
                    key={item.id}
                    src={item.asset_url}
                    alt={item.title ?? ''}
                    onLoad={() => setIsLoaded(true)}
                    onError={() => setIsLoaded(true)}
                    initial={{ opacity: 0, scale: 0.98 }}
                    animate={{ opacity: isLoaded ? 1 : 0, scale: 1 }}
                    exit={{ opacity: 0, scale: 0.98 }}
                    transition={{ duration: 0.25 }}
                    className={className ?? 'max-h-[75vh] w-full object-contain'}
                />
            </div>
        );
    }

    return null;
}

function MediaViewerSkeleton() {
    return (
        <div className="absolute inset-0 z-10 overflow-hidden bg-muted/50">
            <div className="absolute inset-0 bg-[linear-gradient(110deg,transparent_0%,transparent_35%,oklch(1_0_0/0.16)_48%,transparent_62%,transparent_100%)] animate-[portfolio-shimmer_1.4s_ease-in-out_infinite]" />
        </div>
    );
}
