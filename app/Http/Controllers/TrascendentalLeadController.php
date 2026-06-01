<?php

namespace App\Http\Controllers;

use App\Mail\TrascendentalLeadNotification;
use App\Models\ContactLog;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            'company_website' => ['nullable', 'string', 'max:0'],
        ]);

        if (trim($validated['captcha_answer']) !== '11') {
            throw ValidationException::withMessages([
                'captcha_answer' => __('trascendental.contact.captcha_error'),
            ]);
        }

        $customer = Customer::query()->firstOrNew(['email' => $validated['email']]);
        $existingTags = is_array($customer->tags) ? $customer->tags : [];
        $metadata = is_array($customer->metadata) ? $customer->metadata : [];
        $leadType = $validated['lead_type'] ?? 'contact';
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
        ];

        $customer->fill([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $customer->phone,
            'whatsapp' => $validated['phone'] ?? $customer->whatsapp,
            'status' => $customer->exists ? $customer->status : 'lead',
            'source' => $customer->source ?: 'trascendental_contact',
            'lifecycle_stage' => $customer->lifecycle_stage ?: 'lead',
            'lead_score' => max((int) ($customer->lead_score ?? 0), 25),
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

        if ($email = $this->notificationEmail()) {
            Mail::to($email)->send(new TrascendentalLeadNotification($customer, $contactLog));
        }

        return response()->json([
            'success' => true,
            'message' => __('trascendental.contact.success'),
        ]);
    }

    private function serviceLabel(string $service): string
    {
        return $service === 'booking' ? 'Booking' : 'Producción';
    }

    private function notificationEmail(): ?string
    {
        return config('trascendental.lead_notify_email')
            ?: config('mail.from.address');
    }
}
