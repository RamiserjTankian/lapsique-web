<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingSlotResource;
use App\Support\PortfolioCuration;
use Inertia\Inertia;
use Inertia\Response;

class ContentCreationController extends Controller
{
    public function __invoke(): Response
    {
        $booking = ContentBookingController::bookingPageData();
        $variant = request()->routeIs('business-reels.show') ? 'business_reels' : 'content_creation';

        return Inertia::render('ContentCreation/Show', [
            'variant' => $variant,
            'price' => $booking['price'],
            'slots' => BookingSlotResource::collection($booking['slots'])->resolve(),
            'servicePortfolio' => PortfolioCuration::forService($variant),
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }
}
