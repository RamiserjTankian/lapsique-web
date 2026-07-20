export type LandingServiceKey = 'reels_de_comida' | 'cobertura_eventos_electronica' | 'sesiones_de_dron' | 'avances_de_obra';

type LocalizedText = {
    es: string;
    en: string;
};

export type LandingPackage = {
    name: LocalizedText;
    priceLabel: LocalizedText;
    description: LocalizedText;
    includes: LocalizedText[];
};

export type LandingFaq = {
    question: LocalizedText;
    answer: LocalizedText;
};

export type LandingConfig = {
    serviceKey: LandingServiceKey;
    path: string;
    trackingService: string;
    leadType: string;
    headline: LocalizedText;
    intro: LocalizedText;
    primaryCta: LocalizedText;
    finalCta: LocalizedText;
    whatsappMessage: LocalizedText;
    problemTitle: LocalizedText;
    problem: LocalizedText[];
    solutionTitle: LocalizedText;
    solution: LocalizedText[];
    outcomesTitle: LocalizedText;
    outcomes: LocalizedText[];
    audienceTitle: LocalizedText;
    audience: LocalizedText[];
    deliverablesTitle: LocalizedText;
    deliverables: LocalizedText[];
    packages: LandingPackage[];
    process: Array<{
        title: LocalizedText;
        description: LocalizedText;
    }>;
    faqs: LandingFaq[];
    leadForm: {
        title: LocalizedText;
        description: LocalizedText;
        needOptions: LocalizedText[];
    };
};

export const SERVICE_AREAS = [
    'Playa del Carmen',
    'Tulum',
    'Cancun',
    'Puerto Morelos',
    'Puerto Aventuras',
    'Akumal',
    'Mayakoba',
    'Cozumel',
    'Riviera Maya',
] as const;

