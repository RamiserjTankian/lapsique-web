import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { FinalCta, PageShell } from './Partials';

const sections = [
    {
        key: 'production',
        items: ['concept', 'booking', 'production', 'execution', 'marketing'],
    },
    {
        key: 'booking',
        items: ['artists', 'touring', 'routing'],
    },
    {
        key: 'operation',
        items: ['cashless', 'ads', 'content'],
    },
] as const;

export default function Services() {
    const { t } = useTranslations();

    return (
        <TrascendentalLayout>
            <PageShell title={t('trascendental.services.title')} intro={t('trascendental.services.intro')}>
                <div className="divide-y divide-black/15 border-y border-black/15">
                    {sections.map((section) => (
                        <section key={section.key} className="grid gap-8 py-10 md:grid-cols-[0.7fr_1fr]">
                            <div>
                                <p className="text-xs font-bold uppercase text-black/45">{t('trascendental.services.integral_label')}</p>
                                <h2 className="mt-3 text-5xl font-black uppercase leading-none">{t(`trascendental.services.${section.key}`)}</h2>
                            </div>
                            <ul className="grid gap-0">
                                {section.items.map((item, index) => (
                                    <li key={item} className="grid gap-4 border-t border-black/20 py-5 sm:grid-cols-[4rem_1fr]">
                                        <span className="text-sm font-black uppercase text-black/40">{String(index + 1).padStart(2, '0')}</span>
                                        <div>
                                            <h3 className="text-3xl font-black uppercase leading-none">
                                                {t(`trascendental.services.items.${item}.title`)}
                                            </h3>
                                            <p className="mt-3 max-w-2xl text-base leading-relaxed text-black/62">
                                                {t(`trascendental.services.items.${item}.text`)}
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ))}
                </div>
            </PageShell>
            <FinalCta />
        </TrascendentalLayout>
    );
}
