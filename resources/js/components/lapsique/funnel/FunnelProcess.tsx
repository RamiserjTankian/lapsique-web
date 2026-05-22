import { GlassSection } from '@/components/lapsique/GlassSection';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';

const steps = [
    {
        step: '01',
        title: 'Agenda tu horario',
        description: 'Elige fecha, comparte tu idea y asegura la producción desde el home.',
    },
    {
        step: '02',
        title: 'Dirigimos la sesión',
        description: 'Definimos tomas, ritmo, locación y dirección visual para que no improvises frente a cámara.',
    },
    {
        step: '03',
        title: 'Recibes material listo para vender',
        description: 'Entregamos reel y fotos editadas para lanzar campañas, alimentar contenido y reforzar tu marca.',
    },
];

export function FunnelProcess() {
    const ref = useSectionEvent('process_viewed', { section: 'process' });

    return (
        <GlassSection
            eyebrow="Proceso"
            title="Así se cierra en tres pasos"
            description="La compra es simple y la experiencia se siente boutique: poca fricción, dirección clara y entrega profesional."
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
