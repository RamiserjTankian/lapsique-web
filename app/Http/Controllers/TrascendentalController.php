<?php

namespace App\Http\Controllers;

use App\Services\TrascendentalContentService;
use Inertia\Inertia;
use Inertia\Response;

class TrascendentalController extends Controller
{
    public function __construct(private readonly TrascendentalContentService $content)
    {
    }

    public function home(): Response
    {
        return Inertia::render('Trascendental/Home', [
            'cases' => $this->content->caseStudies(limit: 2),
            'tours' => $this->content->tours(),
            'producedEvents' => $this->content->producedEvents(),
        ]);
    }

    public function services(): Response
    {
        return Inertia::render('Trascendental/Services');
    }

    public function cases(): Response
    {
        return Inertia::render('Trascendental/Cases', [
            'cases' => $this->content->caseStudies(),
            'producedEvents' => $this->content->producedEvents(),
        ]);
    }

    public function events(): Response
    {
        $events = $this->content->producedEvents();
        $rosterEvents = $this->content->splitUpcomingEvents();
        $perPage = 12;
        $lastPage = max(1, (int) ceil(count($events) / $perPage));
        $page = min($lastPage, max(1, (int) request()->query('page', 1)));

        return Inertia::render('Trascendental/Events', [
            'events' => array_slice($events, ($page - 1) * $perPage, $perPage),
            'upcomingEvents' => $rosterEvents['upcoming'],
            'pastRosterEvents' => $rosterEvents['past'],
            'pagination' => [
                'currentPage' => $page,
                'lastPage' => $lastPage,
                'perPage' => $perPage,
                'total' => count($events),
            ],
        ]);
    }

    public function tours(): Response
    {
        return Inertia::render('Trascendental/Tours', [
            'tours' => $this->content->tours(),
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('Trascendental/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Trascendental/Contact');
    }
}
