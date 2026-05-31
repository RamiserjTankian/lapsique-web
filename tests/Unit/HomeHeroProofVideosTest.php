<?php

namespace Tests\Unit;

use App\Models\PortfolioItem;
use App\Models\SiteSetting;
use App\Models\Video;
use App\Support\HomeHeroProofVideos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class HomeHeroProofVideosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('landing.output_dir', 'videos/home-hero-proof-test-'.uniqid());
    }

    public function test_resolves_youtube_slot_from_settings(): void
    {
        SiteSetting::query()->create([
            'home_hero_proof_1_title' => 'Traumer drop',
            'home_hero_proof_1_source' => 'youtube',
            'home_hero_proof_1_reference' => 'dQw4w9WgXcQ',
        ]);

        $videos = HomeHeroProofVideos::resolve(SiteSetting::current(), collect());

        $this->assertCount(1, $videos);
        $this->assertSame('Traumer drop', $videos[0]['title']);
        $this->assertSame('youtube', $videos[0]['media_type']);
        $this->assertStringContainsString('dQw4w9WgXcQ', (string) $videos[0]['embed_url']);
        $this->assertStringContainsString('mute=1', (string) $videos[0]['embed_url']);
    }

    public function test_falls_back_to_portfolio_videos_when_settings_empty(): void
    {
        PortfolioItem::create([
            'title' => 'Zepp at UMi',
            'slug' => 'zepp-umi',
            'type' => 'video',
            'source' => 'youtube',
            'youtube_id' => 'abc12345678',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc12345678',
            'is_active' => true,
            'is_featured' => true,
            'priority' => 1,
        ]);

        $videos = HomeHeroProofVideos::resolve(null, PortfolioItem::query()->get());

        $this->assertCount(1, $videos);
        $this->assertSame('youtube', $videos[0]['media_type']);
        $this->assertStringContainsString('abc12345678', (string) $videos[0]['embed_url']);
    }

    public function test_resolves_video_catalog_reference(): void
    {
        $video = Video::create([
            'title' => 'Set grabado',
            'slug' => 'set-grabado',
            'youtube_id' => 'xyz98765432',
            'youtube_url' => 'https://www.youtube.com/watch?v=xyz98765432',
        ]);

        SiteSetting::query()->create([
            'home_hero_proof_1_source' => 'video',
            'home_hero_proof_1_reference' => (string) $video->id,
        ]);

        $videos = HomeHeroProofVideos::resolve(SiteSetting::current(), collect());

        $this->assertCount(1, $videos);
        $this->assertSame('Set grabado', $videos[0]['title']);
        $this->assertStringContainsString('xyz98765432', (string) $videos[0]['embed_url']);
    }
}
