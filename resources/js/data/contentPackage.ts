export interface ContentPackageItem {
    id: string;
    title: string;
    description: string;
}

export const CONTENT_PACKAGE_ITEMS: ContentPackageItem[] = [
    {
        id: 'session',
        title: '1:30 h de sesión',
        description: 'Tiempo de rodaje en locación con dirección, cámara y ritmo pensado para tu oferta.',
    },
    {
        id: 'reel',
        title: '1 reel editado',
        description: 'Pieza vertical lista para pauta y redes, con hook, producto y llamada a la acción.',
    },
    {
        id: 'photos',
        title: '10 fotos editadas',
        description: 'Material retocado y consistente para feed, stories, portada y campañas.',
    },
    {
        id: 'cloud',
        title: 'Nube 6 meses',
        description: 'Acceso seguro a reels, fotos y masters editados durante medio año.',
    },
];
