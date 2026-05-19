<?php

namespace App\Http\Controllers;

use App\Models\ContentBooking;
use App\Models\TicketOrder;
use App\Services\ContentBookingPaymentService;
use App\Services\MercadoPagoService;
use App\Services\TicketOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MercadoPagoWebhookController extends Controller
{
    public function handle(
        Request $request,
        MercadoPagoService $mercadoPago,
        TicketOrderService $orderService,
        ContentBookingPaymentService $bookingPaymentService,
    ): Response {
        if (! $mercadoPago->verifyWebhookSignature($request)) {
            Log::warning('MercadoPago webhook signature invalid', [
                'ip' => $request->ip(),
            ]);

            return response('Invalid signature', 401);
        }

        $topic = (string) ($request->query('topic')
            ?: $request->input('type')
            ?: $request->input('topic'));

        $topic = str_replace('.updated', '', $topic);

        if ($topic === 'merchant_order') {
            return $this->handleMerchantOrder($request, $mercadoPago, $orderService, $bookingPaymentService);
        }

        $paymentId = $request->input('data.id')
            ?? $request->input('data_id')
            ?? $request->query('id')
            ?? $this->extractIdFromResource($request->input('resource') ?: $request->query('resource'));

        if ($topic !== 'payment' || ! $paymentId) {
            return response()->noContent();
        }

        try {
            $payment = $mercadoPago->fetchPayment((string) $paymentId);
        } catch (\Throwable $exception) {
            Log::warning('MercadoPago webhook payment fetch failed', [
                'payment_id' => $paymentId,
                'error' => $exception->getMessage(),
            ]);

            return response()->noContent();
        }

        $externalReference = (string) data_get($payment, 'external_reference');

        if ($externalReference === '') {
            Log::warning('MercadoPago webhook missing external reference', [
                'payment_id' => $paymentId,
            ]);

            return response()->noContent();
        }

        if (str_starts_with($externalReference, 'bkg_')) {
            $publicId = substr($externalReference, 4);
            $booking = ContentBooking::where('public_id', $publicId)->first();

            if ($booking) {
                $bookingPaymentService->syncMercadoPagoPayment($booking, $payment);
            } else {
                Log::warning('MercadoPago webhook booking not found', [
                    'payment_id' => $paymentId,
                    'external_reference' => $externalReference,
                ]);
            }

            return response()->noContent();
        }

        $order = TicketOrder::where('public_id', $externalReference)->first();

        if (! $order) {
            Log::warning('MercadoPago webhook order not found', [
                'payment_id' => $paymentId,
                'external_reference' => $externalReference,
            ]);

            return response()->noContent();
        }

        $orderService->syncPayment($order, $payment);

        return response()->noContent();
    }

    protected function handleMerchantOrder(
        Request $request,
        MercadoPagoService $mercadoPago,
        TicketOrderService $orderService,
        ContentBookingPaymentService $bookingPaymentService,
    ): Response {
        $merchantOrderId = $request->input('data.id')
            ?? $request->query('id')
            ?? $this->extractIdFromResource($request->input('resource') ?: $request->query('resource'));

        if (! $merchantOrderId) {
            return response()->noContent();
        }

        try {
            $merchantOrder = $mercadoPago->fetchMerchantOrder((string) $merchantOrderId);
        } catch (\Throwable $exception) {
            Log::warning('MercadoPago webhook merchant order fetch failed', [
                'merchant_order_id' => $merchantOrderId,
                'error' => $exception->getMessage(),
            ]);

            return response()->noContent();
        }

        foreach (data_get($merchantOrder, 'payments', []) as $paymentRef) {
            $paymentId = (string) data_get($paymentRef, 'id');

            if ($paymentId === '') {
                continue;
            }

            try {
                $payment = $mercadoPago->fetchPayment($paymentId);
                $externalReference = (string) data_get($payment, 'external_reference');

                if ($externalReference === '') {
                    continue;
                }

                if (str_starts_with($externalReference, 'bkg_')) {
                    $publicId = substr($externalReference, 4);
                    $booking = ContentBooking::where('public_id', $publicId)->first();
                    if ($booking) {
                        $bookingPaymentService->syncMercadoPagoPayment($booking, $payment);
                    }
                } else {
                    $order = TicketOrder::where('public_id', $externalReference)->first();
                    if ($order) {
                        $orderService->syncPayment($order, $payment);
                    }
                }
            } catch (\Throwable $exception) {
                Log::warning('MercadoPago merchant order payment sync failed', [
                    'merchant_order_id' => $merchantOrderId,
                    'payment_id' => $paymentId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->noContent();
    }

    protected function extractIdFromResource(?string $resource): ?string
    {
        if (! $resource) {
            return null;
        }

        $resource = trim($resource);

        if ($resource === '') {
            return null;
        }

        return basename(parse_url($resource, PHP_URL_PATH) ?: $resource) ?: null;
    }
}
