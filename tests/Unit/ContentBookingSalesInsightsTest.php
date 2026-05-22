<?php

namespace Tests\Unit;

use App\Models\ContentBooking;
use App\Support\ContentBookingSalesInsights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ContentBookingSalesInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_with_paid_at_counts_in_period_stats(): void
    {
        config(['analytics.dashboard_days' => 30]);

        ContentBooking::create([
            'public_id' => 'paid-at-test',
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'client_name' => 'Cliente',
            'client_email' => 'paid@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now()->subDays(2),
            'payment_provider' => 'stripe',
        ]);

        $stats = ContentBookingSalesInsights::periodStats();

        $this->assertSame(1, $stats['orders']);
        $this->assertSame(3000.0, $stats['revenue']);
    }

    public function test_confirmed_without_paid_at_falls_back_to_created_at(): void
    {
        config(['analytics.dashboard_days' => 30]);

        $booking = ContentBooking::create([
            'public_id' => 'created-at-test',
            'service_type' => ContentBooking::SERVICE_DJ_SET,
            'client_name' => 'Cliente DJ',
            'client_email' => 'dj@example.com',
            'client_phone' => '529841234567',
            'amount' => 12000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'payment_provider' => 'internal',
        ]);

        $booking->forceFill([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ])->save();

        $stats = ContentBookingSalesInsights::periodStats();

        $this->assertSame(1, $stats['orders']);
        $this->assertSame(12000.0, $stats['revenue']);
        $this->assertContains($booking->id, ContentBookingSalesInsights::confirmedSalesQuery()->pluck('id'));
    }

    public function test_revenue_by_provider_sums_amounts(): void
    {
        config(['analytics.dashboard_days' => 30]);

        ContentBooking::create([
            'public_id' => 'stripe-sale',
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'client_name' => 'A',
            'client_email' => 'a@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now()->subDay(),
            'payment_provider' => 'stripe',
        ]);

        ContentBooking::create([
            'public_id' => 'internal-sale',
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'client_name' => 'B',
            'client_email' => 'b@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now()->subDay(),
            'payment_provider' => 'internal',
        ]);

        $byProvider = ContentBookingSalesInsights::revenueByProvider();

        $this->assertSame(3000.0, $byProvider['stripe']);
        $this->assertSame(3000.0, $byProvider['internal']);
    }

    public function test_revenue_by_service_groups_amounts(): void
    {
        config(['analytics.dashboard_days' => 30]);

        ContentBooking::create([
            'public_id' => 'session-sale',
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'client_name' => 'Sesión',
            'client_email' => 'session@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now()->subDay(),
        ]);

        ContentBooking::create([
            'public_id' => 'dj-sale',
            'service_type' => ContentBooking::SERVICE_DJ_SET,
            'client_name' => 'DJ',
            'client_email' => 'dj@example.com',
            'client_phone' => '529841234567',
            'amount' => 12000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now()->subDay(),
        ]);

        $byService = ContentBookingSalesInsights::revenueByService();

        $this->assertSame(3000.0, $byService[ContentBooking::SERVICE_CONTENT_SESSION]);
        $this->assertSame(12000.0, $byService[ContentBooking::SERVICE_DJ_SET]);
    }

    public function test_old_confirmed_booking_outside_period_is_excluded(): void
    {
        config(['analytics.dashboard_days' => 7]);

        $booking = ContentBooking::create([
            'public_id' => 'old-sale',
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'client_name' => 'Old',
            'client_email' => 'old@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => Carbon::parse('2020-01-01 12:00:00'),
        ]);

        $booking->forceFill([
            'created_at' => Carbon::parse('2020-01-01 12:00:00'),
            'updated_at' => Carbon::parse('2020-01-01 12:00:00'),
        ])->save();

        $stats = ContentBookingSalesInsights::periodStats();

        $this->assertSame(0, $stats['orders']);
        $this->assertSame(0.0, $stats['revenue']);
    }
}
