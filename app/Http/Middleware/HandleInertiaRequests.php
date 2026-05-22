<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Services\StripeIntegrationService;
use App\Support\BookingMode;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $settings = SiteSetting::current();

        return [
            ...parent::share($request),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'site' => fn () => [
                'name' => 'lapsique.media',
                'bookingPrice' => $settings?->booking_price ?? (int) config('booking.content_price', 3000),
                'bookingTitle' => $settings?->booking_title,
                'bookingSubtitle' => $settings?->booking_subtitle,
                'bookingTeamName' => $settings?->booking_team_name,
                'bookingTeamBio' => $settings?->booking_team_bio,
                'whatsapp' => $settings?->booking_whatsapp ?: config('lapsique.whatsapp_number'),
                'instagramUrl' => config('lapsique.instagram_url'),
                'youtubeHandle' => config('lapsique.youtube_handle'),
                'studioLocation' => $settings?->booking_studio_location,
            ],
            'booking' => fn () => [
                'skipPayment' => BookingMode::shouldSkipPayment($request),
            ],
            'payments' => fn () => [
                'stripeConfigured' => app(StripeIntegrationService::class)->isConfigured(),
                'mercadopagoConfigured' => filled(config('mercadopago.access_token')),
            ],
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'status' => $request->session()->get('status'),
            ],
            'customer' => fn () => Auth::guard('customer')->user()?->only([
                'id',
                'name',
                'email',
            ]),
            'locale' => fn () => app()->getLocale(),
            'seo' => fn () => PageMeta::forRequest($request)->toArray(),
        ];
    }
}
