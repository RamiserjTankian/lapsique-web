<?php

namespace App\Http\Controllers;

use App\Http\Resources\DjResource;
use App\Http\Resources\VideoResource;
use App\Models\Dj;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DjController extends Controller
{
    public function index(): Response
    {
        abort_unless(config('trascendental.enabled_as_primary'), 404);

        $tagCountOrder = DB::connection()->getDriverName() === 'sqlite'
            ? 'COALESCE(json_array_length(tags), 0) desc'
            : 'COALESCE(JSON_LENGTH(tags), 0) desc';

        $highlightedDj = Dj::query()
            ->with('media')
            ->where('is_highlighted', true)
            ->first();

        $djs = Dj::query()
            ->with('media')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_featured')
            ->orderByRaw($tagCountOrder)
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
        abort_unless(config('trascendental.enabled_as_primary'), 404);

        $dj->load(['media', 'videos.media']);

        return Inertia::render('Djs/Show', [
            'dj' => (new DjResource($dj))->resolve(),
            'videos' => VideoResource::collection($dj->videos)->resolve(),
        ]);
    }
}
