import { useMemo } from 'react';
import { ArrowRight, Building2, Clapperboard, MapPin } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { Button } from '@/components/ui/button';
import { getPortfolioTrustStats } from '@/data/portfolioTrust';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { LucideIcon } from 'lucide-react';
import type { PageProps, PortfolioItemData } from '@/types';

const STAT_ICONS: Record<string, LucideIcon> = {
    productions: Clapperboard,
    clients: Building2,
    local: MapPin,
};

const STAT_ICON_STYLES: Record<string, string> = {
    productions: 'border-amber-500/35 bg-amber-500/12 text-amber-600 dark:text-amber-400',
    clients: 'border-sky-500/35 bg-sky-500/12 text-sky-600 dark:text-sky-400',
    local: 'border-emerald-500/35 bg-emerald-500/12 text-emerald-600 dark:text-emerald-400',
};

function getPortfolioPreviewUrl(item: PortfolioItemData): string | null {
    return item.poster_url ?? item.asset_url ?? null;
}

export function PortfolioTrustSection({
    portfolioItems,
}: {
    portfolioItems: PortfolioItemData[];
}) {
    const { t } = useTranslations();
    const stats = useMemo(() => getPortfolioTrustStats(t), [t]);
    const { ziggy } = usePage<PageProps>().props;
    const ref = useSectionEvent('proof_section_viewed', { section: 'portfolio_trust' });

    const previewItems = portfolioItems
        .filter((item) => Boolean(getPortfolioPreviewUrl(item)))
        .slice(0, 6);

    return (
        <GlassSection
            eyebrow={t('funnel.trust.section_eyebrow')}
            title={t('funnel.trust.section_title')}
            description={t('funnel.trust.section_description')}
            action={
                <Button variant="outline" size="sm" className="hidden sm:inline-flex" asChild>
                    <Link href={route('portfolio.index', undefined, false, ziggy)}>
                        {t('funnel.trust.cta')}
                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </Button>
            }
        >
            <section ref={ref} id="portafolio-confianza" className="scroll-mt-24 space-y-8">
                <div className="grid gap-3 sm:grid-cols-3">
                    {stats.map((stat) => {
                        const Icon = STAT_ICONS[stat.id] ?? Clapperboard;

                        return (
                            <article
                                key={stat.id}
                                className={cn(
                                    glassCardVariants(),
                                    'glass-border-glow flex flex-col gap-3 border p-4 md:p-5',
                                )}
                            >
                                <div className="flex items-start gap-3">
                                    <span
                                        className={cn(
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border',
                                            STAT_ICON_STYLES[stat.id]
                                                ?? 'border-primary/20 bg-primary/10 text-primary',
                                        )}
                                    >
                                        <Icon className="h-4 w-4" aria-hidden />
                                    </span>
                                    <p className="font-display text-2xl font-bold leading-none text-foreground">
                                        {stat.value}
                                    </p>
                                </div>
                                <div>
                                    <h3 className="text-base font-semibold text-foreground">{stat.title}</h3>
                                </div>
                            </article>
                        );
                    })}
                </div>

                {previewItems.length > 0 && (
                    <div className="grid grid-cols-3 gap-2 sm:grid-cols-6">
                        {previewItems.map((item) => {
                            const previewUrl = getPortfolioPreviewUrl(item);

                            if (!previewUrl) {
                                return null;
                            }

                            return (
                                <Link
                                    key={item.id}
                                    href={route('portfolio.index', undefined, false, ziggy)}
                                    className={cn(
                                        glassCardVariants(),
                                        'group relative aspect-[4/5] overflow-hidden',
                                    )}
                                >
                                    <img
                                        src={previewUrl}
                                        alt={item.title ?? t('funnel.trust.portfolio_alt')}
                                        className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.05]"
                                        loading="lazy"
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-transparent opacity-80 transition group-hover:opacity-100" />
                                </Link>
                            );
                        })}
                    </div>
                )}

                <div className="flex justify-center sm:hidden">
                    <Button variant="outline" asChild>
                        <Link href={route('portfolio.index', undefined, false, ziggy)}>
                            {t('funnel.trust.portfolio_full_cta')}
                            <ArrowRight className="h-4 w-4" />
                        </Link>
                    </Button>
                </div>
            </section>
        </GlassSection>
    );
}
