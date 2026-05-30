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
        $baseQuery = PortfolioItem::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->with('media');

        $availableTags = PortfolioItem::query()
            ->where('is_active', true)
            ->get(['id', 'tags'])
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $items = $baseQuery->paginate(36)->withQueryString();

        $resolved = PortfolioItemResource::collection($items->items())->resolve();

        return Inertia::render('Portfolio/Index', [
            'items' => [
                'data' => $resolved,
                'links' => collect($items->linkCollection()->toArray())
                    ->map(fn (array $link): array => [
                        ...$link,
                        'label' => $this->localizedPaginationLabel($link['label'] ?? ''),
                    ])
                    ->all(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                ],
            ],
            'availableTags' => $availableTags,
        ]);
    }

    private function localizedPaginationLabel(string $label): string
    {
        $normalized = strtolower(trim(strip_tags(str_replace(
            ['&laquo;', '&raquo;', '«', '»'],
            '',
            html_entity_decode($label, ENT_QUOTES, 'UTF-8'),
        ))));

        return match ($normalized) {
            'previous' => __('common.actions.previous'),
            'next' => __('common.actions.next'),
            default => strip_tags(html_entity_decode($label, ENT_QUOTES, 'UTF-8')),
        };
    }
}
