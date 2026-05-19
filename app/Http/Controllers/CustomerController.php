<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'instagram_handle' => 'nullable|string|max:255',
            'subscribed_newsletter' => 'boolean',
            'source' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customer = Customer::query()
                ->where('email', $request->email)
                ->first();

            if ($customer) {
                // Update existing customer
                $customer->update([
                    'name' => $request->name,
                    'phone' => $request->phone ?? $customer->phone,
                    'instagram_handle' => $request->instagram_handle ?? $customer->instagram_handle,
                    'subscribed_newsletter' => $request->boolean('subscribed_newsletter', true),
                    'last_interaction_at' => now(),
                ]);
            } else {
                // Create new customer
                $customer = Customer::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'instagram_handle' => $request->instagram_handle,
                    'subscribed_newsletter' => $request->boolean('subscribed_newsletter', true),
                    'source' => $request->source ?? 'popup',
                    'last_interaction_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => '¡Gracias! Te mantendremos informado de nuestros próximos eventos.',
                'customer' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al procesar tu solicitud. Inténtalo de nuevo.',
            ], 500);
        }
    }

    public function portal(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        $customer?->load([
            'contentBookings.slot',
            'contentBookings.media',
            'eventBalances.event',
            'guestListEntries.event',
            'ticketAttendees.event',
            'ticketAttendees.product',
            'ticketOrders.event',
            'ticketOrders.items',
            'ticketOrders.attendees',
        ]);

        $payments = collect();

        if ($customer) {
            $bookingPayments = $customer->contentBookings->map(function ($booking) {
                return (object) [
                    'type' => 'booking',
                    'label' => 'Sesión de contenido',
                    'status' => $booking->payment_status_label,
                    'status_key' => $booking->status,
                    'amount' => $booking->formatted_amount,
                    'date' => $booking->updated_at ?? $booking->created_at,
                    'detail' => $booking->slot_summary,
                ];
            });

            $ticketPayments = $customer->ticketOrders->map(function ($order) {
                return (object) [
                    'type' => 'ticket_order',
                    'label' => $order->event?->title ?? 'Compra de tickets',
                    'status' => match ($order->status) {
                        'paid' => 'Pagado',
                        'pending' => 'Pendiente',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        'refunded' => 'Reembolsado',
                        default => ucfirst($order->status),
                    },
                    'status_key' => $order->status,
                    'amount' => '$' . number_format((float) $order->total, 0) . ' ' . $order->currency,
                    'date' => $order->paid_at ?? $order->created_at,
                    'detail' => $order->buyer_email,
                ];
            });

            $payments = $bookingPayments
                ->merge($ticketPayments)
                ->sortByDesc(fn ($payment) => $payment->date)
                ->values();
        }

        return view('customers.portal', compact('customer', 'payments'));
    }
}
