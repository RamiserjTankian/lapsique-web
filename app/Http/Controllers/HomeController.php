<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingSlotResource;
use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use App\Support\HomeHeroProofVideos;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $bookingData = ContentBookingController::bookingPageData();

        $portfolioItems = PortfolioItem::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->with('media')
            ->take(12)
            ->get();

        $settings = $bookingData['settings'];

        if ($bookingData['slots']->isEmpty() && app()->environment(['local', 'testing'])) {
            Log::warning('Home booking funnel rendered without published slots.', [
                'suggested_command' => 'php artisan booking:ensure-slots',
            ]);
        }

        return Inertia::render('Home', [
            'title' => $settings?->booking_title ?: 'Reels cinematográficos para negocios',
            'subtitle' => $settings?->booking_subtitle ?: 'Producción dirigida para crear reels y fotos premium que expliquen tu oferta, eleven tu marca y alimenten campañas.',
            'price' => $bookingData['price'],
            'slots' => BookingSlotResource::collection($bookingData['slots'])->resolve(),
            'portfolioItems' => PortfolioItemResource::collection($portfolioItems)->resolve(),
            'heroProofVideo' => HomeHeroProofVideos::resolve($settings, $portfolioItems)[0] ?? null,
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }
}
