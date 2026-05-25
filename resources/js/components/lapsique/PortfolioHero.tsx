import { Play } from 'lucide-react';
import { motion } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { PortfolioMediaViewer } from '@/components/lapsique/PortfolioMediaViewer';
import { fadeUp } from '@/lib/motion';
import type { PortfolioItemData } from '@/types';

interface PortfolioHeroProps {
    item: PortfolioItemData;
    onExplore: () => void;
}

export function PortfolioHero({ item, onExplore }: PortfolioHeroProps) {
    const isPlayable = item.media_type === 'youtube' || item.media_type === 'video';
    const previewUrl = isPlayable
        ? (item.poster_url ?? item.asset_url)
        : (item.asset_url ?? item.poster_url);

    return (
        <motion.section
            variants={fadeUp}
            initial="hidden"
            animate="visible"
            className="relative mb-12 mt-20 overflow-hidden rounded-[2rem] border border-border/70 glass-panel sm:mt-24"
        >
            <div className="grid gap-0 lg:grid-cols-[1.2fr_1fr]">
                <div className="relative min-h-[280px] overflow-hidden bg-muted/30 lg:min-h-[360px]">
                    {isPlayable && item.embed_url ? (
                        <PortfolioMediaViewer
                            item={item}
                            className="h-full min-h-[280px] w-full border-0 lg:min-h-[360px]"
                        />
                    ) : isPlayable && item.playback_url ? (
                        <video
                            src={item.playback_url}
                            poster={item.poster_url ?? undefined}
                            muted
                            autoPlay
                            loop
                            playsInline
                            preload="metadata"
                            className="absolute inset-0 h-full w-full object-cover"
                        />
                    ) : previewUrl ? (
                        <img
                            src={previewUrl}
                            alt={item.title ?? 'Destacado'}
                            className="absolute inset-0 h-full w-full object-cover"
                        />
                    ) : null}
                    {isPlayable && (
                        <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-background/40 to-transparent" />
                    )}
                </div>

                <div className="flex flex-col justify-center p-6 md:p-8">
                    <span className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                        Destacado del portafolio
                    </span>
                    <Button variant="cinematic" size="lg" className="mt-6 w-fit rounded-xl" onClick={onExplore}>
                        {isPlayable ? (
                            <>
                                <Play className="mr-2 h-4 w-4 fill-current" />
                                Ver en galería
                            </>
                        ) : (
                            'Explorar galería'
                        )}
                    </Button>
                </div>
            </div>
        </motion.section>
    );
}
