<?php

namespace Tests\Feature;

use App\Models\Dj;
use App\Models\Event;
use App\Models\GuestListInviteLink;
use App\Models\TicketProduct;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LapsiqueEditorialArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('trascendental.enabled_as_primary', false);
        Carbon::setTestNow('2026-07-11 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_home_and_editorial_indexes_only_expose_lapsique_content(): void
    {
        $lapsiqueDj = Dj::create([
            'name' => 'Lapsique Artist',
            'slug' => 'lapsique-artist',
            'trascendental_roster' => false,
            'is_featured' => true,
        ]);
        $trascendentalDj = Dj::create([
            'name' => 'Trascendental Artist',
            'slug' => 'trascendental-artist',
            'trascendental_roster' => true,
        ]);

        $lapsiqueVideo = Video::create([
            'title' => 'Psique Session 001',
            'slug' => 'psique-session-001',
            'youtube_id' => 'abcdefghijk',
            'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
            'thumbnail_url' => 'https://img.youtube.com/vi/abcdefghijk/maxresdefault.jpg',
            'is_featured' => true,
        ]);
        $otherVideo = Video::create([
            'title' => 'Trascendental Tour',
            'slug' => 'trascendental-tour',
            'youtube_id' => 'lmnopqrstuv',
            'youtube_url' => 'https://www.youtube.com/watch?v=lmnopqrstuv',
        ]);
        $lapsiqueDj->videos()->attach($lapsiqueVideo);
        $trascendentalDj->videos()->attach($otherVideo);

        $lapsiqueEvent = Event::create([
            'title' => 'Lapsique Night',
            'slug' => 'lapsique-night',
            'starts_at' => now()->subMonth(),
            'trascendental_visible' => false,
        ]);
        Event::create([
            'title' => 'Trascendental Night',
            'slug' => 'trascendental-night',
            'starts_at' => now()->subMonth(),
            'trascendental_visible' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->has('sceneDjs', 1)
                ->where('sceneDjs.0.name', 'Lapsique Artist')
                ->has('sceneVideos', 1)
                ->where('sceneVideos.0.title', 'Psique Session 001')
                ->has('sceneEvents', 1)
                ->where('sceneEvents.0.title', 'Lapsique Night'));

        $this->get(route('events.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Events/Index')
                ->has('upcomingEvents', 0)
                ->has('archivedEvents', 1)
                ->where('archivedEvents.0.id', $lapsiqueEvent->id));

        $this->get(route('videos.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Videos/Index')
                ->where('featuredVideo.id', $lapsiqueVideo->id));

        $this->get(route('videos.show', $otherVideo))->assertNotFound();
    }

    public function test_event_conversion_actions_are_only_exposed_when_valid(): void
    {
        $future = Event::create([
            'title' => 'Future Date',
            'slug' => 'future-date',
            'starts_at' => now()->addWeek(),
            'trascendental_visible' => false,
        ]);
        TicketProduct::create([
            'event_id' => $future->id,
            'name' => 'General',
            'currency' => 'MXN',
            'price' => 500,
            'access_units' => 1,
            'is_active' => true,
        ]);
        $invite = GuestListInviteLink::create([
            'event_id' => $future->id,
            'token' => str_repeat('a', 64),
            'is_active' => true,
            'max_registrations' => 50,
            'current_registrations' => 10,
            'expires_at' => now()->addDays(5),
        ]);

        $past = Event::create([
            'title' => 'Past Date',
            'slug' => 'past-date',
            'starts_at' => now()->subWeek(),
            'ticket_url' => 'https://tickets.example.test/past',
            'trascendental_visible' => false,
        ]);

        $this->get(route('events.show', $future))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('event.is_upcoming', true)
                ->where('event.has_tickets', true)
                ->where('event.guest_list_url', route('guestlist.register', $invite->token)));

        $this->get(route('events.show', $past))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('event.is_upcoming', false)
                ->where('event.has_tickets', false)
                ->where('event.ticket_url', null)
                ->where('event.guest_list_url', null));
    }

    public function test_dynamic_sitemap_contains_editorial_indexes_and_lapsique_details(): void
    {
        $dj = Dj::create([
            'name' => 'Sitemap Artist',
            'slug' => 'sitemap-artist',
            'trascendental_roster' => false,
        ]);
        $event = Event::create([
            'title' => 'Sitemap Event',
            'slug' => 'sitemap-event',
            'trascendental_visible' => false,
        ]);
        $video = Video::create([
            'title' => 'Sitemap Session',
            'slug' => 'sitemap-session',
            'youtube_id' => 'abcdefghijk',
            'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
        ]);
        $dj->videos()->attach($video);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('djs.index'), false)
            ->assertSee(route('events.index'), false)
            ->assertSee(route('videos.index'), false)
            ->assertSee(route('djs.show', $dj), false)
            ->assertSee(route('events.show', $event), false)
            ->assertSee(route('videos.show', $video), false);
    }
}
