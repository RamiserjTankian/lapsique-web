import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export type SiteNavigationLink = {
    id: string;
    label: string;
    description?: string;
    href: string;
};

export type SiteNavigationGroup = {
    id: string;
    label: string;
    links: SiteNavigationLink[];
};

export type SiteNavigation = {
    portfolio: SiteNavigationLink;
    groups: SiteNavigationGroup[];
};

export function buildSiteNavigation(
    ziggy: PageProps['ziggy'],
    locale: string,
): SiteNavigation {
    const en = locale === 'en';
    const home = route('home', undefined, false, ziggy);
    const videos = route('videos.index', undefined, false, ziggy);

    return {
        portfolio: {
            id: 'portfolio',
            label: en ? 'Portfolio' : 'Portafolio',
            href: route('portfolio.index', undefined, false, ziggy),
        },
        groups: [
            {
                id: 'scene',
                label: en ? 'Scene' : 'Escena',
                links: [
                    {
                        id: 'djs',
                        label: 'DJs',
                        description: en ? 'Artists documented by Lapsique.' : 'Artistas documentados por Lapsique.',
                        href: route('djs.index', undefined, false, ziggy),
                    },
                    {
                        id: 'events',
                        label: en ? 'Events' : 'Eventos',
                        description: en ? 'Shows, collaborations, and archive.' : 'Shows, colaboraciones y archivo.',
                        href: route('events.index', undefined, false, ziggy),
                    },
                    {
                        id: 'sessions',
                        label: 'Psique Sessions',
                        description: en ? 'Complete DJ sets produced by Lapsique.' : 'DJ sets completos producidos por Lapsique.',
                        href: videos,
                    },
                    {
                        id: 'aftermovies',
                        label: 'Aftermovies',
                        description: en ? 'Events and nightlife in motion.' : 'Eventos y nightlife en movimiento.',
                        href: `${videos}#aftermovies`,
                    },
                ],
            },
            {
                id: 'services',
                label: en ? 'Services' : 'Servicios',
                links: [
                    {
                        id: 'content-creation',
                        label: en ? 'Social media content' : 'Contenido para redes',
                        description: en ? 'Reels and photography for Instagram, TikTok, and ads.' : 'Reels y fotografía para Instagram, TikTok y anuncios.',
                        href: route('content-creation.show', undefined, false, ziggy),
                    },
                    {
                        id: 'business-reels',
                        label: en ? 'Reels for business' : 'Reels para negocios',
                        description: en ? 'Commercial content built for ads.' : 'Contenido comercial pensado para anuncios.',
                        href: `${home}#servicios`,
                    },
                    {
                        id: 'food-reels',
                        label: en ? 'Restaurant reels' : 'Reels para restaurantes',
                        href: route('food-reels.show', undefined, false, ziggy),
                    },
                    {
                        id: 'record-dj-set',
                        label: en ? 'Record a DJ set' : 'Grabar un DJ set',
                        href: route('djset.show', undefined, false, ziggy),
                    },
                    {
                        id: 'electronic-event-coverage',
                        label: en ? 'Electronic event coverage' : 'Cobertura de eventos electrónicos',
                        description: en ? 'Aftermovie, drone shots, and edited photography.' : 'Aftermovie, tomas de dron y fotografía editada.',
                        href: route('electronic-event-coverage.show', undefined, false, ziggy),
                    },
                    {
                        id: 'multi-camera',
                        label: en ? 'Multicamera DJ sets' : 'Producción multicámara',
                        description: en ? '10 drops, continuous Log video, audio, and event photos.' : '10 drops, video continuo en Log, audio y fotos del evento.',
                        href: route('multi-camera.show', undefined, false, ziggy),
                    },
                    {
                        id: 'drone',
                        label: en ? 'Drone flights' : 'Vuelos con dron',
                        href: route('drone-sessions.show', undefined, false, ziggy),
                    },
                    {
                        id: 'construction',
                        label: en ? 'Construction progress' : 'Avances de obra',
                        href: route('construction-progress.show', undefined, false, ziggy),
                    },
                ],
            },
        ],
    };
}
