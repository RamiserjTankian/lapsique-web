<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\GuestListEntry;
use App\Models\Customer;
use App\Models\Dj;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestListInviteController extends Controller
{
    /**
     * Mostrar formulario de invitación por token de DJ
     */
    public function show(Request $request, string $token)
    {
        $entry = GuestListEntry::where('invite_token', $token)->firstOrFail();
        
        // Si ya está confirmado, mostrar confirmación
        if ($entry->status === 'confirmed' || $entry->status === 'attended') {
            return view('guest-list.confirmed', [
                'entry' => $entry,
                'event' => $entry->event,
                'dj' => $entry->dj,
            ]);
        }

        return view('guest-list.invite', [
            'entry' => $entry,
            'event' => $entry->event,
            'dj' => $entry->dj,
        ]);
    }

    /**
     * Procesar confirmación de invitación
     */
    public function confirm(Request $request, string $token)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'plus_ones' => 'nullable|integer|min:0',
        ]);

        $entry = GuestListEntry::where('invite_token', $token)->firstOrFail();

        // Verificar límite de invitados del DJ
        if ($entry->dj_id && $entry->event_id) {
            $dj = $entry->dj;
            $limit = $dj->getGuestLimitForEvent($entry->event_id);
            
            if ($limit !== null) {
                $currentCount = $dj->getGuestListCountForEvent($entry->event_id);
                $plusOnes = (int) ($request->plus_ones ?? 0);
                $totalGuests = $currentCount + 1 + $plusOnes; // +1 por el invitado principal
                
                if ($totalGuests > $limit) {
                    return back()->withErrors([
                        'plus_ones' => "El límite de invitados para este DJ es {$limit}. Actualmente hay {$currentCount} invitados confirmados."
                    ])->withInput();
                }
            }
        }

        // Buscar o crear cliente
        $customer = Customer::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'phone' => $request->phone,
                'source' => 'guestlist',
            ]
        );

        // Actualizar información del cliente si es necesario
        if (!$customer->wasRecentlyCreated) {
            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone ?? $customer->phone,
            ]);
        }

        // Actualizar entrada de guest list
        $entry->update([
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'plus_ones' => (int) ($request->plus_ones ?? 0),
        ]);

        return redirect()->route('guestlist.invite.show', $token)
            ->with('success', '¡Invitación confirmada! Te esperamos en el evento.');
    }

    /**
     * Generar link de invitación para un DJ en un evento
     */
    public function generateLink(Request $request, int $eventId, int $djId)
    {
        $event = Event::findOrFail($eventId);
        $dj = Dj::findOrFail($djId);

        // Verificar que el DJ esté en el lineup del evento
        if (!$event->djs()->where('djs.id', $djId)->exists()) {
            abort(404, 'DJ no está en el lineup de este evento');
        }

        // Crear entrada de guest list con token único
        $token = GuestListEntry::generateInviteToken();
        
        $entry = GuestListEntry::create([
            'event_id' => $eventId,
            'dj_id' => $djId,
            'rp_id' => $request->user()?->id ?? null, // Si hay usuario autenticado
            'invite_token' => $token,
            'status' => 'pending',
        ]);

        $inviteUrl = route('guestlist.invite.show', $token);

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => $inviteUrl,
            'entry_id' => $entry->id,
        ]);
    }
}
