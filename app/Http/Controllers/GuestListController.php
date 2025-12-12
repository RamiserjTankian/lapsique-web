<?php

namespace App\Http\Controllers;

use App\Models\GuestListEntry;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class GuestListController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['nullable', 'exists:events,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'instagram_handle' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:femenino,masculino,otro'],
            'notes' => ['nullable', 'string', 'max:500'],
            'accepts_emails' => ['accepted'],
        ]);

        GuestListEntry::create([
            'event_id' => $validated['event_id'] ?? null,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'instagram_handle' => $validated['instagram_handle'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'accepts_emails' => $validated['accepts_emails'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Tu registro fue recibido. Te confirmaremos por correo.');
    }
}
