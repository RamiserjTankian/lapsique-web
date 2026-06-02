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
                ->has('tours', 6)
                ->where('tours.0.artist', 'Crihan')
                ->where('tours.0.status', 'SOLD OUT')
                ->where('tours.5.artist', 'Zone+')
                ->where('tours.5.status', 'LAST DATES'));
    }

    public function test_preview_events_renders_local_event_flyers(): void
    {
        $this->get(route('trascendental.events'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Trascendental/Events')
                ->has('events', 12)
                ->where('events.1.title', 'TDL & Atypical invites Lizz')
                ->where('events.1.source_url', 'https://ra.co/events/2384899')
                ->where('events.2.image', asset('images/trascendental/events/umi-iluminal-ii-original.jpg'))
                ->has('upcomingEvents', 11)
                ->where('upcomingEvents.0.category', 'produced')
                ->where('upcomingEvents.1.category', 'announce')
                ->where('upcomingEvents.2.title', 'Crihan - Besarabia Aniversario 4')
                ->where('upcomingEvents.4.title', 'Crihan - Insight')
                ->where('upcomingEvents.4.tickets_url', 'https://www.passline.com/eventos/insight-pres-crihan-rumania')
                ->where('pagination.currentPage', 1)
                ->where('pagination.lastPage', 2)
                ->where('pagination.perPage', 12)
                ->where('pagination.total', 16)
                ->where('seo.title', 'Events')
                ->where('seo.metaTitle', 'Events · Trascendentalby'));

        $this->get(route('trascendental.events', ['page' => 2]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Trascendental/Events')
                ->has('events', 4)
                ->where('events.0.title', 'Trascendental x White Deer Records: Youandewan')
                ->where('events.3.title', 'TDL presents: Game at Salon Gallos')
                ->where('pagination.currentPage', 2)
                ->where('pagination.lastPage', 2));
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
            'company_website' => 'https://autofilled.example',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('message', 'Thank you. You are on the list. Your registration was saved and we will contact you through Trascendental channels.')
            ->assertJsonMissing(['discount_code']);

        $customer = Customer::where('email', 'list@example.com')->firstOrFail();

        $this->assertContains('trascendental_join_list', $customer->tags);
        $this->assertArrayHasKey('trascendental_join_list', $customer->metadata);
        $this->assertTrue($customer->metadata['trascendental_join_list']['mail_suppressed']);
        $this->assertArrayNotHasKey('discount_code', $customer->metadata['trascendental_join_list']);
        $this->assertArrayNotHasKey('discount_percent', $customer->metadata['trascendental_join_list']);

        $this->assertDatabaseHas('contact_logs', [
            'customer_id' => $customer->id,
            'subject' => 'Join The List Trascendental',
            'status' => 'pending',
        ]);

        Mail::assertNothingSent();
    }
}
