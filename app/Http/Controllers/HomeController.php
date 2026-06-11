<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingSlotResource;
use App\Http\Resources\PortfolioItemResource;
use App\Support\LocalizedBookingCopy;
use App\Support\HomeHeroBackground;
use App\Support\HomeHeroProofVideos;
use App\Support\HomeReelDistribution;
use App\Support\LandingPageVideos;
use App\Support\PortfolioCuration;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $bookingData = ContentBookingController::bookingPageData();

        $portfolioItems = PortfolioCuration::forHome(12);

        $settings = $bookingData['settings'];
        $reelDistribution = HomeReelDistribution::forHome(
            HomeReelDistribution::previewCountForUserAgent(request()->userAgent()),
        );

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
            'heroBackgroundImage' => HomeHeroBackground::resolve($portfolioItems),
            'landingVideos' => $reelDistribution['landingVideos'] ?? LandingPageVideos::forHome(),
            'heroProofVideo' => $reelDistribution['heroProofVideo']
                ?? HomeHeroProofVideos::resolve($settings, $portfolioItems)[0]
                ?? null,
            'reelLibraryPreview' => $reelDistribution['reelLibraryPreview'] ?? [],
            'reelStats' => $reelDistribution['reelStats'] ?? [
                'totalSourceVideos' => 0,
                'uniqueVideos' => 0,
            ],
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }
}
