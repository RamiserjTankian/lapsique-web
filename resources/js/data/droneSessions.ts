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
        id: 'construction-goba-aerial',
        title: 'Goba / avance aéreo',
        caption: 'Plano superior con cuadrilla y estructura para mostrar progreso real, actividad y escala.',
        src: '/videos/drone-sessions/construction-goba-aerial.mp4',
        poster: '/images/drone-sessions/construction-goba-aerial.jpg',
        useCase: 'Goba / reporte de avance',
    },
    {
        id: 'construction-goba-detail',
        title: 'Goba / detalle de obra',
        caption: 'Material editado de trabajo en sitio para reforzar que el reporte muestra avance tangible.',
        src: '/videos/drone-sessions/construction-goba-detail.mp4',
        poster: '/images/drone-sessions/construction-goba-detail.jpg',
        useCase: 'Detalle constructivo',
    },
    {
        id: 'construction-goba-wide',
        title: 'Goba / contexto del desarrollo',
        caption: 'Vista amplia para explicar ubicación, accesos, entorno verde y dimensión del proyecto.',
        src: '/videos/drone-sessions/construction-goba-wide.mp4',
        poster: '/images/drone-sessions/construction-goba-wide.jpg',
        useCase: 'Contexto de zona',
    },
    {
        id: 'construction-okom-nov-aerial',
        title: 'OKOM noviembre / avance temprano',
        caption: 'Vista superior de etapa inicial para documentar progreso y comparar visitas con precisión.',
        src: '/videos/drone-sessions/construction-okom-nov-aerial.mp4',
        poster: '/images/drone-sessions/construction-okom-nov-aerial.jpg',
        useCase: 'OKOM noviembre 2024',
    },
    {
        id: 'construction-okom-nov-site',
        title: 'OKOM noviembre / obra en sitio',
        caption: 'Recorrido de cuadrilla y estructura para dar contexto técnico al reporte de avance.',
        src: '/videos/drone-sessions/construction-okom-nov-site.mp4',
        poster: '/images/drone-sessions/construction-okom-nov-site.jpg',
        useCase: 'Estructura y cuadrilla',
    },
    {
        id: 'construction-okom-jun-interior',
        title: 'OKOM junio / interiores e instalaciones',
        caption: 'Material reciente para mostrar avances interiores, instalaciones y detalles terminados.',
        src: '/videos/drone-sessions/construction-okom-jun-interior.mp4',
        poster: '/images/drone-sessions/construction-okom-jun-interior.jpg',
        useCase: 'OKOM junio 2026',
    },
    {
        id: 'construction-okom-jun-context',
        title: 'OKOM junio / contexto reciente',
        caption: 'Plano de contexto para conectar avance, ubicación y dimensión actual del desarrollo.',
        src: '/videos/drone-sessions/construction-okom-jun-context.mp4',
        poster: '/images/drone-sessions/construction-okom-jun-context.jpg',
        useCase: 'Contexto reciente',
    },
];

function constructionUniqueClip(
    id: string,
    title: string,
    caption: string,
    useCase: string,
): DroneSessionClip {
    return {
        id,
        title,
        caption,
        src: `/videos/drone-sessions/${id}.mp4`,
        poster: `/images/drone-sessions/${id}.jpg`,
        useCase,
    };
}

