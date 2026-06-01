import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { FinalCta, PageShell, TourRows, type TrascendentalTour } from './Partials';

interface ToursProps {
    tours: TrascendentalTour[];
}

export default function Tours({ tours }: ToursProps) {
    const { t } = useTranslations();

    return (
        <TrascendentalLayout>
            <PageShell title={t('trascendental.tours.title')} intro={t('trascendental.tours.intro')}>
                <TourRows tours={tours} />
            </PageShell>
            <FinalCta />
        </TrascendentalLayout>
    );
}
