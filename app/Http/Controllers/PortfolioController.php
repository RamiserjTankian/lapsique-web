<?php

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    public function index(): Response
    {
        $items = PortfolioItem::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->with('media')
            ->get();

        $availableTags = $items
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $resolved = PortfolioItemResource::collection($items)->resolve();
        $featuredItem = collect($resolved)->firstWhere('is_featured', true)
            ?? ($resolved[0] ?? null);

        return Inertia::render('Portfolio/Index', [
            'items' => $resolved,
            'featuredItem' => $featuredItem,
            'availableTags' => $availableTags,
        ]);
    }
}
