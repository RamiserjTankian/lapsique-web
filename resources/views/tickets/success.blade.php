@extends('layouts.site')

@section('title', 'Compra confirmada | ' . $event->title)
@section('hide_navbar', '1')

@section('content')
    @php
        $allRegistered = $order->attendees_registered >= $order->attendees_expected;
    @endphp

    {{-- Barra mínima para página de confirmación --}}
    <nav class="sticky top-0 z-50 flex items-center justify-between px-6 py-4 bg-[var(--beige-50)] border-b border-[var(--beige-300)]">
        <a href="{{ route('home') }}" class="text-[var(--ink)] font-semibold tracking-tight">{{ __('messages.site.brand') }}</a>
        <a href="{{ route('events.show', $event) }}" class="btn btn-primary py-2 px-4 text-xs">Ver evento</a>
    </nav>

    <div class="min-h-screen bg-[var(--beige-100)] pt-10 pb-16">
        <div class="mx-auto max-w-2xl px-6">

            {{-- Header --}}
            <header class="text-center mb-10">
                <p class="label-small text-[var(--marine-500)] mb-2">Compra confirmada</p>
                <h1 class="display font-bold text-[var(--ink)]" style="font-size: clamp(1.75rem, 4vw, 2.5rem);">
                    {{ $allRegistered ? 'Tus accesos están listos' : 'Registra a cada asistente' }}
                </h1>
                <p class="mt-2 text-[var(--muted)] text-sm max-w-md mx-auto">
                    {{ $allRegistered ? 'Los accesos registrados ya pueden usar su QR.' : 'Completa los datos de cada persona. Recibirán su QR al guardar.' }}
                </p>
            </header>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-[var(--marine-300)] bg-[var(--marine-50)] px-4 py-3 text-sm text-[var(--marine-800)]">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Event + order summary card --}}
            <div class="card p-6 mb-6">
                <h2 class="display font-semibold text-[var(--ink)] text-xl">{{ $event->title }}</h2>
                <p class="mt-1 text-sm text-[var(--muted)]">
                    {{ optional($event->starts_at)->translatedFormat('d M Y H:i') ?? 'Fecha por definir' }}
                    @if ($event->venue || $event->city)
                        — {{ trim(implode(' ', array_filter([$event->venue, $event->city]))) }}
                    @endif
                </p>
            </div>

            @if ($allRegistered)
                {{-- List of products and attendees --}}
                <div class="space-y-6">
                    @foreach ($order->items as $item)
                        <div class="card overflow-hidden">
                            <div class="px-6 py-4 border-b border-[var(--beige-300)] bg-[var(--beige-50)]">
                                <h3 class="font-semibold text-[var(--ink)]">{{ $item->name }}</h3>
                                <p class="text-xs text-[var(--muted)] mt-0.5">
                                    {{ $item->attendees->count() }} acceso{{ $item->attendees->count() > 1 ? 's' : '' }} listo{{ $item->attendees->count() > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div class="p-6 space-y-4">
                                @foreach ($item->attendees as $index => $attendee)
                                    <div class="rounded-xl border border-[var(--card-border)] bg-white p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-[var(--ink)] truncate">{{ $attendee->name ?? ('Acceso ' . ($index + 1)) }}</h4>
                                            <p class="text-sm text-[var(--muted)] truncate">{{ $attendee->email }}</p>
                                            <span class="inline-block mt-2 pill text-xs border-[var(--marine-300)] text-[var(--marine-600)] bg-[var(--marine-50)]">Activo</span>
                                        </div>
                                        <div class="flex flex-wrap gap-2 shrink-0">
                                            <a href="{{ $attendee->getCheckInUrl() }}" class="btn btn-primary text-sm py-2.5 px-4">
                                                Ver QR
                                            </a>
                                            @if ($attendee->event)
                                                <a href="{{ route('events.show', $attendee->event) }}" class="btn btn-outline text-sm py-2.5 px-4 border-[var(--beige-400)] text-[var(--ink)] hover:bg-[var(--beige-100)]">
                                                    Ver evento
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Form: register attendees --}}
                <form action="{{ route('tickets.attendees.store', $order) }}" method="POST" class="space-y-6" id="ticket-attendees-form">
                    @csrf

                    @foreach ($order->items as $item)
                        @php
                            $registeredCount = $item->attendees->whereIn('status', ['registered', 'checked_in'])->count();
                            $totalAccess = $item->quantity * max($item->access_units, 1);
                        @endphp
                        <div class="card overflow-hidden">
                            <div class="px-6 py-4 border-b border-[var(--beige-300)] bg-[var(--beige-50)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <h3 class="font-semibold text-[var(--ink)]">{{ $item->name }}</h3>
                                    <p class="text-xs text-[var(--muted)]">
                                        {{ $item->quantity }} compra{{ $item->quantity > 1 ? 's' : '' }}
                                        · {{ $totalAccess }} acceso{{ $totalAccess > 1 ? 's' : '' }}
                                    </p>
                                </div>
                                <span class="pill text-xs {{ $registeredCount >= $totalAccess ? 'border-[var(--marine-300)] text-[var(--marine-600)] bg-[var(--marine-50)]' : 'border-amber-300 text-amber-800 bg-amber-50' }}">
                                    {{ $registeredCount }}/{{ $totalAccess }} registrados
                                </span>
                            </div>
                            <div class="p-6 space-y-4">
                                @foreach ($item->attendees as $index => $attendee)
                                    @php
                                        $isRegistered = in_array($attendee->status, ['registered', 'checked_in']);
                                    @endphp
                                    <div class="rounded-xl border border-[var(--card-border)] bg-white p-4 sm:p-5 space-y-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-sm font-medium text-[var(--muted)]">Acceso {{ $index + 1 }}</span>
                                            <span class="pill text-xs {{ $isRegistered ? 'border-[var(--marine-300)] text-[var(--marine-600)]' : 'border-amber-300 text-amber-800' }}">
                                                {{ $isRegistered ? 'Registrado' : 'Pendiente' }}
                                            </span>
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="attendees[{{ $attendee->id }}][name]" placeholder="Nombre completo" class="field" value="{{ old('attendees.' . $attendee->id . '.name', $attendee->name) }}">
                                            <input type="email" name="attendees[{{ $attendee->id }}][email]" placeholder="Email" class="field" value="{{ old('attendees.' . $attendee->id . '.email', $attendee->email) }}">
                                            <input type="tel" name="attendees[{{ $attendee->id }}][whatsapp]" placeholder="WhatsApp" class="field" value="{{ old('attendees.' . $attendee->id . '.whatsapp', $attendee->whatsapp) }}">
                                            <input type="text" name="attendees[{{ $attendee->id }}][instagram_handle]" placeholder="Instagram @usuario" class="field" value="{{ old('attendees.' . $attendee->id . '.instagram_handle', $attendee->instagram_handle) }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="card p-5">
                        <p class="text-sm text-[var(--muted)] mb-4">Puedes guardar y volver después desde el link de tu compra o desde tu portal.</p>
                        <button type="submit" class="btn btn-aggressive w-full justify-center">Guardar invitados</button>
                    </div>
                </form>
            @endif

            {{-- CTA --}}
            <div class="mt-10 text-center">
                <a href="{{ route('events.index') }}" class="btn btn-outline border-[var(--marine-400)] text-[var(--marine-600)]">
                    Ver más eventos
                </a>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
