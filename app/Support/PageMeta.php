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
    public const SITE_NAME = 'lapsique.media';

    public static function forRequest(Request $request): PageMetaData
    {
        $routeName = $request->route()?->getName();
        $settings = SiteSetting::current();
        $canonicalUrl = url()->current();

        return match ($routeName) {
            'home', 'booking.show' => self::forBookingFunnel($settings, $canonicalUrl),
            'djset.show' => self::forDjSet($settings, $canonicalUrl),
            'djs.show' => self::forDj($request->route('dj'), $canonicalUrl),
            'videos.show' => self::forVideo($request->route('video'), $canonicalUrl),
            'events.show' => self::forEvent($request->route('event'), $canonicalUrl),
            'posts.show' => self::forPost($request->route('post'), $canonicalUrl),
            'djs.index' => self::forSection(
                'DJs',
                'Descubre DJs, sets y perfiles de la escena electrónica con lapsique.media.',
                $canonicalUrl,
                'DJs, música electrónica, techno, house, sets en vivo, lapsique.media',
            ),
            'videos.index' => self::forSection(
                'Videos',
                'Sets, aftermovies y piezas audiovisuales de la escena electrónica en Riviera Maya.',
                $canonicalUrl,
                'videos, DJ sets, música electrónica, lapsique.media',
            ),
            'portfolio.index' => self::forSection(
                'Portafolio',
                'Producción audiovisual, sesiones de contenido y piezas para marcas que quieren verse premium.',
                $canonicalUrl,
                'portafolio, producción audiovisual, sesión de contenido, reels, fotografía, lapsique.media',
            ),
            'booking.confirm' => self::forBookingStatus(
                'Reserva confirmada',
                'Tu sesión de contenido quedó confirmada. Revisa los detalles en lapsique.media.',
                $canonicalUrl,
                noindex: true,
            ),
            'booking.pending' => self::forBookingStatus(
                'Pago pendiente',
                'Estamos procesando tu pago de la sesión de contenido en lapsique.media.',
                $canonicalUrl,
                noindex: true,
            ),
            'booking.failure' => self::forBookingStatus(
                'Pago no completado',
                'El pago de tu sesión no se completó. Puedes reintentar desde lapsique.media.',
                $canonicalUrl,
                noindex: true,
            ),
            'customers.login', 'customers.password.request', 'customers.password.reset' => self::forSection(
                'Acceso al portal',
                'Inicia sesión en tu portal de cliente de lapsique.media.',
                $canonicalUrl,
                'portal cliente, lapsique.media',
                noindex: true,
            ),
            'customers.portal' => self::forSection(
                'Mi portal',
                'Consulta tus reservas y entregables en lapsique.media.',
                $canonicalUrl,
                'portal cliente, lapsique.media',
                noindex: true,
            ),
            default => self::forDefault($canonicalUrl),
        };
    }

    public static function forDjSet(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $price = (int) config('booking.dj_set_price', 12000);
        $title = 'Grabación de DJ Set';
        $metaTitle = "{$title} · ".self::SITE_NAME;
        $description = self::truncate(
            "Graba tu DJ set con 3 cámaras fijas y dron. Video final de una hora por $"
            .number_format($price, 0, '.', ',')
            .' MXN con agenda y pago en línea.',
        );
        $ogImage = self::djsetOgImageUrl($settings);
        $ogImageAlt = 'Grabación de DJ set — '.self::SITE_NAME;

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage,
            ogImageAlt: $ogImageAlt,
            keywords: 'grabación de DJ set, video DJ, DJ set Tulum, 3 cámaras fijas y dron, lapsique.media',
        );
    }

    public static function forBookingFunnel(?SiteSetting $settings, string $canonicalUrl): PageMetaData
    {
        $price = (int) ($settings?->booking_price ?? config('booking.content_price', 3000));
        $subtitle = $settings?->booking_subtitle
            ?: 'Reels y fotografías premium para negocios que necesitan vender con mejor presencia visual.';
        $bookingTitle = $settings?->booking_title
            ?: 'Reels cinematográficos para negocios';

        $title = 'Agenda reels para tu negocio';
        $metaTitle = "{$title} · ".self::SITE_NAME;
        $description = self::truncate(
            "{$subtitle} Sesión reservable desde $".number_format($price, 0, '.', ',').' MXN.',
        );
        $ogImage = self::bookingOgImageUrl($settings);
        $ogImageAlt = 'Sesión de contenido profesional — '.self::SITE_NAME;

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $bookingTitle,
            'description' => $subtitle,
            'image' => $ogImage,
            'provider' => [
                '@type' => 'Organization',
                'name' => self::SITE_NAME,
                'url' => config('app.url'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'MXN',
                'availability' => 'https://schema.org/InStock',
                'url' => route('home').'#agenda',
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
            keywords: 'reels para negocios, sesión de contenido, aftermovies, fotografía profesional, anuncios, producción audiovisual, lapsique.media',
            jsonLd: $jsonLd,
        );
    }

    public static function forDj(mixed $dj, string $canonicalUrl): PageMetaData
    {
        if (! $dj instanceof Dj) {
            return self::forDefault($canonicalUrl);
        }

        $title = $dj->name;
        $metaTitle = "{$title} · ".self::SITE_NAME;
        $description = self::truncate($dj->bio ?: "Perfil de {$dj->name} en lapsique.media.");
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
            ogImageAlt: "{$dj->name} — ".self::SITE_NAME,
            keywords: "{$dj->name}, DJ, música electrónica, lapsique.media",
        );
    }

    public static function forVideo(mixed $video, string $canonicalUrl): PageMetaData
    {
        if (! $video instanceof Video) {
            return self::forDefault($canonicalUrl);
        }

        $title = $video->title;
        $metaTitle = "{$title} · ".self::SITE_NAME;
        $description = self::truncate($video->description ?: "Video: {$video->title} en lapsique.media.");
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
            ogImageAlt: "{$video->title} — ".self::SITE_NAME,
            keywords: "{$video->title}, video, DJ set, lapsique.media",
        );
    }

    public static function forEvent(mixed $event, string $canonicalUrl): PageMetaData
    {
        if (! $event instanceof Event) {
            return self::forDefault($canonicalUrl);
        }

        $title = $event->title;
        $metaTitle = "{$title} · ".self::SITE_NAME;
        $location = $event->location?->name;
        $datePart = $event->starts_at?->translatedFormat('d M Y');
        $description = self::truncate(
            collect([$event->description, $location, $datePart])
                ->filter()
                ->implode(' · ')
                ?: "Evento {$event->title} en lapsique.media.",
        );
        $ogImage = self::absoluteImageUrl($event->getFirstMediaUrl('cover', 'large'));

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'website',
            ogImage: $ogImage ?: self::defaultOgImageUrl(),
            ogImageAlt: "{$event->title} — ".self::SITE_NAME,
            keywords: "{$event->title}, evento, música electrónica, lapsique.media",
        );
    }

    public static function forPost(mixed $post, string $canonicalUrl): PageMetaData
    {
        if (! $post instanceof Post) {
            return self::forDefault($canonicalUrl);
        }

        $title = $post->title;
        $metaTitle = "{$title} · ".self::SITE_NAME;
        $description = self::truncate($post->excerpt ?: Str::limit(strip_tags((string) $post->content), 200));
        $ogImage = self::absoluteImageUrl($post->getFirstMediaUrl('cover', 'large'));

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: $description,
            canonicalUrl: $canonicalUrl,
            ogType: 'article',
            ogImage: $ogImage ?: self::defaultOgImageUrl(),
            ogImageAlt: "{$post->title} — ".self::SITE_NAME,
            keywords: "{$post->title}, blog, lapsique.media",
        );
    }

    public static function forSection(
        string $title,
        string $description,
        string $canonicalUrl,
        string $keywords = '',
        bool $noindex = false,
    ): PageMetaData {
        $metaTitle = "{$title} · ".self::SITE_NAME;

        return new PageMetaData(
            title: $title,
            metaTitle: $metaTitle,
            description: self::truncate($description),
            canonicalUrl: $canonicalUrl,
            ogImage: self::defaultOgImageUrl(),
            ogImageAlt: "{$title} — ".self::SITE_NAME,
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
            self::SITE_NAME,
            'Sesiones de contenido premium: 1 reel editado + 10 fotografías editadas para elevar tu marca.',
            $canonicalUrl,
            'sesión de contenido, producción audiovisual, reels, lapsique.media',
        );
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

        if (file_exists(public_path('images/booking-og.jpg'))) {
            return url('/images/booking-og.jpg');
        }

        return self::defaultOgImageUrl();
    }

    public static function bookingOgImageUrl(?SiteSetting $settings): string
    {
        $uploaded = $settings?->booking_og_image;
        if (filled($uploaded) && Storage::disk('public')->exists($uploaded)) {
            return self::absoluteImageUrl(Storage::disk('public')->url($uploaded)) ?? self::defaultOgImageUrl();
        }

        $portfolioImage = self::portfolioOgImageUrl();
        if (filled($portfolioImage)) {
            return $portfolioImage;
        }

        if (file_exists(public_path('images/booking-og.jpg'))) {
            return url('/images/booking-og.jpg');
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

        return self::absoluteImageUrl(
            $video->getFirstMediaUrl('thumbnail')
                ?: $video->thumbnail_url,
        );
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

        return self::absoluteImageUrl(
            $media->hasGeneratedConversion('large')
                ? $media->getUrl('large')
                : $media->getUrl(),
        );
    }

    public static function defaultOgImageUrl(): string
    {
        return url('/images/og-default.jpg');
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
