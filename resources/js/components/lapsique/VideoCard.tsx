import { Badge } from '@/components/ui/badge';
import { glassCardVariants, tagBadgeClass } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';
import { Link, usePage } from '@inertiajs/react';
import type { PageProps, VideoItem } from '@/types';

interface VideoCardProps {
    video: VideoItem;
}

export function VideoCard({ video }: VideoCardProps) {
    const { ziggy } = usePage<PageProps>().props;
    const isOriginal = video.tags?.includes('psique-originals');

    return (
        <Link
            href={route('videos.show', { video: video.slug }, false, ziggy)}
            className={cn(
                glassCardVariants(),
                'group block overflow-hidden transition-transform duration-150 hover:scale-[1.01]',
            )}
        >
            {video.thumbnail_url && (
                <div className="relative aspect-video overflow-hidden">
                    <img
                        src={video.thumbnail_url}
                        alt={video.title}
                        className="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                    <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-background/80 via-transparent to-transparent" />
                </div>
            )}
            <div className="p-4">
                {isOriginal && (
                    <Badge className={cn('mb-2 hover:opacity-90', tagBadgeClass('psique-originals'))}>
                        PSIQUE Originals
                    </Badge>
                )}
                <h3 className="line-clamp-2 font-semibold leading-snug text-foreground">
                    {video.title}
                </h3>
            </div>
        </Link>
    );
}
