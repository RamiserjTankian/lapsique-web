import { motion } from 'framer-motion';
import { Check, Sparkles, TimerReset, UploadCloud } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';

const items = [
    {
        icon: Check,
        title: '2 reels editados listos para publicar',
        description: 'Piezas con ritmo, hook y acabado premium para anuncios, lanzamientos o presencia organica.',
    },
    {
        icon: Sparkles,
        title: '20 fotos retocadas con look editorial',
        description: 'Material consistente para feed, campañas, portada, stories, press kit y ventas.',
    },
    {
        icon: TimerReset,
        title: 'Dirección + producción + entrega rápida',
        description: 'No solo grabamos: bajamos la idea, dirigimos la sesión y entregamos en cinco días hábiles.',
    },
    {
        icon: UploadCloud,
        title: 'Archivos listos para redes y pauta',
        description: 'Formatos optimizados para Instagram, Meta Ads, lanzamientos, marca personal y campañas.',
    },
];

export function FunnelDeliverables() {
    const ref = useSectionEvent('deliverables_viewed', { section: 'deliverables' });

    return (
        <GlassSection
            eyebrow="Que incluye"
            title="Contenido premium para verte mejor y vender mejor"
            description="Una oferta clara, cerrada y fácil de comprar: piezas pensadas para elevar percepción de marca, autoridad y respuesta comercial."
            className="pt-8"
        >
            <section ref={ref} id="que-incluye" className="grid gap-4 md:grid-cols-2">
                {items.map((item, index) => (
                    <motion.article
                        key={item.title}
                        initial={{ opacity: 0, y: 22 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true, margin: '-40px' }}
                        transition={{ delay: index * 0.06, duration: 0.45 }}
                        className={cn(glassCardVariants(), 'glass-border-glow border p-5 md:p-6')}
                    >
                        <div className="mb-4 inline-flex rounded-2xl border border-primary/20 bg-primary/10 p-3 text-primary">
                            <item.icon className="h-5 w-5" />
                        </div>
                        <h3 className="text-lg font-semibold text-foreground">{item.title}</h3>
                        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {item.description}
                        </p>
                    </motion.article>
                ))}
            </section>
        </GlassSection>
    );
}
