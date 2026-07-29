<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

final class ServicePortfolioCatalog
{
    public const SERVICE_KEYS = [
        'content_creation',
        'business_reels',
        'food_reels',
        'dj_set',
        'event_coverage',
        'multi_camera',
        'drone_sessions',
        'construction_progress',
    ];

    private const SERVICE_META = [
        'content_creation' => [
            'label' => ['es' => 'Contenido para redes', 'en' => 'Social content'],
            'href' => '/creacion-de-contenido-riviera-maya',
        ],
        'business_reels' => [
            'label' => ['es' => 'Reels para negocios', 'en' => 'Business reels'],
            'href' => '/reels-para-negocios',
        ],
        'food_reels' => [
            'label' => ['es' => 'Reels para restaurantes', 'en' => 'Restaurant reels'],
            'href' => '/reels-de-comida',
        ],
        'dj_set' => [
            'label' => ['es' => 'Grabar un DJ set', 'en' => 'Record a DJ set'],
            'href' => '/dj-set',
        ],
        'event_coverage' => [
            'label' => ['es' => 'Cobertura de eventos electrónicos', 'en' => 'Electronic event coverage'],
            'href' => '/cobertura-eventos-electronica',
        ],
        'multi_camera' => [
            'label' => ['es' => 'Producción multicámara', 'en' => 'Multi-camera production'],
            'href' => '/multicamara',
        ],
        'drone_sessions' => [
            'label' => ['es' => 'Vuelos con dron', 'en' => 'Drone flights'],
            'href' => '/sesiones-de-dron',
        ],
        'construction_progress' => [
            'label' => ['es' => 'Avances de obra', 'en' => 'Construction progress'],
            'href' => '/avances-de-obra',
        ],
    ];

    private static ?array $catalog = null;

