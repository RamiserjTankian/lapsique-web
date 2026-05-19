<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\TicketOrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $events = Event::query()
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Events/Index', [
            'events' => EventResource::collection($events)->resolve(),
        ]);
    }

    public function show(Request $request, Event $event, TicketOrderService $orderService): Response
    {
        $event->load([
            'media',
            'guests',
            'djs.media',
            'location.media',
            'ticketProducts' => fn ($query) => $query->active()->orderBy('price'),
        ]);

        return Inertia::render('Events/Show', [
            'event' => (new EventResource($event))->resolve(),
        ]);
    }
}
