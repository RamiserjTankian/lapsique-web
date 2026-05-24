<?php

namespace App\Console\Commands;

use App\Support\ReelLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateReelsManifestCommand extends Command
{
    protected $signature = 'reels:manifest {--ts : Also regenerate resources/js/data/lapsiqueReelLibrary.ts}';

    protected $description = 'Scan public/videos/reels and generate manifest.json (and optionally the TS catalog)';

    public function handle(): int
    {
        $reelsDir = ReelLibrary::reelsDir();

        if (! is_dir($reelsDir)) {
            $this->error("Reels directory not found: {$reelsDir}");

            return self::FAILURE;
        }

        $files = collect(File::files($reelsDir))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'mp4')
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        if ($files->isEmpty()) {
            $this->error('No .mp4 files found in reels directory.');

            return self::FAILURE;
        }

        $publicPrefix = '/'.trim((string) config('landing.reels_dir', 'videos/reels'), '/');
        $entries = [];

        foreach ($files as $index => $file) {
            $filename = $file->getFilename();

            if (ReelLibrary::isExcludedReel($filename)) {
                continue;
            }

            $entries[] = [
                'id' => sprintf('reel-%03d', count($entries) + 1),
                'title' => ReelLibrary::titleFromFilename($filename),
                'src' => $publicPrefix.'/'.$filename,
            ];
        }

        $manifest = [
            'version' => 1,
            'generated_at' => now()->toIso8601String(),
            'total_source_videos' => (int) config('landing.reels_total_source_videos', 101),
            'entries' => $entries,
        ];

        $manifestPath = ReelLibrary::manifestPath();
        File::ensureDirectoryExists(dirname($manifestPath));
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $this->info('Wrote '.count($entries).' entries to '.$manifestPath);

        if ($this->option('ts')) {
            $this->writeTypeScriptCatalog($entries, (int) $manifest['total_source_videos']);
            $this->info('Regenerated resources/js/data/lapsiqueReelLibrary.ts');
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<array{id: string, title: string, src: string}>  $entries
     */
    protected function writeTypeScriptCatalog(array $entries, int $totalSourceVideos): void
    {
        $path = resource_path('js/data/lapsiqueReelLibrary.ts');
        $encodedEntries = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $uniqueCount = count($entries);

        $content = <<<TS
export interface LapsiqueReelEntry {
    id: string;
    title: string;
    src: string;
}

export const lapsiqueReelStats = {
    totalSourceVideos: {$totalSourceVideos},
    uniqueVideos: {$uniqueCount},
    webPreviews: {$uniqueCount},
} as const;

export const lapsiqueReelLibrary: LapsiqueReelEntry[] = {$encodedEntries};

TS;

        File::put($path, $content);
    }
}
