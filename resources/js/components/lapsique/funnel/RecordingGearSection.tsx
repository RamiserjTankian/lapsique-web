import { Aperture, Camera, Plane, Zap } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { LandingReelPreviewGrid } from '@/components/lapsique/LandingReelPreviewGrid';
import { RECORDING_GEAR_GROUPS, type RecordingGearGroup } from '@/data/recordingGear';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { LandingVideoEntry } from '@/types';
import type { LucideIcon } from 'lucide-react';

const GROUP_ICONS: Record<string, LucideIcon> = {
    aerial: Plane,
    cameras: Camera,
    lenses: Aperture,
    light: Zap,
};

const GROUP_STYLES: Record<
    string,
    { card: string; iconBadge: string; label: string }
> = {
    aerial: {
        card: 'border-cyan-500/30 bg-cyan-500/8',
        iconBadge: 'border-cyan-500/35 bg-cyan-500/15 text-cyan-600 dark:text-cyan-400',
        label: 'text-cyan-700 dark:text-cyan-300',
    },
    cameras: {
        card: 'border-amber-500/30 bg-amber-500/8',
        iconBadge: 'border-amber-500/35 bg-amber-500/15 text-amber-600 dark:text-amber-400',
        label: 'text-amber-800 dark:text-amber-300',
    },
    lenses: {
        card: 'border-violet-500/30 bg-violet-500/8',
        iconBadge: 'border-violet-500/35 bg-violet-500/15 text-violet-600 dark:text-violet-400',
        label: 'text-violet-800 dark:text-violet-300',
    },
    light: {
        card: 'border-emerald-500/30 bg-emerald-500/8',
        iconBadge: 'border-emerald-500/35 bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
        label: 'text-emerald-800 dark:text-emerald-300',
    },
};

export function RecordingGearSection({
    videos = [],
    bookingSource = 'gear_reel',
}: {
    videos?: Array<LandingVideoEntry | null | undefined>;
    bookingSource?: string;
}) {
    const ref = useSectionEvent('gear_section_viewed', { section: 'recording_gear' });

    return (
        <GlassSection
            eyebrow="Gear"
            title="Equipo de grabación"
            description="Set listo para multicámara, 3 tomas aéreas con dron DJI y luz en locación."
            className="py-12 md:py-16"
        >
            <section ref={ref} id="equipo-grabacion" className="scroll-mt-24 space-y-6">
                <div className="flex flex-wrap items-center justify-center gap-3 rounded-xl border border-border/70 bg-muted/30 px-4 py-3 sm:justify-start">
                    <span className="inline-flex shrink-0 items-center rounded-lg border border-border/60 bg-white px-3 py-2 dark:bg-zinc-950">
                        <img
                            src="/images/equipment/sony-logo.svg"
                            alt="Sony"
                            className="h-5 w-auto object-contain dark:invert"
                            width={90}
                            height={16}
                        />
                    </span>
                    <span className="hidden h-4 w-px bg-border sm:block" aria-hidden />
                    <p className="text-center text-sm text-muted-foreground sm:text-left">
                        Multicámara Sony · Dron DJI Air 3 · Óptica full frame y luz Godox en set
                    </p>
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                    {RECORDING_GEAR_GROUPS.map((group) => (
                        <GearGroupCard key={group.id} group={group} />
                    ))}
                </div>

                <LandingReelPreviewGrid videos={videos} bookingSource={bookingSource} />
            </section>
        </GlassSection>
    );
}

function GearGroupCard({ group }: { group: RecordingGearGroup }) {
    const Icon = GROUP_ICONS[group.id] ?? Camera;
    const styles = GROUP_STYLES[group.id] ?? GROUP_STYLES.cameras;
    const isLensGrid = group.id === 'lenses';

    return (
        <article
            className={cn(
                glassCardVariants(),
                'glass-border-glow flex h-full flex-col gap-4 border p-4 md:p-5',
                styles.card,
            )}
        >
            <div className="flex items-center gap-3">
                <span
                    className={cn(
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border',
                        styles.iconBadge,
                    )}
                >
                    <Icon className="h-4 w-4" aria-hidden />
                </span>
                <h3 className={cn('text-sm font-semibold uppercase tracking-[0.18em]', styles.label)}>
                    {group.label}
                </h3>
            </div>

            <ul className={cn(isLensGrid ? 'grid grid-cols-2 gap-2' : 'space-y-2')}>
                {group.items.map((item) => (
                    <li key={`${group.id}-${item.name}`}>
                        <GearItem item={item} showImage={group.id === 'cameras'} compact={isLensGrid} />
                    </li>
                ))}
            </ul>
        </article>
    );
}

function GearItem({
    item,
    showImage = false,
    compact = false,
}: {
    item: RecordingGearGroup['items'][number];
    showImage?: boolean;
    compact?: boolean;
}) {
    return (
        <div
            className={cn(
                'flex items-start gap-3 rounded-lg border border-border/60 bg-background/80 p-3',
                compact && 'flex-col gap-1.5 p-2.5',
            )}
        >
            {showImage && item.imageSrc ? (
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border/70 bg-secondary/80">
                    <img
                        src={item.imageSrc}
                        alt=""
                        className="h-8 w-8 object-contain"
                        loading="lazy"
                    />
                </div>
            ) : (
                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-muted font-mono-tabular text-xs font-bold text-foreground">
                    ×{item.quantity}
                </span>
            )}

            <div className="min-w-0 flex-1">
                <p className={cn('font-semibold leading-snug text-foreground', compact ? 'text-xs' : 'text-sm')}>
                    {item.name}
                </p>
                <p className={cn('mt-0.5 text-muted-foreground', compact ? 'text-[11px] leading-snug' : 'text-xs')}>
                    {item.spec}
                </p>
            </div>
        </div>
    );
}
