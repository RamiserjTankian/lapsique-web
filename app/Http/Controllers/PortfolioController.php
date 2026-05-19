<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use Illuminate\Contracts\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $items = PortfolioItem::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->with('media')
            ->get();

        // Obtener todas las tags únicas de los items publicados
        $availableTags = $items
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Obtener un item destacado aleatorio para el SEO preview
        $featuredItem = PortfolioItem::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with('media')
            ->inRandomOrder()
            ->first();

        return view('portfolio.index', compact('items', 'availableTags', 'featuredItem'));
    }
}
