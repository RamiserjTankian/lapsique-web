<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingSlotResource;
use App\Http\Resources\DjResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\PortfolioItemResource;
use App\Http\Resources\VideoResource;
use App\Models\Dj;
use App\Models\Event;
use App\Models\Video;
use App\Support\LandingPageVideos;
use App\Support\LocalizedBookingCopy;
use App\Support\PortfolioCuration;
use App\Support\ServicePortfolioCatalog;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $bookingData = ContentBookingController::bookingPageData();

        $portfolioItems = PortfolioCuration::forHome(12);
        $portfolioOverview = ServicePortfolioCatalog::overview();
        $overviewHeroImage = collect($portfolioOverview['heroMedia'])
            ->firstWhere('kind', 'image');
        $overviewHeroVideo = collect($portfolioOverview['heroMedia'])
            ->firstWhere('kind', 'video');
        $landingVideos = LandingPageVideos::forHome();
        $sceneDjs = Dj::query()
            ->where('trascendental_roster', false)
            ->with('media')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->limit(6)
            ->get();
        $sceneVideos = Video::query()
            ->with('djs.media')
            ->whereHas('djs', fn ($query) => $query->where('trascendental_roster', false))
            ->whereDoesntHave('djs', fn ($query) => $query->where('trascendental_roster', true))
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->limit(3)
            ->get();
        $sceneEvents = Event::query()
            ->where('trascendental_visible', false)
            ->with(['media', 'djs.media', 'location.media', 'ticketProducts', 'guestListInviteLinks'])
            ->orderByRaw('starts_at IS NULL')
            ->orderByDesc('starts_at')
            ->limit(3)
            ->get();

        $settings = $bookingData['settings'];

        if ($bookingData['slots']->isEmpty() && app()->environment(['local', 'testing'])) {
            Log::warning('Home booking funnel rendered without published slots.', [
                'suggested_command' => 'php artisan booking:ensure-slots',
            ]);
        }

        return Inertia::render('Home', [
            'title' => LocalizedBookingCopy::title($settings?->booking_title),
            'subtitle' => LocalizedBookingCopy::subtitle($settings?->booking_subtitle),
            'price' => $bookingData['price'],
            'slots' => BookingSlotResource::collection($bookingData['slots'])->resolve(),
            'portfolioItems' => PortfolioItemResource::collection($portfolioItems)->resolve(),
            'portfolioOverview' => $portfolioOverview,
            'sceneDjs' => DjResource::collection($sceneDjs)->resolve(),
            'sceneVideos' => VideoResource::collection($sceneVideos)->resolve(),
            'sceneEvents' => EventResource::collection($sceneEvents)->resolve(),
            'sceneMedia' => PortfolioItemResource::collection(PortfolioCuration::forScene(10))->resolve(),
            'heroBackgroundImage' => is_array($overviewHeroImage) ? [
                'url' => $overviewHeroImage['src'],
                'alt' => $overviewHeroImage['alt'],
            ] : null,
            'landingVideos' => $landingVideos,
            'heroProofVideo' => is_array($overviewHeroVideo) ? [
                'title' => $overviewHeroVideo['alt'],
                'media_type' => 'video',
                'embed_url' => null,
                'playback_url' => $overviewHeroVideo['src'],
                'poster_url' => $overviewHeroVideo['poster'] ?? null,
            ] : LandingPageVideos::toHeroProofVideo($landingVideos['proof'] ?? null),
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }
}
