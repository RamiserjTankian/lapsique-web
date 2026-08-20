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

    public function test_radio_assets_and_verified_timeline_are_materialized(): void
    {
        $this->assertFileExists(public_path('audio/traumer-shonky/peaks.json'));
        $this->assertFileExists(public_path('images/traumer-shonky/gallery/foto-157.webp'));

        $tracks = json_decode((string) file_get_contents(public_path('audio/traumer-shonky/tracks.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertCount(24, $tracks);
        $this->assertTrue(collect($tracks)->every(fn (array $track) => $track['verifiedBy'] === 'Shazam'));
        $this->assertTrue(collect($tracks)->every(fn (array $track) => str_starts_with($track['cover'], '/images/traumer-shonky/tracks/')));
        $this->assertTrue(collect($tracks)->every(fn (array $track) => file_exists(public_path(ltrim($track['cover'], '/')))));
    }

    public function test_radio_has_specific_indexable_metadata(): void
    {
        $meta = PageMeta::forTraumerShonkyRadio('https://lapsique.media/archivo/traumer-b2b-shonky');

        $this->assertStringContainsString('Traumer b2b Shonky', $meta->metaTitle);
        $this->assertSame('PT2H25M51S', $meta->jsonLd['duration']);
        $this->assertStringContainsString('foto-157.webp', (string) $meta->ogImage);
        $this->assertFalse($meta->noindex);
    }
}
