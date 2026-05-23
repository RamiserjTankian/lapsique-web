import { GlassSection } from '@/components/lapsique/GlassSection';
import { InstagramProfileEmbed } from '@/components/lapsique/InstagramProfileEmbed';
import { CREATOR_PROFILE } from '@/data/creatorProfile';
import { useSectionEvent } from '@/hooks/useSectionEvent';

export function FunnelTeam() {
    const ref = useSectionEvent('proof_section_viewed', { section: 'team' });

    return (
        <GlassSection
            eyebrow="Quién está detrás"
            title={`${CREATOR_PROFILE.name} · ${CREATOR_PROFILE.role}`}
            description="Dirección y captura para piezas que se sienten premium en cámara y en pauta."
        >
            <section
                ref={ref}
                className="grid gap-6 rounded-[2rem] border border-border/70 bg-[radial-gradient(circle_at_top,var(--hero-radial-glow),transparent_45%)] p-6 lg:grid-cols-[minmax(0,1fr)_minmax(300px,400px)] lg:p-8"
            >
                <div className="flex flex-col gap-6">
                    <div>
                        <p className="font-display text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                            {CREATOR_PROFILE.name}
                        </p>
                        <p className="mt-2 text-sm font-medium uppercase tracking-[0.2em] text-primary md:text-base">
                            {CREATOR_PROFILE.role}
                        </p>
                    </div>

                    <div className="rounded-[1.75rem] border border-border/70 bg-secondary p-5 md:p-6">
                        <p className="text-xs font-semibold uppercase tracking-[0.25em] text-muted-foreground">
                            Marcas con las que he colaborado
                        </p>
                        <ul className="mt-4 flex flex-wrap gap-2">
                            {CREATOR_PROFILE.brands.map((brand) => (
                                <li
                                    key={brand.name}
                                    className="rounded-full border border-border/80 bg-background px-3 py-1.5 text-xs font-medium text-foreground md:text-sm"
                                >
                                    {brand.name}
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                <InstagramProfileEmbed
                    username={CREATOR_PROFILE.instagramUsername}
                    profileUrl={CREATOR_PROFILE.instagramUrl}
                    handle={CREATOR_PROFILE.instagramHandle}
                    bio={CREATOR_PROFILE.instagramBio}
                    featuredPostUrls={CREATOR_PROFILE.featuredPostUrls}
                    className="w-full lg:sticky lg:top-24"
                />
            </section>
        </GlassSection>
    );
}
