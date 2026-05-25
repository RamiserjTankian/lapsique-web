<?php

namespace Database\Seeders;

use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PortfolioAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $manifestPath = database_path('data/portfolio_assets.json');

        if (! file_exists($manifestPath)) {
            $this->command?->warn('No existe database/data/portfolio_assets.json; se omite PortfolioAssetsSeeder.');

            return;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        if (! is_array($manifest)) {
            $this->command?->error('No se pudo leer database/data/portfolio_assets.json.');

            return;
        }

        $created = 0;
        $updated = 0;

        foreach (Arr::get($manifest, 'photos', []) as $index => $entry) {
            [$wasCreated] = $this->upsertPortfolioItem($entry, $index, 'photo');
            $wasCreated ? $created++ : $updated++;
        }

        $photoCount = count(Arr::get($manifest, 'photos', []));

        foreach (Arr::get($manifest, 'videos', []) as $index => $entry) {
            [$wasCreated] = $this->upsertPortfolioItem($entry, $photoCount + $index, 'video');
            $wasCreated ? $created++ : $updated++;
        }

        $this->command?->info("PortfolioAssetsSeeder: {$created} creados, {$updated} actualizados.");
    }

    /**
     * @return array{bool, PortfolioItem}
     */
    private function upsertPortfolioItem(array $entry, int $index, string $type): array
    {
        $assetPath = (string) ($entry['src'] ?? '');
        $title = trim((string) ($entry['title'] ?? 'Portafolio'));
        $basename = pathinfo($assetPath, PATHINFO_FILENAME) ?: (string) ($index + 1);
        $slug = Str::slug("portfolio-{$type}-{$basename}");
        $tags = collect($entry['tags'] ?? [])
            ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
            ->map(fn ($tag) => Str::lower(trim($tag)))
            ->unique()
            ->values()
            ->all();

        $item = PortfolioItem::withTrashed()->firstOrNew(['slug' => $slug]);
        $wasCreated = ! $item->exists;

        if ($item->trashed()) {
            $item->restore();
        }

        $item->fill([
            'title' => $title !== '' ? $title : 'Portafolio',
            'type' => $type === 'video' ? ($this->videoType($entry) ?? 'video') : 'photo',
            'orientation' => $entry['orientation'] ?? 'horizontal',
            'source' => 'public',
            'asset_path' => $assetPath,
            'poster_path' => $entry['poster'] ?? null,
            'youtube_url' => null,
            'youtube_id' => null,
            'caption' => $type === 'photo'
                ? "Fotografía editada de {$title}."
                : ($entry['caption'] ?? null),
            'tags' => $tags,
            'is_featured' => $index === 0,
            'is_active' => true,
            'priority' => $index + 1,
        ]);

        $item->save();

        return [$wasCreated, $item];
    }

    private function videoType(array $entry): ?string
    {
        $tags = collect($entry['tags'] ?? [])->map(fn ($tag) => Str::lower((string) $tag));

        if ($tags->contains('aftermovie')) {
            return 'aftermovie';
        }

        if ($tags->contains('reel')) {
            return 'reel';
        }

        return null;
    }
}
