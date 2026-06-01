<?php

namespace Tests\Feature;

use App\Mail\TrascendentalLeadNotification;
use App\Models\Customer;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TrascendentalSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_home_renders_case_studies_and_tours(): void
    {
        Event::create([
            'title' => 'Rebolledo - Zal Marina',
            'slug' => 'rebolledo-zal-marina',
            'is_case_study' => true,
            'case_summary' => 'Sold out.',
            'case_metrics' => [['label' => 'Asistentes', 'value' => '450']],
            'case_services' => ['Produccion integral'],
            'case_sort' => 1,
        ]);

        $this->get(route('trascendental.home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Trascendental/Home')
                ->has('cases', 1)
                ->where('cases.0.title', 'Rebolledo - Zal Marina')
                ->has('tours', 2)
                ->where('tours.0.artist', 'Crihan'));
    }

    public function test_preview_events_renders_local_event_flyers(): void
    {
        $this->get(route('trascendental.events'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Trascendental/Events')
                ->has('events', 16)
                ->where('events.1.title', 'TDL & Atypical invites Lizz')
                ->where('events.2.image', asset('images/trascendental/events/umi-iluminal-ii.webp'))
                ->where('pagination.currentPage', 1)
                ->where('pagination.lastPage', 1));
    }

    public function test_contact_endpoint_creates_customer_contact_log_and_notification(): void
    {
        Mail::fake();

        $response = $this->postJson(route('trascendental.leads.store'), [
            'service_type' => 'production',
            'city' => 'Tulum',
            'event_date' => '2026-08-15',
            'budget' => '$250,000 MXN',
            'name' => 'Cedrick',
            'email' => 'cedrick@example.com',
            'phone' => '+529841234567',
            'message' => 'Busco producir una fecha.',
            'captcha_answer' => '11',
            'privacy_accepted' => true,
            'company_website' => '',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('customers', [
            'email' => 'cedrick@example.com',
            'source' => 'trascendental_contact',
        ]);

        $customer = Customer::where('email', 'cedrick@example.com')->firstOrFail();

        $this->assertDatabaseHas('contact_logs', [
            'customer_id' => $customer->id,
            'channel' => 'manual',
            'type' => 'followup',
            'status' => 'pending',
        ]);

        Mail::assertSent(TrascendentalLeadNotification::class);
    }

    public function test_join_list_endpoint_tags_customer_for_trascendental_list(): void
    {
        Mail::fake();

        $response = $this->postJson(route('trascendental.leads.store'), [
            'lead_type' => 'join_list',
            'service_type' => 'booking',
            'city' => 'Join The List',
            'budget' => 'Join The List',
            'name' => 'Newsletter Lead',
            'email' => 'list@example.com',
            'phone' => '+529991112233',
            'message' => 'Early access to events, announcements and special projects.',
            'captcha_answer' => '11',
            'privacy_accepted' => true,
            'company_website' => '',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $customer = Customer::where('email', 'list@example.com')->firstOrFail();

        $this->assertContains('trascendental_join_list', $customer->tags);
        $this->assertArrayHasKey('trascendental_join_list', $customer->metadata);

        $this->assertDatabaseHas('contact_logs', [
            'customer_id' => $customer->id,
            'subject' => 'Join The List Trascendental',
            'status' => 'pending',
        ]);
    }
}
