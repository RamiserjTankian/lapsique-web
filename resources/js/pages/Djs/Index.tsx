import { Head } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { DjCard } from '@/components/lapsique/DjCard';
import { useTranslations } from '@/hooks/useTranslations';
import type { DjItem } from '@/types';

interface DjsIndexProps {
    djs: DjItem[];
    highlightedDj?: DjItem | null;
}

export default function DjsIndex({ djs, highlightedDj }: DjsIndexProps) {
    const { t } = useTranslations();
    const rest = highlightedDj
        ? djs.filter((d) => d.id !== highlightedDj.id)
        : djs;

    return (
        <SiteLayout>
            <Head title={t('pages.djs.title')} />
            <GlassSection
                eyebrow={t('pages.djs.lineup_eyebrow')}
                title={t('pages.djs.title')}
                description={
                    highlightedDj
                        ? t('pages.djs.highlighted', { name: highlightedDj.name })
                        : t('pages.djs.lineup_description')
                }
            >
                {highlightedDj && (
                    <div className="mb-8">
                        <DjCard dj={highlightedDj} variant="featured" />
                    </div>
                )}
                <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    {rest.map((dj, i) => (
                        <DjCard key={dj.id} dj={dj} index={i} />
                    ))}
                </div>
            </GlassSection>
        </SiteLayout>
    );
}
