<?php

namespace App\Http\Controllers;

use App\Models\Dj;
use Illuminate\Contracts\View\View;

class DjController extends Controller
{
    public function index(): View
    {
        // Obtener el DJ destacado
        $highlightedDj = Dj::query()
            ->where('is_highlighted', true)
            ->first();

        $djs = Dj::query()
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_featured')
            ->orderByRaw('COALESCE(JSON_LENGTH(tags), 0) desc')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->paginate(9);

        return view('djs.index', compact('djs', 'highlightedDj'));
    }

    public function show(Dj $dj): View
    {
        $dj->load([
            'media',
            'videos',
        ]);

        return view('djs.show', compact('dj'));
    }
}
