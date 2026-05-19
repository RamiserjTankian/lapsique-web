import { Head } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { VideoCard } from '@/components/lapsique/VideoCard';
import { VideosFeaturedHero } from '@/components/lapsique/VideosFeaturedHero';
import { PaginationLinks } from '@/components/lapsique/PaginationLinks';
import type { Paginated, VideoItem } from '@/types';

interface VideosIndexProps {
    featuredVideo: VideoItem | null;
    videos: Paginated<VideoItem>;
    highlightedDjName?: string | null;
}

export default function VideosIndex({ featuredVideo, videos, highlightedDjName }: VideosIndexProps) {
    return (
        <SiteLayout>
            <Head title="Videos" />
            {featuredVideo && <VideosFeaturedHero video={featuredVideo} />}
            <GlassSection
                eyebrow="Biblioteca"
                title={featuredVideo ? 'Más videos' : 'Videos'}
                description={
                    highlightedDjName
                        ? `Sets y producción audiovisual — destacando ${highlightedDjName}`
                        : 'Sets, aftermovies y producción audiovisual.'
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
                        <p className="text-center text-muted-foreground">No hay videos publicados aún.</p>
                    )
                )}
            </GlassSection>
        </SiteLayout>
    );
}
