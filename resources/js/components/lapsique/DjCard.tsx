import { motion } from 'framer-motion';
import { AtSign } from 'lucide-react';
import { useState } from 'react';
import { route } from '@/lib/route';
import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { fadeUp } from '@/lib/motion';
import type { DjItem, PageProps } from '@/types';

interface DjCardProps {
    dj: DjItem;
    index?: number;
    variant?: 'compact' | 'featured' | 'spotlight';
}

export function DjCard({ dj, index = 0, variant = 'compact' }: DjCardProps) {
    const { ziggy } = usePage<PageProps>().props;
    const initials = dj.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

    const imageUrl = dj.avatar_url || dj.cover_url;

    if (variant === 'spotlight') {
        return (
            <motion.div
                variants={fadeUp}
                custom={index}
                initial="hidden"
                whileInView="visible"
                viewport={{ once: true }}
                className="h-full"
            >
                <Link
                    href={route('djs.show', { dj: dj.slug }, false, ziggy)}
                    className={cn(
                        'group relative flex h-full min-h-[190px] overflow-hidden rounded-2xl border p-4 text-left transition duration-300',
                        'border-white/60 bg-white/60 shadow-[0_18px_42px_oklch(0.2_0.04_260/0.12)] backdrop-blur',
                        'hover:-translate-y-1 hover:border-primary/55 hover:bg-white/75 hover:shadow-[0_24px_54px_oklch(0.66_0.17_70/0.24)]',
                    )}
                >
                    {dj.cover_url && (
                        <img
                            src={dj.cover_url}
                            alt=""
                            className="absolute inset-0 h-full w-full object-cover opacity-20 saturate-125 transition duration-700 group-hover:scale-[1.04] group-hover:opacity-28"
                            loading="lazy"
                        />
                    )}
                    <span className="absolute inset-0 bg-gradient-to-br from-white/95 via-white/72 to-white/32" />
                    <span className="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-primary via-accent to-primary/40 opacity-70" />
                    <div className="relative z-10 flex w-full items-center gap-4">
                        <div className="relative shrink-0">
                            <MotionAvatarRing highlighted={dj.is_highlighted} />
                            <div className="relative h-20 w-20 overflow-hidden rounded-full border-2 border-white bg-muted/40 shadow-xl ring-1 ring-primary/20 transition duration-300 group-hover:ring-primary/50 sm:h-24 sm:w-24">
                                <AvatarImage imageUrl={imageUrl} name={dj.name} initials={initials} />
                            </div>
                        </div>
                        <div className="min-w-0">
                            <p className="truncate font-display text-xl font-bold tracking-tight text-foreground">
                                {dj.name}
                            </p>
                            {dj.instagram_handle && (
                                <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                    <AtSign className="h-3.5 w-3.5" />
                                    <span className="truncate">{dj.instagram_handle.replace('@', '')}</span>
                                </p>
                            )}
                            <div className="mt-3 flex flex-wrap gap-1.5">
                                {(dj.tags ?? []).slice(0, 2).map((tag) => (
                                    <span
                                        key={tag}
                                        className="rounded-full border border-primary/20 bg-primary/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-primary"
                                    >
                                        {tag}
                                    </span>
                                ))}
                                {dj.is_highlighted && (
                                    <span className="rounded-full border border-accent/25 bg-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-accent">
                                        Lapsique
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </Link>
            </motion.div>
        );
    }

    if (variant === 'featured' && (dj.cover_url || dj.avatar_url)) {
        return (
            <motion.div variants={fadeUp} custom={index} initial="hidden" whileInView="visible" viewport={{ once: true }}>
                <Link
                    href={route('djs.show', { dj: dj.slug }, false, ziggy)}
                    className="group relative block aspect-[16/10] overflow-hidden rounded-xl glass-panel glass-border-glow"
                >
                    <img
                        src={dj.cover_url || dj.avatar_url || ''}
                        alt={dj.name}
                        className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-background via-background/30 to-transparent" />
                    <div className="absolute inset-x-0 bottom-0 p-5">
                        {dj.is_highlighted && (
                            <span className="mb-2 inline-block rounded-full bg-primary/20 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-primary">
                                Destacado
                            </span>
                        )}
                        <h3 className="font-display text-2xl font-bold">{dj.name}</h3>
                    </div>
                </Link>
            </motion.div>
        );
    }

    return (
        <motion.div
            variants={fadeUp}
            custom={index}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
        >
            <Link
                href={route('djs.show', { dj: dj.slug }, false, ziggy)}
                className={cn(
                    'group flex flex-col items-center gap-3 rounded-xl p-3 text-center transition',
                    'hover:bg-secondary',
                )}
            >
                <div className="relative">
                    <MotionAvatarRing highlighted={dj.is_highlighted} />
                    <div className="relative h-[4.5rem] w-[4.5rem] overflow-hidden rounded-full border-2 border-border/80 bg-muted/30 shadow-lg transition duration-300 group-hover:border-primary/50 group-hover:shadow-[0_0_24px_oklch(0.78_0.14_75/0.25)]">
                        <AvatarImage imageUrl={imageUrl} name={dj.name} initials={initials} />
                    </div>
                </div>
                <div className="min-w-0 space-y-0.5">
                    <p className="truncate text-sm font-semibold text-foreground group-hover:text-primary transition-colors">
                        {dj.name}
                    </p>
                    {dj.instagram_handle && (
                        <p className="flex items-center justify-center gap-1 text-[10px] text-muted-foreground">
                            <AtSign className="h-3 w-3" />
                            {dj.instagram_handle.replace('@', '')}
                        </p>
                    )}
                </div>
            </Link>
        </motion.div>
    );
}

function AvatarImage({
    imageUrl,
    name,
    initials,
}: {
    imageUrl?: string | null;
    name: string;
    initials: string;
}) {
    const [failed, setFailed] = useState(false);

    if (!imageUrl || failed) {
        return (
            <span className="flex h-full w-full items-center justify-center font-mono text-sm text-muted-foreground">
                {initials}
            </span>
        );
    }

    return (
        <img
            src={imageUrl}
            alt={name}
            className="h-full w-full object-cover transition duration-500 group-hover:scale-110"
            loading="lazy"
            onError={() => setFailed(true)}
        />
    );
}

function MotionAvatarRing({ highlighted }: { highlighted?: boolean }) {
    if (!highlighted) return null;
    return (
        <div className="absolute -inset-1 rounded-full bg-gradient-to-br from-primary/60 via-accent/30 to-transparent opacity-70 blur-sm" />
    );
}
