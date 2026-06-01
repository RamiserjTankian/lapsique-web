import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { FinalCta, PageShell } from './Partials';

export default function About() {
    const { t } = useTranslations();

    return (
        <TrascendentalLayout>
            <PageShell title={t('trascendental.about.title')} intro={t('trascendental.about.intro')}>
                <div className="grid gap-10 border-y border-black/15 py-10 lg:grid-cols-[1fr_1fr]">
                    <p className="text-4xl font-black uppercase leading-none">
                        Desde 2017 construimos fechas alrededor del minimal, house y techno con una idea simple: buena curaduria, buena operacion y comunidad real.
                    </p>
                    <div className="space-y-6 text-lg leading-relaxed text-black/70">
                        <p>
                            Trascendental empezo como un colectivo de fiestas privadas y evoluciono en una plataforma de eventos, label y booking para artistas y espacios que buscan una fecha bien resuelta.
                        </p>
                        <p>
                            No operamos como proveedor tecnico ni como intermediario aislado. Desarrollamos el concepto, conectamos el booking, cuidamos la produccion y damos seguimiento hasta convertir la fecha en resultado.
                        </p>
                    </div>
                </div>
                <div className="mt-10 grid gap-3 md:grid-cols-[1.15fr_0.85fr]">
                    <img
                        src="/images/trascendental/cases/rebolledo-lasers.webp"
                        alt="Rebolledo en una produccion de Trascendental"
                        className="aspect-[16/10] h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                        <img
                            src="/images/portfolio/photos/097-traumer-shonky-309e63566a.webp"
                            alt="Evento Umi con Traumer y Shonky"
                            className="aspect-[16/10] h-full w-full object-cover"
                            loading="lazy"
                        />
                        <img
                            src="/images/trascendental/cases/priku-crowd.webp"
                            alt="Publico durante una fecha de Priku"
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
