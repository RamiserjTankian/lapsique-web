<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Services\StripeIntegrationService;
use App\Support\BookingMode;
use App\Support\FrontendTranslations;
use App\Support\LocaleResolver;
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
        $isTrascendental = config('trascendental.enabled_as_primary') || $request->is('trascendental*');
        $whatsapp = $isTrascendental
            ? (config('trascendental.whatsapp') ?: ($settings?->booking_whatsapp ?: config('lapsique.whatsapp_number')))
            : ($settings?->booking_whatsapp ?: config('lapsique.whatsapp_number'));

        return [
            ...parent::share($request),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'site' => fn () => [
                'name' => $isTrascendental ? 'Trascendentalby' : 'Lapsique Media',
                'bookingPrice' => $settings?->booking_price ?? (int) config('booking.content_price', 3000),
                'bookingTitle' => $settings?->booking_title,
                'bookingSubtitle' => $settings?->booking_subtitle,
                'bookingTeamName' => $settings?->booking_team_name,
                'bookingTeamBio' => $settings?->booking_team_bio,
                'whatsapp' => $whatsapp,
                'whatsappCommunityUrl' => $isTrascendental ? config('trascendental.whatsapp_community_url') : null,
                'email' => $isTrascendental ? config('trascendental.email') : null,
                'instagramUrl' => $isTrascendental ? config('trascendental.instagram_url') : config('lapsique.instagram_url'),
                'facebookUrl' => $isTrascendental ? config('trascendental.facebook_url') : null,
                'residentAdvisorUrl' => $isTrascendental ? config('trascendental.resident_advisor_url') : null,
                'youtubeHandle' => $isTrascendental ? config('trascendental.youtube_handle') : config('lapsique.youtube_handle'),
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
            'availableLocales' => LocaleResolver::SUPPORTED,
            'translations' => fn () => FrontendTranslations::all(app()->getLocale(), includeTrascendental: $isTrascendental),
            'seo' => fn () => PageMeta::forRequest($request)->toArray(),
        ];
    }
}
