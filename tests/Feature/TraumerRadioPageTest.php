<?php

namespace Tests\Feature;

use App\Support\PageMeta;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TraumerRadioPageTest extends TestCase
{
    public function test_lapsique_exposes_the_traumer_radio_as_an_inertia_page(): void
    {
        config()->set('trascendental.enabled_as_primary', false);

        $this->get(route('radio.traumer-shonky'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Radio/TraumerShonky'));
    }

    public function test_radio_waveform_and_gallery_assets_are_materialized(): void
    {
        $this->assertFileExists(public_path('audio/traumer-shonky/peaks.json'));
        $this->assertFileExists(public_path('images/traumer-shonky/gallery/foto-157.webp'));
        $this->assertFileDoesNotExist(public_path('audio/traumer-shonky/tracks.json'));
        $this->assertDirectoryDoesNotExist(public_path('images/traumer-shonky/tracks'));
    }

    public function test_radio_has_specific_indexable_metadata(): void
    {
        $meta = PageMeta::forTraumerShonkyRadio('https://lapsique.media/archivo/traumer-b2b-shonky');

        $this->assertStringContainsString('Traumer b2b Shonky', $meta->metaTitle);
        $this->assertStringContainsString('waveform', $meta->description);
        $this->assertSame('PT2H25M51S', $meta->jsonLd['duration']);
        $this->assertStringContainsString('foto-157.webp', (string) $meta->ogImage);
        $this->assertFalse($meta->noindex);
    }
}
