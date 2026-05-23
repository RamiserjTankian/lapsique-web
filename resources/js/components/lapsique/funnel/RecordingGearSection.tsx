import { Aperture, Camera, Plane, Zap } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { RECORDING_GEAR_GROUPS, type RecordingGearGroup } from '@/data/recordingGear';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { ReactNode } from 'react';

const GROUP_ICONS: Record<string, ReactNode> = {
    aerial: <Plane className="h-3.5 w-3.5" />,
    cameras: <Camera className="h-3.5 w-3.5" />,
    lenses: <Aperture className="h-3.5 w-3.5" />,
    light: <Zap className="h-3.5 w-3.5" />,
};

const PIECE_COUNT = RECORDING_GEAR_GROUPS.reduce((sum, group) => sum + group.items.length, 0);

export function RecordingGearSection() {
    const ref = useSectionEvent('gear_section_viewed', { section: 'recording_gear' });

    return (
        <GlassSection
            eyebrow="Gear"
            title="Equipo de grabación"
            description="Set listo para multicámara, dron y luz en locación."
            className="py-12 md:py-16"
        >
            <section
                ref={ref}
                className={cn(glassCardVariants(), 'glass-border-glow border px-4 py-4 md:px-5 md:py-5')}
            >
                <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2.5">
                        <span className="inline-flex rounded-lg bg-zinc-900 px-2.5 py-1.5">
                            <img
                                src="/images/equipment/sony-logo.svg"
                                alt="Sony"
                                className="h-4 w-auto object-contain"
                                width={72}
                                height={16}
                            />
                        </span>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.22em] text-muted-foreground">
                            Equipo de grabación
                        </p>
                    </div>
                    <span className="rounded-full border border-primary/25 bg-primary/10 px-2.5 py-1 font-mono-tabular text-[11px] font-semibold text-primary">
                        {PIECE_COUNT} piezas
                    </span>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {RECORDING_GEAR_GROUPS.map((group) => (
                        <GearGroup key={group.id} group={group} />
                    ))}
                </div>
            </section>
        </GlassSection>
    );
}

function GearGroup({ group }: { group: RecordingGearGroup }) {
    const isDenseList = group.id === 'lenses';

    return (
        <div className="rounded-xl border border-border/70 bg-secondary/80 p-3">
            <div className="mb-2.5 flex items-center gap-2 text-primary">
                <span className="flex h-7 w-7 items-center justify-center rounded-md border border-primary/20 bg-primary/10">
                    {GROUP_ICONS[group.id]}
                </span>
                <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-foreground">
                    {group.label}
                </p>
            </div>

            {isDenseList ? (
                <ul className="flex flex-wrap gap-1.5">
                    {group.items.map((item) => (
                        <li key={`${group.id}-${item.name}`}>
                            <GearPill item={item} />
                        </li>
                    ))}
                </ul>
            ) : (
                <ul className="space-y-1.5">
                    {group.items.map((item) => (
                        <li key={`${group.id}-${item.name}`}>
                            <GearItemRow item={item} showImage={group.id === 'cameras'} compact={group.id !== 'cameras'} />
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

function GearPill({ item }: { item: RecordingGearGroup['items'][number] }) {
    return (
        <span
            className="inline-flex max-w-full flex-col rounded-lg border border-border/70 bg-background px-2 py-1.5"
            title={item.spec}
        >
            <span className="text-[11px] font-semibold leading-tight text-foreground">
                <span className="font-mono-tabular text-primary">×{item.quantity}</span> {item.name}
            </span>
            <span className="text-[10px] text-muted-foreground">{item.spec}</span>
        </span>
    );
}

function GearItemRow({
    item,
    showImage = false,
    compact = false,
}: {
    item: RecordingGearGroup['items'][number];
    showImage?: boolean;
    compact?: boolean;
}) {
    return (
        <div className="flex items-center gap-2 rounded-lg border border-border/60 bg-background px-2 py-1.5">
            {showImage && item.imageSrc ? (
                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-border/70 bg-secondary">
                    <img
                        src={item.imageSrc}
                        alt=""
                        className="h-7 w-7 object-contain"
                        loading="lazy"
                    />
                </div>
            ) : (
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-primary/20 bg-primary/10 font-mono-tabular text-xs font-bold text-primary">
                    ×{item.quantity}
                </span>
            )}

            <div className="min-w-0 flex-1">
                <p className={cn('font-semibold leading-tight text-foreground', compact ? 'text-xs' : 'text-sm')}>
                    {item.name}
                </p>
                <p className="truncate text-[10px] text-muted-foreground">{item.spec}</p>
            </div>
        </div>
    );
}
