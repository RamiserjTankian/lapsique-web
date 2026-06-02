import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { FinalCta, PageShell } from './Partials';

const impactValues = ['300+', '30+', '30+', '12+', null] as const;
const impactLabelKeys = ['events', 'bookings', 'venues', 'countries', 'operations'] as const;

export default function About() {
    const { t } = useTranslations();

    return (
        <TrascendentalLayout>
            <PageShell
                title={t('trascendental.about.page_title')}
                intro={t('trascendental.about.page_intro')}
            >
                <div className="grid gap-10 border-y border-black/15 py-10 lg:grid-cols-[1fr_1fr]">
                    <p className="break-words text-3xl font-black uppercase leading-none sm:text-4xl">
                        {t('trascendental.about.lead')}
                    </p>
                    <div className="space-y-6 text-lg leading-relaxed text-black/70">
                        <p>
                            {t('trascendental.about.body_1')}
                        </p>
                        <p>
                            {t('trascendental.about.body_2')}
                        </p>
                    </div>
                </div>
                <section className="mt-10 bg-black p-5 text-white sm:p-7">
                    <p className="text-xs font-bold uppercase text-white/50">{t('trascendental.about.impact')}</p>
                    <div className="mt-6 grid gap-px bg-white/20 sm:grid-cols-2 lg:grid-cols-5">
                        {impactLabelKeys.map((key, index) => (
                            <div key={key} className="bg-black p-4">
                                <p className="text-3xl font-black uppercase leading-none">{impactValues[index] ?? t('trascendental.home.impact.since')}</p>
                                <p className="mt-3 text-xs font-bold uppercase leading-relaxed text-white/58">{t(`trascendental.home.impact.${key}`)}</p>
                            </div>
                        ))}
                    </div>
                </section>
                <section className="mt-10 grid gap-6 border-y border-black/15 py-8 lg:grid-cols-[0.55fr_0.45fr] lg:items-end">
                    <div>
                        <p className="text-xs font-bold uppercase text-black/45">{t('trascendental.about.presence')}</p>
                        <h2 className="mt-3 text-5xl font-black uppercase leading-none">{t('trascendental.about.region')}</h2>
                    </div>
                    <p className="text-lg leading-relaxed text-black/65">
                        {t('trascendental.about.network')}
                    </p>
                </section>
                <div className="mt-10 grid gap-3 md:grid-cols-[1.15fr_0.85fr]">
                    <img
                        src="/images/trascendental/about/jaguar-house-wide.jpeg"
                        alt={t('trascendental.about.jaguar_alt')}
                        className="aspect-[16/10] h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                        <img
                            src="/images/trascendental/about/jaguar-house-crowd.jpeg"
                            alt={t('trascendental.about.crowd_alt')}
                            className="aspect-[16/10] h-full w-full object-cover"
                            loading="lazy"
                        />
                        <img
                            src="/images/trascendental/cases/rebolledo-lasers.webp"
                            alt={t('trascendental.about.stage_alt')}
                            className="aspect-[16/10] h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                </div>
            </PageShell>
            <FinalCta />
        </TrascendentalLayout>
    );
}