export const SERVICE_LANDING_CONFIGS: Record<LandingServiceKey, LandingConfig> = {
    reels_de_comida: {
        serviceKey: 'reels_de_comida',
        path: '/reels-de-comida',
        trackingService: 'reels_de_comida',
        leadType: 'restaurant',
        headline: {
            es: 'Reels de comida para restaurantes en Riviera Maya',
            en: 'Food reels for restaurants in Riviera Maya',
        },
        intro: {
            es: 'Reels y fotos de platillos, barra y ambiente para Instagram, TikTok, Google y campañas de Meta Ads.',
            en: 'Reels and photos of dishes, bar service, and atmosphere for Instagram, TikTok, Google, and Meta Ads.',
        },
        primaryCta: {
            es: 'Cotizar reels para mi restaurante',
            en: 'Quote reels for my restaurant',
        },
        finalCta: {
            es: 'Cotizar reels',
            en: 'Quote reels',
        },
        whatsappMessage: {
            es: 'Hola, quiero cotizar una sesion de reels de comida para mi restaurante en [ciudad].',
            en: 'Hi, I want to quote a food reels session for my restaurant in [city].',
        },
        problemTitle: {
            es: 'Haz que el restaurante provoque antojo antes de la visita.',
            en: 'Make the restaurant look worth visiting before guests arrive.',
        },
        problem: [
            {
                es: 'La comida, el servicio y el ambiente deben verse tan cuidados en pantalla como en la mesa.',
                en: 'Food, service, and atmosphere should look as considered on screen as they do at the table.',
            },
        ],
        solutionTitle: {
            es: 'Grabamos comida, servicio y ambiente en una sola sesión.',
            en: 'We capture food, service, and atmosphere in one session.',
        },
        solution: [
            {
                es: 'Entregamos piezas verticales y fotografías editadas, listas para publicar o pautar.',
                en: 'We deliver edited vertical pieces and photos, ready to publish or run as ads.',
            },
        ],
        outcomesTitle: {
            es: 'Lo que recibes',
            en: 'What you receive',
        },
        outcomes: [
            { es: 'Reels verticales de platillos y servicio.', en: 'Vertical reels of dishes and service.' },
            { es: 'Fotografías editadas para menú y redes.', en: 'Edited photos for menus and social media.' },
            { es: 'Versiones listas para campañas de Meta Ads.', en: 'Versions ready for Meta Ads campaigns.' },
        ],
        audienceTitle: {
            es: 'Ideal para',
            en: 'Best for',
        },
        audience: [
            { es: 'Restaurantes', en: 'Restaurants' },
            { es: 'Sushi bars', en: 'Sushi bars' },
            { es: 'Cafés', en: 'Cafes' },
            { es: 'Bares y beach clubs', en: 'Bars and beach clubs' },
        ],
        deliverablesTitle: {
            es: 'Que grabamos',
            en: 'What we capture',
        },
        deliverables: [
            { es: 'Platillos principales', en: 'Hero dishes' },
            { es: 'Preparación y manos en acción', en: 'Preparation and hands in action' },
            { es: 'Bebidas y barra', en: 'Drinks and bar' },
            { es: 'Ambiente y entrada', en: 'Atmosphere and entrance' },
            { es: 'Detalles de mesa y experiencia', en: 'Table details and experience' },
            { es: 'Tomas para anuncios', en: 'Shots for ads' },
        ],
        packages: [
            {
                name: { es: 'Paquete Express', en: 'Express package' },
                priceLabel: { es: 'Desde $3,000 MXN', en: 'From $3,000 MXN' },
                description: { es: 'Para restaurantes que necesitan contenido rapido para redes.', en: 'For restaurants that need quick social content.' },
                includes: [
                    { es: '1 hora de grabacion', en: '1 hour of production' },
                    { es: '3 reels verticales', en: '3 vertical reels' },
                    { es: '10 fotos editadas', en: '10 edited photos' },
                    { es: 'Entrega en carpeta digital', en: 'Digital folder delivery' },
                ],
            },
            {
                name: { es: 'Paquete Contenido', en: 'Content package' },
                priceLabel: { es: 'Desde $5,500 MXN', en: 'From $5,500 MXN' },
                description: { es: 'Para tener material para varias publicaciones.', en: 'For several days or weeks of posts.' },
                includes: [
                    { es: '2 horas de grabacion', en: '2 hours of production' },
                    { es: '5 reels verticales', en: '5 vertical reels' },
                    { es: '20 fotos editadas', en: '20 edited photos' },
                    { es: 'Version util para anuncios', en: 'Ad-ready version' },
                ],
            },
            {
                name: { es: 'Plan Mensual Restaurante', en: 'Monthly restaurant plan' },
                priceLabel: { es: 'Cotizacion personalizada', en: 'Custom quote' },
                description: { es: 'Para restaurantes que publican cada semana.', en: 'For restaurants that publish every week.' },
                includes: [
                    { es: '2 a 4 visitas mensuales', en: '2 to 4 monthly visits' },
                    { es: 'Reels recurrentes', en: 'Recurring reels' },
                    { es: 'Fotos para redes', en: 'Social photos' },
                    { es: 'Planeacion por temporada', en: 'Seasonal content planning' },
                ],
            },
        ],
        process: [
            { title: { es: 'Nos escribes', en: 'Message us' }, description: { es: 'Cuentanos que tipo de restaurante tienes y en que ciudad estas.', en: 'Tell us your restaurant type and city.' } },
            { title: { es: 'Definimos objetivo', en: 'Set the goal' }, description: { es: 'Elegimos platillos, bebidas o experiencia a mostrar.', en: 'We choose the dishes, drinks, or experience to show.' } },
            { title: { es: 'Grabamos la sesion', en: 'Produce the session' }, description: { es: 'Producimos el contenido con direccion visual clara.', en: 'We produce the content with clear visual direction.' } },
            { title: { es: 'Editamos y entregamos', en: 'Edit and deliver' }, description: { es: 'Recibes reels, fotos y material listo para publicar o pautar.', en: 'You receive reels, photos, and content ready to publish or run as ads.' } },
        ],
        faqs: [
            { question: { es: '¿Cuánto dura una sesión?', en: 'How long does a session take?' }, answer: { es: 'Entre 1 y 2 horas, según el número de platillos, espacios y piezas acordadas.', en: 'Between 1 and 2 hours, depending on the dishes, spaces, and agreed deliverables.' } },
            { question: { es: '¿En qué zonas trabajan?', en: 'Which areas do you cover?' }, answer: { es: 'Trabajamos en Playa del Carmen, Tulum, Cancún y otras zonas de Riviera Maya.', en: 'We work in Playa del Carmen, Tulum, Cancun, and other Riviera Maya areas.' } },
            { question: { es: '¿Los reels sirven para anuncios?', en: 'Can the reels be used for ads?' }, answer: { es: 'Sí. Podemos preparar versiones para publicaciones orgánicas y campañas de Meta Ads.', en: 'Yes. We can prepare versions for organic posts and Meta Ads campaigns.' } },
            { question: { es: '¿La sesión incluye fotos?', en: 'Does the session include photos?' }, answer: { es: 'Depende del paquete. Definimos la cantidad antes de reservar.', en: 'It depends on the package. We confirm the amount before booking.' } },
        ],
        leadForm: {
            title: { es: 'Cotiza una sesión para tu restaurante', en: 'Quote a restaurant session' },
            description: { es: 'Cuéntanos el tipo de restaurante, la zona y las piezas que necesitas.', en: 'Tell us the restaurant type, location, and pieces you need.' },
            needOptions: [
                { es: 'Reels', en: 'Reels' },
                { es: 'Fotos', en: 'Photos' },
                { es: 'Plan mensual', en: 'Monthly plan' },
                { es: 'Campana de Ads', en: 'Ads campaign' },
            ],
        },
    },
    cobertura_eventos_electronica: {
        serviceKey: 'cobertura_eventos_electronica',
        path: '/cobertura-eventos-electronica',
        trackingService: 'electronic_event_coverage',
        leadType: 'electronic_event',
        headline: {
            es: 'Cobertura de eventos electrónicos en Riviera Maya',
            en: 'Electronic event coverage in Riviera Maya',
        },
        intro: {
            es: 'Aftermovie, fotografías editadas y tomas de dron para guardar la energía real de tu fecha.',
            en: 'Aftermovie, edited photography, and drone shots that preserve the real energy of your date.',
        },
        primaryCta: { es: 'Reservar cobertura de evento', en: 'Book event coverage' },
        finalCta: { es: 'Apartar fecha', en: 'Reserve a date' },
        whatsappMessage: {
            es: 'Hola, quiero cotizar cobertura para un evento de música electrónica en [fecha/venue].',
            en: 'Hi, I want to quote coverage for an electronic music event on [date/venue].',
        },
        problemTitle: {
            es: 'La noche pasa rápido; el material tiene que conservar lo que se sintió.',
            en: 'The night moves fast; the material needs to hold on to how it felt.',
        },
        problem: [
            {
                es: 'Un evento no se comunica con una foto aislada: necesita escala, luz, pista, artistas y la respuesta de la gente.',
                en: 'An event is not communicated with one isolated photo: it needs scale, light, dancefloor, artists, and the crowd response.',
            },
        ],
        solutionTitle: {
            es: 'Documentamos la noche desde el lenguaje de la escena.',
            en: 'We document the night through the language of the scene.',
        },
        solution: [
            {
                es: 'Planeamos momentos clave con producción, combinamos cámara en tierra y dron viable, y editamos una pieza que puedes volver a publicar.',
                en: 'We plan key moments with production, combine ground camera and viable drone coverage, and edit a piece you can publish again.',
            },
        ],
        outcomesTitle: { es: 'Cobertura base', en: 'Base coverage' },
        outcomes: [
            { es: '1 aftermovie editado.', en: '1 edited aftermovie.' },
            { es: '30 fotografías editadas desde distintos ángulos.', en: '30 photographs edited from different angles.' },
            { es: 'Tomas de dron sujetas a viabilidad.', en: 'Drone footage, subject to feasibility.' },
        ],
        audienceTitle: { es: 'Ideal para', en: 'Best for' },
        audience: [
            { es: 'Clubs y venues', en: 'Clubs and venues' },
            { es: 'Promotores y colectivos', en: 'Promoters and collectives' },
            { es: 'Festivales y showcases', en: 'Festivals and showcases' },
            { es: 'Marcas activando en la escena', en: 'Brands activating in the scene' },
        ],
        deliverablesTitle: { es: 'Lo que capturamos', en: 'What we capture' },
        deliverables: [
            { es: 'Entrada, venue y atmósfera', en: 'Arrival, venue, and atmosphere' },
            { es: 'Artistas, cabina y momentos clave', en: 'Artists, booth, and key moments' },
            { es: 'Pista, público y energía', en: 'Dancefloor, crowd, and energy' },
            { es: 'Detalles de producción e iluminación', en: 'Production and lighting details' },
            { es: 'Contexto aéreo cuando es viable', en: 'Aerial context when feasible' },
        ],
        packages: [
            {
                name: { es: 'Cobertura electrónica', en: 'Electronic coverage' },
                priceLabel: { es: '$4,500 MXN', en: '$4,500 MXN' },
                description: { es: 'Un paquete directo para comunicar la noche con foto, aftermovie y dron viable.', en: 'A direct package for communicating the night with photography, an aftermovie, and viable drone coverage.' },
                includes: [
                    { es: '1 aftermovie editado', en: '1 edited aftermovie' },
                    { es: '30 fotos editadas desde distintos ángulos', en: '30 photos edited from different angles' },
                    { es: 'Tomas de dron sujetas a viabilidad', en: 'Drone footage subject to feasibility' },
                    { es: 'Planeación con producción', en: 'Planning with production' },
                ],
            },
        ],
        process: [
            { title: { es: 'Compártenos tu fecha', en: 'Share your date' }, description: { es: 'Venue, horario, lineup y los momentos que no pueden faltar.', en: 'Venue, schedule, lineup, and the moments that cannot be missed.' } },
            { title: { es: 'Definimos el plan', en: 'Define the plan' }, description: { es: 'Alineamos acceso, iluminación, puntos de cámara y viabilidad del dron.', en: 'We align access, lighting, camera points, and drone feasibility.' } },
            { title: { es: 'Cubrimos el evento', en: 'Cover the event' }, description: { es: 'Capturamos la noche con dirección editorial y atención a los momentos reales.', en: 'We capture the night with editorial direction and attention to real moments.' } },
            { title: { es: 'Editamos y entregamos', en: 'Edit and deliver' }, description: { es: 'Recibes las fotografías y la pieza final listas para los canales acordados.', en: 'You receive the photographs and final piece ready for the agreed channels.' } },
        ],
        faqs: [
            { question: { es: '¿Qué incluye la cobertura de evento?', en: 'What does event coverage include?' }, answer: { es: 'La cobertura base incluye un aftermovie editado, 30 fotografías editadas desde distintos ángulos y tomas de dron cuando la ubicación, el clima y la normativa lo permiten.', en: 'Base coverage includes one edited aftermovie, 30 photographs edited from different angles, and drone footage when location, weather, and regulations allow it.' } },
            { question: { es: '¿Cuánto cuesta la cobertura?', en: 'How much does coverage cost?' }, answer: { es: 'La cobertura base tiene un precio fijo de $4,500 MXN. Si el evento requiere horario extendido, más entregables o logística especial, lo cotizamos antes de reservar.', en: 'Base coverage has a fixed price of $4,500 MXN. If the event requires extended hours, more deliverables, or special logistics, we quote it before booking.' } },
            { question: { es: '¿Cubren eventos en Playa del Carmen, Tulum y Cancún?', en: 'Do you cover events in Playa del Carmen, Tulum, and Cancun?' }, answer: { es: 'Sí. Trabajamos en Playa del Carmen, Tulum, Cancún y Riviera Maya. Confirma tu venue y horario para revisar la cobertura.', en: 'Yes. We work in Playa del Carmen, Tulum, Cancun, and Riviera Maya. Share your venue and schedule so we can review coverage.' } },
            { question: { es: '¿El aftermovie funciona para redes sociales?', en: 'Does the aftermovie work for social media?' }, answer: { es: 'Sí. Editamos la pieza para comunicar la energía del evento y entregarla lista para publicar en los canales acordados.', en: 'Yes. We edit the piece to communicate the event energy and deliver it ready to publish on the agreed channels.' } },
        ],
        leadForm: {
            title: { es: 'Cotiza cobertura para tu evento', en: 'Quote coverage for your event' },
            description: { es: 'Compártenos fecha, venue, ciudad y formato del evento.', en: 'Share the date, venue, city, and event format.' },
            needOptions: [
                { es: 'Aftermovie', en: 'Aftermovie' },
                { es: 'Fotografía', en: 'Photography' },
                { es: 'Dron', en: 'Drone' },
                { es: 'Cobertura completa', en: 'Full coverage' },
            ],
        },
    },
    sesiones_de_dron: {
        serviceKey: 'sesiones_de_dron',
        path: '/sesiones-de-dron',
        trackingService: 'sesiones_de_dron',
        leadType: 'hotel_property_venue',
        headline: {
            es: 'Sesiones de dron en Riviera Maya para hoteles, propiedades y negocios',
            en: 'Drone sessions in Riviera Maya for hotels, properties, and businesses',
        },
        intro: {
            es: 'Foto y video aéreo para mostrar ubicación, arquitectura, escala y entorno en redes, web y presentaciones.',
            en: 'Aerial photo and video to show location, architecture, scale, and surroundings across social, web, and presentations.',
        },
        primaryCta: { es: 'Cotizar sesion de dron', en: 'Quote drone session' },
        finalCta: { es: 'Cotizar dron', en: 'Quote drone' },
        whatsappMessage: { es: 'Hola, quiero cotizar una sesion de dron para un proyecto en [ciudad/zona].', en: 'Hi, I want to quote a drone session for a project in [city/area].' },
        problemTitle: { es: 'Muestra la escala y el entorno desde el aire.', en: 'Show scale and surroundings from the air.' },
        problem: [
            { es: 'Una toma aérea explica en segundos la ubicación, los accesos y la relación del proyecto con su entorno.', en: 'An aerial shot can explain location, access, and the project’s relationship with its surroundings in seconds.' },
        ],
        solutionTitle: { es: 'Planeamos cada vuelo según tu objetivo y la zona.', en: 'We plan each flight around your goal and the area.' },
        solution: [
            { es: 'Revisamos clima, seguridad y restricciones antes de confirmar la sesión y el plan de tomas.', en: 'We review weather, safety, and restrictions before confirming the session and shot plan.' },
        ],
        outcomesTitle: { es: 'Entregables', en: 'Deliverables' },
        outcomes: [
            { es: 'Video vertical para redes.', en: 'Vertical video for social media.' },
            { es: 'Tomas horizontales para web y presentaciones.', en: 'Horizontal footage for web and presentations.' },
            { es: 'Fotografías aéreas editadas.', en: 'Edited aerial photographs.' },
        ],
        audienceTitle: { es: 'Ideal para', en: 'Best for' },
        audience: [
            { es: 'Hoteles boutique', en: 'Boutique hotels' },
            { es: 'Villas y Airbnb premium', en: 'Villas and premium Airbnb' },
            { es: 'Restaurantes y beach clubs', en: 'Restaurants and beach clubs' },
            { es: 'Inmobiliarias y desarrollos', en: 'Real estate teams and developments' },
            { es: 'Eventos y experiencias turísticas', en: 'Events and tourist experiences' },
        ],
        deliverablesTitle: { es: 'Que podemos grabar', en: 'What we can capture' },
        deliverables: [
            { es: 'Fachada y acceso', en: 'Facade and access' },
            { es: 'Ubicación y entorno', en: 'Location and surroundings' },
            { es: 'Amenidades y arquitectura', en: 'Amenities and architecture' },
            { es: 'Albercas, terrazas y beach clubs', en: 'Pools, terraces, and beach clubs' },
            { es: 'Propiedades y terrenos', en: 'Properties and lots' },
        ],
        packages: [
            { name: { es: 'Dron Express', en: 'Express drone' }, priceLabel: { es: 'Cotizacion segun ubicacion', en: 'Quote by location' }, description: { es: 'Tomas aereas rapidas para negocios o propiedades.', en: 'Quick aerial shots for businesses or properties.' }, includes: [{ es: 'Sesion de dron', en: 'Drone session' }, { es: 'Tomas seleccionadas', en: 'Selected shots' }, { es: 'Video corto vertical', en: 'Short vertical video' }, { es: 'Fotos aereas editadas', en: 'Edited aerial photos' }] },
            { name: { es: 'Dron Comercial', en: 'Commercial drone' }, priceLabel: { es: 'Cotizacion segun proyecto', en: 'Project quote' }, description: { es: 'Para hoteles, villas, restaurantes y propiedades.', en: 'For hotels, villas, restaurants, and properties.' }, includes: [{ es: 'Planeacion de tomas', en: 'Shot planning' }, { es: 'Video vertical y horizontal', en: 'Vertical and horizontal video' }, { es: 'Fotos aereas', en: 'Aerial photos' }, { es: 'Material para web y redes', en: 'Web and social material' }] },
            { name: { es: 'Dron Premium / Proyecto', en: 'Premium / Project drone' }, priceLabel: { es: 'Cotizacion personalizada', en: 'Custom quote' }, description: { es: 'Para desarrollos, campanas, eventos o proyectos con logistica.', en: 'For developments, campaigns, events, or logistics-heavy projects.' }, includes: [{ es: 'Produccion planificada', en: 'Planned production' }, { es: 'Dron + camara en tierra si aplica', en: 'Drone + ground camera if needed' }, { es: 'Video hero y reels', en: 'Hero video and reels' }, { es: 'Material para presentacion o campana', en: 'Presentation or campaign material' }] },
        ],
        process: [
            { title: { es: 'Cuentanos el proyecto', en: 'Tell us the project' }, description: { es: 'Tipo de lugar, ubicacion, objetivo y fecha tentativa.', en: 'Place type, location, goal, and tentative date.' } },
            { title: { es: 'Revisamos viabilidad', en: 'Review viability' }, description: { es: 'Validamos clima, zona, horarios y seguridad.', en: 'We check weather, area, timing, and safety.' } },
            { title: { es: 'Grabamos las tomas', en: 'Capture the shots' }, description: { es: 'Realizamos vuelo y produccion segun objetivo visual.', en: 'We fly and produce according to the visual goal.' } },
            { title: { es: 'Editamos y entregamos', en: 'Edit and deliver' }, description: { es: 'Recibes material listo para redes, web, anuncios o ventas.', en: 'You receive material ready for social, web, ads, or sales.' } },
        ],
        faqs: [
            { question: { es: '¿El vuelo depende del clima?', en: 'Does the flight depend on weather?' }, answer: { es: 'Sí. Confirmamos el vuelo según viento, lluvia, ubicación y condiciones de seguridad.', en: 'Yes. We confirm the flight based on wind, rain, location, and safety conditions.' } },
            { question: { es: '¿Entregan video vertical y horizontal?', en: 'Do you deliver vertical and horizontal video?' }, answer: { es: 'Sí. Definimos ambos formatos antes de grabar.', en: 'Yes. We define both formats before production.' } },
            { question: { es: '¿Funciona para villas y Airbnb?', en: 'Does it work for villas and Airbnb?' }, answer: { es: 'Sí. El dron ayuda a mostrar ubicación, accesos, amenidades y entorno.', en: 'Yes. Drone footage helps show location, access, amenities, and surroundings.' } },
            { question: { es: '¿Pueden grabar también desde tierra?', en: 'Can you also film from the ground?' }, answer: { es: 'Sí. Podemos combinar dron, cámara y fotografía en la misma producción.', en: 'Yes. We can combine drone, camera, and photography in the same production.' } },
        ],
        leadForm: {
            title: { es: 'Cotiza una sesión de dron', en: 'Quote a drone session' },
            description: { es: 'Comparte el tipo de proyecto, la zona y los formatos que necesitas.', en: 'Share the project type, area, and formats you need.' },
            needOptions: [
                { es: 'Video vertical', en: 'Vertical video' },
                { es: 'Video horizontal', en: 'Horizontal video' },
                { es: 'Fotos aereas', en: 'Aerial photos' },
                { es: 'Paquete completo', en: 'Full package' },
            ],
        },
    },
    avances_de_obra: {
        serviceKey: 'avances_de_obra',
        path: '/avances-de-obra',
        trackingService: 'avances_de_obra',
        leadType: 'construction_real_estate',
        headline: { es: 'Avances de obra con dron, foto y video en Riviera Maya', en: 'Construction progress with drone, photo, and video in Riviera Maya' },
        intro: { es: 'Seguimiento de obra con fotografía, video y dron, organizado por fecha para reportes, inversionistas y ventas.', en: 'Construction tracking with photo, video, and drone, organized by date for reports, investors, and sales.' },
        primaryCta: { es: 'Cotizar plan mensual de obra', en: 'Quote monthly construction plan' },
        finalCta: { es: 'Cotizar obra', en: 'Quote construction' },
        whatsappMessage: { es: 'Hola, quiero cotizar documentacion de avances de obra para un proyecto en [ciudad/zona].', en: 'Hi, I want to quote construction progress documentation for a project in [city/area].' },
        problemTitle: { es: 'Documenta el avance con claridad y por fecha.', en: 'Document progress clearly and by date.' },
        problem: [
            { es: 'Una cobertura constante permite comparar etapas y compartir evidencia útil con clientes, inversionistas y equipos de venta.', en: 'Consistent coverage makes it easier to compare stages and share useful evidence with clients, investors, and sales teams.' },
        ],
        solutionTitle: { es: 'Foto, video y dron con el mismo criterio visual.', en: 'Photo, video, and drone with one visual standard.' },
        solution: [
            { es: 'Acordamos la frecuencia, el recorrido y los entregables antes de cada visita.', en: 'We agree on frequency, route, and deliverables before each visit.' },
        ],
        outcomesTitle: { es: 'Entregables', en: 'Deliverables' },
        outcomes: [
            { es: 'Fotografías editadas por visita.', en: 'Edited photographs from each visit.' },
            { es: 'Video corto de avance.', en: 'Short progress video.' },
            { es: 'Archivo organizado por fecha.', en: 'Archive organized by date.' },
        ],
        audienceTitle: { es: 'Ideal para', en: 'Best for' },
        audience: [
            { es: 'Constructoras', en: 'Builders' },
            { es: 'Arquitectos e ingenieros', en: 'Architects and engineers' },
            { es: 'Desarrolladoras', en: 'Developers' },
            { es: 'Inmobiliarias y brokers', en: 'Real estate teams and brokers' },
            { es: 'Project managers', en: 'Project managers' },
            { es: 'Inversionistas y dueños de obra', en: 'Investors and owners' },
        ],
        deliverablesTitle: { es: 'Que documentamos', en: 'What we document' },
        deliverables: [
            { es: 'Avance general de obra', en: 'General construction progress' },
            { es: 'Estructura, fachada e interiores', en: 'Structure, facade, and interiors' },
            { es: 'Instalaciones y amenidades', en: 'Installations and amenities' },
            { es: 'Terreno, accesos y contexto', en: 'Lot, access, and context' },
            { es: 'Comparativos mensuales', en: 'Monthly comparisons' },
            { es: 'Material para reportes y ventas', en: 'Material for reports and sales' },
        ],
        packages: [
            { name: { es: 'Plan Mensual Basico', en: 'Basic monthly plan' }, priceLabel: { es: 'Cotizacion segun proyecto', en: 'Project quote' }, description: { es: 'Para obras que necesitan evidencia mensual.', en: 'For projects that need monthly evidence.' }, includes: [{ es: '1 visita mensual', en: '1 monthly visit' }, { es: 'Fotos editadas', en: 'Edited photos' }, { es: 'Video corto de avance', en: 'Short progress video' }, { es: 'Carpeta por fecha', en: 'Folder by date' }] },
            { name: { es: 'Plan Seguimiento', en: 'Progress plan' }, priceLabel: { es: 'Cotizacion segun proyecto', en: 'Project quote' }, description: { es: 'Para constructoras y desarrolladoras que necesitan mayor constancia.', en: 'For builders and developers that need more consistency.' }, includes: [{ es: '2 visitas mensuales', en: '2 monthly visits' }, { es: 'Foto + video + dron', en: 'Photo + video + drone' }, { es: 'Reels de avance', en: 'Progress reels' }, { es: 'Material para reportes y redes', en: 'Material for reports and social' }] },
            { name: { es: 'Plan Comercial de Obra', en: 'Commercial construction plan' }, priceLabel: { es: 'Cotizacion personalizada', en: 'Custom quote' }, description: { es: 'Para proyectos en preventa o desarrollo comercial.', en: 'For presale projects or commercial developments.' }, includes: [{ es: 'Calendario personalizado', en: 'Custom calendar' }, { es: 'Avance semanal si aplica', en: 'Weekly progress if needed' }, { es: 'Video horizontal y reels', en: 'Horizontal video and reels' }, { es: 'Material para inversionistas', en: 'Investor material' }] },
        ],
        process: [
            { title: { es: 'Revisamos el proyecto', en: 'Review the project' }, description: { es: 'Ubicacion, etapa de obra, objetivo y frecuencia requerida.', en: 'Location, construction stage, goal, and required frequency.' } },
            { title: { es: 'Definimos calendario', en: 'Set the calendar' }, description: { es: 'Acordamos visitas mensuales, quincenales o semanales.', en: 'We agree monthly, biweekly, or weekly visits.' } },
            { title: { es: 'Documentamos avance', en: 'Document progress' }, description: { es: 'Grabamos foto, video y dron segun viabilidad y etapa.', en: 'We capture photo, video, and drone according to viability and stage.' } },
            { title: { es: 'Entregamos organizado', en: 'Deliver organized material' }, description: { es: 'Recibes carpeta por fecha con contenido listo para reportes, redes y ventas.', en: 'You receive folders by date with content ready for reports, social, and sales.' } },
        ],
        faqs: [
            { question: { es: '¿Trabajan con planes mensuales?', en: 'Do you offer monthly plans?' }, answer: { es: 'Sí. Podemos programar visitas mensuales, quincenales o por hitos de obra.', en: 'Yes. We can schedule monthly, biweekly, or milestone-based visits.' } },
            { question: { es: '¿El plan incluye dron?', en: 'Does the plan include drone footage?' }, answer: { es: 'Puede incluirlo según ubicación, clima, seguridad y viabilidad de vuelo.', en: 'It can, depending on location, weather, safety, and flight viability.' } },
            { question: { es: '¿Sirve para reportes de inversionistas?', en: 'Can it be used for investor reports?' }, answer: { es: 'Sí. Entregamos material ordenado para reportes, presentaciones y actualizaciones comerciales.', en: 'Yes. We deliver organized material for reports, presentations, and sales updates.' } },
            { question: { es: '¿Pueden hacer comparativos?', en: 'Can you create progress comparisons?' }, answer: { es: 'Sí. Con visitas recurrentes podemos comparar fechas, etapas o zonas del proyecto.', en: 'Yes. With recurring visits, we can compare dates, stages, or project areas.' } },
        ],
        leadForm: {
            title: { es: 'Cotiza el seguimiento de tu obra', en: 'Quote construction documentation' },
            description: { es: 'Comparte la ubicación, la etapa y la frecuencia que necesitas.', en: 'Share the location, stage, and frequency you need.' },
            needOptions: [
                { es: '1 vez al mes', en: 'Once a month' },
                { es: '2 veces al mes', en: 'Twice a month' },
                { es: 'Semanal', en: 'Weekly' },
                { es: 'Por hito de obra', en: 'By construction milestone' },
            ],
        },
    },
};

export function localized(value: LocalizedText, locale: string): string {
    return locale === 'en' ? value.en : value.es;
}
