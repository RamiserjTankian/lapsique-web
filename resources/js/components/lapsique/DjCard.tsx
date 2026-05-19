import { motion } from 'framer-motion';
import { AtSign } from 'lucide-react';
import { route } from '@/lib/route';
import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { fadeUp } from '@/lib/motion';
import type { DjItem, PageProps } from '@/types';

interface DjCardProps {
    dj: DjItem;
    index?: number;
    variant?: 'compact' | 'featured';
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
                        {imageUrl ? (
                            <img
                                src={imageUrl}
                                alt={dj.name}
                                className="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                                loading="lazy"
                            />
                        ) : (
                            <span className="flex h-full w-full items-center justify-center font-mono text-sm text-muted-foreground">
                                {initials}
                            </span>
                        )}
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

function MotionAvatarRing({ highlighted }: { highlighted?: boolean }) {
    if (!highlighted) return null;
    return (
        <div className="absolute -inset-1 rounded-full bg-gradient-to-br from-primary/60 via-accent/30 to-transparent opacity-70 blur-sm" />
    );
}
