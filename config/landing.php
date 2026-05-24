<?php

$proyectos = env('LANDING_VIDEOS_SOURCE', '/Users/redasoft/Documents/PROYECTOS');

$mediaLocalDir = env('LANDING_MEDIA_LOCAL', '/Users/redasoft/Documents/Lapsique-Media-Local');

$driveLapsiqueContenido = env(
    'LANDING_DRIVE_LAPSIQUE_CONTENIDO',
    '/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@lapsique.media/My Drive/LAPSIQUE.MEDIA/2.- CONTENIDO'
);

$driveBluepoint = env(
    'LANDING_DRIVE_BLUEPOINT',
    '/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@bluepointrs.com/My Drive'
);

$driveLapsiqueProyectos = env(
    'LANDING_DRIVE_LAPSIQUE_PROYECTOS',
    '/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@lapsique.media/My Drive/LAPSIQUE.MEDIA/2.- CONTENIDO/1.- PROYECTOS'
);

$driveNuZaoDrops = $driveLapsiqueProyectos.'/17 .- NU ZAO & SEPP/3.- DROPS';
$driveTraumerDrops = $driveLapsiqueProyectos.'/18.- TRAUMER & SHONKY/3.- DROPS';
$driveBioAftermovie = $driveLapsiqueProyectos.'/55.- BIOEVOLUTION/3.- AFTERMOVIE/BIOEVOLUTION.mp4';

