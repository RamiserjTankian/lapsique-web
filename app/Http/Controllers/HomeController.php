<?php

namespace App\Http\Controllers;

use App\Models\Dj;
use App\Models\Event;
use App\Models\Video;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredEvent = Event::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->first();

        $highlightDj = Dj::query()
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->first();

        $djs = Dj::query()
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        $events = Event::query()
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $formEvents = Event::query()
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $videos = Video::query()
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('published_at')
            ->take(12)
            ->get();

        return view('home', [
            'featuredEvent' => $featuredEvent,
            'highlightDj' => $highlightDj,
            'djs' => $djs,
            'events' => $events,
            'formEvents' => $formEvents,
            'videos' => $videos,
            'instagramUrl' => config('lapsique.instagram_url'),
            'youtubeHandle' => config('lapsique.youtube_handle'),
        ]);
    }
}
