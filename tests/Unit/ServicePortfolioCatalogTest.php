<?php

namespace Tests\Unit;

use App\Support\ServicePortfolioCatalog;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ServicePortfolioCatalogTest extends TestCase
{
    #[DataProvider('serviceMinimums')]
    public function test_service_catalog_is_contextual_readable_and_meets_minimums(
        string $serviceKey,
        int $projects,
        int $images,
        int $videos,
    ): void {
        $bundle = ServicePortfolioCatalog::forService($serviceKey);
        $media = collect($bundle['projects'])->flatMap(fn (array $project): array => $project['media'])->values();

        $this->assertSame($serviceKey, $bundle['serviceKey']);
        $this->assertGreaterThanOrEqual($projects, $bundle['stats']['projectCount']);
        $this->assertGreaterThanOrEqual($images, $bundle['stats']['imageCount']);
        $this->assertGreaterThanOrEqual($videos, $bundle['stats']['videoCount']);
        $this->assertSame($media->count(), $media->unique('src')->count());
        $this->assertContains($bundle['hero']['src'], $media->pluck('src')->all());

        foreach ($media as $item) {
            $this->assertSame('lapsique', $item['site']);
            $this->assertContains($serviceKey, $item['services']);
            $this->assertStringNotContainsString('trascendental', strtolower($item['src']));
            $this->assertFileIsReadable(public_path(ltrim($item['src'], '/')));
            $this->assertNotSame('', trim($item['alt']));

            if (isset($item['poster'])) {
                $this->assertStringNotContainsString('trascendental', strtolower($item['poster']));
                $this->assertFileIsReadable(public_path(ltrim($item['poster'], '/')));
            }
        }
    }

    public function test_home_overview_has_verified_archive_claim_and_diverse_media(): void
    {
        $overview = ServicePortfolioCatalog::overview();
        $featured = collect($overview['featuredMedia']);

        $this->assertSame('lapsique', $overview['site']);
        $this->assertSame(220, $overview['archiveMediaCount']);
        $this->assertGreaterThanOrEqual(200, $overview['archiveMediaCount']);
        $this->assertCount(8, $overview['projects']);
        $this->assertCount(8, $overview['servicePreviews']);
        $this->assertCount(14, $featured);
        $this->assertCount(8, $featured->where('kind', 'image'));
        $this->assertCount(6, $featured->where('kind', 'video'));
        $this->assertSame($featured->count(), $featured->unique('src')->count());
        $previewMedia = collect($overview['servicePreviews'])->pluck('media');
        $this->assertCount(8, $previewMedia->unique('src'));
        $this->assertSame(
            [],
            $featured->pluck('src')->intersect($previewMedia->pluck('src'))->values()->all(),
        );
        $this->assertSame(
            ServicePortfolioCatalog::SERVICE_KEYS,
            collect($overview['servicePreviews'])->pluck('serviceKey')->all(),
        );
    }

    public function test_every_visible_catalog_string_has_an_english_translation_and_is_projected_server_side(): void
    {
        $catalog = $this->rawCatalog();
        $translations = $catalog['localizations']['strings']['en'];
        $visibleStrings = collect($catalog['media'])
            ->flatMap(fn (array $media): array => [
                $media['projectLabel'],
                $media['sessionLabel'] ?? null,
                $media['alt'],
            ])
            ->merge(
                collect($catalog['services'])->flatMap(
                    fn (array $service): array => collect($service['projects'])->pluck('label')->all(),
                ),
            )
            ->filter()
            ->unique()
            ->values();

        $this->assertSame(2, $catalog['version']);
        $this->assertSame(1, $catalog['localizations']['schema_version']);
        $this->assertSame('es', $catalog['localizations']['default_locale']);
        $this->assertSame([], $visibleStrings->reject(fn (string $value): bool => isset($translations[$value]))->all());

        app()->setLocale('en');

        $englishMedia = collect(ServicePortfolioCatalog::SERVICE_KEYS)
            ->flatMap(fn (string $serviceKey): array => collect(ServicePortfolioCatalog::forService($serviceKey)['projects'])
                ->flatMap(fn (array $project): array => $project['media'])
                ->all())
            ->unique('id')
            ->values();

        foreach ($englishMedia as $media) {
            $raw = collect($catalog['media'])->firstWhere('id', $media['id']);

            $this->assertSame($translations[$raw['projectLabel']], $media['projectLabel']);
            $this->assertSame($translations[$raw['sessionLabel']], $media['sessionLabel']);
            $this->assertSame($translations[$raw['alt']], $media['alt']);
        }

        $this->assertSame(
            'Padel campaign',
            collect(ServicePortfolioCatalog::forService('business_reels')['projects'])->firstWhere('key', 'padel-campaign')['label'],
        );
        $this->assertSame(
            'GOBA · recent progress',
            collect(ServicePortfolioCatalog::forService('construction_progress')['projects'])->firstWhere('key', 'goba-current')['label'],
        );
    }

    public function test_image_orientation_matches_every_curated_file(): void
    {
        $images = collect($this->rawCatalog()['media'])->where('kind', 'image');

        $this->assertCount(65, $images);

        foreach ($images as $image) {
            $size = getimagesize(public_path(ltrim($image['src'], '/')));

            $this->assertIsArray($size, "Unable to inspect [{$image['id']}].");
            $actual = $size[0] > $size[1] ? 'horizontal' : 'vertical';
            $this->assertSame($actual, $image['orientation'], "Incorrect orientation for [{$image['id']}].");
        }
    }

    public function test_video_audio_flag_matches_every_curated_file(): void
    {
        $ffprobe = (new ExecutableFinder)->find('ffprobe');

        if ($ffprobe === null) {
            $this->markTestSkipped('ffprobe is required to validate curated audio streams.');
        }

        $videos = collect($this->rawCatalog()['media'])->where('kind', 'video');

        $this->assertCount(51, $videos);
        $this->assertCount(10, $videos->where('hasAudio', true));
        $this->assertCount(41, $videos->where('hasAudio', false));

        foreach ($videos as $video) {
            $process = new Process([
                $ffprobe,
                '-v',
                'error',
                '-select_streams',
                'a:0',
                '-show_entries',
                'stream=index',
                '-of',
                'csv=p=0',
                public_path(ltrim($video['src'], '/')),
            ]);
            $process->run();

            $this->assertTrue($process->isSuccessful(), "ffprobe failed for [{$video['id']}].");
            $this->assertSame(
                trim($process->getOutput()) !== '',
                $video['hasAudio'],
                "Incorrect hasAudio value for [{$video['id']}].",
            );
        }
    }

    public function test_dj_set_exposes_all_six_real_danzahaus_drops(): void
    {
        $bundle = ServicePortfolioCatalog::forService('dj_set');
        $danzahaus = collect($bundle['projects'])->firstWhere('key', 'danzahaus');

        $this->assertIsArray($danzahaus);
        $this->assertCount(6, $danzahaus['media']);
        $this->assertSame(
            collect(range(1, 6))
                ->map(fn (int $number): string => '/videos/reels/2026-07-27-danzahaus-mauro-drop-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'.mp4')
                ->all(),
            collect($danzahaus['media'])->pluck('src')->all(),
        );
        $this->assertTrue(collect($danzahaus['media'])->every(
            fn (array $media): bool => $media['kind'] === 'video'
                && $media['hasAudio'] === true
                && isset($media['poster']),
        ));
    }

    public function test_unknown_service_fails_closed(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ServicePortfolioCatalog::forService('not_a_service');
    }

    /**
     * @return array<string, array{string, int, int, int}>
     */
    public static function serviceMinimums(): array
    {
        return [
            'content creation' => ['content_creation', 5, 12, 4],
            'business reels' => ['business_reels', 5, 8, 6],
            'food reels' => ['food_reels', 4, 12, 4],
            'dj set' => ['dj_set', 5, 8, 6],
            'event coverage' => ['event_coverage', 4, 12, 4],
            'multi camera' => ['multi_camera', 3, 12, 8],
            'drone sessions' => ['drone_sessions', 5, 6, 6],
            'construction progress' => ['construction_progress', 2, 6, 12],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rawCatalog(): array
    {
        $contents = file_get_contents(database_path('data/service_portfolio.json'));
        $catalog = is_string($contents) ? json_decode($contents, true) : null;

        $this->assertIsArray($catalog);

        return $catalog;
    }
}
