<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingSlotResource;
use App\Http\Resources\PortfolioItemResource;
use App\Support\PortfolioCuration;
use Inertia\Inertia;
use Inertia\Response;

class ContentCreationController extends Controller
{
    public function __invoke(): Response
    {
        $booking = ContentBookingController::bookingPageData();
        $portfolio = PortfolioCuration::forHome(12);

        return Inertia::render('ContentCreation/Show', [
            'price' => $booking['price'],
            'slots' => BookingSlotResource::collection($booking['slots'])->resolve(),
            'portfolioItems' => PortfolioItemResource::collection($portfolio)->resolve(),
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }
}
