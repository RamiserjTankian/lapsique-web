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

        // Obtener el DJ destacado (is_highlighted = true)
        $highlightedDj = Dj::query()
            ->where('is_highlighted', true)
            ->first();

        $highlightDj = Dj::query()
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->first();

        // Obtener DJs con el destacado primero
        $djs = Dj::query()
            ->orderByDesc('is_highlighted')
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

        // Obtener videos con los del DJ destacado primero
        $highlightedDjId = $highlightedDj?->id;
        $videos = Video::query()
            ->with('djs')
            ->when($highlightedDjId, function ($query) use ($highlightedDjId) {
                return $query->orderByRaw("EXISTS(
                    SELECT 1 FROM dj_video 
                    WHERE dj_video.video_id = videos.id 
                    AND dj_video.dj_id = ?
                ) DESC", [$highlightedDjId]);
            })
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('published_at')
            ->take(12)
            ->get();

        return view('home', [
            'featuredEvent' => $featuredEvent,
            'highlightDj' => $highlightDj,
            'highlightedDj' => $highlightedDj,
            'djs' => $djs,
            'events' => $events,
            'formEvents' => $formEvents,
            'videos' => $videos,
            'instagramUrl' => config('lapsique.instagram_url'),
            'youtubeHandle' => config('lapsique.youtube_handle'),
        ]);
    }
}
