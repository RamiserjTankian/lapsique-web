import { useMemo } from 'react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { CONTENT_REEL_DURATION_SECONDS } from '@/data/contentOffer';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';

export function FunnelProcess() {
    const { t } = useTranslations();
    const ref = useSectionEvent('process_viewed', { section: 'process' });

    const steps = useMemo(
        () => [
            {
                step: '01',
                title: t('funnel.process.step_1_title'),
                description: t('funnel.process.step_1_description'),
            },
            {
                step: '02',
                title: t('funnel.process.step_2_title'),
                description: t('funnel.process.step_2_description'),
            },
            {
                step: '03',
                title: t('funnel.process.step_3_title'),
                description: t('funnel.process.step_3_description', {
                    seconds: CONTENT_REEL_DURATION_SECONDS,
                }),
            },
        ],
        [t],
    );

    return (
        <GlassSection
            eyebrow={t('funnel.process.section_eyebrow')}
            title={t('funnel.process.title')}
            description={t('funnel.process.section_description')}
        >
            <section ref={ref} className="grid gap-4 lg:grid-cols-3">
                {steps.map((step) => (
                    <article
                        key={step.step}
                        className={cn(glassCardVariants({ elevated: true }), 'border p-5 md:p-6')}
                    >
                        <p className="font-mono-tabular text-xs uppercase tracking-[0.28em] text-primary">
                            {step.step}
                        </p>
                        <h3 className="mt-4 text-xl font-semibold text-foreground">{step.title}</h3>
                        <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                            {step.description}
                        </p>
                    </article>
                ))}
            </section>
        </GlassSection>
    );
}
