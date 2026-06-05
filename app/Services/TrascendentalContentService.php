<?php

namespace App\Services;

use App\Http\Resources\TrascendentalCaseResource;
use App\Models\Dj;
use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrascendentalContentService
{
    public function caseStudies(?int $limit = null): array
    {
        $query = Event::query()
            ->where('is_case_study', true)
            ->with('media')
            ->orderBy('case_sort')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return TrascendentalCaseResource::collection($query->get())->resolve();
    }

    public function tours(): array
    {
        return Dj::query()
            ->where('trascendental_roster', true)
            ->with('media')
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->map(fn (Dj $artist): array => [
                'artist' => $artist->name,
                'status' => $artist->booking_status ?: 'OPEN DATES',
                'nationality' => $artist->nationality ?: 'Mexican',
                'label' => $artist->record_label ?: '',
                'instagram' => $artist->instagram_handle ? '@' . ltrim($artist->instagram_handle, '@') : '',
                'instagram_url' => $artist->instagram_url ?: $this->instagramUrl($artist->instagram_handle),
                'soundcloud_url' => $artist->soundcloud_url ?: '#',
                'bio' => $artist->bio ?: '',
                'image' => $this->modelImage($artist, 'profile') ?: asset('images/og-default.jpg'),
            ])
            ->values()
            ->all();
    }

    public function producedEvents(): array
    {
        return $this->publicEvents(['produced'])
            ->map(fn (Event $event): array => $this->producedEventPayload($event))
            ->values()
            ->all();
    }

    public function splitUpcomingEvents(): array
    {
        $today = Carbon::today(config('app.timezone'));
        $upcoming = [];
        $past = [];

        $this->publicEvents(['produced', 'announcement', 'roster_appearance'])
            ->each(function (Event $event) use (&$upcoming, &$past, $today): void {
                $payload = $this->upcomingEventPayload($event);

                if ($this->eventHasPassed($event, $today)) {
                    if ($event->trascendental_kind === 'roster_appearance') {
                        $past[] = $payload;
                    }

                    return;
                }

                $upcoming[] = $payload;
            });

        return [
            'upcoming' => $upcoming,
            'past' => array_reverse($past),
        ];
    }

    private function publicEvents(array $kinds): Collection
    {
        return Event::query()
            ->where('trascendental_visible', true)
            ->whereIn('trascendental_kind', $kinds)
            ->with(['djs', 'media'])
            ->orderBy('priority')
            ->orderBy('starts_at')
            ->orderBy('title')
            ->get();
    }

    private function producedEventPayload(Event $event): array
    {
        return [
            'title' => $event->title,
            'date' => $this->formattedDate($event),
            'venue' => $event->venue ?: '',
            'city' => $event->city ?: '',
            'lineup' => $event->lineup_text ?: $this->lineupFromDjs($event),
            'summary' => $event->description ?: $event->case_summary ?: '',
            'image' => $this->eventImage($event) ?: asset('images/og-default.jpg'),
            'source_url' => $event->source_url,
        ];
    }

    private function upcomingEventPayload(Event $event): array
    {
        return [
            'category' => match ($event->trascendental_kind) {
                'produced' => 'produced',
                'announcement' => 'announce',
                default => 'roster',
            },
            'title' => $event->title,
            'date' => $this->formattedDate($event),
            'city' => $event->city ?: '',
            'venue' => $event->venue ?: '',
            'lineup' => $event->lineup_text ?: $this->lineupFromDjs($event),
            'image' => $this->eventImage($event),
            'details_url' => $event->details_url ?: $event->source_url,
            'tickets_url' => $event->ticket_url,
        ];
    }

    private function eventHasPassed(Event $event, Carbon $today): bool
    {
        if (! $event->starts_at) {
            return false;
        }

        return $event->starts_at->copy()->timezone(config('app.timezone'))->endOfDay()->lt($today);
    }

    private function formattedDate(Event $event): string
    {
        if (! $event->starts_at) {
            return 'TBA';
        }

        return $event->starts_at->copy()->timezone(config('app.timezone'))->format('M j, Y');
    }

    private function lineupFromDjs(Event $event): string
    {
        if (! $event->relationLoaded('djs') || $event->djs->isEmpty()) {
            return '';
        }

        return $event->djs->pluck('name')->implode(', ');
    }

    private function eventImage(Event $event): ?string
    {
        return $this->publicAsset($event->public_image_path)
            ?? $this->modelImage($event, 'cover_vertical', 'poster_vertical')
            ?? $this->modelImage($event, 'cover', 'cover_large')
            ?? $this->modelImage($event, 'cover_horizontal', 'poster_horizontal');
    }

    private function modelImage(Event|Dj $model, string $collection, ?string $conversion = null): ?string
    {
        $media = $model->getFirstMedia($collection);

        if (! $media) {
            return null;
        }

        if ($conversion && $media->hasGeneratedConversion($conversion) && is_readable($media->getPath($conversion))) {
            return $media->getUrl($conversion);
        }

        return $this->mediaUrl($media);
    }

    private function mediaUrl(Media $media): ?string
    {
        return is_readable($media->getPath()) ? $media->getUrl() : null;
    }

    private function publicAsset(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    private function instagramUrl(?string $handle): string
    {
        if (blank($handle)) {
            return '#';
        }

        return 'https://www.instagram.com/' . ltrim($handle, '@') . '/';
    }
}
