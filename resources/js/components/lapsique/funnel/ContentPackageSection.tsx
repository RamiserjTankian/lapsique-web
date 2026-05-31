import { useMemo } from 'react';
import { Clock3, Cloud, Film, Images, Plane } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { getContentPackageItems } from '@/data/contentPackage';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { solidCardVariants } from '@/lib/variants';
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
            surface="solid"
            eyebrow={t('funnel.package.section_eyebrow')}
            title={t('funnel.package.section_title')}
            description={t('funnel.package.section_description')}
            surfaceClassName="border-primary/20 bg-[linear-gradient(135deg,oklch(0.995_0.005_90),oklch(0.96_0.02_95))] shadow-[0_20px_56px_oklch(0.22_0.03_250/0.08)]"
        >
            <section
                ref={ref}
                id="que-incluye"
                className="scroll-mt-24"
            >
                <Accordion type="single" collapsible className="grid gap-3 md:grid-cols-2">
                    {items.map((item, index) => {
                        const Icon = ITEM_ICONS[item.id] ?? Film;

                        return (
                            <AccordionItem
                                key={item.id}
                                value={item.id}
                                className={cn(
                                    solidCardVariants(),
                                    'overflow-hidden border-border/70 bg-white/75 px-4 shadow-sm data-[state=open]:border-primary/35 data-[state=open]:shadow-[0_18px_42px_oklch(0.66_0.14_75/0.12)]',
                                )}
                            >
                                <AccordionTrigger className="gap-4 py-4 text-left hover:no-underline">
                                    <span
                                        className={cn(
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border',
                                            ITEM_ICON_STYLES[item.id] ?? 'border-primary/20 bg-primary/10 text-primary',
                                        )}
                                    >
                                        <Icon className="h-4 w-4" aria-hidden />
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block text-[10px] font-semibold uppercase tracking-[0.2em] text-primary">
                                            {String(index + 1).padStart(2, '0')}
                                        </span>
                                        <span className="mt-1 block text-base font-semibold text-foreground">
                                            {item.title}
                                        </span>
                                    </span>
                                </AccordionTrigger>
                                <AccordionContent className="pb-4 pl-14 text-sm leading-relaxed text-muted-foreground">
                                    {item.description}
                                </AccordionContent>
                            </AccordionItem>
                        );
                    })}
                </Accordion>
            </section>
        </GlassSection>
    );
}
