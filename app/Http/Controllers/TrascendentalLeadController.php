<?php

namespace App\Http\Controllers;

use App\Mail\TrascendentalLeadNotification;
use App\Mail\TrascendentalJoinListConfirmation;
use App\Models\ContactLog;
use App\Models\Customer;
use App\Services\MailDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Mail\Mailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TrascendentalLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_type' => ['nullable', 'in:contact,join_list'],
            'service_type' => ['required', 'in:booking,production'],
            'city' => ['required', 'string', 'max:120'],
            'event_date' => ['nullable', 'date'],
            'budget' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
            'locale' => ['nullable', 'string', 'max:10'],
            'privacy_accepted' => ['accepted'],
            'captcha_answer' => ['required', 'string', 'max:20'],
            'company_website' => ['nullable', 'string', 'max:255'],
        ]);

        $leadType = $validated['lead_type'] ?? 'contact';

        if ($leadType !== 'join_list' && ! empty($validated['company_website'])) {
            throw ValidationException::withMessages([
                'company_website' => __('trascendental.contact.error'),
            ]);
        }

        if (trim($validated['captcha_answer']) !== '11') {
            throw ValidationException::withMessages([
                'captcha_answer' => __('trascendental.contact.captcha_error'),
            ]);
        }

        $customer = Customer::query()->firstOrNew(['email' => $validated['email']]);
        $existingTags = is_array($customer->tags) ? $customer->tags : [];
        $metadata = is_array($customer->metadata) ? $customer->metadata : [];
        $metadataKey = $leadType === 'join_list' ? 'trascendental_join_list' : 'trascendental_contact';

        $metadata[$metadataKey] = [
            'lead_type' => $leadType,
            'service_type' => $validated['service_type'],
            'city' => $validated['city'],
            'event_date' => $validated['event_date'] ?? null,
            'budget' => $validated['budget'],
            'message' => $validated['message'] ?? null,
            'locale' => $validated['locale'] ?? app()->getLocale(),
            'submitted_at' => now()->toIso8601String(),
            'privacy_accepted_at' => now()->toIso8601String(),
            'url' => $request->input('current_url', $request->headers->get('referer')),
            'mail_status' => 'pending',
        ];

        $customer->fill([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $customer->phone,
            'whatsapp' => $validated['phone'] ?? $customer->whatsapp,
            'status' => $customer->exists ? $customer->status : 'lead',
            'source' => $customer->source ?: ($leadType === 'join_list' ? 'trascendental_join_list' : 'trascendental_contact'),
            'lifecycle_stage' => $customer->lifecycle_stage ?: 'lead',
            'lead_score' => max((int) ($customer->lead_score ?? 0), 25),
            'subscribed_newsletter' => $leadType === 'join_list' ? true : (bool) ($customer->subscribed_newsletter ?? false),
            'subscribed_whatsapp' => $leadType === 'join_list' && ! empty($validated['phone']) ? true : (bool) ($customer->subscribed_whatsapp ?? false),
            'tags' => array_values(array_unique(array_merge($existingTags, [
                'trascendental',
                $leadType === 'join_list' ? 'trascendental_join_list' : 'trascendental_contact',
                $validated['service_type'],
            ]))),
            'metadata' => $metadata,
            'last_interaction_at' => now(),
        ])->save();

        $contactLog = ContactLog::create([
            'customer_id' => $customer->id,
            'channel' => 'manual',
            'type' => 'followup',
            'subject' => $leadType === 'join_list'
                ? 'Join The List Trascendental'
                : 'Lead Trascendental: '.$this->serviceLabel($validated['service_type']),
            'message' => $validated['message'] ?? null,
            'metadata' => $metadata[$metadataKey],
            'status' => 'pending',
        ]);

        $mailResult = null;

        if ($leadType === 'join_list') {
            $mailResult = $this->sendMailSafely(
                new TrascendentalJoinListConfirmation($customer),
                $customer->email,
                $customer->name,
                'trascendental-join-list',
            );
        } elseif ($notificationEmail = $this->notificationEmail()) {
            $mailResult = $this->sendMailSafely(
                new TrascendentalLeadNotification($customer, $contactLog),
                $notificationEmail,
                'Trascendental',
                'trascendental-contact',
            );
        }

        $this->recordMailStatus($customer, $contactLog, $metadataKey, $mailResult);

        return response()->json([
            'success' => true,
            'message' => $leadType === 'join_list'
                ? __('trascendental.join_list.success')
                : __('trascendental.contact.success'),
        ]);
    }

    private function serviceLabel(string $service): string
    {
        return $service === 'booking' ? 'Booking' : 'Produccion';
    }

    private function notificationEmail(): ?string
    {
        $email = config('trascendental.lead_notify_email')
            ?: config('trascendental.email')
            ?: config('mail.from.address');

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function sendMailSafely(Mailable $mailable, string $toEmail, ?string $toName, string $category): string|false|null
    {
        try {
            return app(MailDeliveryService::class)->send($mailable, $toEmail, $toName, $category);
        } catch (\Throwable $exception) {
            Log::warning('Trascendental lead email could not be sent.', [
                'category' => $category,
                'to' => $toEmail,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function recordMailStatus(Customer $customer, ContactLog $contactLog, string $metadataKey, string|false|null $mailResult): void
    {
        $mailStatus = match (true) {
            is_string($mailResult) && $mailResult !== '' => 'sent',
            $mailResult === false => 'failed',
            default => 'sent_without_id',
        };
        $mailMetadata = [
            'mail_status' => $mailStatus,
            'mailtrap_message_id' => is_string($mailResult) && $mailResult !== '' ? $mailResult : null,
        ];

        $contactMetadata = is_array($contactLog->metadata) ? $contactLog->metadata : [];
        $contactLog->forceFill([
            'metadata' => array_merge($contactMetadata, $mailMetadata),
        ])->save();

        $customerMetadata = is_array($customer->metadata) ? $customer->metadata : [];
        $customerMetadata[$metadataKey] = array_merge(
            is_array($customerMetadata[$metadataKey] ?? null) ? $customerMetadata[$metadataKey] : [],
            $mailMetadata,
        );

        $customer->forceFill(['metadata' => $customerMetadata])->save();
    }
}
