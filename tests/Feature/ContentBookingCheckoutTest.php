<?php

namespace Tests\Feature;

use App\Jobs\SendContentBookingConfirmationEmailJob;
use App\Jobs\SendContentBookingReceiptEmailJob;
use App\Jobs\SendCustomerPortalAccessEmailJob;
use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\Dj;
use App\Models\PortfolioItem;
use App\Models\SiteSetting;
use App\Models\Video;
use App\Services\ContentBookingPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContentBookingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('es');
    }

    protected function createAvailableSlot(): BookingSlot
    {
        return BookingSlot::create([
            'date' => now()->addDays(3)->toDateString(),
            'time_label' => '2:00 PM',
            'time_value' => '14:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);
    }

    protected function checkoutPayload(BookingSlot $slot): array
    {
        return [
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente Test',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'client_instagram' => '@cliente',
            'terms_accepted' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function postSignedStripeWebhook(array $payload): \Illuminate\Testing\TestResponse
    {
        $secret = 'whsec_content_booking_test';
        config(['stripe.webhook_secret' => $secret]);

        $body = json_encode($payload);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        return $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
            ],
            $body,
        );
    }

    public function test_home_includes_agenda_section(): void
    {
        SiteSetting::query()->create([
            'booking_price' => 4000,
            'booking_title' => 'Sesión Test',
        ]);

        $this->createAvailableSlot();

        $this->withSession(['locale' => 'es'])
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->where('title', 'Sesión Test')
                ->has('slots', 1)
                ->has('heroProofVideo')
                ->has('landingVideos.offer.src')
            );
    }

    public function test_booking_pages_only_expose_supported_session_times(): void
    {
        $this->createAvailableSlot();

        BookingSlot::create([
            'date' => now()->addDays(3)->toDateString(),
            'time_label' => '3:00 PM',
            'time_value' => '15:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->has('slots', 1)
                ->where('slots.0.time_value', '14:00')
            );

        $this->post(route('booking.checkout'), $this->checkoutPayload(BookingSlot::query()->where('time_value', '15:00')->first()))
            ->assertSessionHasErrors('booking_slot_id');
    }

    public function test_menu_booking_landings_expose_meta_service_type_context(): void
    {
        $this->createAvailableSlot();

        foreach ([
            'food-reels.show' => 'food_reels',
            'djset.show' => 'dj_set',
            'drone-sessions.show' => 'drone_session',
            'construction-progress.show' => 'construction_progress',
        ] as $route => $serviceType) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('"serviceType":"'.$serviceType.'"', false);
        }
    }

    public function test_checkout_defaults_to_stripe_for_home_booking(): void
    {
        config([
            'mercadopago.access_token' => 'TEST-token',
            'stripe.secret_key' => 'sk_test_fake',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
        ]);

        Http::fake([
            '*mercadopago.com*' => Http::response([
                'id' => 'pref_test_123',
                'init_point' => 'https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref_test_123',
            ], 201),
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/pay/cs_test_123',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('booking.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'mercadopago',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $booking = ContentBooking::first();
        $this->assertNotNull($booking);
        $this->assertSame('stripe', $booking->payment_provider);
        $this->assertSame('cs_test_123', $booking->stripe_checkout_session_id);
        $this->assertSame(4000, $booking->amount);
        $this->assertSame('cliente@example.com', $booking->client_email);
        $this->assertNotNull($booking->customer_id);
        $this->assertDatabaseHas('customers', [
            'id' => $booking->customer_id,
            'email' => 'cliente@example.com',
        ]);
        $this->assertSame(1, $slot->fresh()->booked_count);
    }

    public function test_checkout_with_stripe_redirects_to_checkout_session(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_fake',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/pay/cs_test_123',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('booking.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'stripe',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $booking = ContentBooking::first();
        $this->assertSame('stripe', $booking->payment_provider);
        $this->assertSame('cs_test_123', $booking->stripe_checkout_session_id);
    }

    public function test_checkout_sends_meta_payment_info_and_initiate_checkout_with_browser_event_ids(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_fake',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
            'meta.capi.enabled' => true,
            'meta.pixel.id' => '123456789',
            'meta.marketing_api.access_token' => 'test-token',
            'meta.marketing_api.api_version' => 'v21.0',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/pay/cs_test_123',
                'status' => 'open',
            ], 200),
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('booking.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'stripe',
            'checkout_event_id' => 'checkout-event-123',
            'payment_info_event_id' => 'checkout-event-123_payment_info',
            'fbp' => 'fb.1.1710000000000.123456789',
            'fbc' => 'fb.1.1710000000000.fbclid',
            'landing_url' => 'https://lapsique.media/?utm_source=facebook',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '123456789/events')) {
                return false;
            }

            $event = $request->data()['data'][0] ?? [];

            return ($event['event_name'] ?? null) === 'AddPaymentInfo'
                && ($event['event_id'] ?? null) === 'checkout-event-123_payment_info'
                && ($event['event_source_url'] ?? null) === 'https://lapsique.media/?utm_source=facebook'
                && ($event['user_data']['fbp'] ?? null) === 'fb.1.1710000000000.123456789'
                && ($event['user_data']['fbc'] ?? null) === 'fb.1.1710000000000.fbclid'
                && ($event['custom_data']['payment_provider'] ?? null) === 'stripe';
        });

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '123456789/events')) {
                return false;
            }

            $event = $request->data()['data'][0] ?? [];

            return ($event['event_name'] ?? null) === 'InitiateCheckout'
                && ($event['event_id'] ?? null) === 'checkout-event-123'
                && ($event['event_source_url'] ?? null) === 'https://lapsique.media/?utm_source=facebook'
                && ($event['user_data']['fbp'] ?? null) === 'fb.1.1710000000000.123456789'
                && ($event['user_data']['fbc'] ?? null) === 'fb.1.1710000000000.fbclid';
        });
    }

    public function test_inertia_stripe_checkout_uses_external_location_visit(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_fake',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_inertia',
                'url' => 'https://checkout.stripe.com/pay/cs_test_inertia',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $this->withHeader('X-Inertia', 'true')
            ->post(route('booking.checkout'), array_merge($this->checkoutPayload($slot), [
                'payment_provider' => 'stripe',
            ]))
            ->assertConflict()
            ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.com/pay/cs_test_inertia');
    }

    public function test_dj_set_landing_uses_shared_slots_and_dj_material(): void
    {
        $this->createAvailableSlot();

        Video::create([
            'title' => 'Psique DJ set',
            'slug' => 'psique-dj-set',
            'youtube_id' => 'yt-dj-set',
            'youtube_url' => 'https://youtube.test/dj-set',
            'tags' => ['psique-originals'],
            'is_featured' => true,
        ]);

        PortfolioItem::create([
            'title' => 'Nightlife proof',
            'slug' => 'nightlife-proof',
            'type' => 'photo',
            'source' => 'upload',
            'tags' => ['nightlife'],
            'is_active' => true,
        ]);

        Dj::create([
            'name' => 'DJ Proof',
            'slug' => 'dj-proof',
            'is_featured' => true,
        ]);

        $this->get(route('djset.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('DjSet/Show')
                ->where('price', 10000)
                ->has('slots', 1)
                ->has('originals', 1)
                ->has('portfolioItems')
                ->has('djSetReels')
                ->has('djs', 1)
            );
    }

    public function test_dj_set_checkout_forces_stripe_product_and_amount(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_fake',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_dj_set',
                'url' => 'https://checkout.stripe.com/pay/cs_dj_set',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('djset.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'mercadopago',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_dj_set');

        $booking = ContentBooking::firstOrFail();
        $this->assertSame(ContentBooking::SERVICE_DJ_SET, $booking->service_type);
        $this->assertSame('stripe', $booking->payment_provider);
        $this->assertSame(10000, $booking->amount);
        $this->assertSame(1, $slot->fresh()->booked_count);

        Http::assertSent(function ($request) use ($booking): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && data_get($payload, 'line_items.0.price_data.product_data.name') === 'Grabación de DJ Set — multicámara, Ronin, dron y audio 32-bit'
                && data_get($payload, 'line_items.0.price_data.unit_amount') === 1000000
                && data_get($payload, 'metadata.content_booking_public_id') === $booking->public_id;
        });
    }

    public function test_drone_session_landing_uses_shared_slots_and_price(): void
    {
        $this->createAvailableSlot();

        $this->get(route('drone-sessions.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('DroneSessions/Show')
                ->where('price', 3000)
                ->has('slots', 1)
            );
    }

    public function test_drone_session_checkout_forces_stripe_product_and_amount(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_fake',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_drone_session',
                'url' => 'https://checkout.stripe.com/pay/cs_drone_session',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('drone-sessions.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'mercadopago',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_drone_session');

        $booking = ContentBooking::firstOrFail();
        $this->assertSame(ContentBooking::SERVICE_DRONE_SESSION, $booking->service_type);
        $this->assertSame('stripe', $booking->payment_provider);
        $this->assertSame(3000, $booking->amount);
        $this->assertSame(1, $slot->fresh()->booked_count);

        Http::assertSent(function ($request) use ($booking): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && data_get($payload, 'line_items.0.price_data.product_data.name') === 'Sesión de vuelo con dron DJI Air 3 — 15 tomas de hasta 30 seg + 10 fotos'
                && data_get($payload, 'line_items.0.price_data.unit_amount') === 300000
                && data_get($payload, 'metadata.content_booking_public_id') === $booking->public_id;
        });
    }

    public function test_construction_progress_landing_uses_shared_slots_and_price(): void
    {
        $this->createAvailableSlot();

        $this->get(route('construction-progress.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('ConstructionProgress/Show')
                ->where('price', 5000)
                ->has('slots', 1)
            );
    }

    public function test_construction_progress_checkout_forces_stripe_product_and_amount(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_fake',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_construction_progress',
                'url' => 'https://checkout.stripe.com/pay/cs_construction_progress',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('construction-progress.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'mercadopago',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_construction_progress');

        $booking = ContentBooking::firstOrFail();
        $this->assertSame(ContentBooking::SERVICE_CONSTRUCTION_PROGRESS, $booking->service_type);
        $this->assertSame('stripe', $booking->payment_provider);
        $this->assertSame(5000, $booking->amount);
        $this->assertSame(1, $slot->fresh()->booked_count);

        Http::assertSent(function ($request) use ($booking): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && data_get($payload, 'line_items.0.price_data.product_data.name') === 'Avance de obra con dron DJI Air 3 — reporte visual de progreso'
                && data_get($payload, 'line_items.0.price_data.unit_amount') === 500000
                && data_get($payload, 'metadata.content_booking_public_id') === $booking->public_id;
        });
    }

    public function test_dj_set_failure_releases_shared_slot(): void
    {
        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'service_type' => ContentBooking::SERVICE_DJ_SET,
            'client_name' => 'DJ Cliente',
            'client_email' => 'dj@example.com',
            'client_phone' => '529841234567',
            'amount' => 10000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        $this->get(route('booking.failure', $booking->public_id))
            ->assertOk();

        $this->assertSame('failed', $booking->fresh()->status);
        $this->assertSame(0, $slot->fresh()->booked_count);
    }

    public function test_confirm_does_not_confirm_without_payment(): void
    {
        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'mercadopago',
        ]);

        $this->get(route('booking.confirm', $booking->public_id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Booking/Confirm')
                ->where('paymentVerified', false)
                ->where('booking.status', 'pending_payment')
                ->has('booking.service_name')
            );

        $this->assertSame('pending_payment', $booking->fresh()->status);
    }

    public function test_confirm_accepts_only_the_stripe_session_created_for_the_booking(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        $slot = $this->createAvailableSlot();
        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_booking_valid',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_booking_valid*' => Http::response([
                'id' => 'cs_booking_valid',
                'client_reference_id' => 'bkg_'.$booking->public_id,
                'amount_total' => 300000,
                'currency' => 'mxn',
                'payment_status' => 'paid',
                'payment_intent' => ['id' => 'pi_booking_valid', 'status' => 'succeeded'],
                'metadata' => [
                    'content_booking_id' => (string) $booking->id,
                    'content_booking_public_id' => $booking->public_id,
                ],
            ]),
        ]);

        $this->get(route('booking.confirm', $booking->public_id).'?session_id=cs_booking_valid')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Booking/Confirm')
                ->where('paymentVerified', true)
                ->where('booking.status', 'confirmed')
            );

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_confirm_rejects_a_paid_stripe_session_owned_by_another_booking(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        $slot = $this->createAvailableSlot();
        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_booking_owned',
        ]);
        $otherPublicId = (string) Str::uuid();

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_booking_owned*' => Http::response([
                'id' => 'cs_booking_owned',
                'client_reference_id' => 'bkg_'.$otherPublicId,
                'amount_total' => 300000,
                'currency' => 'mxn',
                'payment_status' => 'paid',
                'payment_intent' => ['id' => 'pi_booking_owned', 'status' => 'succeeded'],
                'metadata' => [
                    'content_booking_id' => '999999',
                    'content_booking_public_id' => $otherPublicId,
                ],
            ]),
        ]);

        $this->get(route('booking.confirm', $booking->public_id).'?session_id=cs_booking_owned')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('paymentVerified', false)
                ->where('booking.status', 'pending_payment')
            );

        $booking->refresh();
        $this->assertSame('pending_payment', $booking->status);
        $this->assertNull($booking->stripe_payment_intent_id);
    }

    public function test_confirm_rejects_a_session_when_reference_matches_but_metadata_does_not(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        $slot = $this->createAvailableSlot();
        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_booking_metadata',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_booking_metadata*' => Http::response([
                'id' => 'cs_booking_metadata',
                'client_reference_id' => 'bkg_'.$booking->public_id,
                'amount_total' => 300000,
                'currency' => 'mxn',
                'payment_status' => 'paid',
                'payment_intent' => ['id' => 'pi_booking_metadata', 'status' => 'succeeded'],
                'metadata' => [
                    'content_booking_id' => '999999',
                    'content_booking_public_id' => (string) Str::uuid(),
                ],
            ]),
        ]);

        $this->get(route('booking.confirm', $booking->public_id).'?session_id=cs_booking_metadata')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('paymentVerified', false)
                ->where('booking.status', 'pending_payment')
            );

        $this->assertSame('pending_payment', $booking->fresh()->status);
    }

    public function test_confirm_rejects_a_paid_stripe_session_with_wrong_amount(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        $slot = $this->createAvailableSlot();
        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_booking_amount',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_booking_amount*' => Http::response([
                'id' => 'cs_booking_amount',
                'client_reference_id' => 'bkg_'.$booking->public_id,
                'amount_total' => 100,
                'currency' => 'mxn',
                'payment_status' => 'paid',
                'payment_intent' => ['id' => 'pi_booking_amount', 'status' => 'succeeded'],
                'metadata' => [
                    'content_booking_id' => (string) $booking->id,
                    'content_booking_public_id' => $booking->public_id,
                ],
            ]),
        ]);

        $this->get(route('booking.confirm', $booking->public_id).'?session_id=cs_booking_amount')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('paymentVerified', false)
                ->where('booking.status', 'pending_payment')
            );

        $this->assertSame('pending_payment', $booking->fresh()->status);
    }

    public function test_confirm_rejects_a_paid_stripe_session_with_wrong_currency(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        $slot = $this->createAvailableSlot();
        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_booking_currency',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_booking_currency*' => Http::response([
                'id' => 'cs_booking_currency',
                'client_reference_id' => 'bkg_'.$booking->public_id,
                'amount_total' => 300000,
                'currency' => 'usd',
                'payment_status' => 'paid',
                'payment_intent' => ['id' => 'pi_booking_currency', 'status' => 'succeeded'],
                'metadata' => [
                    'content_booking_id' => (string) $booking->id,
                    'content_booking_public_id' => $booking->public_id,
                ],
            ]),
        ]);

        $this->get(route('booking.confirm', $booking->public_id).'?session_id=cs_booking_currency')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('paymentVerified', false)
                ->where('booking.status', 'pending_payment')
            );

        $this->assertSame('pending_payment', $booking->fresh()->status);
    }

    public function test_mercadopago_webhook_confirms_booking(): void
    {
        config(['mercadopago.access_token' => 'TEST-token', 'mercadopago.webhook_secret' => '']);

        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'mercadopago',
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/payments/999' => Http::response([
                'id' => 999,
                'status' => 'approved',
                'external_reference' => 'bkg_'.$booking->public_id,
            ], 200),
        ]);

        $this->postJson(route('webhooks.mercadopago').'?topic=payment&id=999')
            ->assertNoContent();

        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('approved', $booking->fresh()->mercadopago_status);
    }

    public function test_stripe_webhook_confirms_booking(): void
    {
        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_test',
        ]);

        $payload = [
            'id' => 'evt_test_stripe_confirm',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test',
                    'client_reference_id' => 'bkg_'.$booking->public_id,
                    'amount_total' => 300000,
                    'currency' => 'mxn',
                    'payment_status' => 'paid',
                    'payment_intent' => ['id' => 'pi_test', 'status' => 'succeeded'],
                    'metadata' => [
                        'content_booking_id' => (string) $booking->id,
                        'content_booking_public_id' => $booking->public_id,
                    ],
                ],
            ],
        ];

        $this->postSignedStripeWebhook($payload)->assertNoContent();

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('cs_test', $booking->stripe_checkout_session_id);
        $this->assertNotNull($booking->paid_at);
    }

    public function test_stripe_webhook_is_idempotent(): void
    {
        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_dup',
        ]);

        $payload = [
            'id' => 'evt_idempotent_test',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_dup',
                    'client_reference_id' => 'bkg_'.$booking->public_id,
                    'amount_total' => 300000,
                    'currency' => 'mxn',
                    'payment_status' => 'paid',
                    'payment_intent' => ['id' => 'pi_dup', 'status' => 'succeeded'],
                    'metadata' => [
                        'content_booking_id' => (string) $booking->id,
                        'content_booking_public_id' => $booking->public_id,
                    ],
                ],
            ],
        ];

        $this->postSignedStripeWebhook($payload)->assertNoContent();
        $this->postSignedStripeWebhook($payload)->assertNoContent();

        $this->assertSame(1, \App\Models\StripeWebhookEvent::where('event_id', 'evt_idempotent_test')->count());
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_retry_payment_for_pending_booking(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_retry_123',
                'url' => 'https://checkout.stripe.com/pay/cs_retry_123',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending',
            'payment_provider' => 'stripe',
        ]);

        $this->post(route('booking.retry', $booking->public_id))
            ->assertRedirect('https://checkout.stripe.com/pay/cs_retry_123');
    }

    public function test_confirmed_booking_dispatches_post_payment_emails(): void
    {
        Bus::fake([
            SendContentBookingReceiptEmailJob::class,
            SendContentBookingConfirmationEmailJob::class,
            SendCustomerPortalAccessEmailJob::class,
        ]);

        $slot = $this->createAvailableSlot();

        $customer = Customer::create([
            'name' => 'Cliente Email',
            'email' => 'emails@example.com',
            'status' => 'prospect',
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'customer_id' => $customer->id,
            'client_name' => 'Cliente Email',
            'client_email' => 'emails@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        app(ContentBookingPaymentService::class)->applyStatusTransition($booking, 'confirmed', [
            'source' => 'test',
        ]);

        Bus::assertDispatched(SendContentBookingReceiptEmailJob::class);
        Bus::assertDispatched(SendContentBookingConfirmationEmailJob::class);
        Bus::assertDispatched(SendCustomerPortalAccessEmailJob::class);

        $booking->refresh();
        $this->assertTrue(data_get($booking->metadata, 'receipt_email_sent'));
        $this->assertTrue(data_get($booking->metadata, 'confirmation_email_sent'));
        $this->assertTrue(data_get($booking->metadata, 'portal_access_email_sent'));
        $this->assertSame('customer', $customer->fresh()->status);
    }

    public function test_payment_service_releases_slot_on_failure(): void
    {
        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'mercadopago',
        ]);

        app(ContentBookingPaymentService::class)->releaseSlotIfFailed($booking, 'failed');

        $this->assertSame('failed', $booking->fresh()->status);
        $this->assertSame(0, $slot->fresh()->booked_count);
    }
}
