import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { CaseRows, FinalCta, PageShell, ProducedEventsSection, type ProducedEvent, type TrascendentalCase } from './Partials';

interface CasesProps {
    cases: TrascendentalCase[];
    producedEvents: ProducedEvent[];
}

export default function Cases({ cases, producedEvents }: CasesProps) {
    const { t } = useTranslations();

    return (
        <TrascendentalLayout>
            <PageShell title={t('trascendental.cases.title')} intro={t('trascendental.cases.intro')}>
                <CaseRows cases={cases} />
            </PageShell>
            <ProducedEventsSection events={producedEvents} />
            <FinalCta />
        </TrascendentalLayout>
    );
}
