import { motion } from 'framer-motion';
import { glassCardVariants } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';
import { Link, usePage } from '@inertiajs/react';
import { fadeUp } from '@/lib/motion';
import type { PageProps, PortfolioItemData } from '@/types';

interface PortfolioTileProps {
    item: PortfolioItemData;
    index?: number;
    onSelect?: (item: PortfolioItemData) => void;
}

export function PortfolioTile({ item, index = 0, onSelect }: PortfolioTileProps) {
    const { ziggy } = usePage<PageProps>().props;
    const previewUrl = getPortfolioPreviewUrl(item);

    const tileClassName = cn(
        glassCardVariants(),
        'group relative block aspect-square w-full overflow-hidden',
        onSelect &&
            'cursor-pointer text-left ring-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50',
    );

    const tileContent = (
        <>
            {previewUrl && (
                <img
                    src={previewUrl}
                    alt={item.title ?? item.type}
                    className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.06]"
                    loading="lazy"
                />
            )}
            <div className="absolute inset-0 bg-gradient-to-t from-background/20 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
        </>
    );

    return (
        <motion.div
            variants={fadeUp}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            custom={index}
        >
            {onSelect ? (
                <button type="button" onClick={() => onSelect(item)} className={tileClassName}>
                    {tileContent}
                </button>
            ) : (
                <Link href={route('portfolio.index', undefined, false, ziggy)} className={tileClassName}>
                    {tileContent}
                </Link>
            )}
        </motion.div>
    );
}

function getPortfolioPreviewUrl(item: PortfolioItemData): string | null {
    if (item.media_type === 'video' || item.media_type === 'youtube') {
        return item.poster_url ?? item.asset_url ?? null;
    }

    return item.asset_url ?? item.poster_url ?? null;
}
