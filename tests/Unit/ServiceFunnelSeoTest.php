<?php

namespace Tests\Unit;

use App\Support\PageMeta;
use App\Support\PageMetaData;
use Tests\TestCase;

class ServiceFunnelSeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('es');
        config()->set('trascendental.enabled_as_primary', false);
    }

    public function test_home_and_service_funnels_have_distinct_contextual_metadata_and_open_graph_media(): void
    {
        $metaByService = $this->serviceMeta();

        $this->assertCount(9, array_unique(array_map(
            fn (PageMetaData $meta): string => $meta->canonicalUrl,
            $metaByService,
        )));
        $this->assertCount(9, array_unique(array_map(
            fn (PageMetaData $meta): string => $meta->title,
            $metaByService,
        )));
        $this->assertCount(9, array_unique(array_map(
            fn (PageMetaData $meta): string => (string) $meta->ogImage,
            $metaByService,
        )));

        foreach ($metaByService as $service => $meta) {
            $this->assertNotSame('', $meta->description, "{$service} needs a description.");
            $this->assertNotSame('', $meta->ogImageAlt, "{$service} needs contextual image alt text.");
            $this->assertLessThanOrEqual(60, mb_strlen($meta->metaTitle), "{$service} meta title is too long.");
            $this->assertLessThanOrEqual(160, mb_strlen($meta->description), "{$service} meta description is too long.");
            $this->assertStringNotContainsString('trascendental', mb_strtolower((string) $meta->ogImage));
            $this->assertStringNotContainsString('300 sesiones', mb_strtolower($meta->title.' '.$meta->description));
            $this->assertStringNotContainsString('sesiones exitosas', mb_strtolower($meta->title.' '.$meta->description));

            $path = parse_url((string) $meta->ogImage, PHP_URL_PATH);
            $this->assertIsString($path);
            $this->assertFileExists(public_path(ltrim($path, '/')), "{$service} OG image must exist.");
        }

        $this->assertSame(
            'Más de 200 piezas audiovisuales producidas por Lapsique',
            $metaByService['home']->title,
        );
        $this->assertStringContainsString('archivo real', $metaByService['home']->description);
        $this->assertNotSame(
            $metaByService['content_creation']->ogImage,
            $metaByService['business_reels']->ogImage,
        );
    }

    public function test_service_metadata_and_schema_are_localized_in_english(): void
    {
        app()->setLocale('en');

        $metaByService = $this->serviceMeta();
        $expectedTitles = [
            'home' => 'More than 200 audiovisual pieces produced by Lapsique',
            'content_creation' => 'Social media content creation in Riviera Maya',
            'business_reels' => 'Business reels and ads in Riviera Maya',
            'food_reels' => 'Food reels for restaurants in Riviera Maya',
            'dj_set' => 'Cinematic DJ set recording in Riviera Maya',
            'event_coverage' => 'Electronic music event coverage in Riviera Maya',
            'multi_camera' => 'Multicamera production for DJ sets, clubs, and events',
            'drone_sessions' => 'Drone filming for properties and campaigns in Riviera Maya',
            'construction_progress' => 'Construction progress with drone, photo, and video in Riviera Maya',
        ];

        foreach ($expectedTitles as $service => $title) {
            $this->assertSame($title, $metaByService[$service]->title);
            $this->assertLessThanOrEqual(60, mb_strlen($metaByService[$service]->metaTitle));
            $this->assertLessThanOrEqual(160, mb_strlen($metaByService[$service]->description));
        }

        $droneGraph = collect($metaByService['drone_sessions']->jsonLd['@graph']);
        $this->assertSame(
            'Aerial drone video and photography',
            $droneGraph->firstWhere('@type', 'Service')['serviceType'],
        );
        $this->assertSame(
            'Does the flight depend on weather?',
            $droneGraph->firstWhere('@type', 'FAQPage')['mainEntity'][0]['name'],
        );
    }

    public function test_video_object_schema_is_only_published_for_verified_multicamera_files(): void
    {
        $metaByService = $this->serviceMeta();

        foreach ($metaByService as $service => $meta) {
            $videos = collect($meta->jsonLd['@graph'] ?? [])
                ->where('@type', 'VideoObject')
                ->values();

            if ($service !== 'multi_camera') {
                $this->assertCount(0, $videos, "{$service} must not invent VideoObject metadata.");

                continue;
            }

            $this->assertCount(3, $videos);

            foreach ($videos as $video) {
                $this->assertNotEmpty($video['name']);
                $this->assertNotEmpty($video['description']);
                $this->assertNotEmpty($video['thumbnailUrl'][0] ?? null);
                $this->assertNotEmpty($video['contentUrl']);
                $this->assertNotEmpty($video['uploadDate']);
                $this->assertNotEmpty($video['duration']);

                $contentPath = parse_url($video['contentUrl'], PHP_URL_PATH);
                $thumbnailPath = parse_url($video['thumbnailUrl'][0], PHP_URL_PATH);

                $this->assertFileExists(public_path(ltrim((string) $contentPath, '/')));
                $this->assertFileExists(public_path(ltrim((string) $thumbnailPath, '/')));
            }
        }
    }

    /**
     * @return array<string, PageMetaData>
     */
    private function serviceMeta(): array
    {
        return [
            'home' => PageMeta::forBookingFunnel(null, 'https://lapsique.media/'),
            'content_creation' => PageMeta::forContentCreation(null, 'https://lapsique.media/creacion-de-contenido-riviera-maya'),
            'business_reels' => PageMeta::forBusinessReels(null, 'https://lapsique.media/reels-para-negocios'),
            'food_reels' => PageMeta::forFoodReels(null, 'https://lapsique.media/reels-de-comida'),
            'dj_set' => PageMeta::forDjSet(null, 'https://lapsique.media/dj-set'),
            'event_coverage' => PageMeta::forElectronicEventCoverage('https://lapsique.media/cobertura-eventos-electronica'),
            'multi_camera' => PageMeta::forMultiCamera('https://lapsique.media/multicamara'),
            'drone_sessions' => PageMeta::forDroneSession('https://lapsique.media/sesiones-de-dron'),
            'construction_progress' => PageMeta::forConstructionProgress('https://lapsique.media/avances-de-obra'),
        ];
    }
}
