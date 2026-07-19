<?php

namespace App\Http\Controllers;

use App\Models\Dj;
use App\Models\Event;
use App\Models\PortfolioItem;
use App\Models\Video;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
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
        $djsQuery = Dj::query()->where('trascendental_roster', false);
        $eventsQuery = Event::query()->where('trascendental_visible', false);
        $videosQuery = $this->lapsiqueVideosQuery();
        $portfolioQuery = PortfolioItem::query()->where('is_active', true);

        $static = collect([
            $this->entry(route('home'), $this->lastModified('resources/js/pages/Home.tsx', [
                (clone $djsQuery)->max('updated_at'),
                (clone $eventsQuery)->max('updated_at'),
                (clone $videosQuery)->max('videos.updated_at'),
                (clone $portfolioQuery)->max('updated_at'),
            ]), 'weekly', '1.0'),
            $this->entry(route('portfolio.index'), $this->lastModified('resources/js/pages/Portfolio/Index.tsx', [
                (clone $portfolioQuery)->max('updated_at'),
            ]), 'weekly', '0.8'),
            $this->entry(route('videos.index'), $this->lastModified('resources/js/pages/Videos/Index.tsx', [
                (clone $videosQuery)->max('videos.updated_at'),
            ]), 'weekly', '0.8'),
            $this->entry(route('djs.index'), $this->lastModified('resources/js/pages/Djs/Index.tsx', [
                (clone $djsQuery)->max('updated_at'),
            ]), 'weekly', '0.8'),
            $this->entry(route('events.index'), $this->lastModified('resources/js/pages/Events/Index.tsx', [
                (clone $eventsQuery)->max('updated_at'),
            ]), 'weekly', '0.8'),
            $this->entry(route('food-reels.show'), $this->lastModified('resources/js/pages/FoodReels/Show.tsx'), 'monthly', '0.9'),
            $this->entry(route('content-creation.show'), $this->lastModified('resources/js/pages/ContentCreation/Show.tsx'), 'monthly', '0.9'),
            $this->entry(route('djset.show'), $this->lastModified('resources/js/pages/DjSet/Show.tsx'), 'monthly', '0.9'),
            $this->entry(route('drone-sessions.show'), $this->lastModified('resources/js/pages/DroneSessions/Show.tsx'), 'monthly', '0.9'),
            $this->entry(route('construction-progress.show'), $this->lastModified('resources/js/pages/ConstructionProgress/Show.tsx'), 'monthly', '0.9'),
        ]);

        $djs = (clone $djsQuery)
            ->get(['slug', 'updated_at'])
            ->map(fn (Dj $dj) => $this->entry(route('djs.show', $dj), $dj->updated_at, 'monthly', '0.7'));

        $events = (clone $eventsQuery)
            ->get(['slug', 'updated_at'])
            ->map(fn (Event $event) => $this->entry(route('events.show', $event), $event->updated_at, 'monthly', '0.7'));

        $videos = (clone $videosQuery)
            ->get(['slug', 'updated_at'])
            ->map(fn (Video $video) => $this->entry(route('videos.show', $video), $video->updated_at, 'monthly', '0.7'));

        return $static->concat($djs)->concat($events)->concat($videos)->values();
    }

    /** @return Collection<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}> */
    private function trascendentalUrls(): Collection
    {
        return collect([
            $this->entry(route('home'), $this->lastModified('resources/js/pages/Trascendental/Home.tsx'), 'weekly', '1.0'),
            $this->entry(route('trascendental.services'), $this->lastModified('resources/js/pages/Trascendental/Services.tsx'), 'monthly', '0.8'),
            $this->entry(route('trascendental.cases'), $this->lastModified('resources/js/pages/Trascendental/Cases.tsx'), 'weekly', '0.8'),
            $this->entry(route('trascendental.events'), $this->lastModified('resources/js/pages/Trascendental/Events.tsx'), 'weekly', '0.8'),
            $this->entry(route('trascendental.tours'), $this->lastModified('resources/js/pages/Trascendental/Tours.tsx'), 'weekly', '0.8'),
            $this->entry(route('trascendental.about'), $this->lastModified('resources/js/pages/Trascendental/About.tsx'), 'monthly', '0.6'),
            $this->entry(route('trascendental.contact'), $this->lastModified('resources/js/pages/Trascendental/Contact.tsx'), 'monthly', '0.6'),
        ]);
    }

    private function lapsiqueVideosQuery(): Builder
    {
        return Video::query()
            ->whereHas('djs', fn (Builder $query) => $query->where('trascendental_roster', false))
            ->whereDoesntHave('djs', fn (Builder $query) => $query->where('trascendental_roster', true));
    }

    /** @param array<int, DateTimeInterface|string|null> $contentDates */
    private function lastModified(string $sourcePath, array $contentDates = []): ?CarbonImmutable
    {
        $timestamps = collect($contentDates)
            ->filter()
            ->map(function (DateTimeInterface|string $date): int {
                return $date instanceof DateTimeInterface
                    ? $date->getTimestamp()
                    : CarbonImmutable::parse($date)->getTimestamp();
            });

        $sourceModifiedAt = @filemtime(base_path($sourcePath));
        if ($sourceModifiedAt !== false) {
            $timestamps->push($sourceModifiedAt);
        }

        $latest = $timestamps->max();

        return $latest ? CarbonImmutable::createFromTimestampUTC((int) $latest) : null;
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
