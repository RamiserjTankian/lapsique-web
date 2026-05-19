<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketAccessEmailJob;
use App\Models\Customer;
use App\Models\TicketAttendee;
use App\Models\TicketOrder;
use App\Services\TicketOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TicketAttendeeController extends Controller
{
    public function store(Request $request, TicketOrder $order, TicketOrderService $orderService): RedirectResponse
    {
        if ($order->status !== 'paid') {
            return back()->withErrors(['order' => 'El pago aún está pendiente.'])->withInput();
        }

        $validated = $request->validate([
            'attendees' => ['required', 'array'],
        ]);

        $attendees = $order->attendees()->get()->keyBy('id');
        $rowsToPersist = [];

        foreach ($validated['attendees'] as $attendeeId => $data) {
            /** @var TicketAttendee|null $attendee */
            $attendee = $attendees->get((int) $attendeeId);

            if (! $attendee) {
                continue;
            }

            $payload = [
                'name' => trim((string) ($data['name'] ?? '')),
                'email' => trim((string) ($data['email'] ?? '')),
                'whatsapp' => trim((string) ($data['whatsapp'] ?? '')),
                'instagram_handle' => trim((string) ($data['instagram_handle'] ?? '')),
            ];

            $hasInput = collect($payload)->contains(fn ($value) => $value !== '');

            if (! $hasInput && $attendee->status === 'pending') {
                continue;
            }

            $validator = Validator::make($payload, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'whatsapp' => ['required', 'string', 'max:30'],
                'instagram_handle' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $rowsToPersist[$attendee->id] = $payload;
        }

        if ($rowsToPersist === []) {
            return back()->withErrors([
                'attendees' => 'Ingresa al menos un invitado para guardar cambios.',
            ])->withInput();
        }

        DB::transaction(function () use ($order, $rowsToPersist, $orderService) {
            foreach ($rowsToPersist as $attendeeId => $data) {
                /** @var TicketAttendee $attendee */
                $attendee = $order->attendees()->where('id', $attendeeId)->first();

                if (! $attendee) {
                    continue;
                }

                $customer = Customer::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'whatsapp' => $data['whatsapp'],
                        'phone' => $data['whatsapp'],
                        'instagram_handle' => $data['instagram_handle'],
                        'source' => 'ticketing',
                        'subscribed_whatsapp' => true,
                        'subscribed_sms' => true,
                        'last_interaction_at' => now(),
                    ]
                );

                $wasRegistered = in_array($attendee->status, ['registered', 'checked_in'], true);

                $attendee->update([
                    'customer_id' => $customer->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'whatsapp' => $data['whatsapp'],
                    'phone' => $data['whatsapp'],
                    'instagram_handle' => $data['instagram_handle'],
                    'status' => 'registered',
                    'registered_at' => $attendee->registered_at ?? now(),
                ]);

                if (! $wasRegistered) {
                    SendTicketAccessEmailJob::dispatchAfterResponse($attendee);
                }
            }

            $orderService->updateAttendeesRegisteredCount($order);
        });

        $order->refresh();

        return redirect()->route('tickets.success', $order)
            ->with(
                'success',
                $order->attendees_registered >= $order->attendees_expected
                    ? 'Listo. Todos los accesos quedaron registrados y los QR fueron enviados.'
                    : 'Cambios guardados. Puedes volver después para registrar los invitados faltantes.'
            );
    }
}
