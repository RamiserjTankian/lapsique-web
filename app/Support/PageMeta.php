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
        );
    }

    public static function forDroneSession(string $canonicalUrl): PageMetaData
    {
        $price = (int) config('booking.drone_session_price', 3000);
        $title = __('seo.drone_session.title');
        $description = self::truncate(
            __('seo.drone_session.description', ['price' => number_format($price, 0, '.', ',')]),
        );
        $ogImage = self::absoluteImageUrl('/images/drone-sessions/hero.jpg');

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $title,
            'description' => $description,
            'image' => $ogImage,
            'serviceType' => __('seo.drone_session.service_type'),
            'areaServed' => [
                ['@type' => 'City', 'name' => 'Cancún'],
                ['@type' => 'City', 'name' => 'Playa del Carmen'],
                ['@type' => 'City', 'name' => 'Tulum'],
            ],
            'provider' => [
                '@type' => 'Organization',
                'name' => self::siteName(),
                'url' => config('app.url'),
                'sameAs' => self::sameAsUrls(),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'MXN',
                'availability' => 'https://schema.org/InStock',
                'url' => rtrim($canonicalUrl, '/').'#agenda',
            ],
        ];

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} · ".self::siteName(),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: __('seo.drone_session.og_alt'),
            keywords: __('seo.drone_session.keywords'),
            jsonLd: $jsonLd,
        );
    }

    public static function forConstructionProgress(string $canonicalUrl): PageMetaData
    {
        $price = (int) config('booking.construction_progress_price', 5000);
        $title = __('seo.construction_progress.title');
        $description = self::truncate(
            __('seo.construction_progress.description', ['price' => number_format($price, 0, '.', ',')]),
        );
        $ogImage = self::absoluteImageUrl('/images/drone-sessions/goba-construction.jpg');

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $title,
            'description' => $description,
            'image' => $ogImage,
            'serviceType' => __('seo.construction_progress.service_type'),
            'areaServed' => [
                ['@type' => 'City', 'name' => 'Cancún'],
                ['@type' => 'City', 'name' => 'Playa del Carmen'],
                ['@type' => 'City', 'name' => 'Tulum'],
            ],
            'provider' => [
                '@type' => 'Organization',
                'name' => self::siteName(),
                'url' => config('app.url'),
                'sameAs' => self::sameAsUrls(),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'MXN',
                'availability' => 'https://schema.org/InStock',
                'url' => rtrim($canonicalUrl, '/').'#agenda',
            ],
        ];

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} · ".self::siteName(),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: __('seo.construction_progress.og_alt'),
            keywords: __('seo.construction_progress.keywords'),
            jsonLd: $jsonLd,
        );
    }

    public static function forFoodReels(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $price = (int) ($settings?->booking_price ?? config('booking.content_price', 4000));
        $isEnglish = app()->getLocale() === 'en';
        $title = $isEnglish
            ? 'Food reels and restaurant content'
            : 'Reels de comida y contenido para restaurantes';
        $description = self::truncate($isEnglish
            ? 'Food reels, product photos, and lifestyle activations for restaurants that need sharper Instagram, ads, and menu content.'
            : 'Reels de comida, fotos de producto y activaciones para restaurantes que necesitan verse mejor en Instagram, anuncios y menú digital.');
        $ogImage = self::staticPublicImageUrl('images/food-reels/sushiclub-day-sushi-promo-poster.jpg')
            ?? self::bookingOgImageUrl($settings);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $title,
            'description' => $description,
            'image' => $ogImage,
            'serviceType' => $isEnglish ? 'Food reel production' : 'Producción de reels gastronómicos',
            'areaServed' => [
                ['@type' => 'City', 'name' => 'Cancun'],
                ['@type' => 'City', 'name' => 'Playa del Carmen'],
                ['@type' => 'City', 'name' => 'Tulum'],
            ],
            'provider' => [
                '@type' => 'Organization',
                'name' => self::siteName(),
                'url' => config('app.url'),
                'sameAs' => self::sameAsUrls(),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'MXN',
                'availability' => 'https://schema.org/InStock',
                'url' => rtrim($canonicalUrl, '/').'#agenda',
            ],
        ];

        return new PageMetaData(
            title: $title,
            metaTitle: "{$title} · ".self::siteName(),
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: $isEnglish
                ? 'SushiClub food reel produced by Lapsique Media'
                : 'Reel de comida de SushiClub producido por Lapsique Media',
            keywords: $isEnglish
                ? 'food reels, restaurant content, food photography, Cancun restaurants, Lapsique Media'
                : 'reels de comida, contenido para restaurantes, fotografía gastronómica, restaurantes Cancún, Lapsique Media',
            jsonLd: $jsonLd,
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
            '@type' => 'Service',
            'name' => $bookingTitle,
            'description' => $description,
            'image' => $ogImage,
            'serviceType' => __('seo.home.service_type'),
            'areaServed' => [
                '@type' => 'AdministrativeArea',
                'name' => 'Riviera Maya',
            ],
            'provider' => [
                '@type' => 'Organization',
                'name' => self::siteName(),
                'url' => config('app.url'),
                'sameAs' => self::sameAsUrls(),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'MXN',
                'availability' => 'https://schema.org/InStock',
                'url' => Str::finish($canonicalUrl, '/').'#agenda',
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
        $ogImage = self::absoluteImageUrl($event->getFirstMediaUrl('cover', 'large'));

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage ?: self::defaultOgImageUrl(),
            ogImageAlt: "{$event->title} — ".self::siteName(),
            keywords: "{$event->title}, evento, música electrónica, ".self::siteName(),
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
