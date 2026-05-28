import { GlassSection } from '@/components/lapsique/GlassSection';
import { InstagramProfileEmbed } from '@/components/lapsique/InstagramProfileEmbed';
import { CREATOR_PROFILE } from '@/data/creatorProfile';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';

export function FunnelTeam() {
    const { t } = useTranslations();
    const ref = useSectionEvent('proof_section_viewed', { section: 'team' });

    return (
        <GlassSection
            title={t('funnel.team.title')}
            description={`${CREATOR_PROFILE.name} · videomaker · photographer`}
            className="text-center [&_.relative.z-10]:justify-center [&_.relative.z-10>div]:text-center"
        >
            <section
                ref={ref}
                className="mx-auto flex max-w-md flex-col items-center gap-8"
            >
                <p className="max-w-sm text-sm leading-relaxed text-muted-foreground">
                    {t('funnel.team.body')}
                </p>

                <InstagramProfileEmbed
                    username={CREATOR_PROFILE.instagramUsername}
                    profileUrl={CREATOR_PROFILE.instagramUrl}
                    handle={CREATOR_PROFILE.instagramHandle}
                    featuredPostUrls={CREATOR_PROFILE.featuredPostUrls}
                    className="w-full"
                />
            </section>
        </GlassSection>
    );
}
