import { useMemo, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, PlayCircle } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import { cn } from '@/lib/utils';
import { tagBadgeClass } from '@/lib/variants';
import { videoSurfaceFrameClass } from '@/lib/videoSurface';
import type { PageProps, VideoItem } from '@/types';

interface DjVideoShowcaseProps {
    videos: VideoItem[];
}

export function DjVideoShowcase({ videos }: DjVideoShowcaseProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const [activeId, setActiveId] = useState(videos[0]?.id);

    const activeVideo = useMemo(
        () => videos.find((video) => video.id === activeId) ?? videos[0],
        [activeId, videos],
    );

    if (!activeVideo) {
        return null;
    }

    const embedId = getYoutubeId(activeVideo);
    const hasPlaylist = videos.length > 1;
    const isOriginal = activeVideo.tags?.includes('psique-originals');

    return (
        <section className="rounded-2xl border border-border/60 bg-card/70 p-4 shadow-[0_18px_50px_oklch(0.2_0.03_260/0.08)] sm:p-5 md:p-6">
            <div className="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 className="font-display text-2xl font-bold tracking-tight text-foreground">
                        {t('pages.djs.sets_videos')}
                    </h2>
                    <p className="mt-1 text-xs font-semibold uppercase tracking-[0.22em] text-primary">
                        PSIQUE Originals
                    </p>
                </div>
                <Link
                    href={route('videos.show', { video: activeVideo.slug }, false, ziggy)}
                    className="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background/70 px-3 py-1.5 text-xs font-semibold text-foreground transition hover:border-primary/40 hover:text-primary"
                >
                    {t('pages.videos.watch_full')}
                    <ArrowUpRight className="h-3.5 w-3.5" />
                </Link>
            </div>

            <div className={cn(videoSurfaceFrameClass, 'relative aspect-video rounded-2xl')}>
                {embedId ? (
                    <iframe
                        key={activeVideo.id}
                        title={activeVideo.title}
                        src={`https://www.youtube.com/embed/${embedId}`}
                        className="h-full w-full"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowFullScreen
                    />
                ) : (
                    <Link
                        href={route('videos.show', { video: activeVideo.slug }, false, ziggy)}
                        className="group relative block h-full w-full overflow-hidden"
                    >
                        {activeVideo.thumbnail_url ? (
                            <img
                                src={activeVideo.thumbnail_url}
                                alt={activeVideo.title}
                                className="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                                loading="lazy"
                            />
                        ) : (
                            <div className="h-full w-full bg-muted/40" />
                        )}
                        <span className="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent" />
                        <span className="absolute inset-0 grid place-items-center">
                            <PlayCircle className="h-16 w-16 text-white drop-shadow-lg" />
                        </span>
                    </Link>
                )}
            </div>

            <div className="mt-4 rounded-xl border border-border/60 bg-secondary/35 p-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                        {isOriginal && (
                            <Badge className={cn('mb-2 hover:opacity-90', tagBadgeClass('psique-originals'))}>
                                PSIQUE Originals
                            </Badge>
                        )}
                        <h3 className="font-display text-xl font-bold leading-tight text-foreground md:text-2xl">
                            {activeVideo.title}
                        </h3>
                    </div>
                </div>
                {activeVideo.description && (
                    <p className="mt-3 line-clamp-3 max-w-3xl text-sm leading-relaxed text-muted-foreground">
                        {activeVideo.description}
                    </p>
                )}
            </div>

            {hasPlaylist && (
                <div className="mt-4 flex snap-x gap-3 overflow-x-auto pb-2">
                    {videos.map((video) => {
                        const selected = video.id === activeVideo.id;

                        return (
                            <button
                                key={video.id}
                                type="button"
                                onClick={() => setActiveId(video.id)}
                                className={cn(
                                    'group relative flex w-[min(76vw,300px)] shrink-0 snap-start overflow-hidden rounded-xl border text-left transition sm:w-[320px]',
                                    selected
                                        ? 'border-primary bg-primary/10 shadow-[0_14px_32px_oklch(0.66_0.16_75/0.18)]'
                                        : 'border-border/70 bg-background/70 hover:border-primary/45',
                                )}
                                aria-pressed={selected}
                            >
                                <span className="relative aspect-video w-32 shrink-0 overflow-hidden bg-black sm:w-36">
                                    {video.thumbnail_url ? (
                                        <img
                                            src={video.thumbnail_url}
                                            alt=""
                                            className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            loading="lazy"
                                        />
                                    ) : (
                                        <span className="block h-full w-full bg-muted/40" />
                                    )}
                                    <span className="absolute inset-0 grid place-items-center bg-black/20">
                                        <PlayCircle className="h-7 w-7 text-white" />
                                    </span>
                                </span>
                                <span className="flex min-w-0 flex-1 flex-col justify-center p-3">
                                    <span className="text-[10px] font-semibold uppercase tracking-[0.18em] text-primary">
                                        PSIQUE
                                    </span>
                                    <span className="mt-1 line-clamp-2 text-sm font-semibold leading-snug text-foreground">
                                        {video.title}
                                    </span>
                                </span>
                            </button>
                        );
                    })}
                </div>
            )}
        </section>
    );
}

function getYoutubeId(video: VideoItem): string | null {
    if (video.youtube_id) {
        return video.youtube_id;
    }

    const match = video.youtube_url?.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/)|v=)([\w-]{11})/);

    return match?.[1] ?? null;
}
