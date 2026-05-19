<?php

namespace App\Services;

use App\Models\ContentBooking;
use App\Models\PaymentGatewayConnection;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleCalendarService
{
    const PROVIDER = 'google_calendar';
    const SCOPES = 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/calendar.readonly';
    const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const API_BASE = 'https://www.googleapis.com/calendar/v3';

    public function getConnection(): ?PaymentGatewayConnection
    {
        return PaymentGatewayConnection::query()
            ->provider(self::PROVIDER)
            ->first();
    }

    public function isConnected(): bool
    {
        $connection = $this->getConnection();

        return $connection?->isConnected() ?? false;
    }

    public function getConnectionSummary(): array
    {
        $connection = $this->getConnection();

        if ($connection?->isConnected()) {
            return [
                'connected' => true,
                'account_email' => $connection->account_email,
                'account_name' => $connection->account_name,
                'connected_at' => $connection->connected_at,
                'expires_at' => $connection->expires_at,
                'last_error_message' => $connection->last_error_message,
            ];
        }

        return [
            'connected' => false,
            'account_email' => null,
            'account_name' => null,
            'connected_at' => null,
            'expires_at' => null,
            'last_error_message' => null,
        ];
    }

    public function buildAuthorizationUrl(string $state): string
    {
        $clientId = $this->requireClientId();

        return self::AUTH_URL . '?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPES,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function exchangeAuthorizationCode(string $code): PaymentGatewayConnection
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->requireClientId(),
            'client_secret' => $this->requireClientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            Log::error('Google Calendar OAuth exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('No se pudo conectar con Google Calendar.');
        }

        return $this->storeTokens((array) $response->json());
    }

    public function disconnect(): void
    {
        $connection = $this->getConnection();

        if (! $connection) {
            return;
        }

        $connection->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
            'expires_at' => null,
            'connected_at' => null,
        ]);
    }

    /**
     * List all calendars in the connected Google account.
     * Returns array of ['id' => ..., 'name' => ..., 'primary' => bool]
     */
    public function listCalendars(): array
    {
        $response = $this->httpClient()->get(self::API_BASE . '/users/me/calendarList');

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo listar los calendarios de Google.');
        }

        $items = data_get($response->json(), 'items', []);

        return collect($items)->map(fn ($item) => [
            'id' => $item['id'],
            'name' => $item['summary'] ?? $item['id'],
            'primary' => (bool) ($item['primary'] ?? false),
            'description' => $item['description'] ?? null,
            'color' => $item['backgroundColor'] ?? null,
        ])->sortByDesc('primary')->values()->all();
    }

    /**
     * Get busy time ranges from Google Calendar freebusy API.
     * Returns array of [['start' => Carbon, 'end' => Carbon], ...]
     */
    public function getBusyTimes(string $calendarId, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $response = $this->httpClient()->post(self::API_BASE . '/freeBusy', [
            'timeMin' => (new \DateTime())->setTimestamp($start->getTimestamp())->format(\DateTime::RFC3339),
            'timeMax' => (new \DateTime())->setTimestamp($end->getTimestamp())->format(\DateTime::RFC3339),
            'timeZone' => config('app.timezone', 'America/Mexico_City'),
            'items' => [['id' => $calendarId]],
        ]);

        if (! $response->successful()) {
            Log::warning('Google Calendar freebusy failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        $busySlots = data_get($response->json(), "calendars.{$calendarId}.busy", []);

        return collect($busySlots)->map(fn ($slot) => [
            'start' => \Carbon\Carbon::parse($slot['start']),
            'end' => \Carbon\Carbon::parse($slot['end']),
        ])->all();
    }

    /**
     * Check if a specific time slot is free in the calendar.
     */
    public function isTimeFree(string $calendarId, \Carbon\Carbon $slotStart, \Carbon\Carbon $slotEnd): bool
    {
        try {
            $busyTimes = $this->getBusyTimes($calendarId, $slotStart, $slotEnd);

            foreach ($busyTimes as $busy) {
                if ($slotStart->lt($busy['end']) && $slotEnd->gt($busy['start'])) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('GoogleCalendarService::isTimeFree failed, assuming free', [
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }

    /**
     * Create a Google Calendar event for a confirmed content booking.
     */
    public function createBookingEvent(ContentBooking $booking, string $calendarId = 'primary'): ?string
    {
        if (! $this->isConnected()) {
            return null;
        }

        $settings = SiteSetting::current();
        $durationMinutes = $settings?->booking_duration_minutes ?? 120;

        $slot = $booking->slot;
        $startTime = $slot
            ? \Carbon\Carbon::parse($slot->date->format('Y-m-d') . ' ' . $slot->time_value . ':00', config('app.timezone', 'America/Mexico_City'))
            : now();
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        $instagramStr = $booking->client_instagram ? " (@{$booking->client_instagram})" : '';
        $notesStr = $booking->notes ? "\n\nNotas: {$booking->notes}" : '';

        $event = [
            'summary' => "📸 Sesión de Contenido — {$booking->client_name}",
            'description' => "Cliente: {$booking->client_name}{$instagramStr}\nEmail: {$booking->client_email}\nWhatsApp: {$booking->client_phone}\n\nServicio: 2 Reels + 20 Fotos editadas\nMonto: {$booking->formatted_amount}{$notesStr}\n\nReserva ID: {$booking->public_id}",
            'start' => [
                'dateTime' => $startTime->toRfc3339String(),
                'timeZone' => config('app.timezone', 'America/Mexico_City'),
            ],
            'end' => [
                'dateTime' => $endTime->toRfc3339String(),
                'timeZone' => config('app.timezone', 'America/Mexico_City'),
            ],
            'colorId' => '2', // Sage/green color
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 1440], // 24h before
                    ['method' => 'popup', 'minutes' => 60],   // 1h before
                ],
            ],
        ];

        try {
            $response = $this->httpClient()->post(
                self::API_BASE . '/calendars/' . urlencode($calendarId) . '/events',
                $event
            );

            if (! $response->successful()) {
                Log::error('Google Calendar event creation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'booking_id' => $booking->id,
                ]);

                return null;
            }

            $eventId = data_get($response->json(), 'id');

            Log::info('Google Calendar event created', [
                'booking_id' => $booking->id,
                'event_id' => $eventId,
            ]);

            return $eventId;
        } catch (\Throwable $e) {
            Log::error('Google Calendar event creation exception', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete a Google Calendar event (for cancelled bookings).
     */
    public function deleteBookingEvent(string $eventId, string $calendarId = 'primary'): void
    {
        if (! $this->isConnected() || blank($eventId)) {
            return;
        }

        try {
            $this->httpClient()->delete(
                self::API_BASE . '/calendars/' . urlencode($calendarId) . '/events/' . $eventId
            );
        } catch (\Throwable $e) {
            Log::warning('Google Calendar event deletion failed', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function storeTokens(array $payload, ?PaymentGatewayConnection $connection = null): PaymentGatewayConnection
    {
        $connection = $connection ?? PaymentGatewayConnection::query()->firstOrNew([
            'provider' => self::PROVIDER,
        ]);

        $expiresIn = (int) ($payload['expires_in'] ?? 3600);
        $expiresAt = now()->addSeconds($expiresIn);

        $connection->fill([
            'status' => 'connected',
            'access_token' => $payload['access_token'],
            'refresh_token' => $payload['refresh_token'] ?? $connection->refresh_token,
            'token_type' => $payload['token_type'] ?? 'Bearer',
            'scope' => $payload['scope'] ?? self::SCOPES,
            'expires_at' => $expiresAt,
            'connected_at' => $connection->connected_at ?? now(),
            'last_synced_at' => now(),
            'last_error_at' => null,
            'last_error_message' => null,
        ]);

        $connection->save();

        // Fetch account info
        try {
            $profile = $this->fetchProfile($payload['access_token']);
            $connection->update([
                'account_id' => $profile['id'] ?? null,
                'account_email' => $profile['email'] ?? null,
                'account_name' => $profile['name'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Google Calendar profile fetch failed', ['error' => $e->getMessage()]);
        }

        return $connection->fresh();
    }

    protected function fetchProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo obtener el perfil de Google.');
        }

        return (array) $response->json();
    }

    protected function requireAccessToken(): string
    {
        $connection = $this->getConnection();

        if (! $connection || ! $connection->isConnected()) {
            throw new RuntimeException('Google Calendar no está conectado.');
        }

        if ($connection->isExpired()) {
            $connection = $this->refreshAccessToken($connection);
        }

        return $connection->access_token;
    }

    protected function refreshAccessToken(PaymentGatewayConnection $connection): PaymentGatewayConnection
    {
        if (! $connection->refresh_token) {
            throw new RuntimeException('No hay refresh token de Google Calendar disponible.');
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->requireClientId(),
            'client_secret' => $this->requireClientSecret(),
            'refresh_token' => $connection->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            $connection->update([
                'last_error_at' => now(),
                'last_error_message' => 'Refresh token inválido. Vuelve a conectar Google Calendar.',
                'status' => 'disconnected',
            ]);

            throw new RuntimeException('No se pudo renovar la sesión de Google Calendar. Vuelve a conectar.');
        }

        $tokens = (array) $response->json();
        $tokens['refresh_token'] = $tokens['refresh_token'] ?? $connection->refresh_token;

        return $this->storeTokens($tokens, $connection);
    }

    protected function httpClient()
    {
        $token = $this->requireAccessToken();

        return Http::withToken($token)->acceptJson();
    }

    protected function requireClientId(): string
    {
        $id = (string) config('services.google.client_id', '');

        if ($id === '') {
            throw new RuntimeException('GOOGLE_CLIENT_ID no está configurado.');
        }

        return $id;
    }

    protected function requireClientSecret(): string
    {
        $secret = (string) config('services.google.client_secret', '');

        if ($secret === '') {
            throw new RuntimeException('GOOGLE_CLIENT_SECRET no está configurado.');
        }

        return $secret;
    }

    protected function redirectUri(): string
    {
        return route('google-calendar.oauth.callback');
    }
}
