import { useMemo } from 'react';
import { CalendarCheck, CloudUpload, Film, PackageCheck, Video } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { LandingReelPreviewGrid } from '@/components/lapsique/LandingReelPreviewGrid';
import { getWorkflowSteps } from '@/data/workflowSteps';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { solidCardVariants } from '@/lib/variants';
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
    { stepBadge: string; iconBadge: string }
> = {
    meeting: {
        stepBadge: 'border-primary/40 bg-primary/15 text-primary',
        iconBadge: 'border-border/60 bg-muted text-muted-foreground',
    },
    shoot: {
        stepBadge: 'border-primary/40 bg-primary/15 text-primary',
        iconBadge: 'border-border/60 bg-muted text-muted-foreground',
    },
    drive: {
        stepBadge: 'border-primary/40 bg-primary/15 text-primary',
        iconBadge: 'border-border/60 bg-muted text-muted-foreground',
    },
    edit: {
        stepBadge: 'border-primary/40 bg-primary/15 text-primary',
        iconBadge: 'border-border/60 bg-muted text-muted-foreground',
    },
    delivery: {
        stepBadge: 'border-accent/45 bg-accent/15 text-accent',
        iconBadge: 'border-accent/35 bg-accent/10 text-accent',
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
            surface="solid"
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
                                        solidCardVariants(),
                                        'flex h-full flex-col gap-3 p-4 md:p-5',
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