export const DRONE_SESSION_CONSTRUCTION_LANDING_CLIPS = {
    hero: constructionUniqueClip(
        'construction-unique-01-hero-goba-context',
        'GOBA / contexto de obra y entorno',
        'Aproximacion aerea con desarrollo, entorno verde y escala del proyecto.',
        'Hero',
    ),
    bookingPopup: constructionUniqueClip(
        'construction-unique-02-popup-goba-topdown',
        'GOBA / top-down de estructura',
        'Vista superior clara para abrir la reserva con evidencia de avance real.',
        'Reserva',
    ),
    side: constructionUniqueClip(
        'construction-unique-03-side-okom-nov-site',
        'OKOM noviembre / obra activa',
        'Toma exterior con maquinaria, estructura y actividad en sitio.',
        'Contexto lateral',
    ),
    proof: [
        constructionUniqueClip(
            'construction-unique-04-proof-goba-detail',
            'GOBA / detalle de concreto',
            'Close-up de trabajo tangible para reforzar la evidencia del reporte.',
            'Prueba',
        ),
        constructionUniqueClip(
            'construction-unique-05-proof-okom-jun-electric',
            'OKOM junio / instalaciones electricas',
            'Avance interior de tuberias y cajas electricas con subtitulos del reporte.',
            'Prueba',
        ),
    ],
    camera: [
        constructionUniqueClip(
            'construction-unique-06-goba-concrete-close',
            'GOBA / vertido de concreto',
            'Detalle de concreto y equipo desde camara en sitio.',
            'Camara en sitio',
        ),
        constructionUniqueClip(
            'construction-unique-07-goba-safety-worker',
            'GOBA / operacion en andamio',
            'Trabajador y estructura para dar escala humana al avance.',
            'Camara en sitio',
        ),
        constructionUniqueClip(
            'construction-unique-08-goba-building-wide',
            'GOBA / estructura completa',
            'Plano abierto de fachada, volumen y entorno inmediato.',
            'Camara en sitio',
        ),
        constructionUniqueClip(
            'construction-unique-09-goba-roof-crew',
            'GOBA / cuadrilla en losa',
            'Cuadrilla trabajando en cubierta para mostrar operacion real.',
            'Camara en sitio',
        ),
        constructionUniqueClip(
            'construction-unique-10-okom-nov-ground-pour',
            'OKOM noviembre / colado a nivel de piso',
            'Avance temprano con cuadrilla y concreto en sitio.',
            'Camara en sitio',
        ),
        constructionUniqueClip(
            'construction-unique-11-okom-nov-rebar-formwork',
            'OKOM noviembre / armado y cimbra',
            'Varillas, cimbra y trabajadores para explicar etapa constructiva.',
            'Camara en sitio',
        ),
        constructionUniqueClip(
            'construction-unique-12-okom-jun-ceiling-pipes',
            'OKOM junio / tuberias en plafon',
            'Instalaciones visibles para reportar avance interior.',
            'Instalaciones',
        ),
        constructionUniqueClip(
            'construction-unique-13-okom-jun-ducts',
            'OKOM junio / ductos principales',
            'Ductos y pasos de instalaciones con subtitulos del reporte.',
            'Instalaciones',
        ),
        constructionUniqueClip(
            'construction-unique-14-okom-jun-ladder-services',
            'OKOM junio / servicios y escalera',
            'Recorrido interior para comunicar estado de obra y circulaciones.',
            'Instalaciones',
        ),
        constructionUniqueClip(
            'construction-unique-15-okom-jun-corridor-circuit',
            'OKOM junio / circuito interior',
            'Pasillo y circuito de instalaciones para seguimiento tecnico.',
            'Instalaciones',
        ),
        constructionUniqueClip(
            'construction-unique-16-okom-jun-pool-waterproofing',
            'OKOM junio / alberca e impermeabilizacion',
            'Material para documentar etapa de alberca y control de humedad.',
            'Amenidades',
        ),
        constructionUniqueClip(
            'construction-unique-17-okom-jun-exterior-workers',
            'OKOM junio / cuadrilla exterior',
            'Trabajadores y muros exteriores para cerrar la variedad de obra.',
            'Exterior',
        ),
    ],
    progress: [
        constructionUniqueClip(
            'construction-unique-18-progress-goba-zone',
            'GOBA / ubicacion y zona',
            'Contexto amplio para explicar acceso, entorno y valor del desarrollo.',
            'Reporte',
        ),
        constructionUniqueClip(
            'construction-unique-19-progress-okom-nov-plan',
            'OKOM noviembre / planta desde arriba',
            'Vista superior para comparar avance y distribucion.',
            'Reporte',
        ),
        constructionUniqueClip(
            'construction-unique-20-progress-okom-jun-amenities',
            'OKOM junio / zonas de amenidades',
            'Evidencia de espacios comunes y avance exterior.',
            'Reporte',
        ),
        constructionUniqueClip(
            'construction-unique-21-progress-goba-evening',
            'GOBA / contexto atmosferico',
            'Plano abierto distinto para cierre narrativo y escala del desarrollo.',
            'Reporte',
        ),
    ],
    finalCta: constructionUniqueClip(
        'construction-unique-22-final-okom-nov-overview',
        'OKOM noviembre / vista general',
        'Vista amplia para cierre comercial y llamada a reservar.',
        'CTA final',
    ),
    closing: constructionUniqueClip(
        'construction-unique-23-closing-goba-rooftop',
        'GOBA / cierre en losa',
        'Plano horizontal de cuadrilla en cubierta para reforzar accion y escala.',
        'Cierre',
    ),
};

export const DRONE_SESSION_BOOKING_CLIP = DRONE_SESSION_CONSTRUCTION_CLIPS[0];