return [

    /*
    |--------------------------------------------------------------------------
    | Landing videos — local PROYECTOS + Google Drive (Lapsique / Bluepoint)
    |--------------------------------------------------------------------------
    | Discover (fast, shallow): php artisan landing:videos-discover-drive
    | Import:                  php artisan landing:videos-import --force
    |
    | Tip: In Google Drive, right-click heavy folders → "Available offline"
    | before import so ffmpeg does not hang on cloud-only files.
    */

    'source_root' => $proyectos,

    'media_local_dir' => $mediaLocalDir,

    'output_dir' => 'videos/landing',

    'reels_dir' => 'videos/reels',

    'reels_manifest' => 'videos/reels/manifest.json',

    'reels_library_preview_count' => 10,

    'reels_library_preview_count_mobile' => 6,

    'reels_total_source_videos' => 82,

    'drive_scan_max_depth' => 4,

    'drive_scan_roots' => [
        'lapsique_proyectos' => $driveLapsiqueProyectos,
        'proyectos_local' => $proyectos,
    ],

    /** Shallow paths only (Bluepoint My Drive has 1400+ folders — do not scan it whole). */
    'drive_scan_extra_paths' => [
        'lapsique_bio' => $driveLapsiqueProyectos.'/55.- BIOEVOLUTION/3.- AFTERMOVIE',
    ],

    'ffmpeg' => [
        'max_height' => 720,
        'crf' => 28,
        'preset' => 'medium',
    ],

    /**
     * Each clip uses a different source file (no duplicate aftermovies on Home).
     * `file` may be relative to source_root or an absolute path (Drive).
     *
     * @var array<string, array<string, mixed>>
     */
    'clips' => [
        'hero' => [
            'id' => 'hero',
            'file' => 'VATOS LOCOS AFTERMOVIE.mp4',
            'start' => 4,
            'duration' => 6,
            'title' => 'Vatos Locos — aftermovie',
            'fit' => 'horizontal_cover',
            'crop_y' => 0.42,
        ],
        'offer' => [
            'id' => 'offer',
            'file' => '79.- PROPER COLLECTIVE/AFTERMOVIE PERGOLA.mp4',
            'start' => 2,
            'duration' => 6,
            'title' => 'Proper — Pergola',
            'fit' => 'horizontal_cover',
            'crop_y' => 0.4,
            'color' => 'lift',
        ],
        'proof' => [
            'id' => 'proof',
            'file' => '102.- REBOLLEDO AFTERMOVIE/AFTERMOVIE REBOLLEDO.mp4',
            'start' => 6,
            'duration' => 6,
            'title' => 'Rebolledo — aftermovie',
            'fit' => 'vertical_cover',
            'crop_y' => 0.45,
            'color' => 'lift',
        ],
        'pauta' => [
            'id' => 'pauta',
            'file' => '79.- PROPER COLLECTIVE/AFTERMOVIE PERGOLA.mp4',
            'start' => 0,
            'duration' => 6,
            'title' => 'Proper — Pergola',
            'fit' => 'vertical_cover',
            'crop_y' => 0.5,
        ],
        'creative_1' => [
            'id' => 'creative-1',
            'file' => $driveNuZaoDrops.'/NU ZAO DROP 1.mp4',
            'start' => 0,
            'duration' => 6,
            'title' => 'Nu Zao — drop',
            'fit' => 'vertical_cover',
            'crop_y' => 0.38,
        ],
        'creative_2' => [
            'id' => 'creative-2',
            'file' => $driveNuZaoDrops.'/NU ZAO DROP 4.mp4',
            'start' => 0,
            'duration' => 6,
            'title' => 'Nu Zao — energía',
            'fit' => 'vertical_cover',
            'crop_y' => 0.4,
        ],
        'creative_3' => [
            'id' => 'creative-3',
            'file' => $driveTraumerDrops.'/DROP 3 TRAUMER.mp4',
            'start' => 1,
            'duration' => 6,
            'title' => 'Traumer — drop',
            'fit' => 'vertical_cover',
            'crop_y' => 0.35,
        ],
        'equipment_1' => [
            'id' => 'equipment-1',
            'file' => '79.- PROPER COLLECTIVE/AFTERMOVIE SATOSHI.mp4',
            'start' => 4,
            'duration' => 6,
            'title' => 'Satoshi — aftermovie',
            'fit' => 'vertical_cover',
            'crop_y' => 0.45,
        ],
        'equipment_2' => [
            'id' => 'equipment-2',
            'file' => '102.- REBOLLEDO AFTERMOVIE/REBOLLEDO AFTERMOVIE.mp4',
            'start' => 8,
            'duration' => 6,
            'title' => 'Rebolledo — evento',
            'fit' => 'vertical_cover',
            'crop_y' => 0.4,
            'color' => 'lift',
        ],
        'package' => [
            'id' => 'package',
            'file' => '79.- PROPER COLLECTIVE/AFTERMOVIE - SATOSHI TOMIIE.mp4',
            'start' => 5,
            'duration' => 6,
            'title' => 'Satoshi Tomiie',
            'fit' => 'horizontal_cover',
            'crop_y' => 0.5,
        ],
        'gear' => [
            'id' => 'gear',
            'file' => $driveBioAftermovie,
            'start' => 4,
            'duration' => 6,
            'title' => 'Bioevolution',
            'fit' => 'horizontal_cover',
            'crop_y' => 0.45,
            'color' => 'lift',
        ],
        'float_1' => [
            'id' => 'float-1',
            'file' => $driveNuZaoDrops.'/NU ZAO DROP 2.mp4',
            'start' => 0,
            'duration' => 6,
            'title' => 'Nu Zao',
            'fit' => 'vertical_cover',
            'crop_y' => 0.5,
        ],
        'float_2' => [
            'id' => 'float-2',
            'file' => $driveTraumerDrops.'/DROP 5 TRAUMER.mp4',
            'start' => 1,
            'duration' => 6,
            'title' => 'Traumer',
            'fit' => 'vertical_cover',
            'crop_y' => 0.45,
        ],
        'showcase_1' => [
            'id' => 'showcase-1',
            'file' => 'VATOS LOCOS AFTERMOVIE.mp4',
            'start' => 0,
            'duration' => 6,
            'title' => 'Vatos Locos',
            'fit' => 'vertical_cover',
            'crop_y' => 0.4,
        ],
        'showcase_2' => [
            'id' => 'showcase-2',
            'file' => '102.- REBOLLEDO AFTERMOVIE/AFTERMOVIE REBOLLEDO.mp4',
            'start' => 0,
            'duration' => 6,
            'title' => 'Rebolledo',
            'fit' => 'vertical_cover',
            'crop_y' => 0.42,
            'color' => 'lift',
        ],
        'showcase_3' => [
            'id' => 'showcase-3',
            'file' => '77.- BIOEVOLUTION/AFTERMOVIE.mp4',
            'start' => 0,
            'duration' => 6,
            'title' => 'Bioevolution',
            'fit' => 'vertical_cover',
            'crop_y' => 0.4,
            'color' => 'lift',
        ],
        'showcase_4' => [
            'id' => 'showcase-4',
            'file' => '77.- BIOEVOLUTION/AFTERMOVIE.mp4',
            'start' => 5,
            'duration' => 6,
            'title' => 'Bioevolution',
            'fit' => 'vertical_cover',
            'crop_y' => 0.48,
            'color' => 'lift',
        ],
    ],

];
