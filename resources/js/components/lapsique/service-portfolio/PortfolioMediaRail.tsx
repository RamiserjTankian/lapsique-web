import {
    type KeyboardEvent as ReactKeyboardEvent,
    type ReactNode,
    useEffect,
    useId,
    useMemo,
    useRef,
    useState,
} from 'react';
import { Images, MapPin, Play } from 'lucide-react';
import { EditorialVideoPlayer } from '@/components/lapsique/EditorialVideoPlayer';
import { PortfolioLightbox } from '@/components/lapsique/PortfolioLightbox';
import { getVisiblePortfolioProjects } from '@/components/lapsique/service-portfolio/portfolioUtils';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import type {
    PortfolioItemData,
    ServicePortfolioBundle,
    ServicePortfolioMedia,
} from '@/types';

export interface PortfolioMediaRailProps {
    portfolio: ServicePortfolioBundle;
    projectKey?: string;
    ariaLabel?: string;
    className?: string;
    action?: ReactNode;
}

function toLightboxItems(items: ServicePortfolioMedia[]): PortfolioItemData[] {
    return items
        .filter((item) => item.kind === 'image')
        .map((item, index) => ({
            id: index + 1,
            title: item.alt || item.projectLabel,
            slug: null,
            type: 'service_portfolio',
            source: 'service-curation',
            caption: item.sessionLabel ?? item.location ?? null,
            tags: [item.projectKey],
            asset_url: item.src,
            poster_url: null,
            playback_url: null,
            embed_url: null,
            youtube_id: null,
            youtube_url: null,
            media_type: 'image',
            is_featured: index === 0,
            orientation: item.orientation,
        }));
}

