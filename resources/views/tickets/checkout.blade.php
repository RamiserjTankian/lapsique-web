@extends('layouts.site')

@section('title', 'Tickets | ' . $event->title)

@section('content')
    <div class="space-y-8">
        @php
            $currency = $products->first()?->currency ?? config('mercadopago.currency', 'MXN');
        @endphp
        <div class="flex flex-col gap-3">
            <p class="pill">Tickets</p>
            <h1 class="text-3xl font-semibold text-white">Compra tus accesos</h1>
            <p class="text-gray-300">Registro individual por persona: cada acceso requiere nombre, correo, WhatsApp e Instagram.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card p-6 space-y-2">
            <h2 class="text-2xl font-semibold text-white">{{ $event->title }}</h2>
            <p class="text-sm text-gray-400">
                {{ optional($event->starts_at)->translatedFormat('d M Y H:i') ?? 'Fecha por definir' }}
                @if ($event->venue)
                    — {{ $event->venue }} {{ $event->city }}
                @endif
            </p>
            @if ($inviteLink)
                <p class="text-xs uppercase tracking-[0.18em] text-emerald-300">
                    Invitación registrada: {{ $inviteLink->rp?->name ?? $inviteLink->dj?->name ?? $inviteLink->name }}
                </p>
            @endif
        </div>

        @include('tickets.partials.checkout-form', [
            'event' => $event,
            'products' => $products,
            'inviteToken' => $inviteToken,
            'inviteLink' => $inviteLink,
        ])
    </div>
@endsection
