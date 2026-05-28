import { motion } from 'framer-motion';
import { Badge } from '@/components/ui/badge';
import { glassCardVariants } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';
import { Link, usePage } from '@inertiajs/react';
import { fadeUp } from '@/lib/motion';
import type { PageProps, VideoItem } from '@/types';

interface VideoCardCompactProps {
    video: VideoItem;
    index?: number;
    className?: string;
}

export function VideoCardCompact({ video, index = 0, className }: VideoCardCompactProps) {
    const { ziggy } = usePage<PageProps>().props;
    const isOriginal = video.tags?.includes('psique-originals');

    return (
        <motion.div
            variants={fadeUp}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            custom={index}
            className={cn('shrink-0 snap-start', className)}
        >
            <Link
                href={route('videos.show', { video: video.slug }, false, ziggy)}
                className={cn(
                    glassCardVariants(),
                    'group relative flex w-[min(72vw,260px)] overflow-hidden sm:w-[280px]',
                    'transition-shadow duration-300 hover:shadow-[0_0_40px_oklch(0.78_0.14_75/0.15)]',
                )}
            >
                <div className="relative aspect-[16/10] w-[42%] shrink-0 overflow-hidden">
                    {video.thumbnail_url ? (
                        <img
                            src={video.thumbnail_url}
                            alt=""
                            className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    ) : (
                        <div className="h-full w-full bg-muted/40" />
                    )}
                </div>
                <div className="flex min-w-0 flex-1 flex-col justify-center gap-1.5 p-3">
                    {isOriginal && (
                        <Badge
                            variant="secondary"
                            className="w-fit border-purple-500/30 bg-purple-500/15 px-1.5 py-0 text-[10px] text-purple-200"
                        >
                            PSIQUE
                        </Badge>
                    )}
                    <h3 className="line-clamp-2 text-sm font-semibold leading-snug text-foreground">
                        {video.title}
                    </h3>
                </div>
            </Link>
        </motion.div>
    );
}
