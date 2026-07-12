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
        $tagCountOrder = DB::connection()->getDriverName() === 'sqlite'
            ? 'COALESCE(json_array_length(tags), 0) desc'
            : 'COALESCE(JSON_LENGTH(tags), 0) desc';

        $highlightedDj = Dj::query()
            ->with('media')
            ->where('trascendental_roster', config('trascendental.enabled_as_primary'))
            ->where('is_highlighted', true)
            ->first();

        $djs = Dj::query()
            ->with('media')
            ->where('trascendental_roster', config('trascendental.enabled_as_primary'))
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_featured')
            ->orderByRaw($tagCountOrder)
            ->orderBy('priority')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Djs/Index', [
            'djs' => DjResource::collection($djs)->resolve(),
            'highlightedDj' => $highlightedDj ? (new DjResource($highlightedDj))->resolve() : null,
        ]);
    }

    public function show(Dj $dj): Response
    {
        abort_unless(
            (bool) $dj->trascendental_roster === (bool) config('trascendental.enabled_as_primary'),
            404,
        );

        $dj->load(['media', 'videos.media']);

        return Inertia::render('Djs/Show', [
            'dj' => (new DjResource($dj))->resolve(),
            'videos' => VideoResource::collection($dj->videos)->resolve(),
        ]);
    }
}
