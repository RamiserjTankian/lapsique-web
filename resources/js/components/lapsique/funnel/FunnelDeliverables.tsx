import { motion } from 'framer-motion';
import { Check, Sparkles, TimerReset, UploadCloud } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';

const items = [
    {
        icon: Check,
        title: '1 reel editado listo para pauta',
        description: 'Pieza con hook, producto y acción clara para negocios que necesitan publicar y probar anuncios.',
    },
    {
        icon: Sparkles,
        title: '10 fotos editadas para sostener campaña',
        description: 'Material consistente para feed, portada, stories, catálogo ligero y ventas por mensaje.',
    },
    {
        icon: TimerReset,
        title: 'Dirección + producción + entrega rápida',
        description: 'No solo grabamos: aterrizamos la oferta, dirigimos la sesión y entregamos en cinco días hábiles.',
    },
    {
        icon: UploadCloud,
        title: 'Nube segura 1 año con tu material',
        description:
            'Accede a reels, fotos y masters editados durante 12 meses; guardamos tu material de forma segura.',
    },
];

export function FunnelDeliverables() {
    const ref = useSectionEvent('deliverables_viewed', { section: 'deliverables' });

    return (
        <GlassSection
            eyebrow="Que incluye"
            title="Paquete de producción para negocio, anuncios y redes"
            description="La reserva compra una sesión clara: material útil para elevar percepción, repetir presencia y medir respuesta comercial."
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
