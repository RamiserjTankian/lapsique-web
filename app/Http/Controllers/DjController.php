<?php

namespace App\Http\Controllers;

use App\Http\Resources\DjResource;
use App\Http\Resources\VideoResource;
use App\Models\Dj;
use Inertia\Inertia;
use Inertia\Response;

class DjController extends Controller
{
    public function index(): Response
    {
        $highlightedDj = Dj::query()
            ->with('media')
            ->where('is_highlighted', true)
            ->first();

        $djs = Dj::query()
            ->with('media')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_featured')
            ->orderByRaw('COALESCE(JSON_LENGTH(tags), 0) desc')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->paginate(9);

        return Inertia::render('Djs/Index', [
            'djs' => DjResource::collection($djs->items())->resolve(),
            'highlightedDj' => $highlightedDj ? (new DjResource($highlightedDj))->resolve() : null,
        ]);
    }

    public function show(Dj $dj): Response
    {
        $dj->load(['media', 'videos.media']);

        return Inertia::render('Djs/Show', [
            'dj' => (new DjResource($dj))->resolve(),
            'videos' => VideoResource::collection($dj->videos)->resolve(),
        ]);
    }
}
