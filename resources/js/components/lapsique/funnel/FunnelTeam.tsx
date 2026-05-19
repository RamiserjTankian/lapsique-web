import { usePage } from '@inertiajs/react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import type { PageProps } from '@/types';

export function FunnelTeam() {
    const ref = useSectionEvent('proof_section_viewed', { section: 'team' });
    const { site } = usePage<PageProps>().props;

    return (
        <GlassSection
            eyebrow="Quien esta detras"
            title={site.bookingTeamName || 'Dirección audiovisual para marcas que quieren verse premium'}
            description={site.bookingTeamBio || 'Producción boutique desde Riviera Maya para negocios, marcas personales, artistas y campañas que no pueden verse improvisadas.'}
        >
            <section
                ref={ref}
                className="grid gap-6 rounded-[2rem] border border-border/70 bg-[radial-gradient(circle_at_top,var(--hero-radial-glow),transparent_45%)] p-6 md:grid-cols-[1.15fr_0.85fr] md:p-8"
            >
                <div>
                    <p className="max-w-2xl text-sm leading-7 text-muted-foreground md:text-base">
                        Combinamos dirección, cámara, edición y criterio comercial para que tu contenido no solo se vea bien: tenga intención, se sienta costoso y ayude a convertir mejor.
                    </p>
                    <div className="mt-6 flex flex-wrap gap-2">
                        <SpecBadge highlight>Producción boutique</SpecBadge>
                        <SpecBadge>Riviera Maya</SpecBadge>
                        <SpecBadge>Dirección en set</SpecBadge>
                        <SpecBadge>Entrega lista para pauta</SpecBadge>
                    </div>
                </div>
                <div className="rounded-[1.75rem] border border-border/70 bg-secondary p-5">
                    <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">
                        Ideal para
                    </p>
                    <ul className="mt-4 space-y-3 text-sm text-foreground">
                        <li>Marcas que necesitan verse más serias y mejor posicionadas</li>
                        <li>Negocios que quieren vender con mejor presencia visual</li>
                        <li>Artistas y creadores que necesitan piezas con dirección</li>
                        <li>Campañas, lanzamientos, anuncios y marca personal</li>
                    </ul>
                </div>
            </section>
        </GlassSection>
    );
}
