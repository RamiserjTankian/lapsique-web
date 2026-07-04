export interface DroneSessionClip {
    id: string;
    title: string;
    caption: string;
    src: string;
    poster: string;
    useCase: string;
}

export const DRONE_SESSION_CLIPS: DroneSessionClip[] = [
    {
        id: 'hero',
        title: 'Avance de obra con contexto de zona',
        caption: 'Toma editada de Goba con obra, entorno verde y costa en el mismo encuadre.',
        src: '/videos/drone-sessions/hero.mp4',
        poster: '/images/drone-sessions/hero.jpg',
        useCase: 'Obra',
    },
    {
        id: 'yacht',
        title: 'Yates y lifestyle',
        caption: 'Planos amplios para presentar navegación, club, marina o servicio premium.',
        src: '/videos/drone-sessions/yacht.mp4',
        poster: '/images/drone-sessions/yacht.jpg',
        useCase: 'Yates',
    },
    {
        id: 'construction',
        title: 'Avances de obra',
        caption: 'Goba editado: vista limpia para mostrar progreso, escala, accesos y entorno.',
        src: '/videos/drone-sessions/goba-construction.mp4',
        poster: '/images/drone-sessions/goba-construction.jpg',
        useCase: 'Obra',
    },
    {
        id: 'lot',
        title: 'Terrenos y lotes',
        caption: 'Lectura clara de entorno, accesos, colindancias y dimensión del predio.',
        src: '/videos/drone-sessions/lot.mp4',
        poster: '/images/drone-sessions/lot.jpg',
        useCase: 'Terrenos',
    },
    {
        id: 'event',
        title: 'Eventos sociales',
        caption: 'Tomas de ubicación y ambiente para aftermovie, invitación o recap comercial.',
        src: '/videos/drone-sessions/event.mp4',
        poster: '/images/drone-sessions/event.jpg',
        useCase: 'Eventos',
    },
    {
        id: 'condo',
        title: 'Condominios, Airbnb y hotelería',
        caption: 'Composición aérea para fachada, amenidades, playa cercana y valor de estancia.',
        src: '/videos/drone-sessions/condo.mp4',
        poster: '/images/drone-sessions/condo.jpg',
        useCase: 'Condominios + Airbnb',
    },
    {
        id: 'djset',
        title: 'DJ sets y experiencias',
        caption: 'Tomas aéreas para ubicar el venue, el ambiente y la escala del evento.',
        src: '/videos/drone-sessions/djset.mp4',
        poster: '/images/drone-sessions/djset.jpg',
        useCase: 'DJ set',
    },
];

export const DRONE_SESSION_HERO_CLIP = DRONE_SESSION_CLIPS[0];

export const DRONE_SESSION_CONSTRUCTION_CLIPS: DroneSessionClip[] = [
    {
        id: 'goba-construction',
        title: 'Goba / The Reserve',
        caption: 'Video editado con color final para presentar avance, escala y contexto de zona.',
        src: '/videos/drone-sessions/goba-construction.mp4',
        poster: '/images/drone-sessions/goba-construction.jpg',
        useCase: 'Obra editada',
    },
    {
        id: 'okom-construction',
        title: 'OKOM Living Tulum',
        caption: 'Avance reciente normalizado desde material D-Log para revisión de progreso.',
        src: '/videos/drone-sessions/okom-construction.mp4',
        poster: '/images/drone-sessions/okom-construction.jpg',
        useCase: 'Obra reciente',
    },
];

export const DRONE_SESSION_BOOKING_CLIP = DRONE_SESSION_CONSTRUCTION_CLIPS[0];
