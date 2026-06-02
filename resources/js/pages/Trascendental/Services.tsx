import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { FinalCta, PageShell } from './Partials';

const sections = [
    {
        key: 'production',
        label: 'service_label',
        items: ['concept', 'booking', 'production', 'execution', 'marketing', 'operations'],
        image: '/images/trascendental/events/rebolledo-zal-marina-flyer.jpg',
        imageFit: 'contain',
    },
    {
        key: 'booking',
        label: 'touring_label',
        items: ['artists', 'touring', 'routing'],
        image: '/images/trascendental/artists/jay-tripwire-live.jpeg',
        imageFit: 'cover',
    },
    {
        key: 'capabilities',
        label: 'capabilities_label',
        items: ['cashless', 'ads', 'content', 'logistics'],
        image: '/images/trascendental/about/jaguar-house-wide.jpeg',
        imageFit: 'cover',
    },
] as const;

export default function Services() {
    const { t } = useTranslations();

    return (
        <TrascendentalLayout>
            <PageShell title={t('trascendental.services.title')} intro={t('trascendental.services.intro')}>
                <div className="divide-y divide-black/15 border-y border-black/15">
                    {sections.map((section) => (
                        <section key={section.key} className="grid gap-8 py-10 md:grid-cols-[0.55fr_0.45fr_1fr]">
                            <div>
                                <p className="text-xs font-bold uppercase text-black/45">{t(`trascendental.services.${section.label}`)}</p>
                                <h2 className="mt-3 text-5xl font-black uppercase leading-none">{t(`trascendental.services.${section.key}`)}</h2>
                            </div>
                            <img
                                src={section.image}
                                alt={t(`trascendental.services.${section.key}`)}
                                className={`aspect-[4/5] w-full bg-black/5 ${section.imageFit === 'cover' ? 'object-cover' : 'object-contain'}`}
                                loading="lazy"
                            />
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
