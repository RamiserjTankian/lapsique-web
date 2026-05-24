<?php

namespace Tests\Unit;

use App\Support\ReelLibrary;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReelLibraryTest extends TestCase
{
    public function test_is_excluded_reel_filters_soundwaves_and_sw_slug(): void
    {
        $this->assertTrue(ReelLibrary::isExcludedReel('073-mac-downloads-soundwaves-aftermovie.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('085-mac-proyectos-sw-may-2-reel-1.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Soundwaves Aftermovie'));
        $this->assertFalse(ReelLibrary::isExcludedReel('057-lapsique-aftermovie-pergola.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('031-bluepointrs-provenza-marcha.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Bluepointrs Provenza Marcha'));
        $this->assertTrue(ReelLibrary::isExcludedReel('056-lapsique-ad-3-rebolledo-fix.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Lapsique Ad 3 Rebolledo Fix'));
        $this->assertFalse(ReelLibrary::isExcludedReel('076-mac-proyectos-aftermovie-rebolledo.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('001-bluepointrs-ad-sudbeat.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Bluepointrs Ad Sudbeat'));
        $this->assertFalse(ReelLibrary::isExcludedReel('050-bluepointrs-sudbeat-aftermovie-1-graziano-.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('070-mac-documents-demerry-reel-1vertical.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Mac Documents Demerry Reel 1vertical'));
        $this->assertFalse(ReelLibrary::isExcludedReel('069-mac-documents-demerry-reel-1920-x1080.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('037-bluepointrs-reel-early-render.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Bluepointrs Reel Early Render'));
        $this->assertTrue(ReelLibrary::isExcludedReel('074-mac-downloads-video-reel-bryz-1.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Mac Downloads Video Reel Bryz 1'));
        $this->assertTrue(ReelLibrary::isExcludedReel('071-mac-downloads-reel-empleados.mp4'));
        $this->assertTrue(ReelLibrary::isExcludedReel('Mac Downloads Reel Empleados'));
        $this->assertTrue(ReelLibrary::isExcludedReel('046-bluepointrs-reel.mp4'));
        $this->assertFalse(ReelLibrary::isExcludedReel('045-bluepointrs-reel-review.mp4'));
    }

    public function test_all_skips_excluded_reels_from_directory_scan(): void
    {
        $dir = 'videos/reels-exclude-test-'.uniqid();
        $publicDir = public_path($dir);
        File::ensureDirectoryExists($publicDir);
        File::put($publicDir.'/001-valid-reel.mp4', 'test');
        File::put($publicDir.'/002-soundwaves-aftermovie.mp4', 'test');
        File::put($publicDir.'/003-sw-promo-reel.mp4', 'test');

        config([
            'landing.reels_dir' => $dir,
            'landing.reels_manifest' => $dir.'/missing-manifest.json',
        ]);

        $entries = ReelLibrary::all();

        $this->assertCount(1, $entries);
        $this->assertSame('/'.$dir.'/001-valid-reel.mp4', $entries[0]['src']);

        File::deleteDirectory($publicDir);
    }
}
