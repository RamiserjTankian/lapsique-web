<?php

namespace Tests\Unit;

use App\Support\HomeReelDistribution;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HomeReelDistributionTest extends TestCase
{
    public function test_for_home_returns_null_when_reel_pool_is_empty(): void
    {
        $dir = 'videos/reels-empty-'.uniqid();
        Config::set('landing.reels_dir', $dir);
        Config::set('landing.reels_manifest', $dir.'/manifest.json');

        $this->assertNull(HomeReelDistribution::forHome());
    }

    public function test_pick_entries_returns_unique_videos_without_repeats(): void
    {
        $pool = [
            ['id' => 'a', 'title' => 'A', 'src' => '/videos/reels/a.mp4'],
            ['id' => 'b', 'title' => 'B', 'src' => '/videos/reels/b.mp4'],
            ['id' => 'c', 'title' => 'C', 'src' => '/videos/reels/c.mp4'],
        ];

        $picked = HomeReelDistribution::pickEntries($pool, 5);

        $this->assertCount(3, $picked);
        $this->assertSame(['a', 'b', 'c'], collect($picked)->pluck('id')->sort()->values()->all());
    }

    public function test_for_home_distributes_landing_slots_and_preview(): void
    {
        $dir = 'videos/reels-test-'.uniqid();
        $publicDir = public_path($dir);
        Config::set('landing.reels_dir', $dir);
        Config::set('landing.reels_manifest', $dir.'/manifest.json');
        Config::set('landing.reels_library_preview_count', 5);

        File::ensureDirectoryExists($publicDir);

        for ($i = 1; $i <= 30; $i++) {
            File::put($publicDir.'/reel-'.sprintf('%03d', $i).'.mp4', 'test');
        }

        $result = HomeReelDistribution::forHome();

        $this->assertNotNull($result);
        $this->assertNotNull($result['landingVideos']['offer']['src'] ?? null);
        $this->assertNotNull($result['heroProofVideo']);
        $this->assertCount(2, $result['landingVideos']['floats']);
        $this->assertCount(3, $result['landingVideos']['creative']);
        $this->assertCount(4, $result['landingVideos']['aftermovies']);
        $this->assertCount(5, $result['reelLibraryPreview']);
        $this->assertSame('video', $result['heroProofVideo']['media_type']);
        $this->assertSame(30, $result['reelStats']['uniqueVideos']);

        File::deleteDirectory($publicDir);
    }

    public function test_for_home_assigns_unique_reel_sources_across_page(): void
    {
        $dir = 'videos/reels-unique-test-'.uniqid();
        $publicDir = public_path($dir);
        Config::set('landing.reels_dir', $dir);
        Config::set('landing.reels_manifest', $dir.'/manifest.json');
        Config::set('landing.reels_library_preview_count', 5);

        File::ensureDirectoryExists($publicDir);

        for ($i = 1; $i <= 30; $i++) {
            File::put($publicDir.'/reel-'.sprintf('%03d', $i).'.mp4', 'test');
        }

        $result = HomeReelDistribution::forHome();
        $this->assertNotNull($result);

        $sources = [];

        foreach (['hero', 'offer', 'proof', 'pauta', 'package', 'gear'] as $key) {
            $src = $result['landingVideos'][$key]['src'] ?? null;

            if ($src !== null) {
                $sources[] = $src;
            }
        }

        foreach (['creative', 'equipment', 'aftermovies', 'floats'] as $key) {
            foreach ($result['landingVideos'][$key] as $entry) {
                $sources[] = $entry['src'];
            }
        }

        foreach ($result['reelLibraryPreview'] as $entry) {
            $sources[] = $entry['src'];
        }

        $this->assertSame(count($sources), count(array_unique($sources)));

        File::deleteDirectory($publicDir);
    }

    public function test_preview_count_for_mobile_user_agent(): void
    {
        config([
            'landing.reels_library_preview_count' => 10,
            'landing.reels_library_preview_count_mobile' => 4,
        ]);

        $this->assertSame(4, HomeReelDistribution::previewCountForUserAgent('Mozilla/5.0 (iPhone)'));
        $this->assertSame(10, HomeReelDistribution::previewCountForUserAgent('Mozilla/5.0 (Macintosh)'));
    }
}
