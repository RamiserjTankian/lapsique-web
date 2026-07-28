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
        $price = (int) config('booking.dj_set_price', 10000);
        $title = __('seo.djset.title');
        $metaTitle = "{$title} · ".self::siteName();
        $description = self::truncate(
            __('seo.djset.description', ['price' => number_format($price, 0, '.', ',')]),
        );
        $ogImage = self::djsetOgImageUrl($settings);
        $ogImageAlt = __('seo.djset.og_alt');

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: $ogImageAlt,
            keywords: __('seo.djset.keywords'),
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: 'Producción y grabación audiovisual de DJ set',
                ogImage: $ogImage,
                breadcrumbName: 'Grabación de DJ set',
                faq: [
                    ['Cuánto dura la grabación?', 'La producción contempla hasta cuatro horas de set y una edición principal de hasta dos horas, según la propuesta acordada.'],
                    ['Incluye audio del mixer?', 'Sí. Capturamos la señal del mixer y audio ambiente para construir una mezcla con energía de cabina.'],
                    ['Incluye dron?', 'Puede incluir tomas de dron cuando la ubicación, el clima y las condiciones de seguridad permiten volar.'],
                ],
            ),
        );
    }

    public static function forDroneSession(string $canonicalUrl): PageMetaData
    {
        $title = 'Sesiones de dron en Riviera Maya para hoteles y propiedades';
        $description = 'Video y fotografía con dron para hoteles, villas, restaurantes, eventos, propiedades e inmobiliarias en Playa del Carmen, Tulum, Cancún y Riviera Maya.';
        $ogImage = self::absoluteImageUrl('/images/drone-sessions/hero.jpg');

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} | Lapsique",
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: 'Toma aérea con dron para hotel o propiedad en Riviera Maya',
            keywords: 'sesiones de dron riviera maya, video con dron playa del carmen, dron para hoteles tulum, tomas aéreas cancún, video dron inmobiliario riviera maya',
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: 'Sesiones de dron para hoteles, propiedades y negocios',
                description: 'Tomas aéreas con dron para hoteles, villas, restaurantes, eventos, propiedades, inmobiliarias y proyectos comerciales en Riviera Maya.',
                serviceType: 'Video y fotografía aérea con dron',
                ogImage: $ogImage,
                breadcrumbName: 'Sesiones de dron',
                faq: [
                    ['El dron depende del clima?', 'Sí. La sesión puede depender de viento, lluvia, ubicación y condiciones de seguridad. Antes de confirmar revisamos la viabilidad.'],
                    ['Puedo pedir video vertical y horizontal?', 'Sí. Podemos entregar material vertical para redes y horizontal para web, YouTube, presentaciones o pantallas.'],
                    ['Sirve para Airbnb o villas?', 'Sí. Las tomas de dron ayudan a mostrar ubicación, entorno, acceso y valor visual de propiedades y villas.'],
                    ['Pueden combinar dron con cámara en tierra?', 'Sí. Para proyectos comerciales conviene combinar tomas aéreas con detalles en tierra para contar mejor la experiencia.'],
                ],
            ),
        );
    }

    public static function forConstructionProgress(string $canonicalUrl): PageMetaData
    {
        $title = 'Avances de obra con dron, foto y video en Riviera Maya';
        $description = 'Documentación audiovisual de avances de obra para constructoras, arquitectos y desarrolladoras en Playa del Carmen, Tulum, Cancún y Riviera Maya.';
        $ogImage = self::absoluteImageUrl('/images/drone-sessions/construction-goba-aerial.jpg');

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} | Lapsique",
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: 'Avance de obra con dron en Riviera Maya documentado por Lapsique Media',
            keywords: 'avances de obra con dron riviera maya, seguimiento de obra playa del carmen, video de construcción tulum, fotografía de obra cancún, documentación audiovisual de obra',
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: 'Documentación audiovisual de avances de obra con fotografía, video y dron para constructoras, arquitectos, desarrolladoras e inmobiliarias en Riviera Maya.',
                serviceType: 'Documentación audiovisual de construcción',
                ogImage: $ogImage,
                breadcrumbName: 'Avances de obra',
                faq: [
                    ['Trabajan con planes mensuales?', 'Sí. Para avances de obra recomendamos planes mensuales porque el valor está en documentar la evolución del proyecto de forma constante.'],
                    ['Incluye dron?', 'Puede incluir dron según ubicación, clima, seguridad y viabilidad de vuelo.'],
                    ['Sirve para inversionistas?', 'Sí. El contenido puede usarse para mostrar progreso real a inversionistas, clientes, brokers y equipo comercial.'],
                    ['Pueden hacer comparativos de avance?', 'Sí. Si el proyecto se documenta de forma recurrente, se pueden crear comparativos visuales por fecha, etapa o zona.'],
                ],
            ),
        );
    }

    public static function forFoodReels(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $title = 'Reels de comida para restaurantes en Riviera Maya';
        $description = 'Reels, fotos y contenido para restaurantes, sushi, cafés y bares en Playa del Carmen, Tulum, Cancún y Riviera Maya. Agenda una sesión con Lapsique Media.';
        $ogImage = self::staticPublicImageUrl('images/food-reels/sushiclub-day-sushi-promo-poster.jpg')
            ?? self::bookingOgImageUrl($settings);

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} | Lapsique Media",
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: 'Reel de comida para restaurante en Riviera Maya creado por Lapsique Media',
            keywords: 'reels de comida riviera maya, reels para restaurantes playa del carmen, videos para restaurantes tulum, fotografía gastronómica cancún, contenido para restaurantes riviera maya',
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: 'Producción de reels, fotos y contenido audiovisual para restaurantes, sushi, cafés, bares y conceptos gastronómicos en Riviera Maya.',
                serviceType: 'Producción audiovisual para restaurantes',
                ogImage: $ogImage,
                breadcrumbName: 'Reels de comida',
                faq: [
                    ['Cuánto dura una sesión de reels de comida?', 'Depende del paquete. Una sesión express puede durar alrededor de 1 hora, mientras que una producción más completa puede tomar 2 o más horas según platillos, ambiente y entregables.'],
                    ['Trabajan en Playa del Carmen, Tulum y Cancún?', 'Sí. Atendemos Playa del Carmen, Tulum, Cancún, Puerto Morelos, Puerto Aventuras, Akumal, Mayakoba, Cozumel y zonas cercanas de Riviera Maya.'],
                    ['Puedo usar los reels para anuncios?', 'Sí. Podemos entregar contenido pensado para publicaciones orgánicas y también para campañas de Meta Ads.'],
                    ['Incluye fotos?', 'Puede incluir fotos según el paquete contratado. Recomendamos combinar reels y fotos para tener más material de publicación.'],
                ],
            ),
        );
    }

    public static function forContentCreation(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $title = 'Creación de contenido para redes sociales en Riviera Maya';
        $description = 'Producción de reels y fotografía para Instagram, TikTok y Meta Ads en Playa del Carmen, Tulum, Cancún y Riviera Maya. Agenda con Lapsique Media.';
        $ogImage = self::bookingOgImageUrl($settings);

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} | Lapsique Media",
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: 'Producción de reels y fotografía para redes sociales por Lapsique Media',
            keywords: 'creación de contenido riviera maya, contenido para redes sociales playa del carmen, reels para instagram tulum, videos para tiktok cancún, producción de contenido meta ads',
            jsonLd: self::serviceLandingJsonLd(
                canonicalUrl: $canonicalUrl,
                title: $title,
                description: $description,
                serviceType: 'Creación de contenido para redes sociales',
                ogImage: $ogImage,
                breadcrumbName: 'Creación de contenido',
                faq: [
                    ['Trabajan contenido para Instagram y TikTok?', 'Sí. Grabamos en formato vertical y editamos piezas para consumo móvil, publicaciones orgánicas y anuncios.'],
                    ['Puedo usar el material en Meta Ads?', 'Sí. El reel y las fotografías se entregan con uso comercial para la campaña del negocio contratado.'],
                    ['Qué tipo de negocios atienden?', 'Restaurantes, hoteles, propiedades, desarrollos, experiencias, eventos y marcas de servicio en Riviera Maya.'],
                    ['Incluye estrategia de social media?', 'La producción incluye dirección visual y lista de tomas. La gestión mensual de redes se cotiza aparte según volumen y canales.'],
                ],
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
            metaTitle: "{$title} | Lapsique Media",
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
        ]));
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
            metaTitle: "{$title} | Lapsique Media",
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
        $subtitle = $settings?->booking_subtitle
            ?: ContentSessionOffer::defaultSubtitle();
        $bookingTitle = LocalizedBookingCopy::title($settings?->booking_title);

        $title = __('seo.home.title');
        $metaTitle = "{$title} · ".self::siteName();
        $description = self::truncate(
            __('seo.home.description', ['price' => number_format($price, 0, '.', ',')]),
        );
        $ogImage = self::bookingOgImageUrl($settings);
        $ogImageAlt = __('seo.content_session_alt');

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
                    'description' => __('seo.home.scene_description'),
                    'publisher' => ['@id' => url('/#organization')],
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
            keywords: __('seo.home.keywords'),
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
            collect([$event->description, $location, $datePart])
                ->filter()
                ->implode(' · ')
                ?: __('seo.event_fallback', ['title' => $event->title]),
        );
        $ogImage = self::absoluteImageUrl(
            $event->getFirstMediaUrl('cover', 'cover_large')
                ?: $event->getFirstMediaUrl('cover'),
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
            [
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
            ],
        ];

        foreach ($videos as $video) {
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
