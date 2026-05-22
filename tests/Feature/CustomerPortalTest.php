<?php

namespace Tests\Feature;

use App\Jobs\SendCustomerPortalAccessEmailJob;
use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\ContentBookingDeliverableLink;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomerWithPassword(): Customer
    {
        return Customer::create([
            'name' => 'Cliente Portal',
            'email' => 'portal@example.com',
            'password' => Hash::make('secret-pass'),
            'status' => 'customer',
        ]);
    }

    protected function createBookingForCustomer(Customer $customer, array $overrides = []): ContentBooking
    {
        $slot = BookingSlot::create([
            'date' => now()->addDays(5)->toDateString(),
            'time_label' => '11:00 AM',
            'time_value' => '11:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        return ContentBooking::create(array_merge([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'customer_id' => $customer->id,
            'client_name' => $customer->name,
            'client_email' => $customer->email,
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
            'payment_provider' => 'stripe',
        ], $overrides));
    }

    public function test_customer_can_login_and_view_portal(): void
    {
        $customer = $this->createCustomerWithPassword();
        $this->createBookingForCustomer($customer);

        $this->post(route('customers.login.store'), [
            'email' => 'portal@example.com',
            'password' => 'secret-pass',
        ])->assertRedirect(route('customers.portal'));

        $this->get(route('customers.portal'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Customer/Portal')
                ->where('customer.email', 'portal@example.com')
                ->has('bookings', 1)
            );
    }

    public function test_portal_hides_drive_links_until_published(): void
    {
        $customer = $this->createCustomerWithPassword();

        $this->createBookingForCustomer($customer);

        $this->actingAs($customer, 'customer')
            ->get(route('customers.portal'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->missing('bookings.0.deliverable_links')
            );
    }

    public function test_portal_shows_drive_links_when_published(): void
    {
        $customer = $this->createCustomerWithPassword();
        $driveUrl = 'https://drive.google.com/drive/folders/published';

        $booking = $this->createBookingForCustomer($customer, [
            'deliverables_drive_url' => $driveUrl,
            'deliverables_ready_at' => now(),
        ]);

        ContentBookingDeliverableLink::create([
            'content_booking_id' => $booking->id,
            'label' => 'Material',
            'url' => $driveUrl,
        ]);

        $this->actingAs($customer, 'customer')
            ->get(route('customers.portal'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('bookings.0.deliverable_links', 1)
                ->where('bookings.0.deliverable_links.0.url', $driveUrl)
            );
    }

    public function test_forgot_password_accepts_request(): void
    {
        $customer = $this->createCustomerWithPassword();

        $this->post(route('customers.password.email'), [
            'email' => $customer->email,
        ])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_login_page_renders_inertia(): void
    {
        $this->get(route('customers.login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Login'));
    }

    public function test_checkout_does_not_dispatch_portal_access_email(): void
    {
        Bus::fake([SendCustomerPortalAccessEmailJob::class]);

        config(['mercadopago.access_token' => 'TEST-token']);

        \Illuminate\Support\Facades\Http::fake([
            '*mercadopago.com*' => \Illuminate\Support\Facades\Http::response([
                'id' => 'pref_no_email',
                'init_point' => 'https://www.mercadopago.com.mx/checkout',
            ], 201),
        ]);

        \App\Models\SiteSetting::query()->create(['booking_price' => 3000]);

        $slot = BookingSlot::create([
            'date' => now()->addDays(3)->toDateString(),
            'time_label' => '10:00 AM',
            'time_value' => '10:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        $this->post(route('booking.checkout'), [
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente Test',
            'client_email' => 'checkout@example.com',
            'client_phone' => '529841234567',
            'payment_provider' => 'mercadopago',
        ]);

        Bus::assertNotDispatched(SendCustomerPortalAccessEmailJob::class);
    }
}
