<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterSafeByVarunaDraftTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'safe-by-varuna-1-edition';

    public function test_preview_is_read_only(): void
    {
        $this->artisan('events:register-safe-by-varuna-draft')
            ->expectsOutput('Preview only. No database write was performed.')
            ->assertSuccessful();

        $this->assertSame(0, Event::withTrashed()->where('slug', self::SLUG)->count());
    }

    public function test_write_creates_one_idempotent_unpublished_non_selling_draft(): void
    {
        $this->artisan('events:register-safe-by-varuna-draft', ['--write-draft' => true])
            ->assertSuccessful();
        $this->artisan('events:register-safe-by-varuna-draft', ['--write-draft' => true])
            ->assertSuccessful();

        $this->assertSame(1, Event::withTrashed()->where('slug', self::SLUG)->count());

        $draft = Event::withTrashed()->where('slug', self::SLUG)->firstOrFail();

        $this->assertTrue($draft->trashed());
        $this->assertNull(Event::query()->where('slug', self::SLUG)->first());
        $this->assertSame('Safe by varuna 1 edition', $draft->title);
        $this->assertStringContainsString('Capacidad del venue: 350 pax.', (string) $draft->localizedDescription('es'));
        $this->assertStringContainsString('Dress code: negro.', (string) $draft->localizedDescription('es'));
        $this->assertStringContainsString('Fecha confirmada: 27 de agosto de 2026.', (string) $draft->localizedDescription('es'));
        $this->assertStringContainsString('Dirección anunciada: Tonalá 145, CDMX.', (string) $draft->localizedDescription('es'));
        $this->assertStringContainsString('Confirmed date: August 27, 2026.', (string) $draft->localizedDescription('en'));
        $this->assertStringContainsString('Dress code: black.', (string) $draft->localizedDescription('en'));
        $this->assertSame([
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
        ], $draft->tags);

        $this->assertNull($draft->starts_at);
        $this->assertSame('Casa Luma Cultural Space · Tonalá 145', $draft->venue);
        $this->assertSame('Ciudad de México', $draft->city);
        $this->assertNull($draft->location_id);
        $this->assertSame('KAPI · Minimal house', $draft->lineup_text);
        $this->assertNull($draft->youtube_url);
        $this->assertNull($draft->ticket_url);
        $this->assertSame('/images/events/safe-by-varuna/event-poster.jpg', $draft->public_image_path);
        $this->assertNull($draft->source_url);
        $this->assertNull($draft->details_url);
        $this->assertFalse($draft->is_featured);
        $this->assertFalse($draft->is_case_study);
        $this->assertFalse($draft->trascendental_visible);
        $this->assertSame(0, $draft->ticketProducts()->count());
    }

    public function test_publish_or_sell_flags_are_refused_when_required_facts_are_missing(): void
    {
        $this->artisan('events:register-safe-by-varuna-draft', [
            '--write-draft' => true,
            '--publish' => true,
        ])->expectsOutput('Refusing activation: this command is draft-only and will not publish or sell tickets.')
            ->assertFailed();

        $this->artisan('events:register-safe-by-varuna-draft', [
            '--write-draft' => true,
            '--sell' => true,
        ])->expectsOutput('Refusing activation: this command is draft-only and will not publish or sell tickets.')
            ->assertFailed();

        $this->assertSame(0, Event::withTrashed()->where('slug', self::SLUG)->count());
    }

    public function test_confirmed_announcement_can_publish_without_enabling_sales_or_inventing_a_time(): void
    {
        $this->artisan('events:register-safe-by-varuna-draft', [
            '--publish-announcement' => true,
        ])->expectsOutput('Announcement publication requires --confirm=PUBLISH.')
            ->assertFailed();

        $this->artisan('events:register-safe-by-varuna-draft', [
            '--publish-announcement' => true,
            '--confirm' => 'PUBLISH',
        ])->expectsOutput('Safe by Varuna public announcement published without sales.')
            ->assertSuccessful();

        $event = Event::query()->where('slug', self::SLUG)->firstOrFail();
        $this->assertFalse($event->trashed());
        $this->assertSame('2026-08-27', $event->starts_at?->toDateString());
        $this->assertContains('time_tba', $event->tags);
        $this->assertTrue($event->is_featured);
        $this->assertNull($event->ticket_url);
        $this->assertSame(0, $event->ticketProducts()->count());
    }

    public function test_confirmed_announcement_can_be_safely_unpublished(): void
    {
        $this->artisan('events:register-safe-by-varuna-draft', [
            '--publish-announcement' => true,
            '--confirm' => 'PUBLISH',
        ])->assertSuccessful();

        $this->artisan('events:register-safe-by-varuna-draft', [
            '--unpublish-announcement' => true,
        ])->expectsOutput('Announcement removal requires --confirm=UNPUBLISH.')
            ->assertFailed();

        $this->artisan('events:register-safe-by-varuna-draft', [
            '--unpublish-announcement' => true,
            '--confirm' => 'UNPUBLISH',
        ])->expectsOutput('Safe by Varuna announcement unpublished.')
            ->assertSuccessful();

        $event = Event::withTrashed()->where('slug', self::SLUG)->firstOrFail();
        $this->assertTrue($event->trashed());
        $this->assertSame(0, Event::query()->where('slug', self::SLUG)->count());
    }
}
