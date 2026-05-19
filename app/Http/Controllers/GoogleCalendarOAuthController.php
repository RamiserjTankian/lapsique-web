<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCalendarOAuthController extends Controller
{
    public function redirect(Request $request, GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $state = Str::random(40);
        session(['google_calendar_oauth_state' => $state]);

        try {
            return redirect()->away($googleCalendar->buildAuthorizationUrl($state));
        } catch (\Throwable $e) {
            return redirect()->route('filament.admin.pages.booking-settings')
                ->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request, GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $state = $request->query('state');
        $expectedState = session('google_calendar_oauth_state');

        if (! $state || $state !== $expectedState) {
            return redirect()->route('filament.admin.pages.booking-settings')
                ->with('error', 'Estado OAuth inválido. Inténtalo de nuevo.');
        }

        $error = $request->query('error');
        if ($error) {
            return redirect()->route('filament.admin.pages.booking-settings')
                ->with('error', "Google rechazó la autorización: {$error}");
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()->route('filament.admin.pages.booking-settings')
                ->with('error', 'No se recibió el código de autorización.');
        }

        try {
            $googleCalendar->exchangeAuthorizationCode((string) $code);

            return redirect()->route('filament.admin.pages.booking-settings')
                ->with('success', '¡Google Calendar conectado exitosamente!');
        } catch (\Throwable $e) {
            Log::error('Google Calendar OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('filament.admin.pages.booking-settings')
                ->with('error', 'No se pudo conectar: ' . $e->getMessage());
        }
    }

    public function disconnect(Request $request, GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $googleCalendar->disconnect();

        return redirect()->route('filament.admin.pages.booking-settings')
            ->with('success', 'Google Calendar desconectado.');
    }
}
