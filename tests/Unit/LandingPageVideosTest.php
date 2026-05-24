<?php

namespace Tests\Unit;

use App\Support\LandingPageVideos;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LandingPageVideosTest extends TestCase
{
    public function test_for_home_returns_null_when_manifest_missing(): void
    {
        Config::set('landing.output_dir', 'videos/landing-missing-test-'.uniqid());

        $this->assertNull(LandingPageVideos::forHome());
    }

    public function test_for_home_parses_manifest_and_fills_creative_slots(): void
    {
        $outputKey = 'videos/landing-test-'.uniqid();
        $publicDir = public_path($outputKey);

        Config::set('landing.output_dir', $outputKey);

        File::ensureDirectoryExists($publicDir);

        $base = '/'.$outputKey;
        File::put($publicDir.'/manifest.json', json_encode([
            'version' => 1,
            'hero' => ['id' => 'hero', 'src' => $base.'/hero.mp4', 'poster' => null, 'title' => 'Hero'],
            'offer' => ['id' => 'offer', 'src' => $base.'/offer.mp4', 'poster' => null, 'title' => 'Offer'],
            'proof' => ['id' => 'proof', 'src' => $base.'/proof.mp4', 'poster' => null, 'title' => 'Proof'],
            'pauta' => ['id' => 'pauta', 'src' => $base.'/pauta.mp4', 'poster' => null, 'title' => 'Pauta'],
            'creative' => [
                ['id' => 'creative-1', 'src' => $base.'/creative-1.mp4', 'poster' => null, 'title' => 'C1'],
            ],
        ], JSON_THROW_ON_ERROR));

        foreach (['hero', 'offer', 'proof', 'pauta', 'creative-1'] as $id) {
            File::put($publicDir.'/'.$id.'.mp4', 'test');
        }

        $home = LandingPageVideos::forHome();

        $this->assertNotNull($home);
        $this->assertSame($base.'/hero.mp4', $home['hero']['src']);
        $this->assertCount(3, $home['creative']);
        $this->assertArrayHasKey('aftermovies', $home);
        $this->assertArrayHasKey('equipment', $home);

        $proof = LandingPageVideos::toHeroProofVideo($home['proof']);

        $this->assertNotNull($proof);
        $this->assertSame('video', $proof['media_type']);
        $this->assertSame($base.'/proof.mp4', $proof['playback_url']);

        File::deleteDirectory($publicDir);
    }

    public function test_for_home_uses_imported_manifest_when_present(): void
    {
        if (! is_file(public_path('videos/landing/manifest.json'))) {
            $this->markTestSkipped('Run php artisan landing:videos-import first.');
        }

        Config::set('landing.output_dir', 'videos/landing');

        $home = LandingPageVideos::forHome();

        $this->assertNotNull($home);
        $this->assertNotNull($home['hero']);
        $this->assertGreaterThanOrEqual(1, count($home['creative']));
    }

    public function test_to_hero_proof_video_returns_null_for_missing_file(): void
    {
        $this->assertNull(LandingPageVideos::toHeroProofVideo([
            'src' => '/videos/landing/does-not-exist.mp4',
            'poster' => null,
            'title' => null,
        ]));
    }
}
