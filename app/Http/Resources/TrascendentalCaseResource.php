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
            'summary' => $this->caseSummary(),
            'description' => $this->description,
            'venue' => $this->venue,
            'city' => $this->caseCity(),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'metrics' => $this->caseMetrics(),
            'services' => $this->caseServices(),
            'image_url' => $this->imageUrl(),
            'media' => $this->mediaItems(),
        ];
    }

    private function caseCity(): ?string
    {
        return match ($this->slug) {
            'rebolledo-zal-marina' => 'Progreso, Yucatan',
            default => $this->city,
        };
    }

    private function caseServices(): array
    {
        return match ($this->slug) {
            'rebolledo-zal-marina', 'umi-fest-tulum' => [
                'Concept',
                'Booking',
                'Production',
                'Execution',
                'Marketing',
                'Operations',
            ],
            default => $this->case_services ?? [],
        };
    }

    private function caseSummary(): ?string
    {
        return match ($this->slug) {
            'rebolledo-zal-marina' => __('trascendental.case_data.rebolledo.summary'),
            'umi-fest-tulum' => __('trascendental.case_data.umi.summary'),
            default => $this->case_summary,
        };
    }

    private function caseMetrics(): array
    {
        return match ($this->slug) {
            'rebolledo-zal-marina' => [
                ['label' => __('trascendental.case_data.rebolledo.metrics.attendees'), 'value' => '450'],
                ['label' => __('trascendental.case_data.rebolledo.metrics.result'), 'value' => __('trascendental.case_data.rebolledo.values.sold_out')],
                ['label' => __('trascendental.case_data.rebolledo.metrics.promotion'), 'value' => __('trascendental.case_data.rebolledo.values.promotion')],
            ],
            'umi-fest-tulum' => [
                ['label' => __('trascendental.case_data.umi.metrics.dates'), 'value' => '4'],
                ['label' => __('trascendental.case_data.umi.metrics.attendees'), 'value' => '~2000'],
                ['label' => __('trascendental.case_data.umi.metrics.format'), 'value' => __('trascendental.case_data.umi.values.format')],
            ],
            default => $this->case_metrics ?? [],
        };
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
                    'alt' => __('trascendental.case_data.media_alt.rebolledo_lights'),
                ],
            ])),
            'umi-fest-tulum' => [
                [
                    'type' => 'image',
                    'src' => asset('images/portfolio/photos/045-traumer-shonky-108b989ad6.webp'),
                    'alt' => __('trascendental.case_data.media_alt.traumer_shonky_crowd'),
                ],
                [
                    'type' => 'image',
                    'src' => asset('images/portfolio/photos/097-traumer-shonky-309e63566a.webp'),
                    'alt' => __('trascendental.case_data.media_alt.traumer_shonky_booth'),
                ],
                [
                    'type' => 'image',
                    'src' => asset('images/trascendental/cases/priku-artist.webp'),
                    'alt' => __('trascendental.case_data.media_alt.priku_umi'),
                ],
                [
                    'type' => 'image',
                    'src' => asset('images/trascendental/cases/priku-crowd.webp'),
                    'alt' => __('trascendental.case_data.media_alt.priku_drop'),
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
