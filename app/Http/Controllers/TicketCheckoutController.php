<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TicketOrder;
use App\Services\MercadoPagoService;
use App\Services\Meta\MetaConversionsApiService;
use App\Services\StripeService;
use App\Services\TicketOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TicketCheckoutController extends Controller
{
    public function show(Request $request, Event $event): RedirectResponse
    {
        $query = $request->getQueryString();
        $target = route('events.show', $event) . ($query ? ('?' . $query) : '') . '#tickets';

        return redirect()->to($target);
    }

    public function checkout(Request $request, Event $event, TicketOrderService $orderService, MercadoPagoService $mercadoPago, StripeService $stripe): RedirectResponse
    {
        $validated = $request->validate([
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_whatsapp' => ['required', 'string', 'max:30'],
            'buyer_instagram' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'attendees' => ['nullable', 'array'],
            'attendees.*.name' => ['nullable', 'string', 'max:255'],
            'attendees.*.email' => ['nullable', 'email', 'max:255'],
            'attendees.*.whatsapp' => ['nullable', 'string', 'max:30'],
            'attendees.*.instagram_handle' => ['nullable', 'string', 'max:255'],
            'invite_token' => ['nullable', 'string', 'max:80'],
            'payment_provider' => ['nullable', 'string', 'in:mercadopago,stripe'],
            'checkout_event_id' => ['nullable', 'string', 'max:80'],
            'consent_terms' => ['accepted'],
        ]);
        $filled = static fn ($value): bool => $value !== null && $value !== '';

        $items = collect($validated['items'])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->all();

        if (empty($items)) {
            return back()->withErrors(['items' => 'Selecciona al menos un ticket.'])->withInput();
        }

        $products = $event->ticketProducts()
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        $accessCount = collect($items)->reduce(function (int $carry, $quantity, $productId) use ($products): int {
            $product = $products->get((int) $productId);

            return $carry + (((int) $quantity) * max((int) ($product?->access_units ?? 1), 1));
        }, 0);

        $containsTable = $products->contains(fn ($product) => $product->category === 'table');

        $inviteToken = $validated['invite_token'] ?? $request->query('invite');
        $inviteLink = $orderService->resolveInviteLink($inviteToken);

        $buyer = [
            'name' => $validated['buyer_name'],
            'email' => $validated['buyer_email'],
            'whatsapp' => $validated['buyer_whatsapp'],
            'instagram_handle' => $validated['buyer_instagram'] ?? null,
            'phone' => $validated['buyer_whatsapp'],
        ];

        $additionalAttendees = collect($validated['attendees'] ?? [])
            ->map(function (array $attendee): array {
                return [
                    'name' => trim((string) ($attendee['name'] ?? '')),
                    'email' => trim((string) ($attendee['email'] ?? '')),
                    'whatsapp' => trim((string) ($attendee['whatsapp'] ?? '')),
                    'instagram_handle' => trim((string) ($attendee['instagram_handle'] ?? '')),
                ];
            })
            ->filter(fn (array $attendee) => collect($attendee)->contains(fn ($value) => $value !== ''))
            ->values();

        if ($accessCount > 1 && ! $containsTable) {
            $requiredAdditional = $accessCount - 1;

            if ($additionalAttendees->count() !== $requiredAdditional) {
                return back()
                    ->withErrors([
                        'attendees' => "Debes registrar {$requiredAdditional} integrante(s) antes de continuar al pago.",
                    ])
                    ->withInput();
            }

            foreach ($additionalAttendees as $attendee) {
                if ($attendee['name'] === '' || $attendee['email'] === '' || $attendee['whatsapp'] === '') {
                    return back()
                        ->withErrors([
                            'attendees' => 'Completa nombre, email y WhatsApp de todos los integrantes antes de pagar.',
                        ])
                        ->withInput();
                }
            }
        }

        $prefilledAttendees = collect([$buyer])
            ->merge(
                $containsTable
                    ? $additionalAttendees->filter(fn (array $attendee) => $attendee['name'] !== '' && $attendee['email'] !== '' && $attendee['whatsapp'] !== '')
                    : $additionalAttendees
            )
            ->values()
            ->all();

        $context = [
            'rp_id' => $inviteLink?->rp_id,
            'invite_link_id' => $inviteLink?->id,
            'invite_token' => $inviteToken,
            'utm_source' => $request->input('utm_source') ?: $request->query('utm_source'),
            'utm_medium' => $request->input('utm_medium') ?: $request->query('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign') ?: $request->query('utm_campaign'),
            'utm_term' => $request->input('utm_term') ?: $request->query('utm_term'),
            'utm_content' => $request->input('utm_content') ?: $request->query('utm_content'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => array_filter([
                'invite_dj_id' => $inviteLink?->dj_id,
                'invite_name' => $inviteLink?->name,
                'prefilled_attendees' => $prefilledAttendees,
                'prefilled_attendees_mode' => $containsTable ? 'partial' : 'strict',
                'contains_table' => $containsTable,
                'landing_page' => $request->input('landing_page'),
                'landing_url' => $request->input('landing_url'),
                'page_type' => $request->input('page_type'),
                'page_name' => $request->input('page_name'),
                'referrer' => $request->input('referrer'),
                'analytics_visitor_id' => $request->input('analytics_visitor_id'),
                'analytics_session_id' => $request->input('analytics_session_id'),
                'checkout_event_id' => $request->input('checkout_event_id'),
                'fbp' => $request->input('fbp'),
                'fbc' => $request->input('fbc'),
            ], $filled),
            'payment_provider' => $validated['payment_provider'] ?? 'mercadopago',
        ];

        try {
            $order = $orderService->createOrder($event, $items, $buyer, $context);
            app(MetaConversionsApiService::class)->sendInitiateCheckoutForTicketOrder($order->fresh(['event', 'items']));

            if ($order->payment_provider === 'stripe') {
                $session = $stripe->createCheckoutSession($order->load('items'));
                $metadata = $order->metadata ?? [];
                $metadata['stripe_checkout_url'] = Arr::get($session, 'url');

                $order->update([
                    'stripe_session_id' => Arr::get($session, 'id'),
                    'stripe_status' => Arr::get($session, 'status'),
                    'metadata' => $metadata,
                ]);

                $checkoutUrl = Arr::get($session, 'url');

                if (! $checkoutUrl) {
                    throw new \RuntimeException('No se recibió el link de pago.');
                }

                return redirect()->away($checkoutUrl);
            }

            $preference = $mercadoPago->createPreferenceForOrder($order->load('items'));

            $metadata = $order->metadata ?? [];
            $metadata['mp_init_point'] = Arr::get($preference, 'init_point');
            $metadata['mp_sandbox_init_point'] = Arr::get($preference, 'sandbox_init_point');

            $order->update([
                'mp_preference_id' => Arr::get($preference, 'id'),
                'metadata' => $metadata,
            ]);

            $initPoint = config('mercadopago.sandbox')
                ? Arr::get($preference, 'sandbox_init_point')
                : Arr::get($preference, 'init_point');

            if (! $initPoint) {
                throw new \RuntimeException('No se recibió el link de pago.');
            }

            return redirect()->away($initPoint);
        } catch (\Throwable $exception) {
            Log::error('Ticket checkout failed', [
                'event_id' => $event->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['checkout' => $exception->getMessage()])->withInput();
        }
    }

    public function success(Request $request, TicketOrder $order, TicketOrderService $orderService, MercadoPagoService $mercadoPago, StripeService $stripe): View
    {
        $paymentId = $request->query('payment_id')
            ?? $request->query('collection_id');
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            try {
                $session = $stripe->fetchSession((string) $sessionId);
                if ((string) data_get($session, 'client_reference_id') === $order->public_id) {
                    $order = $orderService->syncStripeSession($order, $session);
                }
            } catch (\Throwable $exception) {
                Log::warning('Stripe session sync failed', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        } elseif ($paymentId) {
            try {
                $payment = $mercadoPago->fetchPayment((string) $paymentId);
                if ((string) data_get($payment, 'external_reference') === $order->public_id) {
                    $order = $orderService->syncPayment($order, $payment);
                }
            } catch (\Throwable $exception) {
                Log::warning('Ticket payment sync failed', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $order->load(['event', 'items.attendees.product', 'attendees.product']);

        if ($order->status !== 'paid') {
            return view('tickets.pending', [
                'order' => $order,
                'event' => $order->event,
            ]);
        }

        $orderService->ensureAttendees($order);
        $orderService->hydratePrefilledAttendees($order);
        $order->load(['attendees.product', 'items.attendees.product', 'event']);

        return view('tickets.success', [
            'order' => $order,
            'event' => $order->event,
        ]);
    }

    public function pending(TicketOrder $order): View
    {
        $order->load(['event', 'items']);

        return view('tickets.pending', [
            'order' => $order,
            'event' => $order->event,
        ]);
    }

    public function retryPayment(
        TicketOrder $order,
        MercadoPagoService $mercadoPago,
        StripeService $stripe
    ): RedirectResponse {
        $order->load(['event', 'items']);

        if ($order->status === 'paid') {
            return redirect()->route('tickets.success', $order);
        }

        try {
            if ($order->payment_provider === 'stripe') {
                $session = $stripe->createCheckoutSession($order);
                $checkoutUrl = Arr::get($session, 'url');

                if (! $checkoutUrl) {
                    throw new \RuntimeException('No se recibió el link de pago.');
                }

                $metadata = $order->metadata ?? [];
                $metadata['stripe_checkout_url'] = $checkoutUrl;
                $metadata['checkout_retried_at'] = now()->toIso8601String();

                $order->update([
                    'stripe_session_id' => Arr::get($session, 'id'),
                    'stripe_status' => Arr::get($session, 'status'),
                    'metadata' => $metadata,
                ]);

                return redirect()->away($checkoutUrl);
            }

            $preference = $mercadoPago->createPreferenceForOrder($order);
            $initPoint = config('mercadopago.sandbox')
                ? Arr::get($preference, 'sandbox_init_point')
                : Arr::get($preference, 'init_point');

            if (! $initPoint) {
                throw new \RuntimeException('No se recibió el link de pago.');
            }

            $metadata = $order->metadata ?? [];
            $metadata['mp_init_point'] = Arr::get($preference, 'init_point');
            $metadata['mp_sandbox_init_point'] = Arr::get($preference, 'sandbox_init_point');
            $metadata['checkout_retried_at'] = now()->toIso8601String();

            $order->update([
                'mp_preference_id' => Arr::get($preference, 'id'),
                'metadata' => $metadata,
            ]);

            return redirect()->away($initPoint);
        } catch (\Throwable $exception) {
            Log::warning('Ticket checkout retry failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('tickets.pending', $order)
                ->withErrors(['checkout' => $exception->getMessage()]);
        }
    }

    public function failure(TicketOrder $order): View
    {
        $order->load('event');

        return view('tickets.failure', [
            'order' => $order,
            'event' => $order->event,
        ]);
    }

    public function manage(TicketOrder $order): RedirectResponse
    {
        return redirect()->route(match ($order->status) {
            'paid' => 'tickets.success',
            'pending' => 'tickets.pending',
            default => 'tickets.failure',
        }, $order);
    }
}
