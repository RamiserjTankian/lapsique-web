<?php

namespace Tests\Feature;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideosIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_videos_index_renders_with_paginated_videos(): void
    {
        $featured = Video::create([
            'title' => 'Featured Set',
            'slug' => 'featured-set',
            'youtube_id' => 'abc123',
            'is_featured' => true,
            'priority' => 1,
            'tags' => ['live'],
        ]);

        Video::create([
            'title' => 'Second Video',
            'slug' => 'second-video',
            'youtube_id' => 'def456',
            'priority' => 2,
            'tags' => [],
        ]);

        $this->get(route('videos.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Videos/Index')
                ->has('featuredVideo')
                ->where('featuredVideo.id', $featured->id)
                ->has('videos.data', 1)
                ->has('videos.links')
                ->has('videos.meta')
            );
    }
}
