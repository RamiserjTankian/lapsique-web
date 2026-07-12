import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, AtSign, Music2, Play } from 'lucide-react';
import { DjGallery } from '@/components/lapsique/DjGallery';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { DjItem, PageProps, VideoItem } from '@/types';

interface DjsShowProps {
    dj: DjItem;
    videos: VideoItem[];
}

export default function DjsShow({ dj, videos }: DjsShowProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';
    const hero = dj.cover_url || dj.avatar_url;

    return (
        <SiteLayout>
            <SeoHead />
            <article>
                <header className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#07090b] text-white">
                    <div className="mx-auto grid min-h-[72svh] max-w-[1440px] lg:grid-cols-[0.42fr_0.58fr]">
                        <div className="flex flex-col justify-between px-5 py-12 sm:px-8 lg:px-12 lg:py-16">
                            <div>
                                <p className="alpha-kicker text-primary">Lapsique Originals / Artist</p>
                                <h1 className="mt-8 break-words text-6xl font-semibold leading-[0.82] text-white sm:text-7xl lg:text-8xl">{dj.name}</h1>
                            </div>
                            <div className="mt-16 border-t border-white/20 pt-6">
                                <p className="font-mono text-xs uppercase tracking-[0.14em] text-white/45">{String(videos.length).padStart(2, '0')} Psique Sessions</p>
                                <div className="mt-5 flex flex-wrap gap-3">
                                    {dj.instagram_handle ? (
                                        <a href={`https://instagram.com/${dj.instagram_handle.replace('@', '')}`} target="_blank" rel="noopener noreferrer" className="inline-flex min-h-11 items-center gap-2 border border-white/30 px-4 text-sm text-white hover:border-primary hover:text-primary">
                                            <AtSign className="size-4" /> {dj.instagram_handle.startsWith('@') ? dj.instagram_handle : `@${dj.instagram_handle}`}
                                        </a>
                                    ) : null}
                                    {dj.soundcloud_url ? <SocialLink href={dj.soundcloud_url} label="SoundCloud" /> : null}
                                    {dj.website_url ? <SocialLink href={dj.website_url} label={en ? 'Website' : 'Sitio web'} /> : null}
                                </div>
                            </div>
                        </div>
                        {hero ? (
                            <div className="relative min-h-[55svh] overflow-hidden bg-black">
                                <img src={hero} alt={dj.name} className="absolute inset-0 h-full w-full object-cover object-top" />
                                <div className="absolute inset-0 bg-black/15" />
                                <div className="absolute inset-x-6 bottom-6 border-t border-white/35 pt-4 sm:inset-x-8">
                                    <p className="alpha-kicker text-white/80">Sony Alpha · Lapsique Media</p>
                                </div>
                            </div>
                        ) : null}
                    </div>
                </header>

                <section className="grid gap-10 border-b border-foreground/20 py-14 md:grid-cols-[0.34fr_0.66fr] md:py-20">
                    <div>
                        <p className="alpha-kicker text-primary">Bio / Artist</p>
                        <h2 className="mt-4 text-4xl font-semibold">{en ? 'Sound and trajectory.' : 'Sonido y trayectoria.'}</h2>
                    </div>
                    <p className="max-w-3xl text-lg leading-relaxed text-muted-foreground">
                        {dj.bio || (en ? `${dj.name} is part of the artists documented by Lapsique Media.` : `${dj.name} forma parte de los artistas documentados por Lapsique Media.`)}
                    </p>
                </section>

                {videos.length > 0 ? (
                    <section className="border-b border-foreground/20 py-14 md:py-20">
                        <div className="mb-8 flex items-end justify-between gap-5">
                            <div>
                                <p className="alpha-kicker text-primary">Psique Sessions</p>
                                <h2 className="mt-3 text-4xl font-semibold md:text-6xl">{en ? 'Sets and videos' : 'Sets y videos'}</h2>
                            </div>
                            <Link href={route('videos.index', undefined, false, ziggy)} className="font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-primary hover:text-foreground">{en ? 'Full archive' : 'Archivo completo'} →</Link>
                        </div>
                        <div className="grid gap-px bg-foreground/20 md:grid-cols-3">
                            {videos.map((video) => (
                                <Link key={video.id} href={route('videos.show', { video: video.slug }, false, ziggy)} className="group bg-background">
                                    {video.thumbnail_url ? <img src={video.thumbnail_url} alt={video.title} loading="lazy" className="aspect-video w-full object-cover" /> : null}
                                    <div className="p-5">
                                        <Play className="size-5 text-primary" />
                                        <h3 className="mt-4 line-clamp-3 text-xl font-semibold leading-tight">{video.title.split('|')[0]}</h3>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </section>
                ) : null}

                <DjGallery images={dj.gallery ?? []} djName={dj.name} />

                <section className="mb-12 grid gap-7 border border-foreground/20 p-7 md:grid-cols-[1fr_auto] md:items-end md:p-10">
                    <div>
                        <p className="alpha-kicker text-primary">Lapsique Media / Production</p>
                        <h2 className="mt-4 max-w-3xl text-4xl font-semibold leading-[0.92] md:text-5xl">{en ? 'Record your own set with this level of production.' : 'Graba tu propio set con este nivel de producción.'}</h2>
                    </div>
                    <Link href={route('djset.show', undefined, false, ziggy)} className="inline-flex min-h-13 items-center justify-center gap-2 bg-foreground px-6 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-background hover:bg-primary hover:text-white">
                        {en ? 'Record my set' : 'Grabar mi set'} <Music2 className="size-4" />
                    </Link>
                </section>
            </article>
        </SiteLayout>
    );
}

function SocialLink({ href, label }: { href: string; label: string }) {
    return <a href={href} target="_blank" rel="noopener noreferrer" className="inline-flex min-h-11 items-center gap-2 border border-white/30 px-4 text-sm text-white hover:border-primary hover:text-primary">{label}<ArrowUpRight className="size-4" /></a>;
}
