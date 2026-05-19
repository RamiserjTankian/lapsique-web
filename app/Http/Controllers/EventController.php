<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\TicketOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->get();

        return view('events.index', compact('events'));
    }

    public function show(Request $request, Event $event, TicketOrderService $orderService): View
    {
        $event->load([
            'media',
            'guests',
            'djs.media',
            'location.media',
            'ticketProducts' => fn ($query) => $query->active()->orderBy('price'),
        ]);

        $inviteToken = $request->query('invite');
        $inviteLink = $orderService->resolveInviteLink($inviteToken);

        return view('events.show', compact('event', 'inviteToken', 'inviteLink'));
    }
}
