<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingSlotResource;
use App\Http\Resources\ContentBookingResource;
use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\PortfolioItem;
use App\Models\SiteSetting;
use App\Services\ContentBookingPaymentService;
use App\Services\CustomerPortalAccessService;
use App\Services\MercadoPagoService;
use App\Services\Meta\MetaConversionsApiService;
use App\Services\StripeService;
use App\Support\BookingMode;
use App\Support\ContentSessionOffer;
use App\Support\LocalizedBookingCopy;
use App\Support\ReelLibrary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ContentBookingController extends Controller
{
    public function show(Request $request): Response
    {
        $data = $this->bookingPageData();
        $settings = $data['settings'];

        return Inertia::render('Booking/Show', [
            'title' => LocalizedBookingCopy::bookingPageTitle($settings?->booking_title),
            'subtitle' => LocalizedBookingCopy::subtitle($settings?->booking_subtitle),
            'price' => $data['price'],
            'slots' => BookingSlotResource::collection($data['slots'])->resolve(),
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }

    public function showDjSet(): Response
    {
        $data = $this->bookingPageData();

        $originals = \App\Models\Video::query()
            ->whereJsonContains('tags', 'psique-originals')
            ->with('djs.media')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('published_at')
            ->take(8)
            ->get();

        $portfolioItems = PortfolioItem::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereJsonContains('tags', 'nightlife')
                    ->orWhereJsonContains('tags', 'events');
            })
            ->with('media')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        if ($portfolioItems->isEmpty()) {
            $portfolioItems = PortfolioItem::query()
                ->where('is_active', true)
                ->with('media')
                ->orderByDesc('is_featured')
                ->orderBy('priority')
                ->orderByDesc('created_at')
                ->take(10)
                ->get();
        }

        $djs = \App\Models\Dj::query()
            ->with('media')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $djSetReels = collect(ReelLibrary::all())
            ->filter(fn (array $reel): bool => (bool) preg_match(
                '/daymon|set|dj|rebolledo|satoshi|basement|pergola|aftermovie|bluepointrs|vatos|umi/i',
                ($reel['title'] ?? '').' '.($reel['src'] ?? ''),
            ))
            ->take(8)
            ->values()
            ->all();

        if ($djSetReels === []) {
            $djSetReels = collect(ReelLibrary::all())->take(8)->values()->all();
        }

        return Inertia::render('DjSet/Show', [
            'price' => (int) config('booking.dj_set_price', 12000),
            'slots' => BookingSlotResource::collection($data['slots'])->resolve(),
            'originals' => \App\Http\Resources\VideoResource::collection($originals)->resolve(),
            'portfolioItems' => \App\Http\Resources\PortfolioItemResource::collection($portfolioItems)->resolve(),
            'djSetReels' => $djSetReels,
            'djs' => \App\Http\Resources\DjResource::collection($djs)->resolve(),
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function bookingPageData(): array
    {
        $settings = SiteSetting::current();
        $availabilityDays = $settings?->bookingAvailabilityDays() ?? config('booking.availability_days', 11);
        $startTime = $settings?->bookingStartTime() ?? config('booking.default_start_time', '14:00');
        $endTime = $settings?->bookingEndTime() ?? config('booking.default_end_time', '17:00');
        $allowedTimeValues = config('booking.allowed_time_values', [$startTime, $endTime]);
        $latestDate = Carbon::today()->addDays($availabilityDays)->toDateString();

        $slots = BookingSlot::available()
            ->whereBetween('date', [Carbon::today()->toDateString(), $latestDate])
            ->where('time_value', '>=', $startTime)
            ->where('time_value', '<=', $endTime)
            ->whereIn('time_value', $allowedTimeValues)
            ->orderBy('date')
            ->orderBy('time_value')
            ->get(['id', 'date', 'time_label', 'time_value']);
        $portfolioPhotos = PortfolioItem::query()
            ->where('is_active', true)
            ->where('type', 'photo')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->with('media')
            ->limit(8)
            ->get();
        $portfolioVideo = PortfolioItem::query()
            ->where('is_active', true)
            ->where('type', 'video')
            ->with('media')
            ->orderByRaw("
                case
                    when lower(coalesce(title, '')) like '%aftermovie%' then 0
                    when lower(coalesce(caption, '')) like '%aftermovie%' then 0
                    else 1
                end
            ")
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->first();

        $price = $settings?->booking_price ?: (int) config('booking.content_price', 3000);

        return [
            'slots' => $slots,
            'portfolioPhotos' => $portfolioPhotos,
            'portfolioVideo' => $portfolioVideo,
            'settings' => $settings,
            'price' => $price,
        ];
    }

    public function checkout(
        Request $request,
        MercadoPagoService $mercadoPago,
        StripeService $stripe,
        ContentBookingPaymentService $bookingPayment,
        CustomerPortalAccessService $portalAccess,
    ): SymfonyResponse {
        return $this->checkoutForService(
            $request,
            $mercadoPago,
            $stripe,
            $bookingPayment,
            $portalAccess,
            ContentBooking::SERVICE_CONTENT_SESSION,
        );
    }

    public function checkoutDjSet(
        Request $request,
        MercadoPagoService $mercadoPago,
        StripeService $stripe,
        ContentBookingPaymentService $bookingPayment,
        CustomerPortalAccessService $portalAccess,
    ): SymfonyResponse {
        return $this->checkoutForService(
            $request,
            $mercadoPago,
            $stripe,
            $bookingPayment,
            $portalAccess,
            ContentBooking::SERVICE_DJ_SET,
        );
    }

    protected function checkoutForService(
        Request $request,
        MercadoPagoService $mercadoPago,
        StripeService $stripe,
        ContentBookingPaymentService $bookingPayment,
        CustomerPortalAccessService $portalAccess,
        string $serviceType,
    ): SymfonyResponse {
        $validated = $request->validate([
            'booking_slot_id' => ['required', 'integer', 'exists:booking_slots,id'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['required', 'string', 'max:30'],
            'client_instagram' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_provider' => ['nullable', 'string', 'in:mercadopago,stripe'],
            'checkout_event_id' => ['nullable', 'string', 'max:64'],
            'terms_accepted' => ['accepted'],
        ]);

        $settings = SiteSetting::current();
        $isDjSet = $serviceType === ContentBooking::SERVICE_DJ_SET;
        $price = $isDjSet
            ? (int) config('booking.dj_set_price', 12000)
            : ($settings?->booking_price ?: (int) config('booking.content_price', 3000));
        $paymentProvider = 'stripe';

        $booking = null;
        $customer = null;

        try {
            DB::transaction(function () use ($validated, $price, $request, $paymentProvider, $serviceType, &$booking, &$customer, $portalAccess, $settings) {
                $slot = BookingSlot::where('id', $validated['booking_slot_id'])
                    ->where('is_active', true)
                    ->whereColumn('booked_count', '<', 'max_bookings')
                    ->lockForUpdate()
                    ->first();

                if (! $slot) {
                    throw new \RuntimeException('slot_unavailable');
                }

                $availabilityDays = $settings?->bookingAvailabilityDays() ?? config('booking.availability_days', 11);
                $startTime = $settings?->bookingStartTime() ?? config('booking.default_start_time', '14:00');
                $endTime = $settings?->bookingEndTime() ?? config('booking.default_end_time', '17:00');
                $allowedTimeValues = config('booking.allowed_time_values', [$startTime, $endTime]);
                $slotDate = $slot->date->toDateString();
                $slotTime = substr((string) $slot->time_value, 0, 5);
                $today = Carbon::today()->toDateString();
                $latestDate = Carbon::today()->addDays($availabilityDays)->toDateString();

                if (
                    $slotDate < $today
                    || $slotDate > $latestDate
                    || $slotTime < $startTime
                    || $slotTime > $endTime
                    || ! in_array($slotTime, $allowedTimeValues, true)
                ) {
                    throw new \RuntimeException('slot_unavailable');
                }

                $customer = $portalAccess->upsertCustomerFromBooking($validated, [
                    'utm_source' => $request->input('utm_source'),
                    'utm_medium' => $request->input('utm_medium'),
                    'utm_campaign' => $request->input('utm_campaign'),
                    'utm_content' => $request->input('utm_content'),
                    'utm_term' => $request->input('utm_term'),
                ]);

                $booking = ContentBooking::create([
                    'public_id' => Str::uuid()->toString(),
                    'booking_slot_id' => $slot->id,
                    'service_type' => $serviceType,
                    'customer_id' => $customer->id,
                    'client_name' => $validated['client_name'],
                    'client_email' => $validated['client_email'],
                    'client_phone' => $validated['client_phone'],
                    'client_instagram' => $validated['client_instagram'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'amount' => $price,
                    'currency' => 'MXN',
                    'status' => 'pending_payment',
                    'payment_provider' => $paymentProvider,
                    'utm_source' => $request->input('utm_source'),
                    'utm_medium' => $request->input('utm_medium'),
                    'utm_campaign' => $request->input('utm_campaign'),
                    'utm_content' => $request->input('utm_content'),
                    'utm_term' => $request->input('utm_term'),
                    'analytics_visitor_id' => $request->input('analytics_visitor_id'),
                    'analytics_session_id' => $request->input('analytics_session_id'),
                    'fbp' => $request->input('fbp'),
                    'fbc' => $request->input('fbc'),
                    'referrer' => $request->input('referrer'),
                    'landing_url' => $request->input('landing_url'),
                    'client_ip_address' => $request->ip(),
                    'client_user_agent' => $request->userAgent(),
                    'metadata' => [
                        'created_from_host' => $request->getHost(),
                        'skip_payment_mode' => BookingMode::shouldSkipPayment($request),
                        'service_type' => $serviceType,
                        'checkout_route' => $request->route()?->getName(),
                        'checkout_event_id' => $request->input('checkout_event_id'),
                    ],
                ]);

                $slot->increment('booked_count');
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'slot_unavailable') {
                return back()->withErrors(['booking_slot_id' => 'El horario seleccionado ya no está disponible. Por favor elige otro.'])->withInput();
            }

            throw $e;
        }

        if (! BookingMode::shouldSkipPayment($request)) {
            app(MetaConversionsApiService::class)->sendInitiateCheckoutForBooking($booking->fresh());
        }

        if (BookingMode::shouldSkipPayment($request)) {
            $booking->update(['payment_provider' => 'internal']);

            $booking = $bookingPayment->applyStatusTransition($booking->fresh(['slot', 'customer']), 'confirmed', [
                'source' => 'test_skip_payment',
                'host' => $request->getHost(),
            ]);

            Log::info('ContentBooking confirmed without payment in test mode', [
                'booking_id' => $booking->id,
                'host' => $request->getHost(),
            ]);

            $this->bootCustomerSession($request, $booking);

            return redirect()
                ->route('booking.confirm', $booking->public_id)
                ->with('success', 'Reserva de prueba confirmada sin cobro real.');
        }

        try {
            if ($paymentProvider === 'stripe') {
                $session = $stripe->createCheckoutSessionForBooking($booking);
                $checkoutUrl = Arr::get($session, 'url');

                if (! $checkoutUrl) {
                    throw new \RuntimeException('No se recibió el link de pago.');
                }

                $booking->update([
                    'stripe_checkout_session_id' => Arr::get($session, 'id'),
                    'stripe_status' => Arr::get($session, 'status'),
                ]);
            } else {
                $preference = $mercadoPago->createPreferenceForBooking($booking);
                $checkoutUrl = config('mercadopago.sandbox')
                    ? ($preference['sandbox_init_point'] ?? $preference['init_point'])
                    : $preference['init_point'];

                $booking->update(['mercadopago_preference_id' => $preference['id']]);
            }

            $booking = $booking->fresh(['slot', 'customer']);

            $this->bootCustomerSession($request, $booking);

            return Inertia::location($checkoutUrl);
        } catch (\Throwable $e) {
            $booking->slot?->decrement('booked_count');
            $booking->update(['status' => 'failed']);

            Log::error('ContentBooking checkout failed', [
                'booking_id' => $booking->id,
                'provider' => $paymentProvider,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['payment' => 'No se pudo iniciar el pago. Inténtalo de nuevo.'])->withInput();
        }
    }

    public function confirm(
        Request $request,
        string $publicId,
        StripeService $stripe,
        ContentBookingPaymentService $bookingPayment,
    ): Response {
        $booking = ContentBooking::where('public_id', $publicId)->with(['slot', 'customer'])->firstOrFail();

        $sessionId = $request->query('session_id');
        if ($sessionId && $booking->payment_provider === 'stripe') {
            try {
                $session = $stripe->fetchSession((string) $sessionId);
                $bookingPayment->syncStripeSession($booking, $session);
                $booking = $booking->fresh(['slot', 'customer']);
            } catch (\Throwable $e) {
                Log::warning('Stripe session sync on booking confirm failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->bootCustomerSession($request, $booking);

        return Inertia::render('Booking/Confirm', [
            'booking' => (new ContentBookingResource($booking))->resolve(),
            'paymentVerified' => $booking->status === 'confirmed',
            'isTestBooking' => (bool) data_get($booking->metadata, 'skip_payment_mode', false),
        ]);
    }

    public function pending(Request $request, string $publicId): Response
    {
        $booking = ContentBooking::where('public_id', $publicId)->with(['slot', 'customer'])->firstOrFail();

        if ($booking->status === 'pending_payment') {
            $booking->update(['status' => 'pending']);
        }

        $this->bootCustomerSession($request, $booking);

        return Inertia::render('Booking/Pending', [
            'booking' => (new ContentBookingResource($booking))->resolve(),
            'errors' => session('errors')?->getBag('default')?->getMessages() ?? [],
        ]);
    }

    public function failure(Request $request, string $publicId): Response
    {
        $booking = ContentBooking::where('public_id', $publicId)->with(['slot', 'customer'])->firstOrFail();

        if (in_array($booking->status, ['pending_payment', 'pending'], true)) {
            app(ContentBookingPaymentService::class)->releaseSlotIfFailed($booking, 'failed');
            $booking = $booking->fresh(['slot', 'customer']);
        }

        $this->bootCustomerSession($request, $booking);

        return Inertia::render('Booking/Failure', [
            'booking' => (new ContentBookingResource($booking))->resolve(),
        ]);
    }

    public function retryPayment(
        string $publicId,
        MercadoPagoService $mercadoPago,
        StripeService $stripe,
    ): SymfonyResponse {
        $booking = ContentBooking::where('public_id', $publicId)->with(['slot', 'customer'])->firstOrFail();

        if ($booking->status === 'confirmed') {
            return redirect()->route('booking.confirm', $booking->public_id);
        }

        if (! in_array($booking->status, ['pending_payment', 'pending', 'failed'], true)) {
            return redirect()->route('booking.failure', $booking->public_id);
        }

        if ($booking->status === 'failed') {
            $booking->update(['status' => 'pending_payment']);
        }

        try {
            if ($booking->payment_provider === 'stripe') {
                $session = $stripe->createCheckoutSessionForBooking($booking);
                $checkoutUrl = Arr::get($session, 'url');

                if (! $checkoutUrl) {
                    throw new \RuntimeException('No se recibió el link de pago.');
                }

                $booking->update([
                    'stripe_checkout_session_id' => Arr::get($session, 'id'),
                    'stripe_status' => Arr::get($session, 'status'),
                ]);

                return Inertia::location($checkoutUrl);
            }

            $preference = $mercadoPago->createPreferenceForBooking($booking);
            $initPoint = config('mercadopago.sandbox')
                ? ($preference['sandbox_init_point'] ?? $preference['init_point'])
                : $preference['init_point'];

            $booking->update(['mercadopago_preference_id' => $preference['id']]);

            return Inertia::location($initPoint);
        } catch (\Throwable $e) {
            Log::error('ContentBooking retry payment failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('booking.pending', $booking->public_id)
                ->withErrors(['payment' => 'No se pudo reiniciar el pago. Inténtalo de nuevo.']);
        }
    }

    protected function bootCustomerSession(Request $request, ContentBooking $booking): void
    {
        $customer = $booking->customer;

        if (! $customer) {
            return;
        }

        if (Auth::guard('customer')->id() !== $customer->id) {
            Auth::guard('customer')->login($customer, true);
            $request->session()->regenerate();
        }

        $customer->updateLastInteraction();
    }
}
