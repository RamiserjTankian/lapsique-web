<?php

namespace App\Http\Controllers;

use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\PortfolioItem;
use App\Models\SiteSetting;
use App\Services\CustomerPortalAccessService;
use App\Services\GoogleCalendarService;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContentBookingController extends Controller
{
    public function show(Request $request): View
    {
        $slots = BookingSlot::available()
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

        $settings = SiteSetting::current();
        $price = $settings?->booking_price ?: 5000;

        return view('booking.show', [
            'slots' => $slots,
            'portfolioPhotos' => $portfolioPhotos,
            'portfolioVideo' => $portfolioVideo,
            'settings' => $settings,
            'price' => $price,
        ]);
    }

    public function checkout(
        Request $request,
        MercadoPagoService $mercadoPago,
        CustomerPortalAccessService $portalAccess,
    ): RedirectResponse {
        $validated = $request->validate([
            'booking_slot_id' => ['required', 'integer', 'exists:booking_slots,id'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['required', 'string', 'max:30'],
            'client_instagram' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $settings = SiteSetting::current();
        $price = $settings?->booking_price ?: 5000;

        $booking = null;
        $customer = null;

        try {
            DB::transaction(function () use ($validated, $price, $request, &$booking, &$customer, $portalAccess) {
                $slot = BookingSlot::where('id', $validated['booking_slot_id'])
                    ->where('is_active', true)
                    ->whereColumn('booked_count', '<', 'max_bookings')
                    ->lockForUpdate()
                    ->first();

                if (! $slot) {
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
                    'customer_id' => $customer->id,
                    'client_name' => $validated['client_name'],
                    'client_email' => $validated['client_email'],
                    'client_phone' => $validated['client_phone'],
                    'client_instagram' => $validated['client_instagram'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'amount' => $price,
                    'currency' => 'MXN',
                    'status' => 'pending_payment',
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
                ]);

                $slot->increment('booked_count');
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'slot_unavailable') {
                return back()->withErrors(['booking_slot_id' => 'El horario seleccionado ya no está disponible. Por favor elige otro.'])->withInput();
            }

            throw $e;
        }

        try {
            $preference = $mercadoPago->createPreferenceForBooking($booking);

            $booking->update(['mercadopago_preference_id' => $preference['id']]);
            $booking = $booking->fresh(['slot', 'customer']);

            $this->bootCustomerSession($request, $booking);
            if ($booking->customer) {
                $portalAccess->ensurePortalAccessAndNotify($booking->customer, booking: $booking);
            }

            $initPoint = config('mercadopago.sandbox')
                ? ($preference['sandbox_init_point'] ?? $preference['init_point'])
                : $preference['init_point'];

            return redirect()->away($initPoint);
        } catch (\Throwable $e) {
            $booking->slot?->decrement('booked_count');
            $booking->update(['status' => 'failed']);

            Log::error('ContentBooking MP preference failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['payment' => 'No se pudo iniciar el pago. Inténtalo de nuevo.'])->withInput();
        }
    }

    public function confirm(Request $request, string $publicId, CustomerPortalAccessService $portalAccess): View
    {
        $booking = ContentBooking::where('public_id', $publicId)->with(['slot', 'customer'])->firstOrFail();

        if ($booking->status === 'pending_payment') {
            $booking->update(['status' => 'confirmed']);

            // Create Google Calendar event
            $this->createGoogleCalendarEvent($booking->fresh());
        }

        $this->bootCustomerSession($request, $booking);
        if ($booking->customer) {
            $portalAccess->ensurePortalAccessAndNotify($booking->customer, booking: $booking);
        }

        return view('booking.confirm', ['booking' => $booking]);
    }

    protected function createGoogleCalendarEvent(ContentBooking $booking): void
    {
        if ($booking->google_calendar_event_id) {
            return;
        }

        try {
            $googleCalendar = app(GoogleCalendarService::class);

            if (! $googleCalendar->isConnected()) {
                return;
            }

            $settings = SiteSetting::current();
            $calendarId = $settings?->google_calendar_id ?? 'primary';
            $eventId = $googleCalendar->createBookingEvent($booking, $calendarId);

            if ($eventId) {
                $booking->update(['google_calendar_event_id' => $eventId]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('GCal event creation failed in controller', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function pending(Request $request, string $publicId): View
    {
        $booking = ContentBooking::where('public_id', $publicId)->with(['slot', 'customer'])->firstOrFail();

        if ($booking->status === 'pending_payment') {
            $booking->update(['status' => 'pending']);
        }

        $this->bootCustomerSession($request, $booking);

        return view('booking.pending', ['booking' => $booking]);
    }

    public function failure(Request $request, string $publicId): View
    {
        $booking = ContentBooking::where('public_id', $publicId)->with(['slot', 'customer'])->firstOrFail();

        if (in_array($booking->status, ['pending_payment', 'pending'])) {
            $booking->slot?->decrement('booked_count');
            $booking->update(['status' => 'failed']);
        }

        $this->bootCustomerSession($request, $booking);

        return view('booking.failure', ['booking' => $booking]);
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
