import { useRef } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { motion } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { VideoCardCompact } from '@/components/lapsique/VideoCardCompact';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { staggerContainer } from '@/lib/motion';
import type { VideoItem } from '@/types';

interface VideoStripProps {
    videos: VideoItem[];
    className?: string;
}

export function VideoStrip({ videos, className }: VideoStripProps) {
    const scrollRef = useRef<HTMLDivElement>(null);
    const sectionRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', { section: 'videos' });

    const scroll = (dir: 'left' | 'right') => {
        const el = scrollRef.current;
        if (!el) return;
        const amount = el.clientWidth * 0.85;
        el.scrollBy({ left: dir === 'left' ? -amount : amount, behavior: 'smooth' });
    };

    if (videos.length === 0) return null;

    return (
        <div ref={sectionRef} className={className}>
            <div className="mb-3 flex items-center justify-end gap-1 sm:absolute sm:right-0 sm:top-0 sm:mb-0">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 rounded-full border border-border/60 bg-background/40 backdrop-blur-sm"
                    onClick={() => scroll('left')}
                    aria-label="Anterior"
                >
                    <ChevronLeft className="h-4 w-4" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 rounded-full border border-border/60 bg-background/40 backdrop-blur-sm"
                    onClick={() => scroll('right')}
                    aria-label="Siguiente"
                >
                    <ChevronRight className="h-4 w-4" />
                </Button>
            </div>

            <motion.div
                ref={scrollRef}
                variants={staggerContainer}
                initial="hidden"
                whileInView="visible"
                viewport={{ once: true, margin: '-20px' }}
                className="cinematic-scroll flex gap-3 overflow-x-auto pb-2 pt-1 scroll-smooth snap-x snap-mandatory [-webkit-overflow-scrolling:touch]"
            >
                {videos.map((video, i) => (
                    <VideoCardCompact key={video.id} video={video} index={i} />
                ))}
            </motion.div>
        </div>
    );
}
