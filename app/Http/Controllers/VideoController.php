<?php

namespace App\Http\Controllers;

use App\Models\Dj;
use App\Models\Video;
use Illuminate\Contracts\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        // Obtener el DJ destacado
        $highlightedDj = Dj::query()
            ->where('is_highlighted', true)
            ->first();

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
            ->get();

        return view('videos.index', compact('videos', 'highlightedDj'));
    }

    public function show(Video $video): View
    {
        $video->load('djs');

        return view('videos.show', [
            'video' => $video,
            'instagramUrl' => config('lapsique.instagram_url'),
        ]);
    }
}
