<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class TrascendentalCaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'headline' => $this->headline,
            'summary' => $this->case_summary,
            'description' => $this->description,
            'venue' => $this->venue,
            'city' => $this->city,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'metrics' => $this->case_metrics ?? [],
            'services' => $this->case_services ?? [],
            'image_url' => $this->imageUrl(),
            'media' => $this->mediaItems(),
        ];
    }

    private function imageUrl(): ?string
    {
        $mediaUrl = $this->getFirstMediaUrl('cover', 'cover_large')
            ?: $this->getFirstMediaUrl('cover');

        if ($mediaUrl) {
            return $mediaUrl;
        }

        return match ($this->slug) {
            'rebolledo-zal-marina' => asset('images/trascendental/cases/rebolledo-crowd.webp'),
            'umi-fest-tulum' => asset('images/portfolio/photos/045-traumer-shonky-108b989ad6.webp'),
            default => null,
        };
    }

    private function mediaItems(): array
    {
        $cover = $this->imageUrl();

        return match ($this->slug) {
            'rebolledo-zal-marina' => array_values(array_filter([
                $cover ? [
                    'type' => 'image',
                    'src' => $cover,
                    'alt' => $this->title,
                ] : null,
                [
                    'type' => 'image',
                    'src' => asset('images/trascendental/cases/rebolledo-lasers.webp'),
                    'alt' => 'Rebolledo bajo luces en Zal Marina',
                ],
            ])),
            'umi-fest-tulum' => [
                [
                    'type' => 'image',
                    'src' => asset('images/portfolio/photos/045-traumer-shonky-108b989ad6.webp'),
                    'alt' => 'Traumer y Shonky frente al publico',
                ],
                [
                    'type' => 'image',
                    'src' => asset('images/portfolio/photos/097-traumer-shonky-309e63566a.webp'),
                    'alt' => 'Traumer y Shonky en cabina',
                ],
                [
                    'type' => 'image',
                    'src' => asset('images/trascendental/cases/priku-artist.webp'),
                    'alt' => 'Priku en Umi',
                ],
                [
                    'type' => 'image',
                    'src' => asset('images/trascendental/cases/priku-crowd.webp'),
                    'alt' => 'Publico en el drop de Priku',
                ],
            ],
            default => $cover ? [[
                'type' => 'image',
                'src' => $cover,
                'alt' => $this->title,
            ]] : [],
        };
    }
}
