<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DiscoverLandingDriveVideosCommand extends Command
{
    protected $signature = 'landing:videos-discover-drive {--json : Output JSON only}';

    protected $description = 'Scan configured Drive/PROYECTOS paths for aftermovie videos (fast, no full-disk find)';

    public function handle(): int
    {
        $found = [];

        foreach (config('landing.drive_scan_roots', []) as $label => $root) {
            $found = array_merge($found, $this->collectFromRoot((string) $label, (string) $root));
        }

        foreach (config('landing.drive_scan_extra_paths', []) as $label => $root) {
            $found = array_merge($found, $this->collectFromRoot((string) $label, (string) $root));
        }

        usort($found, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        $catalogPath = storage_path('app/landing-drive-catalog.json');
        File::put($catalogPath, json_encode([
            'generated_at' => now()->toIso8601String(),
            'count' => count($found),
            'files' => $found,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        if ($this->option('json')) {
            $this->line(File::get($catalogPath));

            return self::SUCCESS;
        }

        $this->info('Found '.count($found).' aftermovie candidate(s).');
        $this->line("Catalog: {$catalogPath}");
        $this->newLine();

        foreach ($found as $row) {
            $this->line(sprintf(
                '[%s] %s MB — %s',
                $row['account'],
                $row['size_mb'],
                $row['path']
            ));
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array{account: string, path: string, name: string, size_mb: float}>
     */
    protected function collectFromRoot(string $label, string $root): array
    {
        $root = rtrim($root, '/');

        if (! is_dir($root)) {
            $this->warn("Missing [{$label}]: {$root}");

            return [];
        }

        $this->line("Scanning {$label}…");

        $rows = [];

        foreach ($this->discoverInTree($root) as $path) {
            $rows[] = [
                'account' => $label,
                'path' => $path,
                'name' => basename($path),
                'size_mb' => round(filesize($path) / 1024 / 1024, 1),
            ];
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    protected function discoverInTree(string $root): array
    {
        $matches = [];

        if ($this->looksLikeAftermovie($root)) {
            foreach (glob($root.'/*.{mp4,MP4,mov,MOV,m4v}', GLOB_BRACE) ?: [] as $path) {
                if (is_file($path)) {
                    $matches[] = $path;
                }
            }
        }

        $projectDirs = @scandir($root) ?: [];

        foreach ($projectDirs as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $projectPath = $root.'/'.$entry;

            if (! is_dir($projectPath)) {
                if ($this->isVideoFile($projectPath) && $this->looksLikeAftermovie($projectPath)) {
                    $matches[] = $projectPath;
                }

                continue;
            }

            $subDirs = @scandir($projectPath) ?: [];

            foreach ($subDirs as $sub) {
                if ($sub === '.' || $sub === '..') {
                    continue;
                }

                $subPath = $projectPath.'/'.$sub;

                if (! is_dir($subPath)) {
                    continue;
                }

                if (! $this->folderIsAftermovieExport($sub)) {
                    continue;
                }

                foreach (glob($subPath.'/*.{mp4,MP4,mov,MOV,m4v}', GLOB_BRACE) ?: [] as $path) {
                    if (is_file($path)) {
                        $matches[] = $path;
                    }
                }
            }

            foreach (glob($projectPath.'/*.{mp4,MP4,mov,MOV}', GLOB_BRACE) ?: [] as $path) {
                if (is_file($path) && $this->looksLikeAftermovie($path)) {
                    $matches[] = $path;
                }
            }
        }

        return array_values(array_unique($matches));
    }

    protected function isVideoFile(string $path): bool
    {
        return (bool) preg_match('/\.(mp4|mov|m4v)$/i', $path);
    }

    protected function folderIsAftermovieExport(string $dirName): bool
    {
        $lower = trim(strtolower($dirName));

        if (str_contains($lower, 'clips para aftermovie') || str_contains($lower, 'clips for aftermovie')) {
            return false;
        }

        return (bool) preg_match('/^\d+\.\-\s*aftermovie\b/', $lower);
    }

    protected function looksLikeAftermovie(string $path): bool
    {
        if ($this->isVideoFile($path)) {
            $name = strtolower(basename($path));

            return str_contains($name, 'aftermovie') || str_contains($name, 'after movie');
        }

        return $this->folderIsAftermovieExport(basename($path));
    }
}
