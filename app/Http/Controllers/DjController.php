<?php

namespace App\Http\Controllers;

use App\Models\Dj;
use Illuminate\Contracts\View\View;

class DjController extends Controller
{
    public function index(): View
    {
        $djs = Dj::query()
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->get();

        return view('djs.index', compact('djs'));
    }

    public function show(Dj $dj): View
    {
        $dj->load('media');

        return view('djs.show', compact('dj'));
    }
}
