<?php

namespace App\Services;

use App\Jobs\SendCustomerPortalAccessEmailJob;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\TicketOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CustomerPortalAccessService
{
    public function upsertCustomerFromBooking(array $bookingData, array $context = []): Customer
    {
        $email = trim((string) Arr::get($bookingData, 'client_email'));

        $customer = Customer::firstOrNew(['email' => $email]);
        $metadata = $customer->metadata ?? [];
        $bookingHistory = $metadata['content_bookings'] ?? [];
        $bookingHistory[] = array_filter([
            'captured_at' => now()->toIso8601String(),
            'utm_source' => Arr::get($context, 'utm_source'),
            'utm_medium' => Arr::get($context, 'utm_medium'),
            'utm_campaign' => Arr::get($context, 'utm_campaign'),
        ]);

        $customer->fill([
            'name' => Arr::get($bookingData, 'client_name'),
            'phone' => Arr::get($bookingData, 'client_phone'),
            'whatsapp' => Arr::get($bookingData, 'client_phone'),
            'instagram_handle' => Arr::get($bookingData, 'client_instagram'),
            'source' => 'content_booking',
            'status' => $customer->exists && $customer->status === 'customer' ? 'customer' : 'prospect',
            'subscribed_whatsapp' => ! empty(Arr::get($bookingData, 'client_phone')),
            'subscribed_sms' => ! empty(Arr::get($bookingData, 'client_phone')),
            'utm_source' => $customer->utm_source ?: Arr::get($context, 'utm_source'),
            'utm_medium' => $customer->utm_medium ?: Arr::get($context, 'utm_medium'),
            'utm_campaign' => $customer->utm_campaign ?: Arr::get($context, 'utm_campaign'),
            'utm_term' => $customer->utm_term ?: Arr::get($context, 'utm_term'),
            'utm_content' => $customer->utm_content ?: Arr::get($context, 'utm_content'),
            'metadata' => array_merge($metadata, [
                'content_bookings' => array_slice($bookingHistory, -10),
            ]),
            'last_interaction_at' => now(),
        ]);

        $customer->save();

        return $customer;
    }

    public function ensurePortalAccess(Customer $customer): ?string
    {
        if (! empty($customer->password)) {
            return null;
        }

        $password = Str::random(10);

        $customer->forceFill([
            'password' => $password,
        ])->save();

        return $password;
    }

    public function ensurePortalAccessAndNotify(
        Customer $customer,
        ?TicketOrder $order = null,
        ?ContentBooking $booking = null,
    ): bool {
        $password = $this->ensurePortalAccess($customer);

        if (! $password) {
            return false;
        }

        SendCustomerPortalAccessEmailJob::dispatchAfterResponse(
            $customer->fresh(),
            $password,
            $order,
            $booking,
        );

        return true;
    }
}
