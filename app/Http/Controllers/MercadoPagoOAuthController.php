<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MercadoPagoOAuthController extends Controller
{
    public function redirect(Request $request, MercadoPagoService $mercadoPago): RedirectResponse
    {
        $state = Str::random(40);

        $request->session()->put('mercadopago_oauth_state', $state);

        try {
            return redirect()->away($mercadoPago->buildAuthorizationUrl($state));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('filament.admin.pages.mercado-pago-connection')
                ->with('mercadopago_error', $exception->getMessage());
        }
    }

    public function callback(Request $request, MercadoPagoService $mercadoPago): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('mercadopago_oauth_state', '');
        $receivedState = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');

        if ($expectedState === '' || ! hash_equals($expectedState, $receivedState)) {
            return redirect()
                ->route('filament.admin.pages.mercado-pago-connection')
                ->with('mercadopago_error', 'La validación OAuth de Mercado Pago expiró. Intenta conectar de nuevo.');
        }

        if ($code === '') {
            $message = (string) ($request->query('error_description') ?: 'Mercado Pago no devolvió el código de autorización.');

            return redirect()
                ->route('filament.admin.pages.mercado-pago-connection')
                ->with('mercadopago_error', $message);
        }

        try {
            $mercadoPago->exchangeAuthorizationCode($code);

            return redirect()
                ->route('filament.admin.pages.mercado-pago-connection')
                ->with('mercadopago_success', 'Mercado Pago quedó conectado correctamente.');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('filament.admin.pages.mercado-pago-connection')
                ->with('mercadopago_error', $exception->getMessage());
        }
    }
}
