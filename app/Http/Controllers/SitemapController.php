<?php

namespace App\Http\Controllers;

use App\Models\Dj;
use App\Models\Event;
use App\Models\Video;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = config('trascendental.enabled_as_primary')
            ? $this->trascendentalUrls()
            : $this->lapsiqueUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$this->xml((string) $url['loc'])."</loc>\n";
            if ($url['lastmod']) {
                $xml .= '    <lastmod>'.$this->xml((string) $url['lastmod'])."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>'.PHP_EOL;

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /** @return Collection<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}> */
    private function lapsiqueUrls(): Collection
    {
        $static = collect([
            $this->entry(route('home'), now(), 'weekly', '1.0'),
            $this->entry(route('portfolio.index'), now(), 'weekly', '0.8'),
            $this->entry(route('videos.index'), now(), 'weekly', '0.8'),
            $this->entry(route('djs.index'), now(), 'weekly', '0.8'),
            $this->entry(route('events.index'), now(), 'weekly', '0.8'),
            $this->entry(route('food-reels.show'), now(), 'monthly', '0.9'),
            $this->entry(route('djset.show'), now(), 'monthly', '0.9'),
            $this->entry(route('drone-sessions.show'), now(), 'monthly', '0.9'),
            $this->entry(route('construction-progress.show'), now(), 'monthly', '0.9'),
        ]);

        $djs = Dj::query()
            ->where('trascendental_roster', false)
            ->get(['slug', 'updated_at'])
            ->map(fn (Dj $dj) => $this->entry(route('djs.show', $dj), $dj->updated_at, 'monthly', '0.7'));

        $events = Event::query()
            ->where('trascendental_visible', false)
            ->get(['slug', 'updated_at'])
            ->map(fn (Event $event) => $this->entry(route('events.show', $event), $event->updated_at, 'monthly', '0.7'));

        $videos = Video::query()
            ->whereHas('djs', fn ($query) => $query->where('trascendental_roster', false))
            ->get(['slug', 'updated_at'])
            ->map(fn (Video $video) => $this->entry(route('videos.show', $video), $video->updated_at, 'monthly', '0.7'));

        return $static->concat($djs)->concat($events)->concat($videos)->values();
    }

    /** @return Collection<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}> */
    private function trascendentalUrls(): Collection
    {
        return collect([
            $this->entry(route('home'), now(), 'weekly', '1.0'),
            $this->entry(route('trascendental.services'), now(), 'monthly', '0.8'),
            $this->entry(route('trascendental.cases'), now(), 'weekly', '0.8'),
            $this->entry(route('trascendental.events'), now(), 'weekly', '0.8'),
            $this->entry(route('trascendental.tours'), now(), 'weekly', '0.8'),
            $this->entry(route('trascendental.about'), now(), 'monthly', '0.6'),
            $this->entry(route('trascendental.contact'), now(), 'monthly', '0.6'),
        ]);
    }

    /** @return array{loc: string, lastmod: ?string, changefreq: string, priority: string} */
    private function entry(string $loc, mixed $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod?->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
