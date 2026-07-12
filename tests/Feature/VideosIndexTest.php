<?php

namespace Tests\Feature;

use App\Models\Video;
use App\Models\Dj;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideosIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_videos_index_renders_with_paginated_videos(): void
    {
        $dj = Dj::create([
            'name' => 'Lapsique Artist',
            'slug' => 'lapsique-artist',
            'trascendental_roster' => false,
        ]);
        $featured = Video::create([
            'title' => 'Featured Set',
            'slug' => 'featured-set',
            'youtube_id' => 'abc123',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc123',
            'is_featured' => true,
            'priority' => 1,
            'tags' => ['live'],
        ]);

        Video::create([
            'title' => 'Second Video',
            'slug' => 'second-video',
            'youtube_id' => 'def456',
            'youtube_url' => 'https://www.youtube.com/watch?v=def456',
            'priority' => 2,
            'tags' => [],
        ]);

        $dj->videos()->attach(Video::query()->pluck('id'));

        $this->get(route('videos.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Videos/Index')
                ->has('featuredVideo')
                ->where('featuredVideo.id', $featured->id)
                ->has('videos.data', 1)
                ->has('videos.links')
                ->has('videos.meta')
                ->has('aftermovies')
            );
    }
}
