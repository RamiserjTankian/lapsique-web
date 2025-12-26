<?php

namespace App\Http\Controllers;

use App\Jobs\SendEventConfirmationJob;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\GuestListInviteLink;
use App\Models\GuestListEntry;
use App\Models\Customer;
use Illuminate\Http\Request;

class GuestListRegisterController extends Controller
{
    /**
     * Mostrar formulario de registro público desde link
     */
    public function show(Request $request, string $token)
    {
        $inviteLink = GuestListInviteLink::where('token', $token)
            ->where('is_active', true)
            ->with(['event', 'dj', 'rp'])
            ->firstOrFail();

        // Verificar si el link puede aceptar más registros
        if (!$inviteLink->canAcceptMoreRegistrations()) {
            return view('guest-list.link-expired', [
                'inviteLink' => $inviteLink,
            ]);
        }

        $event = $inviteLink->event;
        $event->load('djs'); // Cargar DJs del evento para mostrar el lineup

        return view('guest-list.register', [
            'inviteLink' => $inviteLink,
            'event' => $event,
            'dj' => $inviteLink->dj,
            'rp' => $inviteLink->rp,
        ]);
    }

    /**
     * Procesar registro público
     */
    public function store(Request $request, string $token)
    {
        $inviteLink = GuestListInviteLink::where('token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        // Verificar si el link puede aceptar más registros
        if (!$inviteLink->canAcceptMoreRegistrations()) {
            return back()->withErrors(['error' => 'Este link ya no está disponible para nuevos registros.'])->withInput();
        }

        // Validar límite de invitados si es por DJ
        if ($inviteLink->dj_id && $inviteLink->event_id) {
            $dj = $inviteLink->dj;
            $limit = $dj->getGuestLimitForEvent($inviteLink->event_id);
            
            if ($limit !== null) {
                $currentCount = $dj->getGuestListCountForEvent($inviteLink->event_id);
                
                // Cada registro consume 1 uso
                if ($currentCount >= $limit) {
                    return back()->withErrors([
                        'error' => "El límite de invitados para este DJ es {$limit}. Ya se alcanzó el límite."
                    ])->withInput();
                }
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'instagram_handle' => 'nullable|string|max:255',
            'gender' => 'nullable|in:femenino,masculino,otro',
            'consent_marketing' => 'required|accepted',
        ], [
            'consent_marketing.required' => 'Debes aceptar el consentimiento de marketing para continuar.',
            'consent_marketing.accepted' => 'Debes aceptar el consentimiento de marketing para continuar.',
        ]);

        // Buscar o crear cliente
        $customer = Customer::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'instagram_handle' => $request->instagram_handle,
                'source' => 'guestlist',
                'subscribed_whatsapp' => (bool) $request->consent_marketing,
                'subscribed_sms' => (bool) $request->consent_marketing,
            ]
        );

        // Verificar si este cliente ya está registrado en este link específico
        $existingEntry = GuestListEntry::where('invite_link_id', $inviteLink->id)
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->first();

        if ($existingEntry) {
            // Si ya existe un registro activo, redirigir a la página de éxito sin crear duplicado
            return redirect()->route('guestlist.register.thankyou', $token)
                ->with('info', 'Ya estás registrado en esta guest list.');
        }

        // Actualizar información del cliente si es necesario
        if (!$customer->wasRecentlyCreated) {
            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone ?? $customer->phone,
                'whatsapp' => $request->whatsapp ?? $customer->whatsapp,
                'instagram_handle' => $request->instagram_handle ?? $customer->instagram_handle,
                'subscribed_whatsapp' => (bool) $request->consent_marketing,
                'subscribed_sms' => (bool) $request->consent_marketing,
            ]);
        }

        // Crear entrada de guest list (cada registro consume 1 uso)
        $entry = GuestListEntry::create([
            'event_id' => $inviteLink->event_id,
            'customer_id' => $customer->id,
            'dj_id' => $inviteLink->dj_id,
            'rp_id' => $inviteLink->rp_id,
            'invite_link_id' => $inviteLink->id,
            'status' => 'confirmed',
            'plus_ones' => 0, // Sin acompañantes, cada registro es único
            'gender' => $request->gender,
        ]);

        // Incrementar contador del link (cada registro consume 1 uso)
        $inviteLink->incrementRegistrations();

        if ($customer->wasRecentlyCreated) {
            SendWelcomeEmailJob::dispatch($customer)->delay(now()->addSeconds(5));
        }

        SendEventConfirmationJob::dispatch($entry)->delay(now()->addSeconds(10));

        return redirect()->route('guestlist.register.thankyou', $token);
    }

    /**
     * Mostrar página de éxito después del registro
     */
    public function success(Request $request, string $token)
    {
        $inviteLink = GuestListInviteLink::where('token', $token)
            ->with(['event', 'dj', 'rp'])
            ->firstOrFail();

        return view('guest-list.register-success', [
            'inviteLink' => $inviteLink,
            'event' => $inviteLink->event,
            'dj' => $inviteLink->dj,
            'rp' => $inviteLink->rp,
        ]);
    }

    /**
     * Mostrar página de agradecimiento después del registro
     */
    public function thankyou(Request $request, string $token)
    {
        $inviteLink = GuestListInviteLink::where('token', $token)
            ->with(['event', 'dj', 'rp'])
            ->firstOrFail();

        return view('guest-list.thank-you', [
            'inviteLink' => $inviteLink,
            'event' => $inviteLink->event,
            'dj' => $inviteLink->dj,
            'rp' => $inviteLink->rp,
        ]);
    }
}
