<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GuestListEntry;
use App\Models\Event;
use App\Jobs\SendEventConfirmationJob;
use App\Jobs\SendWelcomeEmailJob;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class GuestListController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'instagram_handle' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:femenino,masculino,otro'],
            'notes' => ['nullable', 'string', 'max:500'],
            'accepts_emails' => ['accepted'],
        ]);

        try {
            // Find or create customer
            $customer = Customer::where('email', $validated['email'])->first();
            $isNewCustomer = false;

            if (!$customer) {
                $isNewCustomer = true;
                
                $customer = Customer::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['whatsapp'] ?? null,
                    'whatsapp' => $validated['whatsapp'] ?? null,
                    'instagram_handle' => $validated['instagram_handle'] ?? null,
                    'status' => 'lead',
                    'source' => 'guestlist',
                    'lifecycle_stage' => 'lead',
                    'lead_score' => 15, // Score inicial más alto por registrarse a evento
                    'subscribed_newsletter' => true,
                    'subscribed_sms' => !empty($validated['whatsapp']),
                    'subscribed_whatsapp' => !empty($validated['whatsapp']),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_interaction_at' => now(),
                ]);

                Log::info('New customer created from guest list', [
                    'customer_id' => $customer->id,
                    'event_id' => $validated['event_id'],
                ]);
            } else {
                // Actualizar información del customer si cambió
                $customer->update([
                    'name' => $validated['full_name'],
                    'whatsapp' => $validated['whatsapp'] ?? $customer->whatsapp,
                    'instagram_handle' => $validated['instagram_handle'] ?? $customer->instagram_handle,
                    'subscribed_newsletter' => true,
                    'last_interaction_at' => now(),
                ]);

                // Incrementar lead score por nueva inscripción
                $customer->incrementLeadScore(10);

                Log::info('Existing customer registered to event', [
                    'customer_id' => $customer->id,
                    'event_id' => $validated['event_id'],
                ]);
            }

            // Verificar si ya está registrado en este evento
            $existingEntry = GuestListEntry::where('customer_id', $customer->id)
                ->where('event_id', $validated['event_id'])
                ->first();

            if ($existingEntry) {
                return back()->with('info', 'Ya estás registrado en este evento. ¡Nos vemos!');
            }

            // Create guest list entry (simplificado - sin campos duplicados)
            $guestListEntry = GuestListEntry::create([
                'event_id' => $validated['event_id'],
                'customer_id' => $customer->id,
                'gender' => $validated['gender'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'confirmed', // Auto-confirmar
            ]);

            Log::info('Guest list entry created', [
                'customer_id' => $customer->id,
                'event_id' => $validated['event_id'],
                'entry_id' => $guestListEntry->id,
            ]);

            // Despachar emails asíncronamente
            if ($isNewCustomer) {
                // Enviar email de bienvenida primero
                SendWelcomeEmailJob::dispatch($customer)->delay(now()->addSeconds(5));
            }

            // Enviar confirmación del evento
            SendEventConfirmationJob::dispatch($guestListEntry)->delay(now()->addSeconds(10));

            return back()->with('success', '¡Listo! Revisa tu email para la confirmación del evento.');
        } catch (\Exception $e) {
            Log::error('Failed to create guest list entry', [
                'email' => $validated['email'],
                'event_id' => $validated['event_id'],
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Ocurrió un error. Por favor intenta de nuevo.');
        }
    }
}
