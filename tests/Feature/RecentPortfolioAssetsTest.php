<?php

namespace Tests\Feature;

use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use App\Support\PortfolioCuration;
use Database\Seeders\PortfolioAssetsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentPortfolioAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_drive_exports_are_deterministic_web_ready_assets(): void
    {
        $manifest = $this->manifest();

        $this->assertCount($manifest['photo_count'], $manifest['photos']);
        $this->assertCount($manifest['video_count'], $manifest['videos']);

        $recent = collect([...$manifest['photos'], ...$manifest['videos']])
            ->whereIn('project', ['MTRX', 'Karen Echev'])
            ->values();

        $this->assertCount(14, $recent);
        $this->assertSame(['2026-07-11', '2026-07-13'], $recent->pluck('captured_on')->unique()->sort()->values()->all());
        $this->assertFalse($recent->contains(fn (array $entry): bool => str_contains(strtolower((string) ($entry['project'] ?? '')), 'zoe')));

        foreach ($recent as $entry) {
            $asset = public_path(ltrim((string) $entry['src'], '/'));

            $this->assertFileExists($asset);
            $this->assertGreaterThan(0, filesize($asset));

            if ($entry['kind'] === 'photo') {
                $this->assertLessThan(350 * 1024, filesize($asset));
                $this->assertStringEndsWith('.webp', $asset);
            } else {
                $poster = public_path(ltrim((string) $entry['poster'], '/'));

                $this->assertSame(6, $entry['duration_seconds']);
                $this->assertFalse($entry['has_audio']);
                $this->assertLessThan(2 * 1024 * 1024, filesize($asset));
                $this->assertFileExists($poster);
                $this->assertLessThan(100 * 1024, filesize($poster));
            }
        }
    }

    public function test_recent_assets_seed_with_real_editorial_metadata_and_reach_home_curation(): void
    {
        $this->seed(PortfolioAssetsSeeder::class);

        $mtrx = PortfolioItem::query()
            ->where('asset_path', '/images/portfolio/photos/2026-07-11-mtrx-pista-58db81e520.webp')
            ->firstOrFail();
        $karen = PortfolioItem::query()
            ->where('asset_path', '/videos/reels/2026-07-13-karen-echev-drop-01-5647efd7f8.mp4')
            ->firstOrFail();

        $this->assertSame('MTRX · pista', $mtrx->title);
        $this->assertSame('Pista y producción visual en MTRX, Playa del Carmen.', $mtrx->caption);
        $this->assertContains('2026-07-11', $mtrx->tags);
        $this->assertTrue($mtrx->is_featured);
        $this->assertSame(1, $mtrx->priority);

        $this->assertSame('reel', $karen->type);
        $this->assertSame('vertical', $karen->orientation);
        $this->assertSame(13, $karen->priority);

        $payload = (new PortfolioItemResource($karen))->resolve();

        $this->assertSame('Karen Echev · Drop 01', $payload['title']);
        $this->assertSame('Extracto vertical del reel final de la sesión de Karen Echev.', $payload['caption']);
        $this->assertContains('karen-echev', $payload['tags']);

        $homeTitles = PortfolioCuration::forHome(10)->pluck('title')->all();

        $this->assertSame('MTRX · pista', $homeTitles[0]);
        $this->assertContains('Karen Echev · DJ set', $homeTitles);
    }

    /**
     * @return array{photo_count: int, video_count: int, photos: list<array<string, mixed>>, videos: list<array<string, mixed>>}
     */
    private function manifest(): array
    {
        return json_decode(
            (string) file_get_contents(database_path('data/portfolio_assets.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }
}
