import { Clock3, Cloud, Film, Images } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { CONTENT_PACKAGE_ITEMS } from '@/data/contentPackage';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { LucideIcon } from 'lucide-react';

const ITEM_ICONS: Record<string, LucideIcon> = {
    session: Clock3,
    reel: Film,
    photos: Images,
    cloud: Cloud,
};

export function ContentPackageSection() {
    const ref = useSectionEvent('package_includes_viewed', { section: 'content_package' });

    return (
        <GlassSection
            eyebrow="Paquete"
            title="Qué contiene tu paquete de contenido"
            description="Todo lo que apartas al reservar: tiempo de sesión, piezas editadas y respaldo en la nube."
            className="py-12 md:py-16"
        >
            <section
                ref={ref}
                id="que-incluye"
                className="scroll-mt-24 grid gap-3 sm:grid-cols-2"
            >
                {CONTENT_PACKAGE_ITEMS.map((item) => {
                    const Icon = ITEM_ICONS[item.id] ?? Film;

                    return (
                        <article
                            key={item.id}
                            className={cn(glassCardVariants(), 'glass-border-glow flex gap-3 border p-4 md:p-5')}
                        >
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-primary/20 bg-primary/10 text-primary">
                                <Icon className="h-4 w-4" aria-hidden />
                            </span>
                            <div className="min-w-0">
                                <h3 className="text-base font-semibold text-foreground">{item.title}</h3>
                                <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                                    {item.description}
                                </p>
                            </div>
                        </article>
                    );
                })}
            </section>
        </GlassSection>
    );
}
