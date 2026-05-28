import { useMemo } from 'react';
import { Clock3, Cloud, Film, Images, Plane } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { getContentPackageItems } from '@/data/contentPackage';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { LucideIcon } from 'lucide-react';

const ITEM_ICONS: Record<string, LucideIcon> = {
    session: Clock3,
    reel: Film,
    drone: Plane,
    photos: Images,
    cloud: Cloud,
};

const ITEM_ICON_STYLES: Record<string, string> = {
    session: 'border-sky-500/35 bg-sky-500/12 text-sky-600 dark:text-sky-400',
    reel: 'border-amber-500/35 bg-amber-500/12 text-amber-600 dark:text-amber-400',
    drone: 'border-cyan-500/35 bg-cyan-500/12 text-cyan-600 dark:text-cyan-400',
    photos: 'border-violet-500/35 bg-violet-500/12 text-violet-600 dark:text-violet-400',
    cloud: 'border-emerald-500/35 bg-emerald-500/12 text-emerald-600 dark:text-emerald-400',
};

export function ContentPackageSection() {
    const { t } = useTranslations();
    const items = useMemo(() => getContentPackageItems(t), [t]);
    const ref = useSectionEvent('package_includes_viewed', { section: 'content_package' });

    return (
        <GlassSection
            eyebrow={t('funnel.package.section_eyebrow')}
            title={t('funnel.package.section_title')}
            description={t('funnel.package.section_description')}
        >
            <section
                ref={ref}
                id="que-incluye"
                className="scroll-mt-24 grid gap-3 sm:grid-cols-2"
            >
                {items.map((item) => {
                    const Icon = ITEM_ICONS[item.id] ?? Film;

                    return (
                        <article
                            key={item.id}
                            className={cn(glassCardVariants(), 'glass-border-glow flex gap-3 border p-4 md:p-5')}
                        >
                            <span
                                className={cn(
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border',
                                    ITEM_ICON_STYLES[item.id] ?? 'border-primary/20 bg-primary/10 text-primary',
                                )}
                            >
                                <Icon className="h-4 w-4" aria-hidden />
                            </span>
                            <div className="min-w-0">
                                <h3 className="text-base font-semibold text-foreground">{item.title}</h3>
                                <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                                    {item.description}
                                </p>
                            </div>
                        </article>
                    );
                })}
            </section>
        </GlassSection>
    );
}
