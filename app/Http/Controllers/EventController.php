<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;

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

    public function show(Event $event): View
    {
        $event->load([
            'media',
            'guests',
            'djs.media',
            'location.media',
        ]);

        return view('events.show', compact('event'));
    }
}
