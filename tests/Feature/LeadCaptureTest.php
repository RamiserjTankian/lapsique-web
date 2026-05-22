<?php

namespace Tests\Feature;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class LeadCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_new_lead_from_popup_endpoint(): void
    {
        Bus::fake();

        $response = $this->postJson(route('leads.capture'), [
            'name' => 'Ana Lapsique',
            'email' => 'ana@example.com',
            'phone' => '+529841111111',
            'instagram_handle' => '@ana',
            'interests' => ['events', 'djs'],
            'current_page' => 'https://lapsique.media.test/',
            'utm_source' => 'meta',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'new_customer' => true,
            ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'ana@example.com',
            'name' => 'Ana Lapsique',
            'source' => 'popup',
            'subscribed_newsletter' => true,
        ]);

        Bus::assertDispatched(SendWelcomeEmailJob::class);
    }

    public function test_updates_existing_customer_from_popup_endpoint(): void
    {
        Bus::fake();

        Customer::create([
            'name' => 'Ana Vieja',
            'email' => 'ana@example.com',
            'status' => 'lead',
            'source' => 'manual',
            'subscribed_newsletter' => false,
        ]);

        $response = $this->postJson(route('leads.capture'), [
            'name' => 'Ana Actualizada',
            'email' => 'ana@example.com',
            'interests' => ['production'],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'new_customer' => false,
            ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'ana@example.com',
            'name' => 'Ana Actualizada',
        ]);

        Bus::assertNotDispatched(SendWelcomeEmailJob::class);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson(route('leads.capture'), [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable();
    }
}
