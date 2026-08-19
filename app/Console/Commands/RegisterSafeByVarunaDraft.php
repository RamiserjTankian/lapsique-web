<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\TicketProduct;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegisterSafeByVarunaDraft extends Command
{
    private const SLUG = 'safe-by-varuna-1-edition';

    protected $signature = 'events:register-safe-by-varuna-draft
        {--write-draft : Create or update the internal, unpublished draft}
        {--publish-announcement : Publish the confirmed event announcement without tickets or sales}
        {--activate-testing : Activate the exact, single-phase Safe test catalog}
        {--confirm= : Confirmation token required by publication or testing activation}
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

        if ($this->option('activate-testing')) {
            return $this->activateTesting($draft);
        }

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
            $this->line('Confirmed commercial facts: 22:00 CDMX; $100 MXN base; one phase; 350 tickets; 18+; no refunds.');
            $this->line('Testing catalog remains disabled unless --activate-testing --confirm=ACTIVATE_TESTING is used.');

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
                'Inicio: 10:00 p. m. · Acceso para mayores de 18 años.',
                'Boleto: $100 MXN + 5% de cargo de servicio. Total: $105 MXN.',
                'Una sola fase. Sin reembolsos.',
            ]),
            'en' => implode("\n", [
                'Confirmed date: August 27, 2026.',
                'Venue: Casa Luma Cultural Space.',
                'Announced address: Tonalá 145, Mexico City.',
                'KAPI · Minimal house · Tulum to CDMX.',
                'Venue capacity: 350 guests. Limited capacity.',
                'Dress code: black.',
                '',
                'Starts at 10:00 p.m. · Admission is restricted to guests aged 18 and over.',
                'Ticket: MXN $100 + 5% service charge. Total: MXN $105.',
                'Single sales phase. No refunds.',
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
            'starts_at' => CarbonImmutable::parse('2026-08-27 22:00:00', 'America/Mexico_City')
                ->setTimezone((string) config('app.timezone')),
            'is_featured' => true,
            'tags' => [...$this->draftAttributes()['tags'], 'Inicio: 22:00 CDMX', 'Edad: 18+', 'Sin reembolsos'],
        ]);
        $event->save();
        if ($event->trashed()) {
            $event->restore();
        }

        $this->info('Safe by Varuna public announcement published without sales.');
        $this->line('Date: August 27, 2026 at 22:00 CDMX. 18+ and no refunds. Sales remain disabled.');
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

    private function activateTesting(?Event $event): int
    {
        if ($this->option('confirm') !== 'ACTIVATE_TESTING') {
            $this->error('Testing activation requires --confirm=ACTIVATE_TESTING.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($event): void {
                $event = $event
                    ? Event::withTrashed()->lockForUpdate()->findOrFail($event->getKey())
                    : new Event();

                $existingProducts = $event->exists
                    ? $event->ticketProducts()->withTrashed()->lockForUpdate()->get()
                    : collect();

                if ($existingProducts->contains(fn (TicketProduct $product): bool => $product->orderItems()->exists())) {
                    throw new RuntimeException('Safe already has ticket orders. Refusing to rewrite its catalog.');
                }

                if ($existingProducts->count() > 1
                    || ($existingProducts->first() && data_get($existingProducts->first()->metadata, 'catalog_contract') !== 'safe_single_testing_v1')) {
                    throw new RuntimeException('Safe has an unowned ticket catalog. Review it manually before activation.');
                }

                $event->fill([
                    ...$this->draftAttributes(),
                    // 22:00 Mexico City equals 23:00 in the application timezone
                    // (America/Cancun) on this date.
                    'starts_at' => CarbonImmutable::parse('2026-08-27 22:00:00', 'America/Mexico_City')
                        ->setTimezone((string) config('app.timezone')),
                    'is_featured' => true,
                    'tags' => [
                        ...array_values(array_filter(
                            $this->draftAttributes()['tags'],
                            fn (string $tag): bool => $tag !== 'time_tba',
                        )),
                        'Inicio: 22:00 CDMX',
                        'Edad: 18+',
                        'Boleto base: 100 MXN',
                        'Cargo de servicio: 5%',
                        'Total testing: 105 MXN',
                        'Una sola fase',
                        'Sin reembolsos',
                        'sales_testing',
                    ],
                ]);
                $event->save();
                if ($event->trashed()) {
                    $event->restore();
                }

                $product = $existingProducts->first() ?? new TicketProduct();
                $product->fill([
                    'event_id' => $event->id,
                    'name' => 'Acceso general · Testing',
                    'description' => 'Acceso individual 18+ para Safe by Varuna. Modo testing; no válido para ingresar.',
                    'category' => 'ticket',
                    'currency' => 'MXN',
                    // Ticket base is $100. The existing model stores gross.
                    'price' => 105,
                    'service_charge_pct' => 5,
                    'access_units' => 1,
                    'check_in_limit' => 1,
                    'stock' => 350,
                    'reserved_count' => 0,
                    'sold_count' => 0,
                    'max_per_order' => 6,
                    'starts_at' => now(),
                    'ends_at' => $event->starts_at,
                    'is_active' => true,
                    'metadata' => [
                        'catalog_contract' => 'safe_single_testing_v1',
                        'sales_mode' => 'testing',
                        'phase' => 'single',
                        'base_ticket_price' => 100,
                        'service_charge_pct' => 5,
                        'refund_policy' => 'no_refunds',
                        'minimum_age' => 18,
                    ],
                ]);
                $product->save();
                if ($product->trashed()) {
                    $product->restore();
                }
            });
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Safe by Varuna testing catalog activated.');
        $this->line('One phase · 350 tickets · $100 base + $5 service · $105 test total.');
        $this->line('22:00 CDMX · 18+ · no refunds · no production charge enabled.');

        return self::SUCCESS;
    }
}
