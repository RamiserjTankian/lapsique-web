export type LandingServiceKey = 'reels_de_comida' | 'sesiones_de_dron' | 'avances_de_obra';

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
            es: 'Grabamos reels, fotos y contenido visual para que tu comida, ambiente y experiencia se vean mas profesionales en Instagram, TikTok, Google y campanas de Meta Ads.',
            en: 'We produce reels, photos, and visual content so your food, atmosphere, and experience look stronger on Instagram, TikTok, Google, and Meta Ads campaigns.',
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
            es: 'Tu comida puede estar excelente, pero si en redes se ve improvisada, la percepcion baja.',
            en: 'Your food can be excellent, but if it looks improvised online, perceived value drops.',
        },
        problem: [
            {
                es: 'Muchas personas deciden donde comer por lo que ven en reels, stories, Google Maps e Instagram.',
                en: 'Many people decide where to eat based on reels, stories, Google Maps, and Instagram.',
            },
            {
                es: 'Si tu contenido no provoca antojo o no muestra bien la experiencia, puedes perder reservas, pedidos y visitas frente a negocios que si se ven profesionales.',
                en: 'If your content does not create craving or show the experience well, you can lose bookings, orders, and visits to businesses that look more professional.',
            },
        ],
        solutionTitle: {
            es: 'Contenido gastronomico pensado para vender visualmente.',
            en: 'Food content designed to sell visually.',
        },
        solution: [
            {
                es: 'Grabamos platillos, preparacion, ambiente, detalles, bebidas, equipo y experiencia para construir reels y fotos listos para publicar.',
                en: 'We capture dishes, preparation, atmosphere, details, drinks, team, and guest experience to build reels and photos ready to publish.',
            },
            {
                es: 'El material puede usarse en redes, anuncios, historias, menu digital, Google Business Profile y campanas de Meta.',
                en: 'The content can be used for social media, ads, stories, digital menus, Google Business Profile, and Meta campaigns.',
            },
        ],
        outcomesTitle: {
            es: 'Resultado de negocio',
            en: 'Business outcome',
        },
        outcomes: [
            { es: 'Tu comida se ve mas antojable y profesional.', en: 'Your food looks more appealing and professional.' },
            { es: 'Tienes contenido listo para publicar sin improvisar cada dia.', en: 'You get ready-to-publish content without daily improvisation.' },
            { es: 'Puedes usar los reels en campanas de Meta Ads.', en: 'You can use the reels in Meta Ads campaigns.' },
            { es: 'Refuerzas la experiencia del restaurante, no solo el platillo.', en: 'You strengthen the restaurant experience, not only the dish.' },
        ],
        audienceTitle: {
            es: 'Ideal para',
            en: 'Best for',
        },
        audience: [
            { es: 'Restaurantes', en: 'Restaurants' },
            { es: 'Sushi bars', en: 'Sushi bars' },
            { es: 'Cafes', en: 'Cafes' },
            { es: 'Bares y beach clubs', en: 'Bars and beach clubs' },
            { es: 'Dark kitchens', en: 'Dark kitchens' },
            { es: 'Hoteles con restaurante', en: 'Hotels with restaurants' },
        ],
        deliverablesTitle: {
            es: 'Que grabamos',
            en: 'What we capture',
        },
        deliverables: [
            { es: 'Platillos principales', en: 'Hero dishes' },
            { es: 'Preparacion y manos en accion', en: 'Preparation and hands in action' },
            { es: 'Bebidas y barra', en: 'Drinks and bar' },
            { es: 'Ambiente y entrada del restaurante', en: 'Atmosphere and venue entrance' },
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
            { question: { es: 'Cuanto dura una sesion de reels de comida?', en: 'How long does a food reels session take?' }, answer: { es: 'Depende del paquete. Una sesion express puede durar alrededor de 1 hora; una produccion mas completa puede tomar 2 o mas horas.', en: 'It depends on the package. An express session can take around 1 hour; a fuller production can take 2 or more hours.' } },
            { question: { es: 'Trabajan en Playa del Carmen, Tulum y Cancun?', en: 'Do you work in Playa del Carmen, Tulum, and Cancun?' }, answer: { es: 'Si. Atendemos Riviera Maya y Cancun, incluyendo Playa del Carmen, Tulum, Puerto Morelos, Puerto Aventuras, Akumal, Mayakoba, Cozumel y zonas cercanas.', en: 'Yes. We cover Riviera Maya and Cancun, including Playa del Carmen, Tulum, Puerto Morelos, Puerto Aventuras, Akumal, Mayakoba, Cozumel, and nearby areas.' } },
            { question: { es: 'Puedo usar los reels para anuncios?', en: 'Can I use the reels for ads?' }, answer: { es: 'Si. Podemos entregar contenido pensado para publicaciones organicas y campanas de Meta Ads.', en: 'Yes. We can deliver content for organic posts and Meta Ads campaigns.' } },
            { question: { es: 'Incluye fotos?', en: 'Are photos included?' }, answer: { es: 'Puede incluir fotos segun el paquete contratado. Recomendamos combinar reels y fotos para tener mas material de publicacion.', en: 'Photos can be included depending on the package. We recommend combining reels and photos for more publishing material.' } },
        ],
        leadForm: {
            title: { es: 'Cotiza una sesion para restaurante', en: 'Quote a restaurant session' },
            description: { es: 'Deja los datos basicos y te recomendamos el paquete mas conveniente.', en: 'Leave the basics and we will recommend the most useful package.' },
            needOptions: [
                { es: 'Reels', en: 'Reels' },
                { es: 'Fotos', en: 'Photos' },
                { es: 'Plan mensual', en: 'Monthly plan' },
                { es: 'Campana de Ads', en: 'Ads campaign' },
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
            es: 'Tomas aereas profesionales para mostrar ubicacion, arquitectura, escala, ambiente y experiencia con una imagen mas premium para redes, web, anuncios y presentaciones comerciales.',
            en: 'Professional aerial shots to show location, architecture, scale, atmosphere, and experience with a more premium image for social, web, ads, and presentations.',
        },
        primaryCta: { es: 'Cotizar sesion de dron', en: 'Quote drone session' },
        finalCta: { es: 'Cotizar dron', en: 'Quote drone' },
        whatsappMessage: { es: 'Hola, quiero cotizar una sesion de dron para un proyecto en [ciudad/zona].', en: 'Hi, I want to quote a drone session for a project in [city/area].' },
        problemTitle: { es: 'Desde tierra muchos espacios no muestran su valor completo.', en: 'From the ground, many spaces do not show their full value.' },
        problem: [
            { es: 'El cliente puede no entender ubicacion, vista, acceso, escala, cercania al mar, arquitectura o entorno.', en: 'The client may not understand location, view, access, scale, beach proximity, architecture, or surroundings.' },
            { es: 'Eso baja la percepcion de valor, especialmente en turismo, real estate y experiencias premium.', en: 'That lowers perceived value, especially in tourism, real estate, and premium experiences.' },
        ],
        solutionTitle: { es: 'Ensenamos mejor lo que hace especial a tu espacio.', en: 'We show what makes your space special.' },
        solution: [
            { es: 'Creamos tomas de dron para mostrar ubicacion, arquitectura, amenidades, acceso, paisaje, ambiente y experiencia.', en: 'We create drone shots to show location, architecture, amenities, access, landscape, atmosphere, and experience.' },
            { es: 'Cada vuelo se revisa segun ubicacion, clima, seguridad, restricciones de zona y viabilidad de operacion.', en: 'Each flight is reviewed according to location, weather, safety, area restrictions, and operational viability.' },
        ],
        outcomesTitle: { es: 'Material premium listo para vender mejor', en: 'Premium material ready to sell better' },
        outcomes: [
            { es: 'Muestras ubicacion y entorno.', en: 'Show location and surroundings.' },
            { es: 'Elevas la percepcion premium del lugar.', en: 'Raise the premium perception of the place.' },
            { es: 'Ensenas escala, acceso y amenidades.', en: 'Show scale, access, and amenities.' },
            { es: 'Tienes material para redes, web, anuncios y presentaciones.', en: 'Get material for social, web, ads, and presentations.' },
        ],
        audienceTitle: { es: 'Ideal para', en: 'Best for' },
        audience: [
            { es: 'Hoteles boutique', en: 'Boutique hotels' },
            { es: 'Villas y Airbnb premium', en: 'Villas and premium Airbnb' },
            { es: 'Restaurantes y beach clubs', en: 'Restaurants and beach clubs' },
            { es: 'Inmobiliarias y desarrollos', en: 'Real estate teams and developments' },
            { es: 'Eventos y experiencias turisticas', en: 'Events and tourist experiences' },
            { es: 'Wedding planners y marcas turisticas', en: 'Wedding planners and tourism brands' },
        ],
        deliverablesTitle: { es: 'Que podemos grabar', en: 'What we can capture' },
        deliverables: [
            { es: 'Fachada y acceso', en: 'Facade and access' },
            { es: 'Ubicacion y entorno', en: 'Location and surroundings' },
            { es: 'Amenidades y arquitectura', en: 'Amenities and architecture' },
            { es: 'Albercas, terrazas y beach clubs', en: 'Pools, terraces, and beach clubs' },
            { es: 'Propiedades, terrenos y recorridos aereos', en: 'Properties, lots, and aerial routes' },
            { es: 'Eventos y experiencias', en: 'Events and experiences' },
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
            { question: { es: 'El dron depende del clima?', en: 'Does drone production depend on weather?' }, answer: { es: 'Si. La sesion puede depender de viento, lluvia, ubicacion y condiciones de seguridad.', en: 'Yes. The session can depend on wind, rain, location, and safety conditions.' } },
            { question: { es: 'Puedo pedir video vertical y horizontal?', en: 'Can I request vertical and horizontal video?' }, answer: { es: 'Si. Podemos entregar material vertical para redes y horizontal para web, YouTube, presentaciones o pantallas.', en: 'Yes. We can deliver vertical material for social and horizontal material for web, YouTube, presentations, or screens.' } },
            { question: { es: 'Sirve para Airbnb o villas?', en: 'Does it work for Airbnb or villas?' }, answer: { es: 'Si. Las tomas de dron ayudan a mostrar ubicacion, entorno, acceso y valor visual de propiedades y villas.', en: 'Yes. Drone shots help show location, surroundings, access, and visual value for properties and villas.' } },
            { question: { es: 'Pueden combinar dron con camara en tierra?', en: 'Can drone be combined with ground camera?' }, answer: { es: 'Si. Para proyectos comerciales conviene combinar tomas aereas con detalles en tierra.', en: 'Yes. For commercial projects, combining aerial shots with ground details is useful.' } },
        ],
        leadForm: {
            title: { es: 'Cotiza una sesion de dron', en: 'Quote a drone session' },
            description: { es: 'Comparte tipo de proyecto, zona y formato que necesitas.', en: 'Share project type, area, and needed format.' },
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
        intro: { es: 'Documentamos el progreso de tu construccion con contenido visual profesional para inversionistas, clientes, reportes, redes y ventas.', en: 'We document construction progress with professional visual content for investors, clients, reports, social, and sales.' },
        primaryCta: { es: 'Cotizar plan mensual de obra', en: 'Quote monthly construction plan' },
        finalCta: { es: 'Cotizar obra', en: 'Quote construction' },
        whatsappMessage: { es: 'Hola, quiero cotizar documentacion de avances de obra para un proyecto en [ciudad/zona].', en: 'Hi, I want to quote construction progress documentation for a project in [city/area].' },
        problemTitle: { es: 'Una obra de alto valor no deberia comunicarse con fotos sueltas de WhatsApp.', en: 'A high-value construction project should not be communicated with loose WhatsApp photos.' },
        problem: [
            { es: 'Si estas construyendo, vendiendo, levantando inversion o reportando avances, necesitas evidencia visual clara, profesional y ordenada por fecha.', en: 'If you are building, selling, raising investment, or reporting progress, you need clear, professional evidence organized by date.' },
            { es: 'Las fotos improvisadas pueden verse poco serias, no mostrar escala real y no ayudar al equipo comercial.', en: 'Improvised photos can look unserious, fail to show real scale, and do little for the sales team.' },
        ],
        solutionTitle: { es: 'Documentacion visual constante para generar confianza.', en: 'Consistent visual documentation that builds trust.' },
        solution: [
            { es: 'Documentamos avances de obra con fotografia, video y dron para constructoras, arquitectos, desarrolladoras e inmobiliarias.', en: 'We document construction progress with photography, video, and drone for builders, architects, developers, and real estate teams.' },
            { es: 'Creamos contenido para reportes, inversionistas, actualizaciones comerciales, redes y preventa.', en: 'We create material for reports, investors, sales updates, social media, and presales.' },
        ],
        outcomesTitle: { es: 'Evidencia profesional para vender y reportar', en: 'Professional evidence for sales and reporting' },
        outcomes: [
            { es: 'Reportes visuales mas profesionales.', en: 'More professional visual reports.' },
            { es: 'Evidencia del progreso real.', en: 'Evidence of real progress.' },
            { es: 'Mayor confianza para inversionistas y clientes.', en: 'More trust for investors and clients.' },
            { es: 'Archivo visual historico del proyecto.', en: 'A historical visual archive for the project.' },
        ],
        audienceTitle: { es: 'Ideal para', en: 'Best for' },
        audience: [
            { es: 'Constructoras', en: 'Builders' },
            { es: 'Arquitectos e ingenieros', en: 'Architects and engineers' },
            { es: 'Desarrolladoras', en: 'Developers' },
            { es: 'Inmobiliarias y brokers', en: 'Real estate teams and brokers' },
            { es: 'Project managers', en: 'Project managers' },
            { es: 'Inversionistas y duenos de obra', en: 'Investors and owners' },
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
            { question: { es: 'Trabajan con planes mensuales?', en: 'Do you offer monthly plans?' }, answer: { es: 'Si. Para avances de obra recomendamos planes mensuales porque el valor esta en documentar la evolucion del proyecto de forma constante.', en: 'Yes. For construction progress, monthly plans are recommended because the value is in documenting the project evolution consistently.' } },
            { question: { es: 'Incluye dron?', en: 'Is drone included?' }, answer: { es: 'Puede incluir dron segun ubicacion, clima, seguridad y viabilidad de vuelo.', en: 'Drone can be included depending on location, weather, safety, and flight viability.' } },
            { question: { es: 'Sirve para inversionistas?', en: 'Is it useful for investors?' }, answer: { es: 'Si. El contenido puede usarse para mostrar progreso real a inversionistas, clientes, brokers y equipo comercial.', en: 'Yes. Content can show real progress to investors, clients, brokers, and sales teams.' } },
            { question: { es: 'Pueden hacer comparativos de avance?', en: 'Can you create progress comparisons?' }, answer: { es: 'Si. Si el proyecto se documenta de forma recurrente, se pueden crear comparativos por fecha, etapa o zona.', en: 'Yes. With recurring documentation, comparisons can be created by date, stage, or zone.' } },
        ],
        leadForm: {
            title: { es: 'Cotiza documentacion de obra', en: 'Quote construction documentation' },
            description: { es: 'Comparte ubicacion, etapa y frecuencia deseada.', en: 'Share location, stage, and desired frequency.' },
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
