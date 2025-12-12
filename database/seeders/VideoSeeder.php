<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VideoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channelId = config('lapsique.youtube_channel_id');

        if (! $channelId) {
            $this->command?->warn('LAPSIQUE_YOUTUBE_CHANNEL_ID no está configurado, se omite VideoSeeder.');

            return;
        }

        $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}";
        $response = Http::get($feedUrl);

        if (! $response->ok()) {
            $this->command?->error("No se pudo descargar el feed de YouTube ({$response->status()}).");

            return;
        }

        $xml = @simplexml_load_string($response->body());

        if (! $xml) {
            $this->command?->error('No se pudo parsear el feed XML de YouTube.');

            return;
        }

        $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
        $xml->registerXPathNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');

        $entries = $xml->xpath('//atom:entry') ?: [];

        foreach ($entries as $entry) {
            $videoId = (string) ($entry->children('yt', true)->videoId ?? '');
            $title = (string) ($entry->children('atom', true)->title ?? '');
            $link = (string) ($entry->children('atom', true)->link?->attributes()?->href ?? '');
            $published = Carbon::parse((string) ($entry->children('atom', true)->published ?? now()));
            $description = (string) ($entry->children('media', true)->group?->children('media', true)->description ?? '');
            $thumbnail = (string) ($entry->children('media', true)->group?->children('media', true)->thumbnail?->attributes()?->url ?? '');
            $defaultLocation = config('lapsique.default_video_location');
            $defaultMaps = config('lapsique.default_maps_url');

            if (! $videoId || ! $title) {
                continue;
            }

            $baseSlug = Str::slug($title) ?: $videoId;
            $slug = $baseSlug;
            $suffix = 1;

            while (
                Video::where('slug', $slug)
                    ->where('youtube_id', '!=', $videoId)
                    ->exists()
            ) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            Video::updateOrCreate(
                ['youtube_id' => $videoId],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'youtube_url' => $link ?: "https://www.youtube.com/watch?v={$videoId}",
                    'thumbnail_url' => $thumbnail,
                    'location' => $defaultLocation,
                    'maps_url' => $defaultMaps,
                    'description' => $description,
                    'published_at' => $published,
                    'is_featured' => false,
                    'priority' => 0,
                ]
            );
        }

        $this->command?->info('Videos importados desde el feed de YouTube.');
    }
}
