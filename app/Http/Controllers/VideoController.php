<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioItemResource;
use App\Http\Resources\VideoResource;
use App\Models\Dj;
use App\Models\Video;
use App\Support\PortfolioCuration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(Request $request): Response
    {
        $currentRoster = (bool) config('trascendental.enabled_as_primary');

        $highlightedDj = Dj::query()
            ->where('trascendental_roster', $currentRoster)
            ->where('is_highlighted', true)
            ->first();

        $highlightedDjId = $highlightedDj?->id;

        $baseQuery = Video::query()
            ->with(['djs.media'])
            ->whereHas('djs', fn ($query) => $query->where(
                'trascendental_roster',
                $currentRoster,
            ))
            ->whereDoesntHave('djs', fn ($query) => $query->where('trascendental_roster', ! $currentRoster))
            ->when($highlightedDjId, function ($query) use ($highlightedDjId) {
                return $query->orderByRaw('EXISTS(
                    SELECT 1 FROM dj_video 
                    WHERE dj_video.video_id = videos.id 
                    AND dj_video.dj_id = ?
                ) DESC', [$highlightedDjId]);
            })
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('published_at');

        $featured = (clone $baseQuery)->first();

        $videosQuery = (clone $baseQuery);
        if ($featured) {
            $videosQuery->where('id', '!=', $featured->id);
        }

        $videos = $videosQuery->paginate(12)->withQueryString();

        return Inertia::render('Videos/Index', [
            'featuredVideo' => $featured ? (new VideoResource($featured))->resolve() : null,
            'videos' => [
                'data' => VideoResource::collection($videos->items())->resolve(),
                'links' => $videos->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $videos->currentPage(),
                    'last_page' => $videos->lastPage(),
                    'per_page' => $videos->perPage(),
                    'total' => $videos->total(),
                    'from' => $videos->firstItem(),
                    'to' => $videos->lastItem(),
                ],
            ],
            'highlightedDjName' => $highlightedDj?->name,
            'aftermovies' => PortfolioItemResource::collection(PortfolioCuration::forAftermovies(9))->resolve(),
        ]);
    }

    public function show(Video $video): Response
    {
        $currentRoster = (bool) config('trascendental.enabled_as_primary');
        $video->load('djs.media');
        abort_unless(
            $video->djs->contains(
                fn (Dj $dj) => (bool) $dj->trascendental_roster === $currentRoster,
            ) && $video->djs->doesntContain(
                fn (Dj $dj) => (bool) $dj->trascendental_roster !== $currentRoster,
            ),
            404,
        );

        $related = Video::query()
            ->with('djs.media')
            ->whereKeyNot($video->getKey())
            ->whereHas('djs', fn ($query) => $query->where(
                'trascendental_roster',
                $currentRoster,
            ))
            ->whereDoesntHave('djs', fn ($query) => $query->where('trascendental_roster', ! $currentRoster))
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->limit(3)
            ->get();

        return Inertia::render('Videos/Show', [
            'video' => (new VideoResource($video))->resolve(),
            'relatedVideos' => VideoResource::collection($related)->resolve(),
            'instagramUrl' => config('lapsique.instagram_url'),
        ]);
    }
}
