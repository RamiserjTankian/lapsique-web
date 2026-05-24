<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class ImportLandingVideosCommand extends Command
{
    protected $signature = 'landing:videos-import {--force : Re-encode even if output exists}';

    protected $description = 'Import and optimize landing videos from PROYECTOS into public/videos/landing (requires ffmpeg)';

    public function handle(): int
    {
        if (! $this->ffmpegAvailable()) {
            $this->error('ffmpeg is not installed. Install with: brew install ffmpeg');

            return self::FAILURE;
        }

        $sourceRoot = rtrim((string) config('landing.source_root'), '/');
        $outputDir = public_path((string) config('landing.output_dir'));
        $clips = config('landing.clips', []);
        $force = (bool) $this->option('force');

        File::ensureDirectoryExists($outputDir);

        $manifest = [
            'version' => 2,
            'generated_at' => now()->toIso8601String(),
            'hero' => null,
            'offer' => null,
            'proof' => null,
            'pauta' => null,
            'creative' => [],
            'equipment' => [],
            'aftermovies' => [],
            'floats' => [],
            'package' => null,
            'gear' => null,
        ];

        $roleMap = [
            'hero' => 'hero',
            'offer' => 'offer',
            'proof' => 'proof',
            'pauta' => 'pauta',
        ];

        $encoded = 0;

        foreach ($clips as $key => $clip) {
            $id = (string) ($clip['id'] ?? $key);
            $sourcePath = $this->resolveSourcePath((string) ($clip['file'] ?? ''), $sourceRoot);

            if ($sourcePath === null) {
                $this->warn('Skipping missing source: '.($clip['file'] ?? $key));

                continue;
            }

            $outputMp4 = $outputDir.'/'.$id.'.mp4';
            $outputPoster = $outputDir.'/'.$id.'.jpg';

            if ($force || ! is_file($outputMp4)) {
                $this->info("Encoding {$id}...");
                $ok = $this->encodeVideo($sourcePath, $outputMp4, $clip);

                if (! $ok) {
                    $this->error("Failed to encode {$id}");

                    continue;
                }

                $encoded++;
            } else {
                $this->line("Skipping existing {$id}.mp4 (use --force to re-encode)");
                $encoded++;
            }

            if ($force || ! is_file($outputPoster)) {
                $this->extractPoster($outputMp4, $outputPoster);
            }

            $entry = [
                'id' => $id,
                'src' => '/'.trim((string) config('landing.output_dir'), '/').'/'.$id.'.mp4',
                'poster' => '/'.trim((string) config('landing.output_dir'), '/').'/'.$id.'.jpg',
                'title' => $clip['title'] ?? null,
            ];

            if (str_starts_with($key, 'creative_')) {
                $manifest['creative'][] = $entry;
            } elseif (str_starts_with($key, 'equipment_')) {
                $manifest['equipment'][] = $entry;
            } elseif (str_starts_with($key, 'showcase_')) {
                $manifest['aftermovies'][] = $entry;
            } elseif (str_starts_with($key, 'float_')) {
                $manifest['floats'][] = $entry;
            } elseif ($key === 'package') {
                $manifest['package'] = $entry;
            } elseif ($key === 'gear') {
                $manifest['gear'] = $entry;
            } elseif (isset($roleMap[$key])) {
                $manifest[$roleMap[$key]] = $entry;
            }
        }

        if (count($manifest['creative']) === 0) {
            foreach (['creative-1', 'creative-2', 'creative-3'] as $fallbackId) {
                $fallbackPath = $outputDir.'/'.$fallbackId.'.mp4';
                if (! is_file($fallbackPath)) {
                    continue;
                }

                $manifest['creative'][] = [
                    'id' => $fallbackId,
                    'src' => '/'.trim((string) config('landing.output_dir'), '/').'/'.$fallbackId.'.mp4',
                    'poster' => '/'.trim((string) config('landing.output_dir'), '/').'/'.$fallbackId.'.jpg',
                    'title' => null,
                ];
            }
        }

        $manifestPath = $outputDir.'/manifest.json';
        File::put(
            $manifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->info("Manifest written to {$manifestPath} ({$encoded} clip(s)).");

        if ($encoded === 0) {
            $this->error('No clips encoded. Check paths in config/landing.php or run landing:videos-discover-drive.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function resolveSourcePath(string $file, string $sourceRoot): ?string
    {
        $file = trim($file);

        if ($file === '') {
            return null;
        }

        if (str_starts_with($file, '/')) {
            return is_file($file) ? $file : null;
        }

        if (! is_dir($sourceRoot)) {
            return null;
        }

        $path = $sourceRoot.'/'.ltrim($file, '/');

        return is_file($path) ? $path : null;
    }

    protected function ffmpegAvailable(): bool
    {
        $process = new Process(['ffmpeg', '-version']);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @param  array<string, mixed>  $clip
     */
    protected function encodeVideo(string $input, string $output, array $clip): bool
    {
        $maxHeight = (int) config('landing.ffmpeg.max_height', 720);
        $crf = (string) config('landing.ffmpeg.crf', 28);
        $preset = (string) config('landing.ffmpeg.preset', 'medium');
        $start = max(0, (float) ($clip['start'] ?? 0));
        $duration = isset($clip['duration']) ? (float) $clip['duration'] : null;

        $command = ['ffmpeg', '-y'];

        if ($start > 0) {
            $command[] = '-ss';
            $command[] = (string) $start;
        }

        $vf = $this->buildVideoFilter($clip, $maxHeight);

        $command = array_merge($command, [
            '-i', $input,
            '-an',
            '-vf', $vf,
            '-c:v', 'libx264',
            '-preset', $preset,
            '-crf', $crf,
            '-movflags', '+faststart',
            '-pix_fmt', 'yuv420p',
        ]);

        if ($duration !== null && $duration > 0) {
            $command[] = '-t';
            $command[] = (string) $duration;
        }

        $command[] = $output;

        $process = new Process($command);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->line($process->getErrorOutput());

            return false;
        }

        return is_file($output);
    }

    /**
     * @param  array<string, mixed>  $clip
     */
    protected function buildVideoFilter(array $clip, int $maxHeight): string
    {
        $filters = [];

        if (($clip['color'] ?? null) === 'lift') {
            $filters[] = 'eq=saturation=1.32:contrast=1.06:brightness=0.04';
        }

        $cropY = max(0.0, min(1.0, (float) ($clip['crop_y'] ?? 0.5)));
        $fit = (string) ($clip['fit'] ?? 'horizontal_cover');

        if ($fit === 'vertical_cover') {
            $h = $maxHeight;
            $w = (int) round($h * 9 / 16);
            $filters[] = "scale={$w}:{$h}:force_original_aspect_ratio=increase";
            $filters[] = "crop={$w}:{$h}:(iw-{$w})/2:(ih-{$h})*{$cropY}";
        } else {
            $w = (int) round($maxHeight * 16 / 9);
            $filters[] = "scale={$w}:{$maxHeight}:force_original_aspect_ratio=increase";
            $filters[] = "crop={$w}:{$maxHeight}:(iw-{$w})/2:(ih-{$maxHeight})*{$cropY}";
        }

        return implode(',', $filters);
    }

    protected function extractPoster(string $videoPath, string $posterPath): void
    {
        $process = new Process([
            'ffmpeg', '-y',
            '-i', $videoPath,
            '-vframes', '1',
            '-q:v', '2',
            $posterPath,
        ]);
        $process->setTimeout(120);
        $process->run();
    }
}
