import { useMemo } from 'react';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import type { ServicePortfolioBundle } from '@/types';

export interface ServiceProofBandProps {
    portfolio: ServicePortfolioBundle;
    eyebrow?: string;
    title?: string;
    description?: string;
    className?: string;
    inverse?: boolean;
}

export function ServiceProofBand({
    portfolio,
    eyebrow,
    title,
    description,
    className,
    inverse = false,
}: ServiceProofBandProps) {
    const { locale } = useTranslations();
    const en = locale === 'en';
    const calculatedStats = useMemo(() => {
        const media = portfolio.projects.flatMap((project) => project.media);

        return {
            images: portfolio.stats.imageCount ?? media.filter((item) => item.kind === 'image').length,
            videos: portfolio.stats.videoCount ?? media.filter((item) => item.kind === 'video').length,
        };
    }, [portfolio]);

    if (portfolio.projects.length === 0 || portfolio.stats.mediaCount === 0) {
        return null;
    }

    const metrics = [
        {
            value: portfolio.stats.projectCount,
            label: en ? 'documented projects' : 'proyectos documentados',
        },
        {
            value: calculatedStats.videos,
            label: en ? 'real videos' : 'videos reales',
        },
        {
            value: calculatedStats.images,
            label: en ? 'selected photographs' : 'fotografías seleccionadas',
        },
    ].filter((metric) => metric.value > 0);

    return (
        <section
            className={cn(
                'border-y',
                inverse ? 'border-white/16 text-white' : 'border-border text-foreground',
                className,
            )}
            aria-label={title ?? (en ? 'Verified portfolio evidence' : 'Evidencia verificada del portafolio')}
            data-service-proof-band={portfolio.serviceKey}
        >
            <div className="grid gap-8 py-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:items-end">
                <div className="max-w-2xl">
                    <p className="font-mono text-[0.68rem] font-medium uppercase tracking-[0.19em] text-primary">
                        {eyebrow ?? (en ? 'Real Lapsique archive' : 'Archivo real de Lapsique')}
                    </p>
                    <h2
                        className={cn(
                            'mt-3 text-balance font-display text-3xl font-bold leading-[0.98] tracking-[-0.02em] sm:text-4xl',
                            inverse ? 'text-white' : 'text-foreground',
                        )}
                    >
                        {title ?? (en ? 'Proof grouped by project, not by isolated files.' : 'Evidencia agrupada por proyecto, no por archivos sueltos.')}
                    </h2>
                    {description ? (
                        <p
                            className={cn(
                                'mt-4 max-w-xl text-pretty text-sm leading-[1.65]',
                                inverse ? 'text-white/62' : 'text-muted-foreground',
                            )}
                        >
                            {description}
                        </p>
                    ) : null}
                </div>

                <dl className={cn(
                    'grid grid-cols-2 gap-x-5 gap-y-6 border-t pt-6 sm:grid-cols-3 lg:border-t-0 lg:pt-0',
                    inverse ? 'border-white/16' : 'border-border',
                )}>
                    {metrics.map((metric) => (
                        <div key={metric.label} className="min-w-0">
                            <dt
                                className={cn(
                                    'mt-2 text-pretty text-xs font-semibold uppercase leading-[1.35] tracking-[0.1em]',
                                    inverse ? 'text-white/52' : 'text-muted-foreground',
                                )}
                            >
                                {metric.label}
                            </dt>
                            <dd className="order-first font-mono-tabular text-3xl font-bold text-primary sm:text-4xl">
                                {metric.value}
                            </dd>
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}