    /**
     * @return array{
     *     serviceKey: string,
     *     hero: array<string, mixed>,
     *     projects: array<int, array{key: string, label: string, media: array<int, array<string, mixed>>}>,
     *     stats: array{mediaCount: int, projectCount: int, imageCount: int, videoCount: int}
     * }
     */
    public static function forService(string $serviceKey): array
    {
        if (! in_array($serviceKey, self::SERVICE_KEYS, true)) {
            throw new InvalidArgumentException("Unknown service portfolio key [{$serviceKey}].");
        }

        $catalog = self::catalog();
        $configuration = data_get($catalog, "services.{$serviceKey}");

        if (! is_array($configuration)) {
            throw new RuntimeException("Missing service portfolio configuration [{$serviceKey}].");
        }

        $mediaById = self::resolvedMediaForService($catalog, $serviceKey);
        $seenSources = [];
        $projects = collect(Arr::get($configuration, 'projects', []))
            ->map(function (array $project) use ($catalog, $mediaById, &$seenSources): ?array {
                $media = collect(Arr::get($project, 'media', []))
                    ->map(fn (string $id): ?array => $mediaById->get($id))
                    ->filter()
                    ->reject(function (array $item) use (&$seenSources): bool {
                        if (isset($seenSources[$item['src']])) {
                            return true;
                        }

                        $seenSources[$item['src']] = true;

                        return false;
                    })
                    ->sortBy('priority')
                    ->values()
                    ->all();

                if ($media === []) {
                    return null;
                }

                return [
                    'key' => (string) Arr::get($project, 'key'),
                    'label' => self::localizedString($catalog, Arr::get($project, 'label')),
                    'media' => $media,
                ];
            })
            ->filter()
            ->values();

        $flatMedia = $projects->flatMap(fn (array $project): array => $project['media'])->values();
        $heroId = (string) Arr::get($configuration, 'hero');
        $hero = $mediaById->get($heroId) ?? $flatMedia->first();

        if (! is_array($hero)) {
            throw new RuntimeException("Service portfolio [{$serviceKey}] has no readable contextual media.");
        }

        return [
            'serviceKey' => $serviceKey,
            'hero' => $hero,
            'projects' => $projects->all(),
            'stats' => [
                'mediaCount' => $flatMedia->count(),
                'projectCount' => $projects->count(),
                'imageCount' => $flatMedia->where('kind', 'image')->count(),
                'videoCount' => $flatMedia->where('kind', 'video')->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function overview(): array
    {
        $catalog = self::catalog();
        $bundles = collect(self::SERVICE_KEYS)
            ->mapWithKeys(fn (string $serviceKey): array => [$serviceKey => self::forService($serviceKey)]);
        $allMedia = $bundles
            ->flatMap(fn (array $bundle): array => collect($bundle['projects'])->flatMap(
                fn (array $project): array => $project['media'],
            )->all())
            ->unique('src')
            ->values();

        $mediaById = collect(Arr::get($catalog, 'media', []))
            ->mapWithKeys(function (array $entry) use ($catalog): array {
                $service = collect(Arr::get($entry, 'services', []))
                    ->first(fn (mixed $key): bool => is_string($key) && in_array($key, self::SERVICE_KEYS, true));

                if (! is_string($service)) {
                    return [];
                }

                $resolved = self::resolveMediaEntry($catalog, $entry, $service);

                return $resolved === null ? [] : [$entry['id'] => $resolved];
            });
        $featuredMedia = collect(Arr::get($catalog, 'overview_featured_media', []))
            ->map(fn (string $id): ?array => $mediaById->get($id))
            ->filter()
            ->unique('src')
            ->values();

        $projects = $featuredMedia
            ->groupBy('projectKey')
            ->map(fn (Collection $media, string $key): array => [
                'key' => $key,
                'label' => (string) $media->first()['projectLabel'],
                'media' => $media->values()->all(),
            ])
            ->values()
            ->all();

        $featuredSources = $featuredMedia->pluck('src')->flip();
        $previewSources = [];
        $servicePreviews = $bundles
            ->map(function (array $bundle, string $serviceKey) use ($featuredSources, &$previewSources): array {
                $media = collect($bundle['projects'])
                    ->flatMap(fn (array $project): array => $project['media'])
                    ->first(function (array $candidate) use ($featuredSources, &$previewSources): bool {
                        if ($featuredSources->has($candidate['src']) || isset($previewSources[$candidate['src']])) {
                            return false;
                        }

                        $previewSources[$candidate['src']] = true;

                        return true;
                    });

                if (! is_array($media)) {
                    throw new RuntimeException("Service portfolio preview [{$serviceKey}] has no unique media.");
                }

                return [
                    'serviceKey' => $serviceKey,
                    'label' => self::SERVICE_META[$serviceKey]['label'],
                    'href' => self::SERVICE_META[$serviceKey]['href'],
                    'media' => $media,
                    'stats' => $bundle['stats'],
                ];
            })
            ->values()
            ->all();
        $reservedSources = $featuredSources
            ->merge(collect($previewSources));
        $heroCandidates = $allMedia
            ->reject(fn (array $candidate): bool => $reservedSources->has($candidate['src']))
            ->values();
        $heroImages = $heroCandidates
            ->where('kind', 'image')
            ->unique('projectKey')
            ->take(3)
            ->values();
        $heroMedia = $heroImages;

        if ($heroMedia->count() < 3) {
            $heroMedia = $heroMedia
                ->concat(
                    $heroCandidates
                        ->where('kind', 'image')
                        ->reject(fn (array $candidate): bool => $heroMedia->contains('src', $candidate['src'])),
                )
                ->concat(
                    $heroCandidates
                        ->where('kind', 'video')
                        ->reject(fn (array $candidate): bool => $heroMedia->contains('src', $candidate['src'])),
                )
                ->unique('src')
                ->take(3)
                ->values();
        }

        return [
            'site' => 'lapsique',
            'archiveMediaCount' => (int) Arr::get($catalog, 'archive_media_count', 0),
            'claim' => [
                'es' => 'Más de 200 piezas audiovisuales producidas por Lapsique.',
                'en' => 'More than 200 audiovisual pieces produced by Lapsique.',
            ],
            'copy' => [
                'es' => 'Un archivo real de fotografía y video para restaurantes, marcas, artistas, eventos, propiedades y desarrollos, creado desde Riviera Maya y Mérida para vender, documentar y permanecer.',
                'en' => 'A real photography and video archive for restaurants, brands, artists, events, properties, and developments, created from Riviera Maya and Mérida to sell, document, and endure.',
            ],
            'totalCuratedMedia' => $allMedia->count(),
            'projects' => $projects,
            'heroMedia' => $heroMedia->all(),
            'featuredMedia' => $featuredMedia->all(),
            'servicePreviews' => $servicePreviews,
        ];
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    private static function resolvedMediaForService(array $catalog, string $serviceKey): Collection
    {
        return collect(Arr::get($catalog, 'media', []))
            ->filter(fn (array $entry): bool => in_array($serviceKey, Arr::get($entry, 'services', []), true))
            ->mapWithKeys(function (array $entry) use ($catalog, $serviceKey): array {
                $resolved = self::resolveMediaEntry($catalog, $entry, $serviceKey);

                return $resolved === null ? [] : [(string) $entry['id'] => $resolved];
            });
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function resolveMediaEntry(array $catalog, array $entry, string $serviceKey): ?array
    {
        $src = (string) Arr::get($entry, 'src');
        $poster = filled(Arr::get($entry, 'poster')) ? (string) Arr::get($entry, 'poster') : null;

        if (
            Arr::get($entry, 'site') !== 'lapsique'
            || ! in_array($serviceKey, Arr::get($entry, 'services', []), true)
            || self::containsTrascendentalPath($src)
            || ($poster !== null && self::containsTrascendentalPath($poster))
            || ! self::isReadablePublicAsset($src)
        ) {
            return null;
        }

        if ($poster !== null && ! self::isReadablePublicAsset($poster)) {
            $poster = null;
        }

        $kind = Arr::get($entry, 'kind');
        $orientation = Arr::get($entry, 'orientation');

        if (! in_array($kind, ['image', 'video'], true) || ! in_array($orientation, ['horizontal', 'vertical'], true)) {
            return null;
        }

        return array_filter([
            'id' => (string) Arr::get($entry, 'id'),
            'projectKey' => (string) Arr::get($entry, 'projectKey'),
            'projectLabel' => self::localizedString($catalog, Arr::get($entry, 'projectLabel')),
            'sessionLabel' => self::localizedString($catalog, Arr::get($entry, 'sessionLabel')),
            'kind' => $kind,
            'src' => $src,
            'poster' => $poster,
            'orientation' => $orientation,
            'duration' => is_numeric(Arr::get($entry, 'duration')) ? (int) Arr::get($entry, 'duration') : null,
            'hasAudio' => is_bool(Arr::get($entry, 'hasAudio')) ? Arr::get($entry, 'hasAudio') : null,
            'location' => Arr::get($entry, 'location'),
            'alt' => self::localizedString($catalog, Arr::get($entry, 'alt')),
            'priority' => (int) Arr::get($entry, 'priority', 999),
            'site' => 'lapsique',
            'services' => array_values(array_intersect(
                self::SERVICE_KEYS,
                array_filter(Arr::get($entry, 'services', []), 'is_string'),
            )),
        ], static fn (mixed $value): bool => $value !== null);
    }

    private static function isReadablePublicAsset(string $path): bool
    {
        if (
            $path === ''
            || ! str_starts_with($path, '/')
            || (! str_starts_with($path, '/images/') && ! str_starts_with($path, '/videos/'))
        ) {
            return false;
        }

        return is_readable(public_path(ltrim($path, '/')));
    }

    private static function containsTrascendentalPath(string $path): bool
    {
        return str_contains(strtolower($path), 'trascendental');
    }

    private static function localizedString(array $catalog, mixed $value): string
    {
        $source = is_string($value) ? trim($value) : '';
        $locale = strtolower(substr((string) app()->getLocale(), 0, 2));
        $defaultLocale = (string) Arr::get($catalog, 'localizations.default_locale', 'es');

        if ($source === '' || $locale === $defaultLocale) {
            return $source;
        }

        $strings = Arr::get($catalog, "localizations.strings.{$locale}", []);
        $translation = is_array($strings) ? ($strings[$source] ?? null) : null;

        return is_string($translation) && trim($translation) !== ''
            ? trim($translation)
            : $source;
    }

    private static function catalog(): array
    {
        if (self::$catalog !== null) {
            return self::$catalog;
        }

        $path = database_path('data/service_portfolio.json');
        $contents = is_readable($path) ? file_get_contents($path) : false;
        $catalog = is_string($contents) ? json_decode($contents, true) : null;

        if (
            ! is_array($catalog)
            || Arr::get($catalog, 'site') !== 'lapsique'
            || (int) Arr::get($catalog, 'version') < 2
            || (int) Arr::get($catalog, 'localizations.schema_version') !== 1
        ) {
            throw new RuntimeException('The Lapsique service portfolio catalog is missing or invalid.');
        }

        return self::$catalog = $catalog;
    }
}
