<?php

namespace App\Http\Controllers;

use App\Models\TicketOrder;
use App\Services\MercadoPagoService;
use App\Services\Meta\MetaConversionsApiService;
use App\Support\MercadoPagoEmbeddedCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Browser adapter for Mercado Pago's Card Payment Brick.
 *
 * The browser may submit only Mercado Pago's one-time token and selected
 * payment metadata. PAN, CVV and expiry must never reach this application.
 */
final class MercadoPagoEmbeddedPaymentController extends Controller
{
    public function show(TicketOrder $order): View
    {
        $order->loadMissing(['event', 'items.product']);
        abort_unless(MercadoPagoEmbeddedCheckout::isEligible($order), 404);

        return view('tickets.mercadopago-embedded', [
            'order' => $order,
            'event' => $order->event,
            'configurationUrl' => URL::temporarySignedRoute(
                'tickets.mercadopago.embedded.configuration',
                now()->addMinutes(30),
                ['order' => $order],
            ),
            'resultUrl' => route('tickets.success', $order),
        ]);
    }

    public function configuration(TicketOrder $order, MercadoPagoService $mercadoPago): JsonResponse
    {
        abort_unless(MercadoPagoEmbeddedCheckout::isEligible($order), 404);
        $configuration = $mercadoPago->embeddedConfigurationForOrder($order);

        return response()->json([
            ...$configuration,
            'payment_url' => URL::temporarySignedRoute(
                'tickets.mercadopago.embedded.payment',
                now()->addMinutes(30),
                ['order' => $order],
            ),
            'result_url' => route('tickets.success', $order),
            'meta_event_id' => 'ticket_payment_info_'.$order->public_id,
        ]);
    }

    public function createPayment(
        Request $request,
        TicketOrder $order,
        MercadoPagoService $mercadoPago,
        MetaConversionsApiService $meta,
    ): JsonResponse {
        abort_unless(MercadoPagoEmbeddedCheckout::isEligible($order), 404);

        $body = $request->isJson() ? $request->json()->all() : $request->request->all();
        $unexpectedFields = array_diff(array_keys($body), [
            '_token',
            'token',
            'payment_method_id',
            'issuer_id',
            'installments',
            'payer',
        ]);

        if ($unexpectedFields !== []) {
            throw ValidationException::withMessages([
                'payment' => 'El formulario incluyó campos de pago no permitidos.',
            ]);
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'payment_method_id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'issuer_id' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'installments' => ['required', 'integer', 'min:1', 'max:48'],
            'payer' => ['nullable', 'array:identification'],
            'payer.identification' => ['nullable', 'array:type,number'],
            'payer.identification.type' => ['nullable', 'string', 'max:16', 'regex:/^[A-Za-z0-9_-]+$/'],
            'payer.identification.number' => ['nullable', 'string', 'max:32', 'regex:/^[A-Za-z0-9-]+$/'],
        ]);

        $payment = $mercadoPago->createEmbeddedPayment($order, $validated);
        $meta->sendAddPaymentInfoForTicketOrder($order->fresh(['event', 'items']));

        // The provider response is informative only. Tickets remain pending
        // until the signed webhook independently fetches and verifies payment.
        return response()->json([
            'id' => $payment['id'],
            'status' => $payment['status'],
            'status_detail' => $payment['status_detail'],
            'fulfilment' => 'pending_webhook_verification',
        ]);
    }
}
