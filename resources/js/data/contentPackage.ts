import {
    CONTENT_DRONE_DESCRIPTION,
    CONTENT_DRONE_SHOTS,
    CONTENT_PHOTOS_COUNT,
    CONTENT_REEL_DESCRIPTION,
    CONTENT_REEL_DURATION_SECONDS,
} from '@/data/contentOffer';

export interface ContentPackageItem {
    id: string;
    title: string;
    description: string;
}

export const CONTENT_PACKAGE_ITEMS: ContentPackageItem[] = [
    {
        id: 'session',
        title: '1:30 h de sesión',
        description:
            'Tiempo de rodaje en locación con dirección, cámara Sony full frame, dron DJI y ritmo pensado para tu oferta.',
    },
    {
        id: 'reel',
        title: `1 reel editado (${CONTENT_REEL_DURATION_SECONDS} s · Sony)`,
        description: CONTENT_REEL_DESCRIPTION,
    },
    {
        id: 'drone',
        title: `${CONTENT_DRONE_SHOTS} tomas aéreas con dron DJI`,
        description: CONTENT_DRONE_DESCRIPTION,
    },
    {
        id: 'photos',
        title: `${CONTENT_PHOTOS_COUNT} fotos editadas`,
        description: 'Material retocado y consistente para feed, stories, portada y campañas.',
    },
    {
        id: 'cloud',
        title: 'Nube 6 meses',
        description: 'Acceso seguro a reels, fotos y masters editados durante medio año.',
    },
];
