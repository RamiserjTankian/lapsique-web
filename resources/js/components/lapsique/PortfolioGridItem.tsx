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
    return (
        <motion.button
            type="button"
            variants={fadeUp}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: '-40px' }}
            custom={index}
            onClick={() => onSelect(item)}
            className={cn(
                glassCardVariants(),
                'group relative block w-full aspect-square overflow-hidden text-left',
                'ring-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50',
                className,
            )}
        >
            {item.asset_url ? (
                <img
                    src={item.asset_url}
                    alt={item.title ?? item.type}
                    className="absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.06]"
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
            <motionCaption item={item} />
            <motionHoverRing />
        </motion.button>
    );
}

function motionOverlay() {
    return (
        <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-background via-background/20 to-transparent opacity-90 transition-opacity group-hover:opacity-100" />
    );
}

function motionCaption({ item }: { item: PortfolioItemData }) {
    return (
        <div className="absolute inset-x-0 bottom-0 translate-y-1 p-4 pt-16 transition-transform duration-300 group-hover:translate-y-0">
            {item.type && (
                <span className="font-mono text-[10px] uppercase tracking-[0.2em] text-primary">
                    {item.type}
                </span>
            )}
            <p className="mt-1 line-clamp-2 text-sm font-semibold text-foreground">
                {item.title || item.caption || 'Ver proyecto'}
            </p>
        </div>
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