export function PortfolioMediaRail({
    portfolio,
    projectKey,
    ariaLabel,
    className,
    action,
}: PortfolioMediaRailProps) {
    const { locale } = useTranslations();
    const en = locale === 'en';
    const railId = useId();
    const visibleProjects = useMemo(() => getVisiblePortfolioProjects(portfolio), [portfolio]);
    const project = visibleProjects.find((candidate) => candidate.key === projectKey)
        ?? visibleProjects[0];
    const media = useMemo(
        () => project?.media ?? [],
        [project],
    );
    const [activeIndex, setActiveIndex] = useState(0);
    const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);
    const mediaButtonsRef = useRef<Array<HTMLButtonElement | null>>([]);
    const trackedVideoIds = useRef(new Set<string>());
    const activeMedia = media[activeIndex] ?? media[0];
    const lightboxItems = useMemo(() => toLightboxItems(media), [media]);

    useEffect(() => {
        setActiveIndex(0);
        setLightboxIndex(null);
    }, [project?.key]);

    if (!project || !activeMedia || media.length === 0) {
        return null;
    }

    const trackVideo = (item: ServicePortfolioMedia, source: 'player' | 'selector') => {
        if (item.kind !== 'video' || trackedVideoIds.current.has(item.id)) {
            return;
        }

        trackedVideoIds.current.add(item.id);
        trackBookingEvent('portfolio_media_played', {
            service_name: portfolio.serviceKey,
            asset_id: item.id,
            project_key: item.projectKey,
            content_format: item.kind,
            orientation: item.orientation,
            duration_seconds: item.duration ?? undefined,
            source,
        });
    };

    const selectMedia = (index: number, focus = false) => {
        const nextIndex = (index + media.length) % media.length;
        setActiveIndex(nextIndex);
        setLightboxIndex(null);
        if (focus) mediaButtonsRef.current[nextIndex]?.focus();
    };

    const handleRailKeyDown = (event: ReactKeyboardEvent<HTMLDivElement>) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
            return;
        }

        event.preventDefault();
        if (event.key === 'Home') selectMedia(0, true);
        else if (event.key === 'End') selectMedia(media.length - 1, true);
        else selectMedia(activeIndex + (event.key === 'ArrowRight' ? 1 : -1), true);
    };

    const openImage = (item: ServicePortfolioMedia) => {
        const nextIndex = lightboxItems.findIndex((candidate) => candidate.asset_url === item.src);
        if (nextIndex >= 0) setLightboxIndex(nextIndex);
    };

    return (
        <div
            className={cn('min-w-0', className)}
            role="region"
            aria-label={ariaLabel ?? (en ? `${project.label} media` : `Contenido de ${project.label}`)}
            data-portfolio-media-rail={project.key}
        >
            <div className="flex min-w-0 flex-col">
                {media.length > 1 ? (
                    <div
                        className="mt-3 flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 [scrollbar-color:oklch(var(--primary))_transparent]"
                        role="tablist"
                        aria-label={en ? `${project.label} media selection` : `Selección de contenido de ${project.label}`}
                        onKeyDown={handleRailKeyDown}
                    >
                        {media.map((item, index) => {
                            const selected = index === activeIndex;

                            return (
                                <button
                                    key={`${item.id}:${item.src}`}
                                    ref={(node) => {
                                        mediaButtonsRef.current[index] = node;
                                    }}
                                    id={`${railId}-tab-${index}`}
                                    type="button"
                                    role="tab"
                                    aria-selected={selected}
                                    aria-controls={`${railId}-panel`}
                                    tabIndex={selected ? 0 : -1}
                                    onClick={() => selectMedia(index)}
                                    className={cn(
                                        'group/thumb relative min-h-24 min-w-[7.5rem] flex-[0_0_7.5rem] snap-start overflow-hidden border bg-black text-start transition-[border-color,opacity,transform] duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-[#07090b] active:scale-[0.96] motion-reduce:transition-none sm:min-w-[9rem] sm:flex-basis-[9rem]',
                                        selected
                                            ? 'border-primary opacity-100'
                                            : 'border-white/18 opacity-68 hover:border-white/50 hover:opacity-100',
                                    )}
                                    aria-label={`${index + 1}. ${item.alt}`}
                                >
                                    {item.poster || item.kind === 'image' ? (
                                        <img
                                            src={item.poster ?? item.src}
                                            alt=""
                                            loading="lazy"
                                            decoding="async"
                                            className="absolute inset-0 h-full w-full object-cover"
                                        />
                                    ) : (
                                        <span className="absolute inset-0 bg-white/[0.04]" />
                                    )}
                                    <span className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent" />
                                    <span className="absolute inset-x-2 bottom-2 flex items-center justify-between gap-2 font-mono text-[0.6rem] uppercase tracking-[0.1em] text-white">
                                        <span>{String(index + 1).padStart(2, '0')}</span>
                                        {item.kind === 'video' ? <Play className="size-3.5 fill-current" aria-hidden="true" /> : null}
                                    </span>
                                </button>
                            );
                        })}
                    </div>
                ) : null}

                <div
                    id={`${railId}-panel`}
                    role="tabpanel"
                    aria-labelledby={media.length > 1 ? `${railId}-tab-${activeIndex}` : undefined}
                    aria-label={media.length === 1 ? activeMedia.alt : undefined}
                    className="relative order-first overflow-hidden bg-black outline outline-1 -outline-offset-1 outline-white/15"
                >
                    <div
                        className={cn(
                            'relative mx-auto w-full bg-black',
                            activeMedia.orientation === 'vertical'
                                ? 'aspect-[4/5] max-h-[46rem] max-w-[36.8rem]'
                                : 'aspect-video',
                        )}
                    >
                        {activeMedia.kind === 'video' ? (
                            <div className="h-full w-full">
                                <EditorialVideoPlayer
                                    key={activeMedia.id}
                                    src={activeMedia.src}
                                    poster={activeMedia.poster}
                                    title={activeMedia.alt || activeMedia.projectLabel}
                                    preload="none"
                                    autoPlay={false}
                                    muted={false}
                                    hasAudio={activeMedia.hasAudio ?? false}
                                    onPlay={() => trackVideo(activeMedia, 'player')}
                                    className="h-full w-full"
                                    videoClassName={cn(
                                        'h-full w-full bg-black',
                                        activeMedia.orientation === 'vertical' ? 'object-contain' : 'object-cover',
                                    )}
                                />
                            </div>
                        ) : (
                            <button
                                type="button"
                                className="group/image relative block h-full w-full cursor-zoom-in overflow-hidden bg-black focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary"
                                onClick={() => openImage(activeMedia)}
                                aria-label={en ? `Open ${activeMedia.alt}` : `Abrir ${activeMedia.alt}`}
                            >
                                <img
                                    src={activeMedia.src}
                                    alt={activeMedia.alt}
                                    loading="lazy"
                                    decoding="async"
                                    className={cn(
                                        'h-full w-full transition-transform duration-300 group-hover/image:scale-[1.02] motion-reduce:transition-none',
                                        activeMedia.orientation === 'vertical' ? 'object-contain' : 'object-cover',
                                    )}
                                />
                                <span className="absolute bottom-4 right-4 flex min-h-11 items-center gap-2 border border-white/30 bg-black/72 px-3 font-mono text-[0.65rem] uppercase tracking-[0.12em] text-white">
                                    <Images className="size-4" aria-hidden="true" />
                                    {en ? 'Open photograph' : 'Abrir fotografía'}
                                </span>
                            </button>
                        )}
                    </div>

                    <div className="pointer-events-none absolute inset-x-0 top-0 flex flex-wrap items-start justify-between gap-3 bg-gradient-to-b from-black/80 to-transparent px-4 pb-12 pt-4 sm:px-5">
                        <div>
                            <p className="font-mono text-[0.64rem] uppercase tracking-[0.16em] text-primary">
                                {project.label}
                            </p>
                            {activeMedia.sessionLabel ? (
                                <p className="mt-1 text-sm font-semibold text-white">
                                    {activeMedia.sessionLabel}
                                </p>
                            ) : null}
                        </div>
                        {activeMedia.location ? (
                            <p className="inline-flex items-center gap-1.5 text-xs text-white/70">
                                <MapPin className="size-3.5 text-primary" aria-hidden="true" />
                                {activeMedia.location}
                            </p>
                        ) : null}
                    </div>
                </div>
            </div>

            {action ? (
                <div
                    className="mt-6 flex flex-wrap items-center gap-3"
                    onClickCapture={() => {
                        trackBookingEvent('portfolio_cta_clicked', {
                            service_name: portfolio.serviceKey,
                            project_key: project.key,
                            source: 'portfolio_media_rail',
                        });
                    }}
                >
                    {action}
                </div>
            ) : null}

            <PortfolioLightbox
                items={lightboxItems}
                activeIndex={lightboxIndex}
                onClose={() => setLightboxIndex(null)}
                onNavigate={setLightboxIndex}
            />
        </div>
    );
}
