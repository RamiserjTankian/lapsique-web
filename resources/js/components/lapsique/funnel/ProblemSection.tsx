import { CheckCircle2, Hourglass, ScanEye, SearchCheck, TrendingDown } from 'lucide-react';
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
            surfaceClassName="relative overflow-hidden border-destructive/25 shadow-[0_24px_70px_oklch(0.48_0.16_32/0.12)]"
            surfaceStyle={{
                background: 'radial-gradient(circle at 12% 18%, oklch(0.72 0.2 28 / 0.16), transparent 34%), linear-gradient(135deg, oklch(0.99 0.01 88 / 0.98), oklch(0.96 0.03 55 / 0.9))',
            }}
        >
            <div ref={ref} className="grid gap-4 lg:grid-cols-[0.9fr_1.4fr]">
                <aside className="relative overflow-hidden rounded-2xl border border-destructive/20 bg-white/75 p-5 shadow-[0_18px_40px_oklch(0.35_0.08_35/0.08)]">
                    <span className="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-destructive/10 blur-2xl" aria-hidden />
                    <div className="relative">
                        <span className="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-destructive/25 bg-destructive/10 text-destructive">
                            <SearchCheck className="h-5 w-5" aria-hidden />
                        </span>
                        <p className="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-destructive">
                            {t('funnel.problem.diagnosis_label')}
                        </p>
                        <h3 className="mt-2 font-display text-2xl font-bold leading-tight text-foreground">
                            {t('funnel.problem.diagnosis_title')}
                        </h3>
                        <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                            {t('funnel.problem.diagnosis_copy')}
                        </p>
                    </div>
                </aside>

                <div className="grid gap-3 sm:grid-cols-3">
                    {points.map((point, index) => {
                        const Icon = PROBLEM_ICONS[index] ?? ScanEye;

                        return (
                            <article
                                key={point.title}
                                className={cn(
                                    solidCardVariants(),
                                    'flex flex-col gap-3 border-destructive/15 bg-white/70 p-4 shadow-sm md:p-5',
                                )}
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
                <div className="rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm font-medium text-foreground lg:col-span-2">
                    <CheckCircle2 className="mr-2 inline h-4 w-4 text-primary" aria-hidden />
                    {t('funnel.problem.bridge_copy')}
                </div>
            </div>
        </GlassSection>
    );
}
