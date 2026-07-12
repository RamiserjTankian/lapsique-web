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
        abort_if(config('trascendental.enabled_as_primary'), 404);

        $events = Event::query()
            ->where('trascendental_visible', false)
            ->with(['media', 'djs.media', 'location.media', 'ticketProducts', 'guestListInviteLinks'])
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->get();

        $upcoming = $events->filter(fn (Event $event) => $event->starts_at?->isFuture() ?? false)->values();
        $archive = $events->reject(fn (Event $event) => $event->starts_at?->isFuture() ?? false)->values();

        return Inertia::render('Events/Index', [
            'upcomingEvents' => EventResource::collection($upcoming)->resolve(),
            'archivedEvents' => EventResource::collection($archive)->resolve(),
        ]);
    }

    public function show(Request $request, Event $event, TicketOrderService $orderService): Response
    {
        abort_if(config('trascendental.enabled_as_primary') || $event->trascendental_visible, 404);

        $event->load([
            'media',
            'guests',
            'djs.media',
            'location.media',
            'ticketProducts' => fn ($query) => $query->active()->orderBy('price'),
            'guestListInviteLinks' => fn ($query) => $query
                ->where('is_active', true)
                ->orderByDesc('created_at'),
        ]);

        return Inertia::render('Events/Show', [
            'event' => (new EventResource($event))->resolve(),
        ]);
    }
}
