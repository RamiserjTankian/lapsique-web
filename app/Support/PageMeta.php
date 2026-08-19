<?php

namespace App\Support;

use App\Models\Dj;
use App\Models\Event;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageMeta
{
    public const LAPSIQUE_SITE_NAME = 'Lapsique Media';

    public const TRASCENDENTAL_SITE_NAME = 'Trascendentalby';

    public static function forRequest(Request $request): PageMetaData
    {
        $routeName = $request->route()?->getName();
        $settings = SiteSetting::current();
        $canonicalUrl = url()->current();
        $page = $request->integer('page', 1);

        if ($page > 1 && in_array($routeName, ['videos.index', 'portfolio.index'], true)) {
            $canonicalUrl .= '?page='.$page;
        }

        if ($routeName === 'home' && config('trascendental.enabled_as_primary')) {
            return self::forTrascendental($canonicalUrl);
        }

        return match ($routeName) {
            'home', 'booking.show' => self::forBookingFunnel($settings, $canonicalUrl),
            'trascendental.home' => self::forTrascendental($canonicalUrl),
            'trascendental.services' => self::forTrascendentalSection(__('trascendental.services.title'), __('trascendental.services.intro'), $canonicalUrl),
            'trascendental.cases' => self::forTrascendentalSection(__('trascendental.cases.title'), __('trascendental.cases.intro'), $canonicalUrl),
            'trascendental.events' => self::forTrascendentalSection(__('trascendental.events.title'), __('trascendental.events.intro'), $canonicalUrl),
            'trascendental.tours' => self::forTrascendentalSection(__('trascendental.tours.title'), __('trascendental.tours.intro'), $canonicalUrl),
            'trascendental.about' => self::forTrascendentalSection(__('trascendental.about.title'), __('trascendental.about.intro'), $canonicalUrl),
            'trascendental.contact' => self::forTrascendentalSection(__('trascendental.contact.title'), __('trascendental.contact.intro'), $canonicalUrl),
            'djset.show' => self::forDjSet($settings, $canonicalUrl),
            'drone-sessions.show' => self::forDroneSession($canonicalUrl),
            'construction-progress.show' => self::forConstructionProgress($canonicalUrl),
            'food-reels.show' => self::forFoodReels($settings, $canonicalUrl),
            'content-creation.show' => self::forContentCreation($settings, $canonicalUrl),
            'business-reels.show' => self::forBusinessReels($settings, $canonicalUrl),
            'electronic-event-coverage.show' => self::forElectronicEventCoverage($canonicalUrl),
            'multi-camera.show' => self::forMultiCamera($canonicalUrl),
            'djs.show' => self::forDj($request->route('dj'), $canonicalUrl),
            'videos.show' => self::forVideo($request->route('video'), $canonicalUrl),
            'events.show' => self::forEvent($request->route('event'), $canonicalUrl),
            'posts.show' => self::forPost($request->route('post'), $canonicalUrl),
            'djs.index' => self::forSection(
                __('pages.djs.title'),
                __('seo.djs_index.description'),
                $canonicalUrl,
                __('seo.djs_index.keywords'),
            ),
            'videos.index' => self::forSection(
                __('pages.videos.title'),
                __('seo.videos_index.description'),
                $canonicalUrl,
                __('seo.videos_index.keywords'),
            ),
            'events.index' => self::forSection(
                __('pages.events.title'),
                __('seo.events_index.description'),
                $canonicalUrl,
                __('seo.events_index.keywords'),
            ),
            'portfolio.index' => self::forSection(
                __('pages.portfolio.title'),
                __('seo.portfolio_index.description'),
                $canonicalUrl,
                __('seo.portfolio_index.keywords'),
            ),
            'booking.confirm' => self::forBookingStatus(
                __('seo.booking_confirm.title'),
                __('seo.booking_confirm.description'),
                $canonicalUrl,
                noindex: true,
            ),
            'booking.pending' => self::forBookingStatus(
                __('seo.booking_pending.title'),
                __('seo.booking_pending.description'),
                $canonicalUrl,
                noindex: true,
            ),
            'booking.failure' => self::forBookingStatus(
                __('seo.booking_failure.title'),
                __('seo.booking_failure.description'),
                $canonicalUrl,
                noindex: true,
            ),
            'customers.login', 'customers.password.request', 'customers.password.reset' => self::forSection(
                __('seo.customer_login.title'),
                __('seo.customer_login.description'),
                $canonicalUrl,
                __('seo.customer_login.keywords'),
                noindex: true,
            ),
            'customers.portal' => self::forSection(
                __('seo.customer_portal.title'),
                __('seo.customer_portal.description'),
                $canonicalUrl,
                __('seo.customer_portal.keywords'),
                noindex: true,
            ),
            default => self::forDefault($canonicalUrl),
        };
    }

    public static function forDjSet(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $title = self::localized(
            'Grabación cinematográfica de DJ sets en Riviera Maya',
            'Cinematic DJ set recording in Riviera Maya',
        );
        $metaTitle = self::localized(
            'Graba tu DJ set en Riviera Maya | Lapsique',
            'Record your DJ set in Riviera Maya | Lapsique',
        );
        $description = self::localized(
            'Video continuo, piezas cortas, audio de cabina y fotografía editorial para presentar tu DJ set a promotores, venues y audiencia.',
            'Continuous video, short edits, booth audio, and editorial photography to present your DJ set to promoters, venues, and audiences.',
        );
        $ogImage = self::djsetOgImageUrl($settings);
        $ogImageAlt = self::localized(
            'DJ set documentado por Lapsique Media en Riviera Maya',
            'DJ set documented by Lapsique Media in Riviera Maya',
        );

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: $ogImageAlt,
            keywords: self::localized(
                'grabación de DJ set Riviera Maya, video DJ set Tulum, contenido para DJs, fotografía nightlife, Psique Sessions',
                'DJ set recording Riviera Maya, DJ set video Tulum, content for DJs, nightlife photography, Psique Sessions',
            ),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: self::localized(
                    'Producción y grabación audiovisual de DJ sets',
                    'Audiovisual production and recording for DJ sets',
                ),
                ogImage: $ogImage,
                breadcrumbName: self::localized('Grabación de DJ set', 'DJ set recording'),
                faq: [
                    [
                        self::localized('¿Cuánto dura la grabación?', 'How long does the recording last?'),
                        self::localized(
                            'La duración se define según el set, el venue y las piezas acordadas antes de grabar.',
                            'The duration is defined around the set, venue, and deliverables agreed before filming.',
                        ),
                    ],
                    [
                        self::localized('¿Incluye audio del mixer?', 'Does it include mixer audio?'),
                        self::localized(
                            'Sí. Capturamos la señal del mixer y audio ambiente para construir una mezcla con energía de cabina.',
                            'Yes. We capture the mixer signal and room audio to build a mix that preserves the booth energy.',
                        ),
                    ],
                    [
                        self::localized('¿Incluye piezas cortas para redes?', 'Does it include short edits for social media?'),
                        self::localized(
                            'Sí. La propuesta puede incluir drops, fotografías y un video continuo según el paquete y los formatos acordados.',
                            'Yes. The proposal can include drops, photographs, and a continuous video based on the package and agreed formats.',
                        ),
                    ],
                ],
            ),
        );
    }

    public static function forDroneSession(string $canonicalUrl): PageMetaData
    {
        $title = self::localized(
            'Vuelos con dron para propiedades y campañas en Riviera Maya',
            'Drone filming for properties and campaigns in Riviera Maya',
        );
        $description = self::localized(
            'Video y fotografía aérea para hoteles, villas, yates, terrenos, venues y campañas en Playa del Carmen, Tulum, Cancún y Riviera Maya.',
            'Aerial video and photography for hotels, villas, yachts, land, venues, and campaigns in Playa del Carmen, Tulum, Cancun, and Riviera Maya.',
        );
        $ogImage = self::absoluteImageUrl('/images/drone-sessions/hero.jpg');

        return new PageMetaData(
            title: $title,
            metaTitle: self::localized(
                'Vuelos con dron en Riviera Maya | Lapsique',
                'Drone filming in Riviera Maya | Lapsique',
            ),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: self::localized(
                'Toma aérea con dron para una propiedad en Riviera Maya',
                'Aerial drone view of a property in Riviera Maya',
            ),
            keywords: self::localized(
                'vuelos con dron Riviera Maya, video con dron Playa del Carmen, dron para hoteles Tulum, tomas aéreas Cancún, video inmobiliario',
                'drone filming Riviera Maya, drone video Playa del Carmen, hotel drone Tulum, aerial video Cancun, real estate video',
            ),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: self::localized('Video y fotografía aérea con dron', 'Aerial drone video and photography'),
                ogImage: $ogImage,
                breadcrumbName: self::localized('Vuelos con dron', 'Drone filming'),
                faq: [
                    [
                        self::localized('¿El vuelo depende del clima?', 'Does the flight depend on weather?'),
                        self::localized(
                            'Sí. Antes de confirmar revisamos viento, lluvia, ubicación y condiciones de seguridad.',
                            'Yes. Before confirming, we review wind, rain, location, and safety conditions.',
                        ),
                    ],
                    [
                        self::localized('¿Puedo pedir video vertical y horizontal?', 'Can I request vertical and horizontal video?'),
                        self::localized(
                            'Sí. Podemos entregar material vertical para redes y horizontal para web, YouTube, presentaciones o pantallas.',
                            'Yes. We can deliver vertical material for social media and horizontal material for web, YouTube, presentations, or screens.',
                        ),
                    ],
                    [
                        self::localized('¿Sirve para hoteles, villas o yates?', 'Is it suitable for hotels, villas, or yachts?'),
                        self::localized(
                            'Sí. Las tomas aéreas muestran ubicación, entorno, acceso, escala y valor visual.',
                            'Yes. Aerial views show location, surroundings, access, scale, and visual value.',
                        ),
                    ],
                    [
                        self::localized('¿Pueden combinar dron con cámara en tierra?', 'Can drone footage be combined with ground cameras?'),
                        self::localized(
                            'Sí. Combinamos tomas aéreas y detalles en tierra cuando el proyecto necesita contar una experiencia completa.',
                            'Yes. We combine aerial footage and ground details when the project needs to tell a complete story.',
                        ),
                    ],
                ],
            ),
        );
    }

    public static function forConstructionProgress(string $canonicalUrl): PageMetaData
    {
        $title = self::localized(
            'Avances de obra con dron, foto y video en Riviera Maya',
            'Construction progress with drone, photo, and video in Riviera Maya',
        );
        $description = self::localized(
            'Documentación audiovisual por etapas para constructoras, arquitectos y desarrolladoras en Playa del Carmen, Tulum, Cancún y Riviera Maya.',
            'Stage-by-stage audiovisual documentation for builders, architects, and developers in Playa del Carmen, Tulum, Cancun, and Riviera Maya.',
        );
        $ogImage = self::absoluteImageUrl('/images/drone-sessions/construction-goba-aerial.jpg');

        return new PageMetaData(
            title: $title,
            metaTitle: self::localized(
                'Avances de obra con dron | Lapsique',
                'Drone construction progress | Lapsique',
            ),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: self::localized(
                'Avance de obra documentado con dron en Riviera Maya',
                'Construction progress documented by drone in Riviera Maya',
            ),
            keywords: self::localized(
                'avances de obra con dron Riviera Maya, seguimiento de obra Playa del Carmen, video de construcción Tulum, fotografía de obra Cancún',
                'construction progress drone Riviera Maya, construction monitoring Playa del Carmen, construction video Tulum, site photography Cancun',
            ),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: self::localized('Documentación audiovisual de construcción', 'Construction audiovisual documentation'),
                ogImage: $ogImage,
                breadcrumbName: self::localized('Avances de obra', 'Construction progress'),
                faq: [
                    [
                        self::localized('¿Trabajan con planes mensuales?', 'Do you offer monthly plans?'),
                        self::localized(
                            'Sí. Los planes recurrentes permiten documentar la evolución del proyecto de forma consistente.',
                            'Yes. Recurring plans document the project evolution consistently.',
                        ),
                    ],
                    [
                        self::localized('¿Incluye dron?', 'Does it include drone footage?'),
                        self::localized(
                            'Puede incluir dron según ubicación, clima, seguridad y viabilidad de vuelo.',
                            'It can include drone footage depending on location, weather, safety, and flight viability.',
                        ),
                    ],
                    [
                        self::localized('¿Sirve para reportes e inversionistas?', 'Can it be used for reports and investors?'),
                        self::localized(
                            'Sí. El contenido muestra progreso real a inversionistas, clientes, brokers y equipos comerciales.',
                            'Yes. The material shows real progress to investors, clients, brokers, and commercial teams.',
                        ),
                    ],
                    [
                        self::localized('¿Pueden hacer comparativos de avance?', 'Can you create progress comparisons?'),
                        self::localized(
                            'Sí. Con documentación recurrente creamos comparativos visuales por fecha, etapa o zona.',
                            'Yes. With recurring documentation, we create visual comparisons by date, stage, or area.',
                        ),
                    ],
                ],
            ),
        );
    }

    public static function forFoodReels(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $title = self::localized(
            'Reels de comida para restaurantes en Riviera Maya',
            'Food reels for restaurants in Riviera Maya',
        );
        $description = self::localized(
            'Portafolio real de comida, ambiente y servicio: reels y fotografía para restaurantes, cafés y bares en Riviera Maya y Cancún.',
            'A real portfolio of food, atmosphere, and service: reels and photography for restaurants, cafés, and bars in Riviera Maya and Cancun.',
        );
        $ogImage = self::staticPublicImageUrl('images/portfolio/photos/095-the-roof-comida-a715561b91.webp')
            ?? self::bookingOgImageUrl($settings);

        return new PageMetaData(
            title: $title,
            metaTitle: self::localized(
                'Reels para restaurantes en Riviera Maya | Lapsique',
                'Restaurant reels in Riviera Maya | Lapsique',
            ),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: self::localized(
                'Fotografía gastronómica producida por Lapsique Media en Riviera Maya',
                'Food photography produced by Lapsique Media in Riviera Maya',
            ),
            keywords: self::localized(
                'reels de comida Riviera Maya, reels para restaurantes Playa del Carmen, video gastronómico Tulum, fotografía de restaurantes Cancún',
                'food reels Riviera Maya, restaurant reels Playa del Carmen, food video Tulum, restaurant photography Cancun',
            ),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: self::localized('Producción audiovisual para restaurantes', 'Audiovisual production for restaurants'),
                ogImage: $ogImage,
                breadcrumbName: self::localized('Reels de comida', 'Food reels'),
                faq: [
                    [
                        self::localized('¿Cuánto dura una producción de reels de comida?', 'How long does a food reel production take?'),
                        self::localized(
                            'Depende de los platillos, el ambiente y los entregables acordados antes de grabar.',
                            'It depends on the dishes, atmosphere, and deliverables agreed before filming.',
                        ),
                    ],
                    [
                        self::localized('¿Trabajan en Playa del Carmen, Tulum y Cancún?', 'Do you work in Playa del Carmen, Tulum, and Cancun?'),
                        self::localized(
                            'Sí. Atendemos Riviera Maya, Cancún y zonas cercanas según la logística del proyecto.',
                            'Yes. We cover Riviera Maya, Cancun, and nearby areas depending on project logistics.',
                        ),
                    ],
                    [
                        self::localized('¿Puedo usar los reels para anuncios?', 'Can I use the reels in ads?'),
                        self::localized(
                            'Sí. Podemos producir piezas para publicaciones orgánicas y campañas de Meta Ads.',
                            'Yes. We can produce pieces for organic publishing and Meta Ads campaigns.',
                        ),
                    ],
                    [
                        self::localized('¿Incluye fotografías?', 'Does it include photographs?'),
                        self::localized(
                            'Puede incluir fotografía de producto, preparación, servicio y ambiente según el paquete.',
                            'It can include product, preparation, service, and atmosphere photography depending on the package.',
                        ),
                    ],
                ],
            ),
        );
    }

    public static function forContentCreation(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $title = self::localized(
            'Creación de contenido para redes en Riviera Maya',
            'Social media content creation in Riviera Maya',
        );
        $description = self::localized(
            'Reels y fotografía para Instagram, TikTok y Meta Ads, con portafolio real de marcas, hospitality, experiencias y servicios.',
            'Reels and photography for Instagram, TikTok, and Meta Ads, backed by a real portfolio of brands, hospitality, experiences, and services.',
        );
        $ogImage = self::staticPublicImageUrl('images/portfolio/photos/050-zal-marina-5399c16416.webp')
            ?? self::bookingOgImageUrl($settings);

        return new PageMetaData(
            title: $title,
            metaTitle: self::localized(
                'Contenido para redes en Riviera Maya | Lapsique',
                'Social media content in Riviera Maya | Lapsique',
            ),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: self::localized(
                'Contenido para redes producido por Lapsique Media en Riviera Maya',
                'Social media content produced by Lapsique Media in Riviera Maya',
            ),
            keywords: self::localized(
                'creación de contenido Riviera Maya, contenido para redes Playa del Carmen, reels Instagram Tulum, videos TikTok Cancún, Meta Ads',
                'content creation Riviera Maya, social media content Playa del Carmen, Instagram reels Tulum, TikTok videos Cancun, Meta Ads',
            ),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: self::localized('Creación de contenido para redes sociales', 'Social media content creation'),
                ogImage: $ogImage,
                breadcrumbName: self::localized('Creación de contenido', 'Content creation'),
                faq: [
                    [
                        self::localized('¿Producen contenido para Instagram y TikTok?', 'Do you produce content for Instagram and TikTok?'),
                        self::localized(
                            'Sí. Grabamos en formato vertical y editamos piezas para consumo móvil, publicaciones orgánicas y anuncios.',
                            'Yes. We film vertically and edit for mobile viewing, organic publishing, and ads.',
                        ),
                    ],
                    [
                        self::localized('¿Puedo usar el material en Meta Ads?', 'Can I use the material in Meta Ads?'),
                        self::localized(
                            'Sí. Los reels y fotografías se entregan con el uso comercial acordado para la campaña.',
                            'Yes. Reels and photographs are delivered with the commercial usage agreed for the campaign.',
                        ),
                    ],
                    [
                        self::localized('¿Qué tipos de negocio atienden?', 'What kinds of businesses do you work with?'),
                        self::localized(
                            'Marcas, hospitality, experiencias, servicios, restaurantes y propiedades en Riviera Maya.',
                            'Brands, hospitality, experiences, services, restaurants, and properties in Riviera Maya.',
                        ),
                    ],
                    [
                        self::localized('¿Incluye estrategia de redes?', 'Does it include social media strategy?'),
                        self::localized(
                            'La producción incluye dirección visual y lista de tomas. La gestión mensual de redes se cotiza aparte.',
                            'Production includes visual direction and a shot list. Monthly social media management is quoted separately.',
                        ),
                    ],
                ],
            ),
        );
    }

    public static function forBusinessReels(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $title = self::localized(
            'Reels para negocios y anuncios en Riviera Maya',
            'Business reels and ads in Riviera Maya',
        );
        $description = self::localized(
            'Reels comerciales construidos con hook, oferta y CTA para campañas de Instagram, TikTok y Meta Ads.',
            'Commercial reels built around a hook, offer, and CTA for Instagram, TikTok, and Meta Ads campaigns.',
        );
        $ogImage = self::staticPublicImageUrl('images/portfolio/photos/063-dpm-ce73daedf9.webp')
            ?? self::bookingOgImageUrl($settings);

        return new PageMetaData(
            title: $title,
            metaTitle: self::localized(
                'Reels para negocios en Riviera Maya | Lapsique',
                'Business reels in Riviera Maya | Lapsique',
            ),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: self::localized(
                'Producción comercial para negocios realizada por Lapsique Media',
                'Commercial production for businesses by Lapsique Media',
            ),
            keywords: self::localized(
                'reels para negocios Riviera Maya, videos para anuncios Playa del Carmen, reels comerciales Tulum, producción Meta Ads Cancún',
                'business reels Riviera Maya, ad videos Playa del Carmen, commercial reels Tulum, Meta Ads production Cancun',
            ),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: self::localized('Producción de reels para negocios', 'Business reel production'),
                ogImage: $ogImage,
                breadcrumbName: self::localized('Reels para negocios', 'Business reels'),
                faq: [],
            ),
        );
    }

    public static function forElectronicEventCoverage(string $canonicalUrl): PageMetaData
    {
        $price = (int) config('booking.electronic_event_coverage_price', 4500);
        $title = __('seo.electronic_event_coverage.title');
        $description = self::truncate(__('seo.electronic_event_coverage.description', [
            'price' => number_format($price, 0, '.', ','),
        ]));
        $ogImage = self::staticPublicImageUrl('images/portfolio/video-posters/2026-07-11-mtrx-dumas-a0794b89f7.jpg')
            ?? self::defaultOgImageUrl();

        return new PageMetaData(
            title: $title,
            metaTitle: self::localized(
                'Cobertura de eventos electrónicos | Lapsique',
                'Electronic event coverage | Lapsique',
            ),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: __('seo.electronic_event_coverage.og_alt'),
            keywords: __('seo.electronic_event_coverage.keywords'),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: __('seo.electronic_event_coverage.service_type'),
                ogImage: $ogImage,
                breadcrumbName: __('seo.electronic_event_coverage.breadcrumb'),
                faq: [
                    [
                        __('seo.electronic_event_coverage.faq.includes.question'),
                        __('seo.electronic_event_coverage.faq.includes.answer'),
                    ],
                    [
                        __('seo.electronic_event_coverage.faq.price.question'),
                        __('seo.electronic_event_coverage.faq.price.answer'),
                    ],
                    [
                        __('seo.electronic_event_coverage.faq.areas.question'),
                        __('seo.electronic_event_coverage.faq.areas.answer'),
                    ],
                    [
                        __('seo.electronic_event_coverage.faq.aftermovie.question'),
                        __('seo.electronic_event_coverage.faq.aftermovie.answer'),
                    ],
                ],
                offerPrice: $price,
            ),
        );
    }

    public static function forMultiCamera(string $canonicalUrl): PageMetaData
    {
        $price = (int) config('booking.multi_camera_price', 5000);
        $title = __('seo.multi_camera.title');
        $description = self::truncate(__('seo.multi_camera.description', [
            'price' => number_format($price, 0, '.', ','),
        ]), 157);
        $ogImage = self::staticPublicImageUrl('images/og/multicamara.jpg')
            ?? self::defaultOgImageUrl();
        $videos = [
            [
                '@type' => 'VideoObject',
                '@id' => rtrim($canonicalUrl, '/').'#coverage-video-1',
                'name' => __('seo.multi_camera.video_names.1'),
                'description' => __('seo.multi_camera.video_description'),
                'thumbnailUrl' => [self::staticPublicImageUrl('images/portfolio/video-posters/2026-07-28-multicamera-open-booth-01.jpg')],
                'contentUrl' => url('/videos/reels/2026-07-28-multicamera-open-booth-01.mp4'),
                'uploadDate' => '2026-07-28',
                'duration' => 'PT52S',
            ],
            [
                '@type' => 'VideoObject',
                '@id' => rtrim($canonicalUrl, '/').'#coverage-video-2',
                'name' => __('seo.multi_camera.video_names.2'),
                'description' => __('seo.multi_camera.video_description'),
                'thumbnailUrl' => [self::staticPublicImageUrl('images/portfolio/video-posters/2026-07-27-danzahaus-mauro-drop-01.jpg')],
                'contentUrl' => url('/videos/reels/2026-07-27-danzahaus-mauro-drop-01.mp4'),
                'uploadDate' => '2026-07-27',
                'duration' => 'PT32S',
            ],
            [
                '@type' => 'VideoObject',
                '@id' => rtrim($canonicalUrl, '/').'#coverage-video-3',
                'name' => __('seo.multi_camera.video_names.3'),
                'description' => __('seo.multi_camera.video_description'),
                'thumbnailUrl' => [self::staticPublicImageUrl('images/portfolio/video-posters/2026-07-27-danzahaus-track-drop-01.jpg')],
                'contentUrl' => url('/videos/reels/2026-07-27-danzahaus-track-drop-01.mp4'),
                'uploadDate' => '2026-07-27',
                'duration' => 'PT46S',
            ],
        ];

        return new PageMetaData(
            title: $title,
            metaTitle: self::localized(
                'Producción multicámara para DJ sets | Lapsique',
                'Multicamera DJ set production | Lapsique',
            ),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: __('seo.multi_camera.og_alt'),
            keywords: __('seo.multi_camera.keywords'),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: __('seo.multi_camera.service_type'),
                ogImage: $ogImage,
                breadcrumbName: __('seo.multi_camera.breadcrumb'),
                faq: [
                    [__('seo.multi_camera.faq.includes.question'), __('seo.multi_camera.faq.includes.answer')],
                    [__('seo.multi_camera.faq.log.question'), __('seo.multi_camera.faq.log.answer')],
                    [__('seo.multi_camera.faq.audio.question'), __('seo.multi_camera.faq.audio.answer')],
                    [__('seo.multi_camera.faq.areas.question'), __('seo.multi_camera.faq.areas.answer')],
                ],
                offerPrice: $price,
                additionalAreaServed: ['Mérida'],
                videos: $videos,
            ),
        );
    }

    public static function forTrascendental(string $canonicalUrl): PageMetaData
    {
        $ogImage = self::absoluteImageUrl('/images/trascendental/og-logo.webp');

        return new PageMetaData(
            title: 'Trascendentalby',
            metaTitle: 'Trascendentalby · Artists. Events. Culture.',
            description: 'International booking, executive production and strategic artist development.',
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: 'Trascendentalby event production and artist booking',
            keywords: 'Trascendentalby, international booking, artists, events, electronic music, event production',
        );
    }

    public static function forTrascendentalSection(string $title, string $description, string $canonicalUrl): PageMetaData
    {
        $ogImage = self::absoluteImageUrl('/images/trascendental/og-logo.webp');

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} · Trascendentalby",
            description: self::truncate($description),
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: 'Trascendentalby event production and artist booking',
            keywords: 'Trascendentalby, booking, touring, event production',
        );
    }

    public static function forBookingFunnel(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $price = (int) ($settings?->booking_price ?? config('booking.content_price', 4000));
        $bookingTitle = LocalizedBookingCopy::title($settings?->booking_title);

        $title = self::localized(
            'Más de 200 piezas audiovisuales producidas por Lapsique',
            'More than 200 audiovisual pieces produced by Lapsique',
        );
        $metaTitle = self::localized(
            'Más de 200 piezas audiovisuales | Lapsique Media',
            'More than 200 audiovisual pieces | Lapsique Media',
        );
        $description = self::truncate(
            self::localized(
                'Un archivo real de fotografía y video para restaurantes, marcas, artistas, eventos, propiedades y desarrollos, creado desde Riviera Maya y Mérida para vender, documentar y permanecer.',
                'A real photography and video archive for restaurants, brands, artists, events, properties, and developments, created from Riviera Maya and Merida to sell, document, and endure.',
            ),
            157,
        );
        $ogImage = self::bookingOgImageUrl($settings);
        $ogImageAlt = self::localized(
            'Producción audiovisual de Lapsique Media en Riviera Maya',
            'Lapsique Media audiovisual production in Riviera Maya',
        );

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/#organization'),
                    'name' => self::siteName(),
                    'url' => url('/'),
                    'sameAs' => self::sameAsUrls(),
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/#website'),
                    'url' => url('/'),
                    'name' => self::siteName(),
                    'description' => $description,
                    'publisher' => ['@id' => url('/#organization')],
                ],
                [
                    '@type' => 'WebPage',
                    '@id' => rtrim($canonicalUrl, '/').'#webpage',
                    'url' => $canonicalUrl,
                    'name' => $metaTitle,
                    'headline' => $title,
                    'description' => $description,
                    'primaryImageOfPage' => [
                        '@type' => 'ImageObject',
                        'contentUrl' => $ogImage,
                    ],
                    'about' => ['@id' => url('/#organization')],
                    'isPartOf' => ['@id' => url('/#website')],
                    'inLanguage' => app()->getLocale() === 'en' ? 'en-US' : 'es-MX',
                ],
                [
                    '@type' => 'Service',
                    '@id' => rtrim($canonicalUrl, '/').'#service',
                    'name' => $bookingTitle,
                    'description' => $description,
                    'image' => $ogImage,
                    'serviceType' => __('seo.home.service_type'),
                    'areaServed' => ['@type' => 'AdministrativeArea', 'name' => 'Riviera Maya'],
                    'provider' => ['@id' => url('/#organization')],
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => $price,
                        'priceCurrency' => 'MXN',
                        'availability' => 'https://schema.org/InStock',
                        'url' => Str::finish($canonicalUrl, '/').'#agenda',
                    ],
                ],
            ],
        ];

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: $ogImageAlt,
            keywords: self::localized(
                'producción audiovisual Riviera Maya, reels para negocios, fotografía gastronómica, DJ sets, aftermovies, dron, avances de obra, Lapsique Media',
                'audiovisual production Riviera Maya, business reels, food photography, DJ sets, aftermovies, drone, construction progress, Lapsique Media',
            ),
            jsonLd: $jsonLd,
        );
    }

    public static function forDj(mixed $dj, string $canonicalUrl): PageMetaData
    {
        if (! $dj instanceof Dj) {
            return self::forDefault($canonicalUrl);
        }

        $title = $dj->name;
        $metaTitle = "{$title} · ".self::siteName();
        $description = self::truncate($dj->bio ?: __('seo.dj_profile', ['name' => $dj->name]));
        $ogImage = self::absoluteImageUrl(
            $dj->getFirstMediaUrl('profile', 'card')
                ?: $dj->getFirstMediaUrl('profile', 'hero')
                ?: $dj->getFirstMediaUrl('profile', 'thumb')
                ?: $dj->getFirstMediaUrl('gallery', 'thumb'),
        );

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'profile',
            ogImage: $ogImage ?: self::defaultOgImageUrl(),
            ogImageAlt: "{$dj->name} — ".self::siteName(),
            keywords: "{$dj->name}, DJ, música electrónica, ".self::siteName(),
            jsonLd: [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $dj->name,
                'description' => $description,
                'image' => $ogImage,
                'url' => $canonicalUrl,
                'sameAs' => array_values(array_filter([
                    $dj->instagram_url,
                    $dj->youtube_url,
                    $dj->soundcloud_url,
                    $dj->website_url,
                ])),
                'knowsAbout' => array_values(array_filter($dj->tags ?? [])),
            ],
        );
    }

    public static function forVideo(mixed $video, string $canonicalUrl): PageMetaData
    {
        if (! $video instanceof Video) {
            return self::forDefault($canonicalUrl);
        }

        $title = $video->title;
        $metaTitle = "{$title} · ".self::siteName();
        $description = self::truncate($video->description ?: __('seo.video_description', ['title' => $video->title]));
        $ogImage = self::absoluteImageUrl(
            $video->getFirstMediaUrl('thumbnail')
                ?: $video->thumbnail_url,
        );

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'video.other',
            ogImage: $ogImage ?: self::defaultOgImageUrl(),
            ogImageAlt: "{$video->title} — ".self::siteName(),
            keywords: "{$video->title}, video, DJ set, ".self::siteName(),
            jsonLd: array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'VideoObject',
                'name' => $video->title,
                'description' => $description,
                'thumbnailUrl' => $ogImage ? [$ogImage] : null,
                'uploadDate' => $video->published_at?->toIso8601String(),
                'embedUrl' => $video->youtube_id ? 'https://www.youtube-nocookie.com/embed/'.$video->youtube_id : null,
                'contentUrl' => $video->youtube_url,
                'url' => $canonicalUrl,
                'publisher' => ['@id' => url('/#organization')],
            ], fn ($value) => $value !== null && $value !== ''),
        );
    }

    public static function forEvent(mixed $event, string $canonicalUrl): PageMetaData
    {
        if (! $event instanceof Event) {
            return self::forDefault($canonicalUrl);
        }

        $title = $event->title;
        $metaTitle = "{$title} · ".self::siteName();
        $location = $event->location?->name;
        $datePart = $event->starts_at?->translatedFormat('d M Y');
        $description = self::truncate(
            collect([$event->localizedDescription(app()->getLocale()), $location, $datePart])
                ->filter()
                ->implode(' · ')
                ?: __('seo.event_fallback', ['title' => $event->title]),
        );
        $ogImage = self::absoluteImageUrl(
            $event->getFirstMediaUrl('cover', 'cover_large')
                ?: $event->getFirstMediaUrl('cover')
                ?: $event->public_image_path,
        );

        $jsonLd = $event->starts_at
            ? array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $event->title,
                'description' => $description,
                'image' => $ogImage ? [$ogImage] : null,
                'startDate' => $event->starts_at->toIso8601String(),
                'eventStatus' => $event->starts_at->isPast()
                    ? 'https://schema.org/EventCompleted'
                    : 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'location' => [
                    '@type' => 'Place',
                    'name' => $location ?: $event->venue ?: $event->city ?: 'Riviera Maya',
                    'address' => array_filter([
                        '@type' => 'PostalAddress',
                        'addressLocality' => $event->city,
                        'addressCountry' => 'MX',
                    ]),
                ],
                'url' => $canonicalUrl,
                'organizer' => ['@id' => url('/#organization')],
                'performer' => $event->relationLoaded('djs')
                    ? $event->djs->map(fn (Dj $dj) => ['@type' => 'Person', 'name' => $dj->name])->values()->all()
                    : null,
            ], fn ($value) => $value !== null && $value !== '')
            : array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $event->title,
                'description' => $description,
                'image' => $ogImage,
                'url' => $canonicalUrl,
                'publisher' => ['@id' => url('/#organization')],
            ], fn ($value) => $value !== null && $value !== '');

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage ?: self::defaultOgImageUrl(),
            ogImageAlt: "{$event->title} — ".self::siteName(),
            keywords: "{$event->title}, evento, música electrónica, ".self::siteName(),
            jsonLd: $jsonLd,
        );
    }

    public static function forPost(mixed $post, string $canonicalUrl): PageMetaData
    {
        if (! $post instanceof Post) {
            return self::forDefault($canonicalUrl);
        }

        $title = $post->title;
        $metaTitle = "{$title} · ".self::siteName();
        $description = self::truncate($post->excerpt ?: Str::limit(strip_tags((string) $post->content), 200));
        $ogImage = self::absoluteImageUrl($post->getFirstMediaUrl('cover', 'large'));

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'article',
            ogImage: $ogImage ?: self::defaultOgImageUrl(),
            ogImageAlt: "{$post->title} — ".self::siteName(),
            keywords: "{$post->title}, blog, ".self::siteName(),
        );
    }

    public static function forSection(
        string $title,
        string $description,
        string $canonicalUrl,
        string $keywords = '',
        bool $noindex = false,
    ): PageMetaData {
        $metaTitle = "{$title} · ".self::siteName();

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: self::truncate($description),
            canonicalUrl: $canonicalUrl,
            ogImage: self::defaultOgImageUrl(),
            ogImageAlt: "{$title} — ".self::siteName(),
            keywords: $keywords,
            noindex: $noindex,
            jsonLd: $noindex ? null : [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'Organization',
                        '@id' => url('/#organization'),
                        'name' => self::siteName(),
                        'url' => url('/'),
                        'logo' => self::staticPublicImageUrl('images/lapsique-media-logo-dark.png') ?? self::defaultOgImageUrl(),
                        'sameAs' => self::sameAsUrls(),
                    ],
                    [
                        '@type' => 'CollectionPage',
                        '@id' => rtrim($canonicalUrl, '/').'#webpage',
                        'url' => $canonicalUrl,
                        'name' => $metaTitle,
                        'description' => self::truncate($description),
                        'isPartOf' => ['@id' => url('/#website')],
                        'about' => ['@id' => url('/#organization')],
                        'inLanguage' => app()->getLocale() === 'en' ? 'en-US' : 'es-MX',
                    ],
                    [
                        '@type' => 'WebSite',
                        '@id' => url('/#website'),
                        'url' => url('/'),
                        'name' => self::siteName(),
                        'publisher' => ['@id' => url('/#organization')],
                    ],
                ],
            ],
        );
    }

    public static function forBookingStatus(
        string $title,
        string $description,
        string $canonicalUrl,
        bool $noindex = true,
    ): PageMetaData {
        return self::forSection($title, $description, $canonicalUrl, noindex: $noindex);
    }

    public static function forDefault(string $canonicalUrl): PageMetaData
    {
        return self::forSection(
            __('seo.default.title'),
            __('seo.default.description').': '.ContentSessionOffer::description(),
            $canonicalUrl,
            __('seo.default.keywords'),
        );
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $faq
     * @param  array<int, string>  $additionalAreaServed
     * @param  array<int, array<string, mixed>>  $videos
     */
    private static function serviceLandingJsonLd(
        string $canonicalUrl,
        string $title,
        string $description,
        string $serviceType,
        ?string $ogImage,
        string $breadcrumbName,
        array $faq,
        ?int $offerPrice = null,
        array $additionalAreaServed = [],
        array $videos = [],
    ): array {
        $areaServed = array_values(array_unique(array_merge([
            'Playa del Carmen',
            'Tulum',
            'Cancún',
            'Puerto Morelos',
            'Puerto Aventuras',
            'Akumal',
            'Mayakoba',
            'Cozumel',
            'Riviera Maya',
        ], $additionalAreaServed)));

        $service = [
            '@type' => 'Service',
            '@id' => rtrim($canonicalUrl, '/').'#service',
            'name' => $title,
            'serviceType' => $serviceType,
            'provider' => [
                '@id' => url('/#organization'),
            ],
            'areaServed' => array_map(
                fn (string $area): array => [
                    '@type' => 'Place',
                    'name' => $area,
                ],
                $areaServed,
            ),
            'description' => $description,
            'image' => $ogImage,
            'url' => $canonicalUrl,
        ];

        if ($offerPrice !== null) {
            $service['offers'] = [
                '@type' => 'Offer',
                'price' => $offerPrice,
                'priceCurrency' => 'MXN',
                'availability' => 'https://schema.org/InStock',
                'url' => $canonicalUrl,
            ];
        }

        $graph = [
            [
                '@type' => 'Organization',
                '@id' => url('/#organization'),
                'name' => self::LAPSIQUE_SITE_NAME,
                'url' => url('/'),
                'logo' => self::staticPublicImageUrl('images/lapsique-media-logo-dark.png') ?? self::defaultOgImageUrl(),
                'sameAs' => self::sameAsUrls(),
            ],
            $service,
            [
                '@type' => 'BreadcrumbList',
                '@id' => rtrim($canonicalUrl, '/').'#breadcrumb',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Inicio',
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => $breadcrumbName,
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
        ];

        if ($faq !== []) {
            $graph[] = [
                '@type' => 'FAQPage',
                '@id' => rtrim($canonicalUrl, '/').'#faq',
                'mainEntity' => array_map(
                    fn (array $item): array => [
                        '@type' => 'Question',
                        'name' => $item[0],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item[1],
                        ],
                    ],
                    $faq,
                ),
            ];
        }

        foreach ($videos as $video) {
            if (
                blank($video['name'] ?? null)
                || blank($video['description'] ?? null)
                || blank($video['thumbnailUrl'] ?? null)
                || blank($video['contentUrl'] ?? null)
                || blank($video['uploadDate'] ?? null)
            ) {
                continue;
            }

            $graph[] = array_merge($video, [
                'publisher' => [
                    '@id' => url('/#organization'),
                ],
            ]);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    private static function localized(string $spanish, string $english): string
    {
        return app()->getLocale() === 'en' ? $english : $spanish;
    }

    private static function siteName(): string
    {
        return config('trascendental.enabled_as_primary')
            ? self::TRASCENDENTAL_SITE_NAME
            : self::LAPSIQUE_SITE_NAME;
    }

    private static function sameAsUrls(): array
    {
        if (config('trascendental.enabled_as_primary')) {
            return array_values(array_filter([
                config('trascendental.instagram_url'),
                config('trascendental.facebook_url'),
            ]));
        }

        return array_values(array_filter([
            config('lapsique.instagram_url'),
        ]));
    }

    public static function djsetOgImageUrl(?SiteSetting $settings): string
    {
        $uploaded = $settings?->djset_og_image;
        if (filled($uploaded) && Storage::disk('public')->exists($uploaded)) {
            return self::absoluteImageUrl(Storage::disk('public')->url($uploaded)) ?? self::defaultOgImageUrl();
        }

        $contextualImage = self::staticPublicImageUrl('images/portfolio/photos/067-fotos-proper-54490411c4.webp');
        if (filled($contextualImage)) {
            return $contextualImage;
        }

        $portfolioImage = self::portfolioOgImageUrl();
        if (filled($portfolioImage)) {
            return $portfolioImage;
        }

        $videoImage = self::featuredVideoOgImageUrl();
        if (filled($videoImage)) {
            return $videoImage;
        }

        $bookingFallback = self::bookingOgImageUrl($settings);
        if (! str_contains($bookingFallback, 'og-default.jpg')) {
            return $bookingFallback;
        }

        if (self::staticPublicImageUrl('images/booking-og.jpg')) {
            return self::staticPublicImageUrl('images/booking-og.jpg');
        }

        return self::defaultOgImageUrl();
    }

    public static function bookingOgImageUrl(?SiteSetting $settings): string
    {
        $uploaded = $settings?->booking_og_image;
        if (filled($uploaded) && Storage::disk('public')->exists($uploaded)) {
            return self::absoluteImageUrl(Storage::disk('public')->url($uploaded)) ?? self::defaultOgImageUrl();
        }

        if (self::staticPublicImageUrl('images/booking-og.jpg')) {
            return self::staticPublicImageUrl('images/booking-og.jpg');
        }

        $portfolioImage = self::portfolioOgImageUrl();
        if (filled($portfolioImage)) {
            return $portfolioImage;
        }

        return self::defaultOgImageUrl();
    }

    public static function featuredVideoOgImageUrl(): ?string
    {
        $video = Video::query()
            ->whereDoesntHave('djs', fn ($query) => $query->where('trascendental_roster', true))
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->first();

        if (! $video) {
            return null;
        }

        $media = $video->getFirstMedia('thumbnail');

        if ($media && self::mediaFileIsReadable($media)) {
            return self::absoluteImageUrl($media->getUrl());
        }

        return self::absoluteImageUrl($video->thumbnail_url);
    }

    public static function portfolioOgImageUrl(): ?string
    {
        $item = PortfolioItem::query()
            ->where('is_active', true)
            ->where('type', 'photo')
            ->whereHas('media', fn ($query) => $query
                ->where('collection_name', 'asset')
                ->where('mime_type', 'like', 'image/%'))
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->with('media')
            ->first();
        $media = $item?->getFirstMedia('asset');

        if (! $media) {
            return null;
        }

        if (! self::mediaFileIsReadable($media)) {
            return null;
        }

        return self::absoluteImageUrl($media->getUrl());
    }

    public static function defaultOgImageUrl(): string
    {
        return self::staticPublicImageUrl('images/booking-og.jpg')
            ?? self::staticPublicImageUrl('images/og-default.jpg')
            ?? url('/images/og-default.jpg');
    }

    public static function staticPublicImageUrl(string $relativePath): ?string
    {
        $normalized = ltrim($relativePath, '/');
        $path = public_path($normalized);

        if (! is_readable($path)) {
            return null;
        }

        return url('/'.$normalized);
    }

    public static function mediaFileIsReadable(\Spatie\MediaLibrary\MediaCollections\Models\Media $media, ?string $conversion = null): bool
    {
        try {
            $path = filled($conversion) ? $media->getPath($conversion) : $media->getPath();
            $url = filled($conversion) ? $media->getUrl($conversion) : $media->getUrl();
        } catch (\Throwable) {
            return false;
        }

        $urlPath = parse_url($url, PHP_URL_PATH);

        if (is_string($urlPath) && is_readable(public_path(ltrim($urlPath, '/')))) {
            return true;
        }

        if (app()->environment('testing')) {
            return is_string($path) && is_readable($path);
        }

        return is_string($path) && is_readable($path) && ! str_starts_with((string) $urlPath, '/storage/');
    }

    public static function absoluteImageUrl(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    public static function truncate(?string $text, int $limit = 160): string
    {
        return Str::limit(trim(strip_tags((string) $text)), $limit);
    }
}
