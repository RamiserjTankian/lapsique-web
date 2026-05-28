import { Head } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { glassCardVariants } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { useTranslations } from '@/hooks/useTranslations';
import type { VideoItem } from '@/types';

interface VideosShowProps {
    video: VideoItem & { description?: string | null };
    instagramUrl: string;
}

export default function VideosShow({ video, instagramUrl }: VideosShowProps) {
    const { t } = useTranslations();
    const embedId = video.youtube_url?.match(/(?:v=|\/)([\w-]{11})/)?.[1];

    return (
        <SiteLayout>
            <Head title={video.title} />
            <article className="py-12">
                {embedId && (
                    <div className={cn(glassCardVariants(), 'aspect-video overflow-hidden')}>
                        <iframe
                            title={video.title}
                            src={`https://www.youtube.com/embed/${embedId}`}
                            className="h-full w-full"
                            allowFullScreen
                        />
                    </div>
                )}
                <h1 className="font-display mt-8 text-3xl font-bold">{video.title}</h1>
                {video.description && (
                    <p className="mt-4 max-w-2xl text-muted-foreground">{video.description}</p>
                )}
                <a
                    href={instagramUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="mt-6 inline-block text-sm text-primary hover:underline"
                >
                    {t('pages.videos.show_instagram')}
                </a>
            </article>
        </SiteLayout>
    );
}
