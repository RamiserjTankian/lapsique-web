<?php

namespace App\Services;

use App\Models\Event;
use App\Models\GuestListEntry;
use App\Models\TicketAttendee;
use App\Models\TicketOrder;
use App\Support\EventLineup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TicketPassPdfService
{
    public function buildForOrder(TicketOrder $order)
    {
        $order->loadMissing([
            'event.djs',
            'event.location',
            'items.attendees',
        ]);

        $viewData = $this->buildViewData($order, null);

        return $this->makePdf($viewData);
    }

    public function buildForAttendee(TicketAttendee $attendee)
    {
        $attendee->loadMissing([
            'event.djs',
            'event.location',
            'product',
            'order.items.attendees',
        ]);

        $viewData = $this->buildViewData($attendee->order, $attendee);

        return $this->makePdf($viewData);
    }

    public function buildForGuestListEntry(GuestListEntry $entry)
    {
        $entry->loadMissing([
            'event.djs',
            'event.location',
            'customer',
            'dj',
            'rp',
            'inviteLink',
        ]);

        return $this->makePdf($this->buildGuestListViewData($entry));
    }

    public function filenameForEvent(?Event $event): string
    {
        return 'pase-' . ($event ? Str::slug($event->title) : 'acceso') . '.pdf';
    }

    protected function makePdf(array $viewData)
    {
        return Pdf::loadView('pdfs.ticket-pass', $viewData)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
            ]);
    }

    protected function buildViewData(?TicketOrder $order, ?TicketAttendee $fallbackAttendee): array
    {
        $event = $order?->event ?? $fallbackAttendee?->event;
        $assets = $this->buildEventAssets($event);
        $attendees = $order
            ? $this->buildAttendeesForOrder($order)
            : collect();

        if ($attendees->isEmpty() && $fallbackAttendee) {
            $attendees = $this->buildFallbackAttendeeCollection($fallbackAttendee);
        }

        return [
            'event' => $event,
            'order' => $order,
            'attendees' => $attendees,
            'total' => $attendees->count(),
            'flyerUrl' => $assets['flyerUrl'],
            'venueUrl' => $assets['venueUrl'],
            'lineup' => $assets['lineup'],
            'digitalAccessCopy' => 'Este QR funciona para acceso, relecturas y consumos permitidos del evento. Guardalo en tu telefono.',
            'usageCopy' => 'Presenta este pase en la entrada y conservalo para accesos, relecturas y consumos permitidos.',
            'summaryCopy' => 'Pase individual emitido para acceso y control de consumo en el evento.',
            'testMode' => (bool) ($order?->items->contains(
                fn ($item) => data_get($item->metadata, 'sales_mode') === 'testing'
            ) || data_get($fallbackAttendee?->product?->metadata, 'sales_mode') === 'testing'),
        ];
    }

    protected function buildGuestListViewData(GuestListEntry $entry): array
    {
        $event = $entry->event;
        $assets = $this->buildEventAssets($event);

        return [
            'event' => $event,
            'order' => null,
            'attendees' => $this->buildGuestListAttendeeCollection($entry),
            'total' => 1,
            'flyerUrl' => $assets['flyerUrl'],
            'venueUrl' => $assets['venueUrl'],
            'lineup' => $assets['lineup'],
            'digitalAccessCopy' => 'Este QR corresponde a una guest list. Solo permite el acceso al evento y no incluye consumo ni beneficios adicionales.',
            'usageCopy' => 'Presenta este pase en la entrada. Este QR es unicamente para acceso y no incluye consumo.',
            'summaryCopy' => 'Pase de guest list emitido unicamente para acceso al evento. No incluye consumo.',
            'testMode' => false,
        ];
    }

    protected function buildAttendeesForOrder(TicketOrder $order): Collection
    {
        $attendees = collect();
        $index = 1;

        foreach ($order->items as $item) {
            $isTable = $item->category === 'table';
            $accessUnits = max((int) ($item->access_units ?? 1), 1);
            $unitBasePrice = (float) data_get($item->metadata, 'unit_base_price', 0);
            $consumoBase = $isTable
                ? number_format((float) $item->unit_price / (1 + 0.15), 0)
                : null;

            foreach ($item->attendees->sortBy('id') as $attendee) {
                $attendee->ensureInviteToken();
                $hasAssignedName = filled($attendee->name);

                $attendees->push([
                    'name' => $attendee->name ?: 'Acceso pendiente de asignar',
                    'is_unassigned' => ! $hasAssignedName,
                    'product' => $item->name,
                    'is_table' => $isTable,
                    'access_units' => $accessUnits,
                    'consumo_note' => $isTable
                        ? 'Consumible total de la mesa: $' . $consumoBase . ' MXN para ' . $accessUnits . ' personas'
                        : ($unitBasePrice > 0 ? 'Consumible incluido: $' . number_format($unitBasePrice, 0) . ' MXN' : null),
                    'code' => strtoupper(substr($attendee->invite_token, -6)),
                    'qrUrl' => route('tickets.checkin.qr', ['token' => $attendee->invite_token]),
                    'index' => $index,
                ]);

                $index++;
            }
        }

        return $attendees;
    }

    protected function buildFallbackAttendeeCollection(TicketAttendee $attendee): Collection
    {
        $attendee->ensureInviteToken();

        return collect([
            [
                'name' => $attendee->name ?: 'Acceso pendiente de asignar',
                'is_unassigned' => ! filled($attendee->name),
                'product' => $attendee->product?->name ?? 'Acceso',
                'is_table' => false,
                'access_units' => 1,
                'consumo_note' => $attendee->product
                    ? 'Consumible incluido: $' . number_format((float) $attendee->product->base_price, 0) . ' MXN'
                    : null,
                'code' => strtoupper(substr($attendee->invite_token, -6)),
                'qrUrl' => route('tickets.checkin.qr', ['token' => $attendee->invite_token]),
                'index' => 1,
            ],
        ]);
    }

    protected function buildGuestListAttendeeCollection(GuestListEntry $entry): Collection
    {
        $entry->ensureInviteToken();

        return collect([
            [
                'name' => $entry->customer?->name ?: 'Invitado',
                'is_unassigned' => false,
                'product' => 'Guest List',
                'is_table' => false,
                'access_units' => 1,
                'consumo_note' => 'Guest list sin consumo. Este pase incluye unicamente el acceso al evento.',
                'code' => $entry->getCheckInCode(),
                'qrUrl' => $entry->getCheckInQrUrl(),
                'index' => 1,
            ],
        ]);
    }

    protected function buildEventAssets(?Event $event): array
    {
        $flyerUrl = null;
        if ($event) {
            $flyerUrl = $event->getFirstMediaUrl('cover_vertical', 'poster_vertical')
                ?: $event->getFirstMediaUrl('cover_vertical')
                ?: $event->getFirstMediaUrl('cover', 'cover_large')
                ?: $event->getFirstMediaUrl('cover');

            if ($flyerUrl && ! str_starts_with($flyerUrl, 'http')) {
                $flyerUrl = url($flyerUrl);
            }
        }

        $venueUrl = null;
        if ($event?->location) {
            $venueUrl = $event->location->getFirstMediaUrl('cover')
                ?: $event->location->getFirstMediaUrl('gallery');

            if ($venueUrl && ! str_starts_with($venueUrl, 'http')) {
                $venueUrl = url($venueUrl);
            }
        }

        $lineup = collect();
        if ($event && $event->djs->isNotEmpty()) {
            $roleLabels = ['headliner' => 'Headliner', 'warmup' => 'Warm Up', 'local' => 'Local'];
            $roleOrder = ['headliner' => 0, 'warmup' => 1, 'local' => 2];

            $lineup = EventLineup::displayEntries($event->djs)
                ->map(function ($entry) use ($roleLabels, $roleOrder) {
                    $djs = collect($entry['djs'] ?? []);
                    $isB2b = ($entry['type'] ?? 'single') === 'b2b' && $djs->count() === 2;
                    $photos = $djs->map(function ($dj) {
                        $url = $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');

                        if ($url && ! str_starts_with($url, 'http')) {
                            $url = url($url);
                        }

                        return [
                            'url' => $url ?: null,
                            'name' => $dj->name,
                        ];
                    })->values();

                    return [
                        'role_label' => $roleLabels[$entry['role'] ?? 'warmup'] ?? 'Warm Up',
                        'role_order' => $roleOrder[$entry['role'] ?? 'warmup'] ?? 9,
                        'name' => $djs->pluck('name')->implode(' × '),
                        'time_slot' => $entry['time_slot'] ?? null,
                        'is_b2b' => $isB2b,
                        'photos' => $photos,
                        'bio' => $djs->first()?->bio,
                    ];
                })
                ->sortBy('role_order')
                ->values();
        }

        return [
            'flyerUrl' => $flyerUrl,
            'venueUrl' => $venueUrl,
            'lineup' => $lineup,
        ];
    }
}
