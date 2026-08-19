<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class RegisterSafeByVarunaDraft extends Command
{
    private const SLUG = 'safe-by-varuna-1-edition';

    protected $signature = 'events:register-safe-by-varuna-draft
        {--write-draft : Create or update the internal, unpublished draft}
        {--publish-announcement : Publish the confirmed event announcement without tickets or sales}
        {--confirm= : Must equal PUBLISH for --publish-announcement}
        {--unpublish-announcement : Soft-delete only the non-selling Safe by Varuna announcement}
        {--publish : Refused: publication is outside this draft-only command}
        {--sell : Refused: ticket selling is outside this draft-only command}';

    protected $description = 'Previsualiza o registra el borrador interno y no comercial de Safe by Varuna 1 edition';

    public function handle(): int
    {
        if ($this->option('publish') || $this->option('sell')) {
            return $this->refuseActivation();
        }

        $draft = Event::withTrashed()->where('slug', self::SLUG)->first();

        if ($this->option('unpublish-announcement')) {
            return $this->unpublishAnnouncement($draft);
        }

        if ($this->option('publish-announcement')) {
            return $this->publishAnnouncement($draft);
        }

        if (! $this->option('write-draft')) {
            $this->info('Preview only. No database write was performed.');
            $this->line('Slug: ' . self::SLUG);
            $this->line('Existing draft: ' . ($draft?->trashed() ? 'soft-deleted draft' : ($draft ? 'active event (will be refused on write)' : 'none')));
            $this->line('Confirmed: August 27, 2026; KAPI; Casa Luma Cultural Space; Tonalá 145, CDMX.');
            $this->line('Commercial state: disabled (no exact time, price, ticket URL, ticket products, publication, or selling).');

            return self::SUCCESS;
        }

        if ($draft && ! $draft->trashed()) {
            $this->error('Refusing to overwrite an active event with this draft command. Archive or review it manually first.');

            return self::FAILURE;
        }

        $attributes = $this->draftAttributes();
        $created = ! $draft;
        $draft ??= new Event();
        $draft->fill($attributes);
        $draft->save();

        if ($created) {
            // The existing schema has no draft/publication column for the primary site.
            // A soft-deleted row is the only fail-closed state that keeps this draft out
            // of public Event queries while retaining it for reviewed recovery.
            $draft->delete();
        }

        $this->info($created ? 'Internal Safe by Varuna draft created.' : 'Internal Safe by Varuna draft updated.');
        $this->line('State: soft-deleted / unpublished / non-selling.');
        $this->line('No ticket product, provider call, payment URL, or publication action was created.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function draftAttributes(): array
    {
        return [
            'title' => 'Safe by varuna 1 edition',
            'slug' => self::SLUG,
            'headline' => 'KAPI · Minimal house · Tulum to CDMX',
            'description' => json_encode($this->localizedDescriptions(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'lineup_text' => 'KAPI · Minimal house',
            'starts_at' => null,
            'venue' => 'Casa Luma Cultural Space · Tonalá 145',
            'city' => 'Ciudad de México',
            'location_id' => null,
            'technical_rider' => null,
            'tags' => [
                'Safe by varuna',
                '27 agosto 2026',
                'KAPI',
                'Minimal house',
                'Casa Luma Cultural Space',
                'Tonalá 145',
                'Capacidad: 350 pax',
                'Cupo limitado',
                'Dress code: negro',
                'Pauta: Condesa',
                'Pauta: Roma',
                'Pauta: Polanco',
            ],
            'youtube_url' => null,
            'ticket_url' => null,
            'public_image_path' => '/images/events/safe-by-varuna/event-poster.jpg',
            'source_url' => null,
            'details_url' => null,
            'is_featured' => false,
            'is_case_study' => false,
            'trascendental_kind' => null,
            'trascendental_visible' => false,
            'case_summary' => null,
            'case_metrics' => null,
            'case_services' => null,
            'case_sort' => 0,
            'priority' => 0,
            'has_vertical_poster' => false,
            'has_horizontal_poster' => false,
        ];
    }

    private function refuseActivation(): int
    {
        $this->error('Refusing activation: this command is draft-only and will not publish or sell tickets.');
        $this->line('A separate reviewed activation flow must validate the exact time, positive price, real HTTPS ticket URL, age rule, tenant and explicit publication authority before it can modify any public or selling state.');

        return self::FAILURE;
    }

    /** @return array{es: string, en: string} */
    private function localizedDescriptions(): array
    {
        return [
            'es' => implode("\n", [
                'Fecha confirmada: 27 de agosto de 2026.',
                'Venue: Casa Luma Cultural Space.',
                'Dirección anunciada: Tonalá 145, CDMX.',
                'KAPI · Minimal house · Tulum to CDMX.',
                'Capacidad del venue: 350 pax. Cupo limitado.',
                'Dress code: negro.',
                '',
                'Horario, precio, fases de venta y regla de edad por confirmar.',
            ]),
            'en' => implode("\n", [
                'Confirmed date: August 27, 2026.',
                'Venue: Casa Luma Cultural Space.',
                'Announced address: Tonalá 145, Mexico City.',
                'KAPI · Minimal house · Tulum to CDMX.',
                'Venue capacity: 350 guests. Limited capacity.',
                'Dress code: black.',
                '',
                'Exact time, ticket price, sale phases and age policy are still to be confirmed.',
            ]),
        ];
    }

    private function publishAnnouncement(?Event $event): int
    {
        if ($this->option('confirm') !== 'PUBLISH') {
            $this->error('Announcement publication requires --confirm=PUBLISH.');

            return self::FAILURE;
        }

        if ($event && ! $event->trashed() && (filled($event->ticket_url) || $event->ticketProducts()->exists())) {
            $this->error('Refusing to replace an active selling event. Review its tickets manually.');

            return self::FAILURE;
        }

        $event ??= new Event();
        $event->fill([
            ...$this->draftAttributes(),
            // Noon is an intentionally non-displayed date anchor. The
            // time_tba tag makes every public formatter omit the clock until
            // the exact event time is confirmed.
            'starts_at' => '2026-08-27 12:00:00',
            'is_featured' => true,
            'tags' => [...$this->draftAttributes()['tags'], 'time_tba'],
        ]);
        $event->save();
        if ($event->trashed()) {
            $event->restore();
        }

        $this->info('Safe by Varuna public announcement published without sales.');
        $this->line('Date: August 27, 2026. Exact time, price and ticket phases remain unannounced.');
        $this->line('No ticket product, provider call or payment URL was created.');

        return self::SUCCESS;
    }

    private function unpublishAnnouncement(?Event $event): int
    {
        if ($this->option('confirm') !== 'UNPUBLISH') {
            $this->error('Announcement removal requires --confirm=UNPUBLISH.');

            return self::FAILURE;
        }

        if (! $event || $event->trashed()) {
            $this->info('Safe by Varuna announcement is already unpublished.');

            return self::SUCCESS;
        }

        if (filled($event->ticket_url) || $event->ticketProducts()->exists()) {
            $this->error('Refusing to unpublish an event with selling configuration. Review its tickets manually.');

            return self::FAILURE;
        }

        $event->delete();
        $this->info('Safe by Varuna announcement unpublished.');
        $this->line('Only the exact non-selling event slug was soft-deleted.');

        return self::SUCCESS;
    }
}
