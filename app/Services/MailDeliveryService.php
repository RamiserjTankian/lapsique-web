<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MailDeliveryService
{
    public function send(
        Mailable $mailable,
        string $toEmail,
        ?string $toName = null,
        ?string $category = null
    ): ?string
    {
        if ($this->isMailtrapApiEnabled()) {
            return $this->sendViaMailtrapApi($mailable, $toEmail, $toName, $category);
        }

        Mail::to($toEmail)->send($mailable);

        return null;
    }

    protected function isMailtrapApiEnabled(): bool
    {
        return (string) config('services.mailtrap.api_token', '') !== '';
    }

    protected function sendViaMailtrapApi(
        Mailable $mailable,
        string $toEmail,
        ?string $toName = null,
        ?string $category = null
    ): ?string {
        $token = (string) config('services.mailtrap.api_token', '');
        $endpoint = (string) config('services.mailtrap.api_endpoint', 'https://send.api.mailtrap.io/api/send');
        $timeout = (int) config('services.mailtrap.api_timeout', 15);
        $delayMs = (int) config('services.mailtrap.send_delay_ms', 0);

        if ($token === '') {
            throw new \RuntimeException('MAILTRAP_API_TOKEN no configurado.');
        }

        $html = $mailable->render();
        $subject = $mailable->subject ?: Str::title(Str::snake(class_basename($mailable), ' '));
        $text = $this->toPlainText($html);

        $fromConfig = config('mail.from', []);
        $fromEmail = $fromConfig['address'] ?? null;
        $fromName = $fromConfig['name'] ?? config('app.name');
        $fromAvatar = $fromConfig['avatar'] ?? null;

        if (! $fromEmail) {
            throw new \RuntimeException('MAIL_FROM_ADDRESS no configurado.');
        }

        $fromPayload = [
            'email' => $fromEmail,
            'name' => $fromName,
        ];

        // Agregar avatar si está disponible (para Mailtrap)
        if ($fromAvatar) {
            // Asegurar que sea una URL absoluta
            if (!str_starts_with($fromAvatar, 'http')) {
                $fromAvatar = url($fromAvatar);
            }
            $fromPayload['avatar_url'] = $fromAvatar;
        }

        $payload = [
            'from' => $fromPayload,
            'to' => [
                array_filter([
                    'email' => $toEmail,
                    'name' => $toName,
                ], static fn ($value) => $value !== null && $value !== ''),
            ],
            'subject' => $subject,
            'html' => $html,
            'text' => $text,
        ];

        if ($category) {
            $payload['category'] = $category;
        }

        $attachments = $this->extractMailtrapAttachments($mailable);

        if ($attachments !== []) {
            $payload['attachments'] = $attachments;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout($timeout)
            ->post($endpoint, $payload);

        if (! $response->successful()) {
            Log::error('Mailtrap API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Mailtrap API error: ' . $response->status());
        }

        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }

        $data = $response->json();

        if (is_array($data)) {
            if (! empty($data['message_id'])) {
                return (string) $data['message_id'];
            }

            if (! empty($data['message_ids']) && is_array($data['message_ids'])) {
                return (string) $data['message_ids'][0];
            }
        }

        return null;
    }

    protected function toPlainText(string $html): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));

        return $text !== '' ? $text : ' ';
    }

    protected function extractMailtrapAttachments(Mailable $mailable): array
    {
        $attachments = [];

        foreach ($mailable->rawAttachments as $attachment) {
            $content = $attachment['data'] ?? null;

            if (! is_string($content) || $content === '') {
                continue;
            }

            $attachments[] = [
                'filename' => $attachment['name'] ?? 'attachment.bin',
                'content' => base64_encode($content),
                'mimetype' => $attachment['options']['mime'] ?? 'application/octet-stream',
                'disposition' => 'attachment',
            ];
        }

        foreach ($mailable->attachments as $attachment) {
            $path = $attachment['file'] ?? null;

            if (! is_string($path) || $path === '' || ! is_file($path)) {
                continue;
            }

            $content = @file_get_contents($path);

            if (! is_string($content) || $content === '') {
                continue;
            }

            $attachments[] = [
                'filename' => $attachment['options']['as'] ?? basename($path),
                'content' => base64_encode($content),
                'mimetype' => $attachment['options']['mime'] ?? 'application/octet-stream',
                'disposition' => 'attachment',
            ];
        }

        foreach ($mailable->diskAttachments as $attachment) {
            $disk = $attachment['disk'] ?? null;
            $path = $attachment['path'] ?? null;

            if (! is_string($path) || $path === '') {
                continue;
            }

            $storage = Storage::disk($disk);

            if (! $storage->exists($path)) {
                continue;
            }

            $content = $storage->get($path);

            if ($content === '') {
                continue;
            }

            $attachments[] = [
                'filename' => $attachment['name'] ?? basename($path),
                'content' => base64_encode($content),
                'mimetype' => $attachment['options']['mime'] ?? $storage->mimeType($path) ?? 'application/octet-stream',
                'disposition' => 'attachment',
            ];
        }

        return $attachments;
    }
}
