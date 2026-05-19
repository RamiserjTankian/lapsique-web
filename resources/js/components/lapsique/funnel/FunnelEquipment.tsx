import { GlassSection } from '@/components/lapsique/GlassSection';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { useSectionEvent } from '@/hooks/useSectionEvent';

export function FunnelEquipment() {
    const ref = useSectionEvent('equipment_viewed', { section: 'equipment' });

    return (
        <div ref={ref}>
        <GlassSection
            eyebrow="Produccion"
            title="Equipo y look que sostienen la promesa premium"
            description="La percepción de marca también se construye desde la captura: cámara full frame, óptica, luz y criterio visual."
        >
            <div className="rounded-[2rem] border border-border/70 bg-secondary p-6 md:p-8">
                <div className="flex flex-wrap gap-2">
                    <SpecBadge highlight>Sony α7 full frame</SpecBadge>
                    <SpecBadge>Lentes luminosos</SpecBadge>
                    <SpecBadge>Luz de apoyo</SpecBadge>
                    <SpecBadge>Audio para piezas habladas</SpecBadge>
                    <SpecBadge>Edición premium</SpecBadge>
                </div>
                <p className="mt-5 max-w-3xl text-sm leading-7 text-muted-foreground md:text-base">
                    Todo el sistema está pensado para producir imágenes más limpias, más consistentes y más utilizables en venta, pauta y construcción de marca.
                </p>
            </div>
        </GlassSection>
        </div>
    );
}
