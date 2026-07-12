import { Link, usePage } from '@inertiajs/react';
import { Play } from 'lucide-react';
import { PaginationLinks } from '@/components/lapsique/PaginationLinks';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { PageProps, Paginated, PortfolioItemData, VideoItem } from '@/types';

interface VideosIndexProps {
    featuredVideo: VideoItem | null;
    videos: Paginated<VideoItem>;
    highlightedDjName?: string | null;
    aftermovies: PortfolioItemData[];
}

export default function VideosIndex({ featuredVideo, videos, aftermovies }: VideosIndexProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';

    return (
        <SiteLayout>
            <SeoHead />
            <section className="relative left-1/2 w-screen -translate-x-1/2 bg-[#07090b] text-white">
                <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 md:py-24">
                    <p className="alpha-kicker text-primary">Lapsique Originals / Moving archive</p>
                    <div className="mt-6 grid gap-8 border-b border-white/20 pb-12 lg:grid-cols-[0.75fr_0.25fr] lg:items-end">
                        <h1 className="max-w-5xl text-6xl font-semibold leading-[0.84] text-white sm:text-7xl md:text-8xl">{en ? 'Stories, sets, and events in motion.' : 'Historias, sets y eventos en movimiento.'}</h1>
                        <p className="text-base leading-relaxed text-white/60">{en ? 'Psique Sessions, aftermovies, and audiovisual pieces produced by Lapsique Media.' : 'Psique Sessions, aftermovies y piezas audiovisuales producidas por Lapsique Media.'}</p>
                    </div>

                    {featuredVideo ? (
                        <Link href={route('videos.show', { video: featuredVideo.slug }, false, ziggy)} className="group mt-10 grid border border-white/20 md:grid-cols-[0.62fr_0.38fr]">
                            {featuredVideo.thumbnail_url ? <img src={featuredVideo.thumbnail_url} alt={featuredVideo.title} className="aspect-video h-full w-full object-cover" /> : null}
                            <div className="flex flex-col justify-between border-t border-white/20 p-7 md:border-l md:border-t-0 md:p-9">
                                <p className="alpha-kicker text-primary">Featured / Psique Session</p>
                                <div className="mt-14">
                                    <Play className="size-8 text-primary" />
                                    <h2 className="mt-5 text-3xl font-semibold leading-tight text-white group-hover:text-primary">{featuredVideo.title}</h2>
                                    {featuredVideo.location ? <p className="mt-4 text-sm text-white/50">{featuredVideo.location}</p> : null}
                                </div>
                            </div>
                        </Link>
                    ) : null}
                </div>
            </section>

            <section className="py-14 md:py-20">
                <p className="alpha-kicker text-primary">Psique Sessions / {String(videos.meta.total + (featuredVideo ? 1 : 0)).padStart(2, '0')}</p>
                <h2 className="mt-4 text-4xl font-semibold md:text-6xl">{en ? 'Complete DJ sets' : 'DJ sets completos'}</h2>
                {(videos.data ?? []).length > 0 ? (
                    <div className="mt-8 grid gap-px bg-foreground/20 md:grid-cols-2 lg:grid-cols-3">
                        {videos.data.map((video) => <VideoEditorialCard key={video.id} video={video} />)}
                    </div>
                ) : !featuredVideo ? <p className="mt-8 text-muted-foreground">{en ? 'No published sessions yet.' : 'Todavía no hay sesiones publicadas.'}</p> : null}
                <PaginationLinks links={videos.links ?? []} />
            </section>

            {aftermovies.length > 0 ? (
                <section id="aftermovies" className="relative left-1/2 w-screen -translate-x-1/2 scroll-mt-20 bg-[#111416] py-14 text-white md:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <p className="alpha-kicker text-primary">Aftermovies / Events</p>
                        <div className="mt-4 grid gap-6 border-b border-white/20 pb-7 md:grid-cols-[1fr_0.35fr] md:items-end">
                            <h2 className="text-4xl font-semibold md:text-6xl">{en ? 'Nightlife, venues, and crowds.' : 'Nightlife, locaciones y público.'}</h2>
                            <p className="text-sm leading-relaxed text-white/55">{en ? 'Short films that preserve the scale and atmosphere of each production.' : 'Piezas que conservan la escala y la atmósfera de cada producción.'}</p>
                        </div>
                        <div className="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {aftermovies.map((item) => (
                                <article key={item.id} className="border border-white/20 bg-black">
                                    {item.playback_url ? (
                                        <video src={item.playback_url} poster={item.poster_url ?? undefined} controls playsInline preload="none" className="aspect-video w-full object-cover" />
                                    ) : item.poster_url || item.asset_url ? (
                                        <img src={item.poster_url || item.asset_url || ''} alt={item.title || 'Lapsique aftermovie'} loading="lazy" className="aspect-video w-full object-cover" />
                                    ) : null}
                                    <div className="border-t border-white/15 p-5">
                                        <p className="alpha-kicker text-primary">Aftermovie</p>
                                        <h3 className="mt-3 text-xl font-semibold leading-tight text-white">{item.title || 'Lapsique Media'}</h3>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            ) : null}
        </SiteLayout>
    );
}

function VideoEditorialCard({ video }: { video: VideoItem }) {
    const { ziggy } = usePage<PageProps>().props;
    return (
        <Link href={route('videos.show', { video: video.slug }, false, ziggy)} className="group bg-background">
            {video.thumbnail_url ? <img src={video.thumbnail_url} alt={video.title} loading="lazy" className="aspect-video w-full object-cover" /> : null}
            <div className="p-5">
                <p className="alpha-kicker text-primary">Psique Session</p>
                <h3 className="mt-3 line-clamp-3 text-2xl font-semibold leading-tight group-hover:text-primary">{video.title}</h3>
                {video.djs?.length ? <p className="mt-3 text-sm text-muted-foreground">{video.djs.map((dj) => dj.name).join(' · ')}</p> : null}
            </div>
        </Link>
    );
}
