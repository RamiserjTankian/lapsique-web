<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingSlotResource;
use App\Http\Resources\DjResource;
use App\Http\Resources\PortfolioItemResource;
use App\Http\Resources\VideoResource;
use App\Models\Dj;
use App\Models\PortfolioItem;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $bookingData = ContentBookingController::bookingPageData();

        $originals = Video::query()
            ->whereJsonContains('tags', 'psique-originals')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        $portfolioItems = PortfolioItem::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->with('media')
            ->take(12)
            ->get();

        $djs = Dj::query()
            ->with('media')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        $settings = $bookingData['settings'];

        if ($bookingData['slots']->isEmpty() && app()->environment(['local', 'testing'])) {
            Log::warning('Home booking funnel rendered without published slots.', [
                'suggested_command' => 'php artisan booking:ensure-slots',
            ]);
        }

        return Inertia::render('Home', [
            'title' => $settings?->booking_title ?: 'Contenido premium para marcas que quieren verse y vender mejor',
            'subtitle' => $settings?->booking_subtitle ?: '2 reels editados + 20 fotografías profesionales en una sesión dirigida para elevar tu percepción de marca y ayudarte a convertir mejor.',
            'price' => $bookingData['price'],
            'slots' => BookingSlotResource::collection($bookingData['slots'])->resolve(),
            'originals' => VideoResource::collection($originals)->resolve(),
            'portfolioItems' => PortfolioItemResource::collection($portfolioItems)->resolve(),
            'djs' => DjResource::collection($djs)->resolve(),
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }
}
