<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Contracts\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        $videos = Video::query()
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('published_at')
            ->get();

        return view('videos.index', compact('videos'));
    }

    public function show(Video $video): View
    {
        return view('videos.show', [
            'video' => $video,
            'instagramUrl' => config('lapsique.instagram_url'),
        ]);
    }
}
