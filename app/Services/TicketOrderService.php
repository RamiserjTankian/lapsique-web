<?php

namespace App\Services;

use App\Jobs\SendTicketAccessEmailJob;
use App\Jobs\SendTicketOrderConfirmationJob;
use App\Jobs\SendTicketProspectEmailJob;
use App\Models\Customer;
use App\Models\CustomerEventBalance;
use App\Models\Event;
use App\Models\GuestListInviteLink;
use App\Models\TicketAttendee;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketProduct;
use App\Services\Meta\MetaConversionsApiService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TicketOrderService
{
    public function createOrder(Event $event, array $items, array $buyer, array $context = []): TicketOrder
    {
        if (empty($items)) {
            throw new RuntimeException('Selecciona al menos un ticket.');
        }

        return DB::transaction(function () use ($event, $items, $buyer, $context) {
            $productIds = array_keys($items);
            $products = TicketProduct::query()
                ->where('event_id', $event->id)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $fee = 0;
            $itemsQuantity = 0;
            $attendeesExpected = 0;
            $orderItems = [];

            foreach ($items as $productId => $quantity) {
                $quantity = (int) $quantity;
                $product = $products->get($productId);

                if (! $product) {
                    throw new RuntimeException('Ticket no disponible para este evento.');
                }

                if (! $product->canReserve($quantity)) {
                    throw new RuntimeException("{$product->name} no está disponible en la cantidad solicitada.");
                }

                if ($product->stock !== null) {
                    $product->increment('reserved_count', $quantity);
                }

                $unitPrice = (float) $product->price;
                $unitBasePrice = (float) $product->base_price;
                $unitFee = round($unitPrice - $unitBasePrice, 2);
                $lineSubtotal = round($unitBasePrice * $quantity, 2);
                $lineFee = round($unitFee * $quantity, 2);
                $lineTotal = round($lineSubtotal + $lineFee, 2);

                $subtotal += $lineSubtotal;
                $fee += $lineFee;
                $itemsQuantity += $quantity;
                $attendeesExpected += $quantity * max($product->access_units, 1);

                $orderItems[] = [
                    'ticket_product_id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'access_units' => max($product->access_units, 1),
                    'check_in_limit' => max($product->check_in_limit, 1),
                    'metadata' => [
                        'unit_base_price' => $unitBasePrice,
                        'unit_fee' => $unitFee,
                        'line_subtotal' => $lineSubtotal,
                        'line_fee' => $lineFee,
                    ],
                ];
            }

            $customer = $this->resolveBuyerCustomer($buyer);

            $metadata = Arr::get($context, 'metadata', []);
            if (! empty($context['invite_token'])) {
                $metadata['invite_token'] = $context['invite_token'];
            }
            $metadata['reservation_status'] = 'reserved';

            $currency = $products->first()?->currency ?? config('mercadopago.currency', 'MXN');

            $order = TicketOrder::create([
                'event_id' => $event->id,
                'customer_id' => $customer?->id,
                'rp_id' => Arr::get($context, 'rp_id'),
                'invite_link_id' => Arr::get($context, 'invite_link_id'),
                'status' => 'pending',
                'payment_provider' => Arr::get($context, 'payment_provider', 'mercadopago'),
                'currency' => Arr::get($context, 'currency', $currency),
                'subtotal' => $subtotal,
                'fee' => $fee,
                'total' => round($subtotal + $fee, 2),
                'items_quantity' => $itemsQuantity,
                'attendees_expected' => $attendeesExpected,
                'attendees_registered' => 0,
                'buyer_name' => Arr::get($buyer, 'name'),
                'buyer_email' => Arr::get($buyer, 'email'),
                'buyer_phone' => Arr::get($buyer, 'phone'),
                'buyer_whatsapp' => Arr::get($buyer, 'whatsapp'),
                'buyer_instagram' => Arr::get($buyer, 'instagram_handle'),
                'utm_source' => Arr::get($context, 'utm_source'),
                'utm_medium' => Arr::get($context, 'utm_medium'),
                'utm_campaign' => Arr::get($context, 'utm_campaign'),
                'utm_term' => Arr::get($context, 'utm_term'),
                'utm_content' => Arr::get($context, 'utm_content'),
                'ip_address' => Arr::get($context, 'ip_address'),
                'user_agent' => Arr::get($context, 'user_agent'),
                'metadata' => $metadata,
            ]);

            foreach ($orderItems as $item) {
                $item['ticket_order_id'] = $order->id;
                TicketOrderItem::create($item);
            }

            $this->markCustomerAsProspect($customer, $order, $context);
            $this->sendProspectEmail($order);
            $customer?->incrementLeadScore(15);
            $customer?->updateLastInteraction();

            if ($customer) {
                app(CustomerAnalyticsAttributionService::class)->identify(
                    $customer,
                    Arr::get($metadata, 'analytics_visitor_id'),
                    Arr::get($metadata, 'analytics_session_id'),
                    'ticket_order',
                );
            }

            app(CustomerJourneyInsightsService::class)->clearCache();

            return $order->fresh(['items']);
        });
    }

    public function syncPayment(TicketOrder $order, array $payment): TicketOrder
    {
        return DB::transaction(function () use ($order, $payment) {
            $order->refresh();

            $status = (string) data_get($payment, 'status');
            $payload = [
                'mp_payment_id' => (string) data_get($payment, 'id'),
                'mp_status' => $status,
                'mp_status_detail' => (string) data_get($payment, 'status_detail'),
                'mp_payment_method' => (string) data_get($payment, 'payment_method_id'),
                'mp_merchant_order_id' => (string) data_get($payment, 'order.id'),
                'mp_external_reference' => (string) data_get($payment, 'external_reference'),
                'payment_provider' => 'mercadopago',
            ];

            if ($status === 'approved') {
                if ($order->status !== 'paid') {
                    $this->commitReservation($order);
                    $order->markAsPaid($payload);
                    $this->ensureAttendees($order);
                    $this->hydratePrefilledAttendees($order);
                    $this->promoteCustomerFromOrder($order);
                    $this->creditCustomerBalance($order);
                    $this->sendCustomerPortalAccess($order);
                    $this->sendBuyerConfirmation($order);
                    app(CustomerJourneyInsightsService::class)->clearCache();
                } else {
                    $order->fill($payload)->save();
                    app(CustomerJourneyInsightsService::class)->clearCache();
                }

                return $order->fresh();
            }

            if (in_array($status, ['pending', 'in_process'], true)) {
                $order->markAsPending($payload);
                app(MetaConversionsApiService::class)->sendPaymentPendingForTicketOrder($order->fresh(['event', 'items']));
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            if ($status === 'cancelled') {
                $this->releaseReservation($order);
                $order->markAsCancelled($payload);
                app(MetaConversionsApiService::class)->sendPaymentFailedForTicketOrder($order->fresh(['event', 'items']));
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            if ($status === 'rejected') {
                $this->releaseReservation($order);
                $order->markAsFailed((string) data_get($payment, 'status_detail'), $payload);
                app(MetaConversionsApiService::class)->sendPaymentFailedForTicketOrder($order->fresh(['event', 'items']));
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            if (in_array($status, ['refunded', 'charged_back'], true)) {
                $this->revertCustomerBalance($order);
                $order->markAsRefunded($payload);
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            $order->fill($payload)->save();

            return $order->fresh();
        });
    }

    public function syncStripeSession(TicketOrder $order, array $session): TicketOrder
    {
        return DB::transaction(function () use ($order, $session) {
            $order->refresh();

            $paymentIntent = data_get($session, 'payment_intent', []);
            $paymentStatus = (string) data_get($session, 'payment_status');
            $intentStatus = is_array($paymentIntent) ? (string) data_get($paymentIntent, 'status') : '';
            $paymentIntentId = is_array($paymentIntent) ? (string) data_get($paymentIntent, 'id') : (string) $paymentIntent;

            $payload = [
                'payment_provider' => 'stripe',
                'stripe_session_id' => (string) data_get($session, 'id'),
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_status' => $intentStatus ?: $paymentStatus,
                'stripe_payment_method' => is_array($paymentIntent) ? (string) data_get($paymentIntent, 'payment_method') : null,
            ];

            if ($paymentStatus === 'paid' || $intentStatus === 'succeeded') {
                if ($order->status !== 'paid') {
                    $this->commitReservation($order);
                    $order->markAsPaid($payload);
                    $this->ensureAttendees($order);
                    $this->hydratePrefilledAttendees($order);
                    $this->promoteCustomerFromOrder($order);
                    $this->creditCustomerBalance($order);
                    $this->sendCustomerPortalAccess($order);
                    $this->sendBuyerConfirmation($order);
                    app(CustomerJourneyInsightsService::class)->clearCache();
                } else {
                    $order->fill($payload)->save();
                    app(CustomerJourneyInsightsService::class)->clearCache();
                }

                return $order->fresh();
            }

            if (in_array($intentStatus, ['processing', 'requires_action'], true) || $paymentStatus === 'unpaid') {
                $order->markAsPending($payload);
                app(MetaConversionsApiService::class)->sendPaymentPendingForTicketOrder($order->fresh(['event', 'items']));
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            if (in_array($intentStatus, ['canceled', 'requires_payment_method'], true)) {
                $this->releaseReservation($order);
                $order->markAsFailed($intentStatus ?: $paymentStatus, $payload);
                app(MetaConversionsApiService::class)->sendPaymentFailedForTicketOrder($order->fresh(['event', 'items']));
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            $order->fill($payload)->save();

            return $order->fresh();
        });
    }

    public function syncStripePaymentIntent(TicketOrder $order, array $intent): TicketOrder
    {
        return DB::transaction(function () use ($order, $intent) {
            $order->refresh();

            $status = (string) data_get($intent, 'status');

            $payload = [
                'payment_provider' => 'stripe',
                'stripe_payment_intent_id' => (string) data_get($intent, 'id'),
                'stripe_status' => $status,
                'stripe_payment_method' => (string) data_get($intent, 'payment_method'),
            ];

            if ($status === 'succeeded') {
                if ($order->status !== 'paid') {
                    $this->commitReservation($order);
                    $order->markAsPaid($payload);
                    $this->ensureAttendees($order);
                    $this->hydratePrefilledAttendees($order);
                    $this->promoteCustomerFromOrder($order);
                    $this->creditCustomerBalance($order);
                    $this->sendCustomerPortalAccess($order);
                    $this->sendBuyerConfirmation($order);
                    app(CustomerJourneyInsightsService::class)->clearCache();
                } else {
                    $order->fill($payload)->save();
                    app(CustomerJourneyInsightsService::class)->clearCache();
                }

                return $order->fresh();
            }

            if (in_array($status, ['processing', 'requires_action', 'requires_capture'], true)) {
                $order->markAsPending($payload);
                app(MetaConversionsApiService::class)->sendPaymentPendingForTicketOrder($order->fresh(['event', 'items']));
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            if (in_array($status, ['canceled', 'requires_payment_method'], true)) {
                $this->releaseReservation($order);
                $order->markAsFailed($status, $payload);
                app(MetaConversionsApiService::class)->sendPaymentFailedForTicketOrder($order->fresh(['event', 'items']));
                app(CustomerJourneyInsightsService::class)->clearCache();

                return $order->fresh();
            }

            $order->fill($payload)->save();

            return $order->fresh();
        });
    }

    public function syncStripeRefund(TicketOrder $order, array $charge): TicketOrder
    {
        return DB::transaction(function () use ($order, $charge) {
            $order->refresh();

            $payload = [
                'payment_provider' => 'stripe',
                'stripe_payment_intent_id' => (string) data_get($charge, 'payment_intent') ?: $order->stripe_payment_intent_id,
                'stripe_status' => 'refunded',
            ];

            if ($order->status === 'paid') {
                $this->rollbackCommittedOrder($order);
                $this->revertCustomerBalance($order);
                $this->cancelAttendees($order);
            } elseif ($order->status === 'pending') {
                $this->releaseReservation($order);
            }

            $order->markAsRefunded($payload);
            app(CustomerJourneyInsightsService::class)->clearCache();

            return $order->fresh(['items', 'attendees']);
        });
    }

    public function expireStripeCheckout(TicketOrder $order, array $payload = []): TicketOrder
    {
        return DB::transaction(function () use ($order, $payload) {
            $order->refresh();

            if (! in_array($order->status, ['paid', 'refunded', 'cancelled'], true)) {
                $this->releaseReservation($order);
            }

            $order->markAsCancelled(array_merge([
                'payment_provider' => 'stripe',
            ], $payload));

            return $order->fresh(['items']);
        });
    }

    public function failStripeCheckout(TicketOrder $order, array $payload = []): TicketOrder
    {
        return DB::transaction(function () use ($order, $payload) {
            $order->refresh();

            if (! in_array($order->status, ['paid', 'refunded', 'cancelled'], true)) {
                $this->releaseReservation($order);
            }

            $reason = (string) ($payload['stripe_status'] ?? 'failed');
            $order->markAsFailed($reason, array_merge([
                'payment_provider' => 'stripe',
            ], $payload));

            return $order->fresh(['items']);
        });
    }

    public function cancelOrderFromAdmin(TicketOrder $order, ?string $reason = null, ?int $adminUserId = null): TicketOrder
    {
        $cancelReason = trim((string) $reason);

        return DB::transaction(function () use ($order, $cancelReason, $adminUserId) {
            $order->refresh();

            if (in_array($order->status, ['cancelled', 'refunded', 'failed'], true)) {
                return $order->fresh();
            }

            if ($order->attendees()->where('status', 'checked_in')->exists()) {
                throw new RuntimeException('No se puede cancelar una venta con asistentes ya ingresados.');
            }

            if ($order->status === 'paid') {
                $this->rollbackCommittedOrder($order);
                $this->revertCustomerBalance($order);
            } elseif ($order->status === 'pending') {
                $this->releaseReservation($order);
            }

            $this->cancelAttendees($order);

            $metadata = $order->metadata ?? [];
            $metadata['admin_cancelled_at'] = now()->toIso8601String();
            $metadata['admin_cancelled_by'] = $adminUserId;
            $metadata['admin_cancelled_reason'] = $cancelReason !== '' ? $cancelReason : 'cancelled_from_panel';

            $order->markAsCancelled([
                'attendees_registered' => 0,
                'metadata' => $metadata,
            ]);

            $this->syncCustomerBalanceLastOrder($order);
            app(AnalyticsInsightsService::class)->clearCachedSnapshots();

            return $order->fresh(['items', 'attendees']);
        });
    }

    public function ensureAttendees(TicketOrder $order): void
    {
        if ($order->attendees()->exists()) {
            return;
        }

        $order->loadMissing(['items', 'event']);

        foreach ($order->items as $item) {
            $count = $item->quantity * max($item->access_units, 1);

            for ($i = 0; $i < $count; $i++) {
                TicketAttendee::create([
                    'ticket_order_id' => $order->id,
                    'ticket_order_item_id' => $item->id,
                    'ticket_product_id' => $item->ticket_product_id,
                    'event_id' => $order->event_id,
                    'rp_id' => $order->rp_id,
                    'invite_link_id' => $order->invite_link_id,
                    'status' => 'pending',
                    'check_in_limit' => max($item->check_in_limit, 1),
                    'check_in_count' => 0,
                ]);
            }
        }
    }

    public function updateAttendeesRegisteredCount(TicketOrder $order): void
    {
        $registered = $order->attendees()
            ->whereIn('status', ['registered', 'checked_in'])
            ->count();

        $order->update(['attendees_registered' => $registered]);
    }

    public function hydratePrefilledAttendees(TicketOrder $order): void
    {
        $metadata = $order->metadata ?? [];
        $prefilled = collect($metadata['prefilled_attendees'] ?? [])
            ->filter(fn ($attendee) => is_array($attendee))
            ->values();

        if ($prefilled->isEmpty()) {
            return;
        }

        $order->loadMissing('attendees');

        $attendees = $order->attendees()
            ->orderBy('id')
            ->get()
            ->values();

        foreach ($prefilled as $index => $data) {
            $attendee = $attendees->get($index);

            if (! $attendee) {
                break;
            }

            $payload = [
                'name' => trim((string) ($data['name'] ?? '')),
                'email' => trim((string) ($data['email'] ?? '')),
                'whatsapp' => trim((string) ($data['whatsapp'] ?? '')),
                'instagram_handle' => trim((string) ($data['instagram_handle'] ?? '')),
            ];

            if ($payload['name'] === '' || $payload['email'] === '' || $payload['whatsapp'] === '') {
                continue;
            }

            $this->registerAttendee($attendee, $payload);
        }

        $metadata['prefilled_attendees_hydrated_at'] = now()->toIso8601String();
        $order->update(['metadata' => $metadata]);
        $this->updateAttendeesRegisteredCount($order);
    }

    protected function resolveBuyerCustomer(array $buyer): ?Customer
    {
        $email = trim((string) Arr::get($buyer, 'email'));

        if ($email === '') {
            return null;
        }

        $customer = Customer::firstOrNew(['email' => $email]);

        $customer->fill([
            'name' => Arr::get($buyer, 'name'),
            'phone' => Arr::get($buyer, 'phone'),
            'whatsapp' => Arr::get($buyer, 'whatsapp'),
            'instagram_handle' => Arr::get($buyer, 'instagram_handle'),
            'source' => 'ticketing',
            'status' => $customer->exists && $customer->status === 'customer' ? 'customer' : 'prospect',
            'subscribed_whatsapp' => ! empty(Arr::get($buyer, 'whatsapp')),
            'subscribed_sms' => ! empty(Arr::get($buyer, 'whatsapp')),
            'last_interaction_at' => now(),
        ]);

        $customer->save();

        return $customer;
    }

    protected function registerAttendee(TicketAttendee $attendee, array $data): void
    {
        $customer = Customer::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'whatsapp' => $data['whatsapp'],
                'phone' => $data['whatsapp'],
                'instagram_handle' => $data['instagram_handle'] ?: null,
                'source' => 'ticketing',
                'subscribed_whatsapp' => true,
                'subscribed_sms' => true,
                'last_interaction_at' => now(),
            ]
        );

        $wasRegistered = in_array($attendee->status, ['registered', 'checked_in'], true);

        $attendee->update([
            'customer_id' => $customer->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'],
            'phone' => $data['whatsapp'],
            'instagram_handle' => $data['instagram_handle'] ?: null,
            'status' => 'registered',
            'registered_at' => $attendee->registered_at ?? now(),
        ]);

        if (! $wasRegistered) {
            SendTicketAccessEmailJob::dispatchAfterResponse($attendee);
        }
    }

    protected function commitReservation(TicketOrder $order): void
    {
        $metadata = $order->metadata ?? [];
        if (($metadata['reservation_status'] ?? null) !== 'reserved') {
            return;
        }

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product || $product->stock === null) {
                continue;
            }

            $quantity = $item->quantity;
            $product->decrement('reserved_count', min($quantity, $product->reserved_count));
            $product->increment('sold_count', $quantity);
        }

        $metadata['reservation_status'] = 'committed';
        $order->update(['metadata' => $metadata]);
    }

    protected function rollbackCommittedOrder(TicketOrder $order): void
    {
        $metadata = $order->metadata ?? [];
        $reservationStatus = $metadata['reservation_status'] ?? null;

        if (! in_array($reservationStatus, ['committed', 'reserved'], true)) {
            return;
        }

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || $product->stock === null) {
                continue;
            }

            $quantity = (int) $item->quantity;

            if ($reservationStatus === 'committed') {
                $product->decrement('sold_count', min($quantity, (int) $product->sold_count));

                continue;
            }

            $product->decrement('reserved_count', min($quantity, (int) $product->reserved_count));
        }

        $metadata['reservation_status'] = 'cancelled_by_admin';
        $order->update(['metadata' => $metadata]);
    }

    protected function releaseReservation(TicketOrder $order): void
    {
        $metadata = $order->metadata ?? [];
        if (($metadata['reservation_status'] ?? null) !== 'reserved') {
            return;
        }

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product || $product->stock === null) {
                continue;
            }

            $quantity = $item->quantity;
            $product->decrement('reserved_count', min($quantity, $product->reserved_count));
        }

        $metadata['reservation_status'] = 'released';
        $order->update(['metadata' => $metadata]);
    }

    protected function sendBuyerConfirmation(TicketOrder $order): void
    {
        $metadata = $order->metadata ?? [];

        if (! empty($metadata['buyer_email_sent'])) {
            return;
        }

        if (! $order->buyer_email) {
            return;
        }

        try {
            SendTicketOrderConfirmationJob::dispatchAfterResponse($order);
            $metadata['buyer_email_sent'] = true;
            $order->update(['metadata' => $metadata]);
        } catch (\Throwable $exception) {
            Log::warning('Ticket order confirmation email failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function sendProspectEmail(TicketOrder $order): void
    {
        $metadata = $order->metadata ?? [];

        if (! empty($metadata['prospect_email_sent']) || ! $order->buyer_email) {
            return;
        }

        try {
            SendTicketProspectEmailJob::dispatchAfterResponse($order);
            $metadata['prospect_email_sent'] = true;
            $order->update(['metadata' => $metadata]);
        } catch (\Throwable $exception) {
            Log::warning('Ticket prospect email failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function markCustomerAsProspect(?Customer $customer, TicketOrder $order, array $context): void
    {
        if (! $customer) {
            return;
        }

        $metadata = $customer->metadata ?? [];
        $prospects = $metadata['ticketing_prospects'] ?? [];
        $eventKey = (string) $order->event_id;

        $prospects[$eventKey] = array_filter([
            'event_id' => $order->event_id,
            'event_title' => $order->event?->title,
            'ticket_order_id' => $order->id,
            'ticket_order_public_id' => $order->public_id,
            'utm_source' => $context['utm_source'] ?? null,
            'registered_at' => now()->toIso8601String(),
        ]);

        $customer->update([
            'status' => $customer->status === 'customer' ? 'customer' : 'prospect',
            'source' => $customer->source ?: 'ticketing',
            'utm_source' => $customer->utm_source ?: Arr::get($context, 'utm_source'),
            'utm_medium' => $customer->utm_medium ?: Arr::get($context, 'utm_medium'),
            'utm_campaign' => $customer->utm_campaign ?: Arr::get($context, 'utm_campaign'),
            'utm_term' => $customer->utm_term ?: Arr::get($context, 'utm_term'),
            'utm_content' => $customer->utm_content ?: Arr::get($context, 'utm_content'),
            'ip_address' => $customer->ip_address ?: Arr::get($context, 'ip_address'),
            'user_agent' => $customer->user_agent ?: Arr::get($context, 'user_agent'),
            'metadata' => array_merge($metadata, [
                'ticketing_prospects' => $prospects,
            ]),
        ]);
    }

    protected function promoteCustomerFromOrder(TicketOrder $order): void
    {
        $order->loadMissing('customer', 'event');

        $customer = $order->customer;

        if (! $customer) {
            return;
        }

        $metadata = $customer->metadata ?? [];

        $customer->update([
            'status' => 'customer',
            'source' => 'ticketing',
            'last_interaction_at' => now(),
            'metadata' => array_merge($metadata, [
                'last_paid_ticket_order_id' => $order->id,
                'last_paid_event_id' => $order->event_id,
                'last_paid_at' => $order->paid_at?->toIso8601String() ?? now()->toIso8601String(),
            ]),
        ]);

        $customer->incrementLeadScore(35);
    }

    protected function creditCustomerBalance(TicketOrder $order): void
    {
        $order->loadMissing('customer', 'event');

        if (! $order->customer) {
            return;
        }

        $metadata = $order->metadata ?? [];

        if (! empty($metadata['customer_balance_applied_at'])) {
            return;
        }

        $creditedAmount = round((float) $order->subtotal, 2);

        if ($creditedAmount <= 0) {
            return;
        }

        $balance = CustomerEventBalance::query()->firstOrCreate(
            [
                'customer_id' => $order->customer_id,
                'event_id' => $order->event_id,
            ],
            [
                'currency' => $order->currency,
                'balance' => 0,
                'total_credited' => 0,
                'total_consumed' => 0,
            ]
        );

        $balance->update([
            'currency' => $order->currency,
            'balance' => round(((float) $balance->balance) + $creditedAmount, 2),
            'total_credited' => round(((float) $balance->total_credited) + $creditedAmount, 2),
            'last_ticket_order_id' => $order->id,
            'metadata' => array_merge($balance->metadata ?? [], [
                'last_credit_order_id' => $order->id,
                'last_credit_order_public_id' => $order->public_id,
                'last_credit_at' => now()->toIso8601String(),
            ]),
        ]);

        $metadata['customer_balance_applied_at'] = now()->toIso8601String();
        $metadata['customer_balance_amount'] = $creditedAmount;
        $order->update(['metadata' => $metadata]);
    }

    protected function revertCustomerBalance(TicketOrder $order): void
    {
        $order->loadMissing('customer');

        $metadata = $order->metadata ?? [];
        $creditedAmount = round((float) ($metadata['customer_balance_amount'] ?? 0), 2);

        if (! $order->customer || empty($metadata['customer_balance_applied_at']) || $creditedAmount <= 0) {
            return;
        }

        $balance = CustomerEventBalance::query()
            ->where('customer_id', $order->customer_id)
            ->where('event_id', $order->event_id)
            ->first();

        if ($balance) {
            $balance->update([
                'balance' => max(round(((float) $balance->balance) - $creditedAmount, 2), 0),
                'last_ticket_order_id' => $order->id,
                'metadata' => array_merge($balance->metadata ?? [], [
                    'last_reverted_order_id' => $order->id,
                    'last_reverted_at' => now()->toIso8601String(),
                ]),
            ]);
        }

        unset($metadata['customer_balance_applied_at'], $metadata['customer_balance_amount']);
        $order->update(['metadata' => $metadata]);
    }

    protected function cancelAttendees(TicketOrder $order): void
    {
        $order->attendees()
            ->whereIn('status', ['pending', 'registered'])
            ->update([
                'status' => 'cancelled',
            ]);
    }

    protected function syncCustomerBalanceLastOrder(TicketOrder $order): void
    {
        if (! $order->customer_id) {
            return;
        }

        $balance = CustomerEventBalance::query()
            ->where('customer_id', $order->customer_id)
            ->where('event_id', $order->event_id)
            ->first();

        if (! $balance) {
            return;
        }

        $lastPaidOrderId = TicketOrder::query()
            ->where('customer_id', $order->customer_id)
            ->where('event_id', $order->event_id)
            ->where('status', 'paid')
            ->latest('paid_at')
            ->value('id');

        $balance->update([
            'last_ticket_order_id' => $lastPaidOrderId,
        ]);
    }

    protected function sendCustomerPortalAccess(TicketOrder $order): void
    {
        try {
            $order->loadMissing('customer');

            if (! $order->customer) {
                return;
            }

            app(CustomerPortalAccessService::class)->ensurePortalAccessAndNotify(
                $order->customer->loadMissing('ticketOrders'),
                order: $order,
            );
        } catch (\Throwable $exception) {
            Log::warning('Customer portal access email failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function resolveInviteLink(?string $token): ?GuestListInviteLink
    {
        if (! $token) {
            return null;
        }

        $link = GuestListInviteLink::query()
            ->where('token', $token)
            ->first();

        if (! $link || ! $link->is_active) {
            return null;
        }

        if ($link->expires_at && $link->expires_at->isPast()) {
            return null;
        }

        return $link;
    }
}
