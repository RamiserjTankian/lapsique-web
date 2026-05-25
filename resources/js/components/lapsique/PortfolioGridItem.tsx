import { useState } from 'react';
import { motion } from 'framer-motion';
import { Play } from 'lucide-react';
import { glassCardVariants } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { fadeUp } from '@/lib/motion';
import type { PortfolioItemData } from '@/types';

interface PortfolioGridItemProps {
    item: PortfolioItemData;
    index?: number;
    onSelect: (item: PortfolioItemData) => void;
    className?: string;
}

export function PortfolioGridItem({
    item,
    index = 0,
    onSelect,
    className,
}: PortfolioGridItemProps) {
    const previewUrl = getPortfolioPreviewUrl(item);
    const canAutoplayPreview = item.media_type === 'video' && Boolean(item.playback_url);
    const [isLoaded, setIsLoaded] = useState(false);

    return (
        <motion.button
            type="button"
            variants={fadeUp}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: '-40px' }}
            custom={index}
            onClick={() => onSelect(item)}
            aria-label={`Abrir ${item.title ?? 'proyecto de portafolio'}`}
            className={cn(
                glassCardVariants(),
                'group relative block h-full min-h-[9rem] w-full overflow-hidden text-left',
                'ring-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50',
                className,
            )}
        >
            {!isLoaded && <PortfolioTileSkeleton />}
            {canAutoplayPreview ? (
                <video
                    src={item.playback_url ?? undefined}
                    poster={item.poster_url ?? undefined}
                    muted
                    autoPlay
                    loop
                    playsInline
                    preload="metadata"
                    onLoadedData={() => setIsLoaded(true)}
                    onCanPlay={() => setIsLoaded(true)}
                    className={cn(
                        'absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.04]',
                        isLoaded ? 'opacity-100' : 'opacity-0',
                    )}
                />
            ) : previewUrl ? (
                <img
                    src={previewUrl}
                    alt={item.title ?? item.type}
                    onLoad={() => setIsLoaded(true)}
                    onError={() => setIsLoaded(true)}
                    className={cn(
                        'absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.06]',
                        isLoaded ? 'opacity-100' : 'opacity-0',
                    )}
                    loading="lazy"
                />
            ) : (
                <div className="absolute inset-0 flex items-center justify-center bg-muted/30">
                    <span className="text-xs text-muted-foreground">Sin preview</span>
                </div>
            )}
            {(item.media_type === 'youtube' || item.media_type === 'video') && (
                <span className="pointer-events-none absolute inset-0 z-10 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                    <span className="flex h-12 w-12 items-center justify-center rounded-full border border-primary/40 bg-background/70 text-primary backdrop-blur-sm">
                        <Play className="ml-0.5 h-5 w-5 fill-current" />
                    </span>
                </span>
            )}
            <motionOverlay />
            <motionHoverRing />
        </motion.button>
    );
}

function PortfolioTileSkeleton() {
    return (
        <div className="absolute inset-0 overflow-hidden bg-muted/50">
            <div className="absolute inset-0 bg-[linear-gradient(110deg,transparent_0%,transparent_35%,oklch(1_0_0/0.18)_48%,transparent_62%,transparent_100%)] animate-[portfolio-shimmer_1.4s_ease-in-out_infinite]" />
            <div className="absolute inset-x-3 bottom-3 space-y-2">
                <div className="h-2 w-16 rounded-full bg-foreground/10" />
                <div className="h-3 w-2/3 rounded-full bg-foreground/10" />
            </div>
        </div>
    );
}

function motionOverlay() {
    return (
        <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-background/20 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
    );
}

function motionHoverRing() {
    return (
        <div className="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            <motionHoverRingInner />
        </div>
    );
}

function motionHoverRingInner() {
    return <motion.div className="absolute inset-0 ring-1 ring-inset ring-border/50" />;
}

function getPortfolioPreviewUrl(item: PortfolioItemData): string | null {
    if (item.media_type === 'video' || item.media_type === 'youtube') {
        return item.poster_url ?? item.asset_url ?? null;
    }

    return item.asset_url ?? item.poster_url ?? null;
}
