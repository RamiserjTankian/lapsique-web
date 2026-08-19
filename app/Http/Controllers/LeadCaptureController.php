<?php

namespace App\Http\Controllers;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\Customer;
use App\Services\CustomerAnalyticsAttributionService;
use App\Services\Meta\MetaConversionsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadCaptureController extends Controller
{
    /**
     * Capturar lead desde popup
     */
    public function capture(Request $request, CustomerAnalyticsAttributionService $attributionService): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'instagram_handle' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'marketing_consent' => ['required', 'accepted'],
            'meta_marketing_consent' => ['required', 'accepted'],
        ]);
        $filled = static fn ($value): bool => $value !== null && $value !== '';

        try {
            // Verificar si el cliente ya existe
            $customer = Customer::where('email', $validated['email'])->first();

            if ($customer) {
                // Cliente existente - actualizar información
                $customer->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'] ?? $customer->phone,
                    'whatsapp' => $validated['phone'] ?? $customer->whatsapp,
                    'instagram_handle' => $validated['instagram_handle'] ?? $customer->instagram_handle,
                    'tags' => array_unique(array_merge($customer->tags ?? [], $validated['interests'] ?? [])),
                    'last_interaction_at' => now(),
                ]);

                $metadata = is_array($customer->metadata) ? $customer->metadata : [];
                $metadata['popup_capture'] = array_filter([
                    'signup_page' => $request->input('current_page'),
                    'landing_page' => $request->input('landing_page'),
                    'landing_url' => $request->input('landing_url'),
                    'page_type' => $request->input('page_type'),
                    'page_name' => $request->input('page_name'),
                    'message' => $validated['message'] ?? null,
                    'referrer' => $request->input('referrer'),
                    'analytics_visitor_id' => $request->input('analytics_visitor_id'),
                    'analytics_session_id' => $request->input('analytics_session_id'),
                    'fbp' => $request->input('fbp'),
                    'fbc' => $request->input('fbc'),
                    'marketing_consent' => true,
                    'meta_marketing_consent' => true,
                    'consented_at' => now()->toIso8601String(),
                    'captured_at' => now()->toIso8601String(),
                ], $filled);

                $customer->forceFill([
                    'utm_source' => $customer->utm_source ?: $request->input('utm_source'),
                    'utm_medium' => $customer->utm_medium ?: $request->input('utm_medium'),
                    'utm_campaign' => $customer->utm_campaign ?: $request->input('utm_campaign'),
                    'utm_term' => $customer->utm_term ?: $request->input('utm_term'),
                    'utm_content' => $customer->utm_content ?: $request->input('utm_content'),
                    'ip_address' => $customer->ip_address ?: $request->ip(),
                    'user_agent' => $customer->user_agent ?: $request->userAgent(),
                    'metadata' => $metadata,
                ])->save();

                Log::info('Existing customer updated from popup', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                ]);

                $attributionService->identify(
                    $customer,
                    $request->input('analytics_visitor_id'),
                    $request->input('analytics_session_id'),
                    'popup_existing_customer',
                );

                $metaEventId = 'lead_customer_'.$customer->id;

                try {
                    app(MetaConversionsApiService::class)->sendLeadFromCustomer(
                        $customer->fresh(),
                        $request->input('landing_url') ?: $request->input('current_page'),
                    );
                } catch (\Throwable $e) {
                    Log::warning('Meta lead event failed after popup capture', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => trans('funnel.newsletter.success_existing'),
                    'new_customer' => false,
                    'meta_event_id' => $metaEventId,
                ]);
            }

            // Nuevo cliente - crear
            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'instagram_handle' => $validated['instagram_handle'] ?? null,
                'tags' => $validated['interests'] ?? [],
                'status' => 'lead',
                'source' => 'popup',
                'lifecycle_stage' => 'subscriber',
                'lead_score' => 10, // Score inicial por registrarse
                'subscribed_newsletter' => true,

                // Tracking UTM
                'utm_source' => $request->input('utm_source'),
                'utm_medium' => $request->input('utm_medium'),
                'utm_campaign' => $request->input('utm_campaign'),
                'utm_term' => $request->input('utm_term'),
                'utm_content' => $request->input('utm_content'),

                // Info técnica
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_interaction_at' => now(),

                // Metadata adicional
                'metadata' => [
                    'signup_page' => $request->input('current_page'),
                    'landing_page' => $request->input('landing_page'),
                    'landing_url' => $request->input('landing_url'),
                    'page_type' => $request->input('page_type'),
                    'page_name' => $request->input('page_name'),
                    'message' => $validated['message'] ?? null,
                    'referrer' => $request->input('referrer'),
                    'analytics_visitor_id' => $request->input('analytics_visitor_id'),
                    'analytics_session_id' => $request->input('analytics_session_id'),
                    'fbp' => $request->input('fbp'),
                    'fbc' => $request->input('fbc'),
                    'marketing_consent' => true,
                    'meta_marketing_consent' => true,
                    'consented_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('New customer created from popup', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'source' => 'popup',
            ]);

            $attributionService->identify(
                $customer,
                $request->input('analytics_visitor_id'),
                $request->input('analytics_session_id'),
                'popup_lead',
            );

            $metaEventId = 'lead_customer_'.$customer->id;

            try {
                app(MetaConversionsApiService::class)->sendLeadFromCustomer(
                    $customer,
                    $request->input('landing_url') ?: $request->input('current_page'),
                );
            } catch (\Throwable $e) {
                Log::warning('Meta lead event failed after popup capture', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Despachar email de bienvenida
            SendWelcomeEmailJob::dispatch($customer);

            return response()->json([
                'success' => true,
                'message' => trans('funnel.newsletter.success_default'),
                'new_customer' => true,
                'meta_event_id' => $metaEventId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to capture lead from popup', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('funnel.newsletter.capture_error'),
            ], 500);
        }
    }

    /**
     * Desuscribir cliente
     */
    public function unsubscribe(Request $request): mixed
    {
        $email = $request->query('email');

        if (! $email) {
            abort(404);
        }

        $customer = Customer::where('email', $email)->first();

        if (! $customer) {
            abort(404);
        }

        if ($request->isMethod('post')) {
            $customer->update([
                'subscribed_newsletter' => false,
                'subscribed_sms' => false,
                'subscribed_whatsapp' => false,
                'status' => 'inactive',
            ]);

            Log::info('Customer unsubscribed', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
            ]);

            return redirect()->route('customer.unsubscribe', ['email' => $email, 'success' => 1]);
        }

        return view('customer.unsubscribe', compact('customer'));
    }
}
