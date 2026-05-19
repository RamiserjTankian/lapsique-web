import { Link, usePage } from '@inertiajs/react';
import { Play } from 'lucide-react';
import { motion } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { tagBadgeClass } from '@/lib/variants';
import { route } from '@/lib/route';
import { fadeUp } from '@/lib/motion';
import { cn } from '@/lib/utils';
import type { PageProps, VideoItem } from '@/types';

interface VideosFeaturedHeroProps {
    video: VideoItem;
}

export function VideosFeaturedHero({ video }: VideosFeaturedHeroProps) {
    const { ziggy } = usePage<PageProps>().props;
    const djsList = Array.isArray(video.djs)
        ? video.djs
        : ((video.djs as { data?: VideoItem['djs'] })?.data ?? []);
    const primaryDj = djsList[0];
    const isOriginal = video.tags?.includes('psique-originals');

    return (
        <motion.section
            variants={fadeUp}
            initial="hidden"
            animate="visible"
            className="relative mb-12 mt-20 overflow-hidden rounded-[2rem] border border-border/70 glass-panel sm:mt-24"
        >
            <div className="grid gap-0 lg:grid-cols-[1.35fr_1fr]">
                <Link
                    href={route('videos.show', { video: video.slug }, false, ziggy)}
                    className="group relative block aspect-video overflow-hidden lg:aspect-auto lg:min-h-[320px]"
                >
                    {video.thumbnail_url ? (
                        <img
                            src={video.thumbnail_url}
                            alt={video.title}
                            className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                        />
                    ) : (
                        <div className="absolute inset-0 bg-muted/40" />
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-background/90 via-background/20 to-transparent" />
                    <span className="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                        <span className="flex h-16 w-16 items-center justify-center rounded-full border border-primary/40 bg-primary/20 text-primary backdrop-blur-md">
                            <Play className="ml-1 h-7 w-7 fill-current" />
                        </span>
                    </span>
                </Link>

                <div className="flex flex-col justify-center p-6 md:p-8">
                    <span className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                        Destacado
                    </span>
                    {isOriginal && (
                        <span
                            className={cn(
                                'mt-3 inline-flex w-fit rounded-full border px-3 py-1 text-xs font-medium',
                                tagBadgeClass('psique-originals'),
                            )}
                        >
                            PSIQUE Originals
                        </span>
                    )}
                    <h2 className="font-display mt-3 text-2xl font-bold leading-tight text-foreground md:text-3xl">
                        {video.title}
                    </h2>
                    {video.description && (
                        <p className="mt-3 line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                            {video.description}
                        </p>
                    )}

                    {primaryDj && (
                        <div className="mt-6 flex items-start gap-3 rounded-xl border border-border/60 bg-secondary/50 p-4">
                            <Avatar className="h-12 w-12 border border-border/60">
                                <AvatarImage src={primaryDj.avatar_url ?? undefined} alt={primaryDj.name} />
                                <AvatarFallback>{primaryDj.name.slice(0, 2).toUpperCase()}</AvatarFallback>
                            </Avatar>
                            <div className="min-w-0 flex-1">
                                <p className="text-[10px] uppercase tracking-widest text-muted-foreground">Artista</p>
                                <Link
                                    href={route('djs.show', { dj: primaryDj.slug }, false, ziggy)}
                                    className="font-display text-lg font-semibold text-foreground hover:text-primary"
                                >
                                    {primaryDj.name}
                                </Link>
                                {primaryDj.bio && (
                                    <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">{primaryDj.bio}</p>
                                )}
                            </div>
                        </div>
                    )}

                    {video.tags && video.tags.length > 0 && (
                        <div className="mt-6 flex flex-wrap gap-2">
                            {video.tags.slice(0, 4).map((tag) => (
                                <SpecBadge key={tag}>{tag}</SpecBadge>
                            ))}
                        </div>
                    )}

                    <Button variant="cinematic" size="lg" className="mt-6 w-fit rounded-xl" asChild>
                        <Link href={route('videos.show', { video: video.slug }, false, ziggy)}>
                            Ver video completo
                        </Link>
                    </Button>
                </div>
            </div>
        </motion.section>
    );
}
