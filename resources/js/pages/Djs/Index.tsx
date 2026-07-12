import { Link, usePage } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import SiteLayout from '@/layouts/SiteLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { DjItem, PageProps } from '@/types';

interface DjsIndexProps {
    djs: DjItem[];
    highlightedDj?: DjItem | null;
}

export default function DjsIndex({ djs, highlightedDj }: DjsIndexProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const en = locale === 'en';
    const artists = highlightedDj ? djs.filter((dj) => dj.id !== highlightedDj.id) : djs;

    return (
        <SiteLayout>
            <SeoHead />
            <section className="relative left-1/2 w-screen -translate-x-1/2 bg-[#07090b] text-white">
                <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 md:py-24">
                    <p className="alpha-kicker text-primary">Lapsique Originals / Artists</p>
                    <div className="mt-6 grid gap-8 border-b border-white/20 pb-12 lg:grid-cols-[0.72fr_0.28fr] lg:items-end">
                        <h1 className="max-w-5xl text-6xl font-semibold leading-[0.84] text-white sm:text-7xl md:text-9xl">DJs</h1>
                        <p className="text-base leading-relaxed text-white/60">
                            {en
                                ? 'Artists whose sound, performance, and presence have been documented by Lapsique Media.'
                                : 'Artistas cuyo sonido, performance y presencia han sido documentados por Lapsique Media.'}
                        </p>
                    </div>

                    {highlightedDj ? (
                        <Link href={route('djs.show', { dj: highlightedDj.slug }, false, ziggy)} className="group mt-10 grid border border-white/20 md:grid-cols-[0.58fr_0.42fr]">
                            {highlightedDj.cover_url ? (
                                <div className="min-h-[24rem] overflow-hidden bg-black md:min-h-[34rem]">
                                    <img src={highlightedDj.cover_url} alt={highlightedDj.name} className="h-full w-full object-cover grayscale transition duration-700 group-hover:scale-[1.02] group-hover:grayscale-0" />
                                </div>
                            ) : null}
                            <div className="flex flex-col justify-between border-t border-white/20 p-7 md:border-l md:border-t-0 md:p-10">
                                <p className="alpha-kicker text-primary">{en ? 'Featured artist' : 'Artista destacado'}</p>
                                <div className="mt-16">
                                    <h2 className="text-5xl font-semibold leading-none text-white md:text-7xl">{highlightedDj.name}</h2>
                                    {highlightedDj.bio ? <p className="mt-5 line-clamp-4 text-sm leading-relaxed text-white/60">{highlightedDj.bio}</p> : null}
                                    <span className="mt-8 inline-flex items-center gap-2 font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-primary">
                                        {en ? 'Open profile' : 'Abrir perfil'} <ArrowRight className="size-4" />
                                    </span>
                                </div>
                            </div>
                        </Link>
                    ) : null}
                </div>
            </section>

            <section className="py-14 md:py-20">
                <div className="mb-8 flex items-end justify-between border-b border-foreground/20 pb-5">
                    <div>
                        <p className="alpha-kicker text-primary">{String(djs.length).padStart(2, '0')} / Artists</p>
                        <h2 className="mt-3 text-4xl font-semibold md:text-6xl">{en ? 'Documented artists' : 'Artistas documentados'}</h2>
                    </div>
                </div>
                {artists.length > 0 ? (
                    <div className="grid grid-cols-2 gap-px bg-foreground/20 md:grid-cols-3 lg:grid-cols-4">
                        {artists.map((dj) => (
                            <Link key={dj.id} href={route('djs.show', { dj: dj.slug }, false, ziggy)} className="group bg-background">
                                {dj.avatar_url ? (
                                    <div className="aspect-[3/4] overflow-hidden bg-black">
                                        <img src={dj.avatar_url} alt={dj.name} loading="lazy" className="h-full w-full object-cover grayscale transition duration-500 group-hover:scale-[1.03] group-hover:grayscale-0" />
                                    </div>
                                ) : null}
                                <div className="border-t border-foreground/15 p-4 md:p-5">
                                    <h3 className="font-ui-display text-xl font-bold uppercase leading-none md:text-2xl">{dj.name}</h3>
                                    {dj.tags?.length ? <p className="mt-2 line-clamp-1 font-mono text-[10px] uppercase tracking-[0.12em] text-muted-foreground">{dj.tags.slice(0, 2).join(' · ')}</p> : null}
                                </div>
                            </Link>
                        ))}
                    </div>
                ) : (
                    <p className="py-16 text-muted-foreground">{en ? 'The artist archive is being prepared.' : 'El archivo de artistas se está preparando.'}</p>
                )}
            </section>
        </SiteLayout>
    );
}
