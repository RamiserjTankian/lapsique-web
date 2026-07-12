<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Support\BrowserUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $now = now();
        $isUpcoming = $this->starts_at?->isFuture() ?? false;
        $ticketProducts = $this->whenLoaded('ticketProducts', fn () => $this->ticketProducts, collect());
        $activeTicketProduct = $ticketProducts->first(
            fn ($product) => $product->isOnSale($now) && ($product->availableStock() === null || $product->availableStock() > 0),
        );
        $guestLinks = $this->whenLoaded('guestListInviteLinks', fn () => $this->guestListInviteLinks, collect());
        $guestLink = $guestLinks->first(fn ($link) => $link->canAcceptMoreRegistrations());
        $gallery = $this->getMedia('gallery')->map(fn ($media) => [
            'id' => $media->id,
            'url' => BrowserUrl::normalize($media->getUrl('cover_large') ?: $media->getUrl()),
            'thumb_url' => BrowserUrl::normalize($media->getUrl('thumb') ?: $media->getUrl()),
        ])->values()->all();
        $venueGallery = collect($this->location?->getMedia('gallery') ?? [])->map(fn ($media) => [
            'id' => $media->id,
            'url' => BrowserUrl::normalize($media->getUrl()),
            'thumb_url' => BrowserUrl::normalize($media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl()),
        ])->values()->all();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'cover_url' => BrowserUrl::normalize($this->getFirstMediaUrl('cover', 'cover_large')
                ?: $this->getFirstMediaUrl('cover')
                ?: ($this->public_image_path ? asset(ltrim($this->public_image_path, '/')) : null)),
            'location_name' => $this->location?->name,
            'venue' => $this->venue,
            'city' => $this->city,
            'headline' => $this->headline,
            'description' => $this->description,
            'youtube_url' => $this->youtube_url,
            'ticket_url' => $isUpcoming ? $this->ticket_url : null,
            'is_upcoming' => $isUpcoming,
            'has_tickets' => $isUpcoming && (filled($this->ticket_url) || $activeTicketProduct !== null),
            'guest_list_url' => $isUpcoming && $guestLink ? $guestLink->invite_url : null,
            'lineup' => $this->whenLoaded(
                'djs',
                fn () => DjResource::collection($this->djs)->resolve(),
                [],
            ),
            'gallery' => $gallery,
            'venue_gallery' => $venueGallery,
        ];
    }
}
