import {
    type KeyboardEvent as ReactKeyboardEvent,
    type ReactNode,
    useEffect,
    useId,
    useMemo,
    useRef,
    useState,
} from 'react';
import { PortfolioMediaRail } from '@/components/lapsique/service-portfolio/PortfolioMediaRail';
import { getVisiblePortfolioProjects } from '@/components/lapsique/service-portfolio/portfolioUtils';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import type { ServicePortfolioBundle } from '@/types';

export interface ServicePortfolioShowcaseProps {
    portfolio: ServicePortfolioBundle;
    eyebrow?: string;
    title?: string;
    description?: string;
    action?: ReactNode;
    className?: string;
}

export function ServicePortfolioShowcase({
    portfolio,
    eyebrow,
    title,
    description,
    action,
    className,
}: ServicePortfolioShowcaseProps) {
    const { locale } = useTranslations();
    const en = locale === 'en';
    const tabsId = useId();
    const sectionRef = useRef<HTMLElement>(null);
    const tabRefs = useRef<Array<HTMLButtonElement | null>>([]);
    const hasTrackedView = useRef(false);
    const [activeProjectIndex, setActiveProjectIndex] = useState(0);
    const projects = useMemo(() => getVisiblePortfolioProjects(portfolio), [portfolio]);
    const visiblePortfolio = useMemo(
        () => ({ ...portfolio, projects }),
        [portfolio, projects],
    );
    const activeProject = projects[activeProjectIndex] ?? projects[0];

    useEffect(() => {
        setActiveProjectIndex(0);
    }, [portfolio.serviceKey, portfolio.hero.id]);

    useEffect(() => {
        const node = sectionRef.current;
        if (!node || hasTrackedView.current) return;

        const trackView = () => {
            if (hasTrackedView.current) return;
            hasTrackedView.current = true;
            trackBookingEvent('service_portfolio_viewed', {
                service_name: portfolio.serviceKey,
                project_count: portfolio.stats.projectCount,
                media_count: portfolio.stats.mediaCount,
                source: 'service_portfolio_showcase',
            });
        };

        if (!('IntersectionObserver' in window)) {
            trackView();
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (!entry?.isIntersecting) return;
                trackView();
                observer.disconnect();
            },
            { threshold: 0.3 },
        );

        observer.observe(node);
        return () => observer.disconnect();
    }, [portfolio.serviceKey, portfolio.stats.mediaCount, portfolio.stats.projectCount]);

    if (!activeProject || projects.length === 0) {
        return null;
    }

    const selectProject = (index: number, focus = false) => {
        const nextIndex = (index + projects.length) % projects.length;
        const nextProject = projects[nextIndex];
        setActiveProjectIndex(nextIndex);
        trackBookingEvent('portfolio_project_selected', {
            service_name: portfolio.serviceKey,
            project_key: nextProject.key,
            project_name: nextProject.label,
            position: nextIndex + 1,
            source: 'service_portfolio_showcase',
        });
        if (focus) tabRefs.current[nextIndex]?.focus();
    };

    const handleTabsKeyDown = (event: ReactKeyboardEvent<HTMLDivElement>) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
            return;
        }

        event.preventDefault();
        if (event.key === 'Home') selectProject(0, true);
        else if (event.key === 'End') selectProject(projects.length - 1, true);
        else selectProject(activeProjectIndex + (event.key === 'ArrowRight' ? 1 : -1), true);
    };

    return (
        <section
            ref={sectionRef}
            className={cn(
                'relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#07090b] text-white',
                className,
            )}
            data-service-portfolio-showcase={portfolio.serviceKey}
        >
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20 lg:py-24">
                <div className="grid gap-8 border-b border-white/16 pb-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div className="max-w-3xl">
                        <p className="font-mono text-[0.68rem] font-medium uppercase tracking-[0.19em] text-primary">
                            {eyebrow ?? (en ? 'Selected real work' : 'Trabajo real seleccionado')}
                        </p>
                        <h2 className="mt-4 text-balance font-display text-4xl font-bold leading-[0.96] tracking-[-0.02em] text-white sm:text-5xl lg:text-6xl">
                            {title ?? (en ? 'Different projects. One clear production standard.' : 'Proyectos distintos. Un mismo estándar de producción.')}
                        </h2>
                        <p className="mt-5 max-w-2xl text-pretty text-base leading-[1.65] text-white/64">
                            {description ?? (en
                                ? 'Browse photographs and videos together as they were delivered for each documented project.'
                                : 'Explora fotografía y video juntos, tal como se produjeron para cada proyecto documentado.')}
                        </p>
                    </div>
                    {action ? (
                        <div
                            className="flex flex-wrap items-center gap-3"
                            onClickCapture={() => {
                                trackBookingEvent('portfolio_cta_clicked', {
                                    service_name: portfolio.serviceKey,
                                    project_key: activeProject.key,
                                    source: 'service_portfolio_showcase',
                                });
                            }}
                        >
                            {action}
                        </div>
                    ) : null}
                </div>

                {projects.length > 1 ? (
                    <div
                        className="mt-6 flex snap-x snap-mandatory gap-2 overflow-x-auto pb-2"
                        role="tablist"
                        aria-label={en ? 'Select a documented project' : 'Selecciona un proyecto documentado'}
                        onKeyDown={handleTabsKeyDown}
                    >
                        {projects.map((project, index) => {
                            const selected = index === activeProjectIndex;

                            return (
                                <button
                                    key={project.key}
                                    ref={(node) => {
                                        tabRefs.current[index] = node;
                                    }}
                                    id={`${tabsId}-tab-${index}`}
                                    type="button"
                                    role="tab"
                                    aria-selected={selected}
                                    aria-controls={`${tabsId}-panel`}
                                    tabIndex={selected ? 0 : -1}
                                    onClick={() => selectProject(index)}
                                    className={cn(
                                        'min-h-11 shrink-0 snap-start border px-4 py-2.5 text-start font-mono text-[0.68rem] font-semibold uppercase tracking-[0.13em] transition-[background-color,border-color,color,transform] duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-[#07090b] active:scale-[0.96] motion-reduce:transition-none',
                                        selected
                                            ? 'border-primary bg-primary text-primary-foreground'
                                            : 'border-white/18 bg-transparent text-white/68 hover:border-white/50 hover:text-white',
                                    )}
                                >
                                    {project.label}
                                </button>
                            );
                        })}
                    </div>
                ) : null}

                <div
                    id={`${tabsId}-panel`}
                    role="tabpanel"
                    aria-labelledby={projects.length > 1 ? `${tabsId}-tab-${activeProjectIndex}` : undefined}
                    aria-label={projects.length === 1 ? activeProject.label : undefined}
                    className="mt-6"
                >
                    <PortfolioMediaRail
                        key={activeProject.key}
                        portfolio={visiblePortfolio}
                        projectKey={activeProject.key}
                        ariaLabel={en ? `${activeProject.label} portfolio` : `Portafolio de ${activeProject.label}`}
                    />
                </div>
            </div>
        </section>
    );
}
