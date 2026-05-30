import { ScanEye, TrendingDown, Hourglass } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { solidCardVariants } from '@/lib/variants';
import type { LucideIcon } from 'lucide-react';

const PROBLEM_ICONS: LucideIcon[] = [ScanEye, TrendingDown, Hourglass];

export function ProblemSection() {
    const { t } = useTranslations();
    const ref = useSectionEvent('problem_section_viewed', { section: 'problem' });

    const points = [
        {
            title: t('funnel.problem.point_scroll_title'),
            copy: t('funnel.problem.point_scroll_copy'),
        },
        {
            title: t('funnel.problem.point_cheap_title'),
            copy: t('funnel.problem.point_cheap_copy'),
        },
        {
            title: t('funnel.problem.point_improvised_title'),
            copy: t('funnel.problem.point_improvised_copy'),
        },
    ];

    return (
        <GlassSection
            surface="solid"
            eyebrow={t('funnel.problem.section_eyebrow')}
            title={t('funnel.problem.section_title')}
            description={t('funnel.problem.section_description')}
        >
            <div ref={ref} className="grid gap-3 sm:grid-cols-3">
                {points.map((point, index) => {
                    const Icon = PROBLEM_ICONS[index] ?? ScanEye;

                    return (
                        <article
                            key={point.title}
                            className={cn(solidCardVariants(), 'flex flex-col gap-3 p-4 md:p-5')}
                        >
                            <span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-destructive/30 bg-destructive/10 text-destructive">
                                <Icon className="h-4 w-4" aria-hidden />
                            </span>
                            <div className="min-w-0">
                                <h3 className="text-base font-semibold text-foreground">{point.title}</h3>
                                <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                                    {point.copy}
                                </p>
                            </div>
                        </article>
                    );
                })}
            </div>
        </GlassSection>
    );
}
