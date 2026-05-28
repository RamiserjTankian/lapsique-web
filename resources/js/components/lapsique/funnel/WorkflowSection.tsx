import { useMemo } from 'react';
import { CalendarCheck, CloudUpload, Film, PackageCheck, Video } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { LandingReelPreviewGrid } from '@/components/lapsique/LandingReelPreviewGrid';
import { getWorkflowSteps } from '@/data/workflowSteps';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { LandingVideoEntry } from '@/types';
import type { LucideIcon } from 'lucide-react';

const STEP_ICONS: Record<string, LucideIcon> = {
    meeting: CalendarCheck,
    shoot: Video,
    drive: CloudUpload,
    edit: Film,
    delivery: PackageCheck,
};

const STEP_STYLES: Record<
    string,
    { card: string; stepBadge: string; iconBadge: string }
> = {
    meeting: {
        card: 'border-sky-500/30 bg-sky-500/10',
        stepBadge: 'border-sky-500/45 bg-sky-500/20 text-sky-700 dark:text-sky-300',
        iconBadge: 'border-sky-500/35 bg-sky-500/15 text-sky-600 dark:text-sky-400',
    },
    shoot: {
        card: 'border-amber-500/30 bg-amber-500/10',
        stepBadge: 'border-amber-500/45 bg-amber-500/20 text-amber-800 dark:text-amber-300',
        iconBadge: 'border-amber-500/35 bg-amber-500/15 text-amber-600 dark:text-amber-400',
    },
    drive: {
        card: 'border-cyan-500/30 bg-cyan-500/10',
        stepBadge: 'border-cyan-500/45 bg-cyan-500/20 text-cyan-800 dark:text-cyan-300',
        iconBadge: 'border-cyan-500/35 bg-cyan-500/15 text-cyan-600 dark:text-cyan-400',
    },
    edit: {
        card: 'border-violet-500/30 bg-violet-500/10',
        stepBadge: 'border-violet-500/45 bg-violet-500/20 text-violet-800 dark:text-violet-300',
        iconBadge: 'border-violet-500/35 bg-violet-500/15 text-violet-600 dark:text-violet-400',
    },
    delivery: {
        card: 'border-emerald-500/30 bg-emerald-500/10',
        stepBadge: 'border-emerald-500/45 bg-emerald-500/20 text-emerald-800 dark:text-emerald-300',
        iconBadge: 'border-emerald-500/35 bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
    },
};

export function WorkflowSection({
    videos = [],
    bookingSource = 'workflow_reel',
}: {
    videos?: Array<LandingVideoEntry | null | undefined>;
    bookingSource?: string;
}) {
    const { t } = useTranslations();
    const steps = useMemo(() => getWorkflowSteps(t), [t]);
    const ref = useSectionEvent('workflow_section_viewed', { section: 'production_workflow' });

    return (
        <GlassSection
            eyebrow={t('funnel.workflow.section_eyebrow')}
            title={t('funnel.workflow.section_title')}
            description={t('funnel.workflow.section_description')}
        >
            <section
                ref={ref}
                id="nuestro-workflow"
                className="scroll-mt-24"
            >
                <ol className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    {steps.map((item) => {
                        const Icon = STEP_ICONS[item.id] ?? Film;
                        const styles = STEP_STYLES[item.id] ?? STEP_STYLES.meeting;

                        return (
                            <li key={item.id} className="relative">
                                <article
                                    className={cn(
                                        glassCardVariants(),
                                        'glass-border-glow flex h-full flex-col gap-3 border p-4 md:p-5',
                                        styles.card,
                                    )}
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <span
                                            className={cn(
                                                'flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-xs font-bold',
                                                styles.stepBadge,
                                            )}
                                        >
                                            {item.step}
                                        </span>
                                        <span
                                            className={cn(
                                                'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border',
                                                styles.iconBadge,
                                            )}
                                        >
                                            <Icon className="h-4 w-4" aria-hidden />
                                        </span>
                                    </div>
                                    <div className="min-w-0">
                                        <h3 className="text-sm font-semibold leading-snug text-foreground md:text-base">
                                            {item.title}
                                        </h3>
                                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                                            {item.description}
                                        </p>
                                    </div>
                                </article>
                            </li>
                        );
                    })}
                </ol>

                <LandingReelPreviewGrid videos={videos} className="mt-8" bookingSource={bookingSource} />
            </section>
        </GlassSection>
    );
}
