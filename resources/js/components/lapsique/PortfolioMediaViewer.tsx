import { motion } from 'framer-motion';
import type { PortfolioItemData } from '@/types';

interface PortfolioMediaViewerProps {
    item: PortfolioItemData;
    className?: string;
}

export function PortfolioMediaViewer({ item, className }: PortfolioMediaViewerProps) {
    if (item.media_type === 'youtube' && item.embed_url) {
        return (
            <iframe
                title={item.title ?? 'Video de portafolio'}
                src={item.embed_url}
                className={className ?? 'aspect-video w-full rounded-xl border border-border/60'}
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowFullScreen
            />
        );
    }

    if (item.media_type === 'video' && item.playback_url) {
        return (
            <video
                key={item.id}
                src={item.playback_url}
                poster={item.poster_url ?? undefined}
                controls
                playsInline
                className={className ?? 'max-h-[75vh] w-full rounded-xl bg-black'}
            />
        );
    }

    if (item.asset_url) {
        return (
            <motion.img
                key={item.id}
                src={item.asset_url}
                alt={item.title ?? ''}
                initial={{ opacity: 0, scale: 0.98 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.98 }}
                transition={{ duration: 0.25 }}
                className={className ?? 'max-h-[75vh] w-full object-contain'}
            />
        );
    }

    return null;
}
