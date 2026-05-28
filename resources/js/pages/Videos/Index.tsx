import { Head } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { VideoCard } from '@/components/lapsique/VideoCard';
import { VideosFeaturedHero } from '@/components/lapsique/VideosFeaturedHero';
import { PaginationLinks } from '@/components/lapsique/PaginationLinks';
import { useTranslations } from '@/hooks/useTranslations';
import type { Paginated, VideoItem } from '@/types';

interface VideosIndexProps {
    featuredVideo: VideoItem | null;
    videos: Paginated<VideoItem>;
    highlightedDjName?: string | null;
}

export default function VideosIndex({ featuredVideo, videos, highlightedDjName }: VideosIndexProps) {
    const { t } = useTranslations();

    return (
        <SiteLayout>
            <Head title={t('pages.videos.title')} />
            {featuredVideo && <VideosFeaturedHero video={featuredVideo} />}
            <GlassSection
                eyebrow={t('pages.videos.library')}
                title={featuredVideo ? t('pages.videos.more') : t('pages.videos.title')}
                description={
                    highlightedDjName
                        ? t('pages.videos.highlighting_dj', { name: highlightedDjName })
                        : t('pages.videos.index_description')
                }
            >
                {(videos?.data ?? []).length > 0 ? (
                    <>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {(videos?.data ?? []).map((video) => (
                                <VideoCard key={video.id} video={video} />
                            ))}
                        </div>
                        <PaginationLinks links={videos?.links ?? []} />
                    </>
                ) : (
                    !featuredVideo && (
                        <p className="text-center text-muted-foreground">{t('pages.videos.index_empty')}</p>
                    )
                )}
            </GlassSection>
        </SiteLayout>
    );
}
