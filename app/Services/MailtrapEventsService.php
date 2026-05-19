<?php

namespace App\Services;

use App\Models\ContactLog;
use App\Models\EmailTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailtrapEventsService
{
    public function processEvents(array $events): int
    {
        $processed = 0;

        foreach ($events as $event) {
            if (! is_array($event)) {
                continue;
            }

            $this->processEvent($event);
            $processed++;
        }

        return $processed;
    }

    public function sync(?Carbon $since = null): int
    {
        $token = (string) config('services.mailtrap.api_token', '');
        $endpoint = (string) config('services.mailtrap.events_endpoint', '');
        $perPage = (int) config('services.mailtrap.events_per_page', 100);
        $maxPages = (int) config('services.mailtrap.events_max_pages', 5);
        $timeout = (int) config('services.mailtrap.api_timeout', 15);

        if ($token === '' || $endpoint === '') {
            throw new \RuntimeException('Mailtrap events endpoint no configurado.');
        }

        $processed = 0;
        $page = 1;

        while ($page <= $maxPages) {
            $url = $this->buildEventsUrl($endpoint, [
                'page' => $page,
                'per_page' => $perPage,
                'since' => $since?->timestamp,
            ]);

            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout($timeout)
                ->get($url);

            if (! $response->successful()) {
                $status = $response->status();
                $message = $status === 404
                    ? 'Mailtrap events endpoint no encontrado (404). Revisa MAILTRAP_EVENTS_ENDPOINT.'
                    : 'Mailtrap events sync failed: ' . $status;

                Log::error('Mailtrap events sync failed', [
                    'status' => $status,
                    'body' => $response->body(),
                    'endpoint' => $url,
                ]);
                throw new \RuntimeException($message);
            }

            $events = $this->normalizeEvents($response->json());

            if (empty($events)) {
                break;
            }

            $processed += $this->processEvents($events);

            if (count($events) < $perPage) {
                break;
            }

            $page++;
        }

        return $processed;
    }

    protected function processEvent(array $event): void
    {
        $eventType = $event['type'] ?? $event['event'] ?? null;
        $email = $event['email'] ?? null;
        $messageId = $event['message_id'] ?? null;

        if (! $eventType || ! $email) {
            return;
        }

        match ($eventType) {
            'sent', 'queued' => $this->handleSent($event),
            'delivered' => $this->handleDelivered($event),
            'opened' => $this->handleOpened($event),
            'clicked' => $this->handleClicked($event),
            'bounced', 'hard_bounced', 'soft_bounced', 'deferred' => $this->handleBounced($event),
            'complained' => $this->handleComplained($event),
            default => null,
        };
    }

    protected function handleSent(array $event): void
    {
        $log = $this->resolveContactLog($event);

        if (! $log) {
            return;
        }

        $log->markAsSent();
    }

    protected function handleDelivered(array $event): void
    {
        $log = $this->resolveContactLog($event);

        if (! $log) {
            return;
        }

        $log->markAsDelivered();
    }

    protected function handleOpened(array $event): void
    {
        $log = $this->resolveContactLog($event);

        if (! $log) {
            return;
        }

        $log->markAsOpened();

        // También actualizar el EmailTracking con detalles de apertura
        $emailTracking = $this->resolveEmailTracking($log);

        if ($emailTracking) {
            $ip = $event['ip'] ?? null;
            $userAgent = $event['user_agent'] ?? null;

            $emailTracking->recordOpen($ip, $userAgent);

            Log::info('Email open processed from Mailtrap webhook', [
                'tracking_token' => $emailTracking->tracking_token,
                'customer_id' => $emailTracking->customer_id,
                'opens_count' => $emailTracking->fresh()->opens_count,
            ]);
        }
    }

    protected function handleClicked(array $event): void
    {
        $log = $this->resolveContactLog($event);

        if (! $log) {
            return;
        }

        $log->markAsClicked();

        // También actualizar el EmailTracking con detalles del click
        $emailTracking = $this->resolveEmailTracking($log);

        if ($emailTracking) {
            $url = $event['url'] ?? null;
            $ip = $event['ip'] ?? null;
            $userAgent = $event['user_agent'] ?? null;

            if ($url) {
                $emailTracking->recordClick($url, $ip, $userAgent);

                Log::info('Email click processed from Mailtrap webhook', [
                    'tracking_token' => $emailTracking->tracking_token,
                    'customer_id' => $emailTracking->customer_id,
                    'url' => $url,
                    'clicks_count' => $emailTracking->fresh()->clicks_count,
                ]);
            }
        }
    }

    protected function handleBounced(array $event): void
    {
        $log = $this->resolveContactLog($event);

        if (! $log) {
            return;
        }

        $log->markAsBounced();

        $response = $event['response'] ?? $event['description'] ?? null;

        if ($response) {
            $log->update([
                'error_message' => $response,
            ]);
        }
    }

    protected function handleComplained(array $event): void
    {
        $log = $this->resolveContactLog($event);

        if (! $log) {
            return;
        }

        $response = $event['response'] ?? $event['description'] ?? 'Spam complaint';

        $log->markAsFailed($response);
    }

    protected function resolveContactLog(array $event): ?ContactLog
    {
        $messageId = $event['message_id'] ?? null;
        $email = $event['email'] ?? null;

        // Primero intentar por message_id (más preciso)
        if ($messageId) {
            $log = ContactLog::query()
                ->where('channel', 'email')
                ->where('metadata->mailtrap_message_id', $messageId)
                ->latest('created_at')
                ->first();

            if ($log) {
                return $log;
            }
        }

        // Si no se encuentra por message_id, buscar por email en estados válidos
        if ($email) {
            return ContactLog::query()
                ->where('channel', 'email')
                ->whereIn('status', ['pending', 'sent', 'delivered', 'opened', 'clicked'])
                ->whereHas('customer', fn ($query) => $query->where('email', $email))
                ->latest('created_at')
                ->first();
        }

        return null;
    }

    protected function resolveEmailTracking(ContactLog $contactLog): ?EmailTracking
    {
        return EmailTracking::query()
            ->where('contact_log_id', $contactLog->id)
            ->first();
    }

    protected function normalizeEvents(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (isset($payload['events']) && is_array($payload['events'])) {
            return $payload['events'];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        return array_is_list($payload) ? $payload : [];
    }

    protected function buildEventsUrl(string $endpoint, array $params): string
    {
        $params = array_filter($params, static fn ($value) => $value !== null && $value !== '');
        $delimiter = str_contains($endpoint, '?') ? '&' : '?';

        return $endpoint . ($params ? $delimiter . http_build_query($params) : '');
    }
}
