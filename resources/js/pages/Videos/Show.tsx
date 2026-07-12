import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, Play } from 'lucide-react';
import { useState } from 'react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { PageProps, VideoItem } from '@/types';

interface VideosShowProps {
    video: VideoItem;
    relatedVideos: VideoItem[];
    instagramUrl: string;
}

export default function VideosShow({ video, relatedVideos, instagramUrl }: VideosShowProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';
    const [playing, setPlaying] = useState(false);
    const id = video.youtube_id || youtubeId(video.youtube_url);

    return (
        <SiteLayout>
            <SeoHead />
            <article>
                <header className="relative left-1/2 w-screen -translate-x-1/2 bg-[#07090b] py-12 text-white md:py-16">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <p className="alpha-kicker text-primary">Lapsique Originals / Psique Session</p>
                        <h1 className="mt-5 max-w-5xl text-4xl font-semibold leading-[0.9] text-white sm:text-5xl md:text-7xl">{video.title}</h1>
                        <div className="mt-7 flex flex-wrap gap-x-7 gap-y-3 border-t border-white/20 pt-5 font-mono text-xs uppercase tracking-[0.12em] text-white/50">
                            {video.location ? <span>{video.location}</span> : null}
                            {video.published_at ? <span>{new Intl.DateTimeFormat(en ? 'en-US' : 'es-MX', { dateStyle: 'long' }).format(new Date(video.published_at))}</span> : null}
                            {video.djs?.map((dj) => <Link key={dj.id} href={route('djs.show', { dj: dj.slug }, false, ziggy)} className="text-primary hover:text-white">{dj.name}</Link>)}
                        </div>
                    </div>
                </header>

                <section className="relative left-1/2 w-screen -translate-x-1/2 bg-black">
                    <div className="mx-auto max-w-[1440px]">
                        <div className="relative aspect-video bg-black">
                            {playing && id ? (
                                <iframe title={video.title} src={`https://www.youtube-nocookie.com/embed/${id}?autoplay=1`} className="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen />
                            ) : video.thumbnail_url ? (
                                <button type="button" onClick={() => setPlaying(true)} className="group absolute inset-0" disabled={!id} aria-label={en ? 'Play session' : 'Reproducir sesión'}>
                                    <img src={video.thumbnail_url} alt={video.title} className="h-full w-full object-cover opacity-80 transition group-hover:opacity-100" />
                                    {id ? <span className="absolute left-1/2 top-1/2 flex size-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center bg-primary text-white md:size-20"><Play className="size-8 fill-current" /></span> : null}
                                </button>
                            ) : null}
                        </div>
                    </div>
                </section>

                <section className="grid gap-10 border-b border-foreground/20 py-14 md:grid-cols-[0.34fr_0.66fr] md:py-20">
                    <div>
                        <p className="alpha-kicker text-primary">About / Session</p>
                        <h2 className="mt-4 text-4xl font-semibold">{en ? 'Sound in context.' : 'El sonido en contexto.'}</h2>
                    </div>
                    <div>
                        <p className="max-w-3xl text-lg leading-relaxed text-muted-foreground">{video.description || (en ? 'A complete DJ set produced and documented by Lapsique Media.' : 'Un DJ set completo producido y documentado por Lapsique Media.')}</p>
                        <a href={instagramUrl} target="_blank" rel="noopener noreferrer" className="mt-6 inline-flex items-center gap-2 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-primary hover:text-foreground">Instagram <ArrowUpRight className="size-4" /></a>
                    </div>
                </section>

                {relatedVideos.length > 0 ? (
                    <section className="py-14 md:py-20">
                        <p className="alpha-kicker text-primary">Next / Psique Sessions</p>
                        <h2 className="mt-4 text-4xl font-semibold md:text-6xl">{en ? 'Continue through the archive.' : 'Continúa por el archivo.'}</h2>
                        <div className="mt-8 grid gap-px bg-foreground/20 md:grid-cols-3">
                            {relatedVideos.map((item) => (
                                <Link key={item.id} href={route('videos.show', { video: item.slug }, false, ziggy)} className="group bg-background">
                                    {item.thumbnail_url ? <img src={item.thumbnail_url} alt={item.title} loading="lazy" className="aspect-video w-full object-cover" /> : null}
                                    <h3 className="p-5 text-xl font-semibold leading-tight group-hover:text-primary">{item.title}</h3>
                                </Link>
                            ))}
                        </div>
                    </section>
                ) : null}

                <section className="mb-14 grid gap-7 border border-foreground/20 p-7 md:grid-cols-[1fr_auto] md:items-end md:p-10">
                    <div>
                        <p className="alpha-kicker text-primary">Lapsique Media / Production</p>
                        <h2 className="mt-4 max-w-3xl text-4xl font-semibold leading-[0.92] md:text-5xl">{en ? 'Your next set can live here.' : 'Tu próximo set puede vivir aquí.'}</h2>
                    </div>
                    <Link href={route('djset.show', undefined, false, ziggy)} className="inline-flex min-h-13 items-center justify-center bg-foreground px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-background hover:bg-primary hover:text-white">{en ? 'Record my DJ set' : 'Grabar mi DJ set'}</Link>
                </section>
            </article>
        </SiteLayout>
    );
}

function youtubeId(url?: string | null): string | null {
    if (!url) return null;
    return url.match(/(?:v=|youtu\.be\/|embed\/)([\w-]{11})/)?.[1] ?? null;
}