@php
    $ticketPurchaseContents = $order->items->map(fn ($item) => [
        'id' => (string) $item->ticket_product_id,
        'quantity' => (int) $item->quantity,
        'item_price' => (float) $item->unit_price,
    ])->values();
    $ticketContentIds = $order->items->pluck('ticket_product_id')->map(fn ($id) => (string) $id)->values();
@endphp
<script>
    const orderTotal = @json((float) $order->total);
    const orderCurrency = @json($order->currency);
    const purchaseEventId = @json('ticket_order_'.$order->public_id);
    const purchaseStorageKey = 'lapsique_purchase_tracked_{{ $order->public_id }}';
    const purchaseAlreadyTracked = window.localStorage.getItem(purchaseStorageKey) === '1';

    if (window.trackMetaPixel && !purchaseAlreadyTracked) {
        const purchaseContents = @json($ticketPurchaseContents);
        window.trackMetaPixel('Purchase', {
            value: orderTotal,
            currency: orderCurrency,
            content_type: 'product',
            content_ids: @json($ticketContentIds),
            content_name: @json($event->title),
            contents: purchaseContents,
        }, {
            eventID: purchaseEventId,
        });
        window.localStorage.setItem(purchaseStorageKey, '1');
    }

    if (!purchaseAlreadyTracked) {
        window.LapsiqueTracker.track('purchase_completed', {
            category: 'commerce',
            label: @json($event->title),
            value: orderTotal,
            metadata: {
                order_id: '{{ $order->public_id }}',
                event_id: '{{ $event->id }}',
                content_ids: @json($ticketContentIds),
                currency: orderCurrency,
            },
        });
    }

    document.getElementById('ticket-attendees-form')?.addEventListener('submit', () => {
        if (window.trackMetaPixel) {
            window.trackMetaPixel('CompleteRegistration', {
                status: 'attendees_submitted',
                content_ids: @json($ticketContentIds),
                value: orderTotal,
                currency: orderCurrency,
            });
        }

        window.LapsiqueTracker.track('attendees_registered', {
            category: 'commerce',
            label: 'ticket_attendees',
            value: orderTotal,
            metadata: {
                order_id: '{{ $order->public_id }}',
                event_id: '{{ $event->id }}',
                currency: orderCurrency,
            },
        });
    });
</script>
@endpush
