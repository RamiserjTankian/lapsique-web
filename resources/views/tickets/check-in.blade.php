@extends('layouts.site')

@section('title', 'Tu pase digital | ' . ($event?->title ?? 'Evento'))
@section('hide_navbar', '1')

@section('content')
    {{-- Barra mínima --}}
    <nav class="sticky top-0 z-50 flex items-center justify-between px-6 py-4 bg-[var(--beige-50)] border-b border-[var(--beige-300)]">
        <a href="{{ route('home') }}" class="text-[var(--ink)] font-semibold tracking-tight">{{ __('messages.site.brand') }}</a>
        <a href="{{ route('events.show', $event) }}" class="btn btn-primary py-2 px-4 text-xs">Ver evento</a>
    </nav>

    <div class="min-h-screen bg-[var(--beige-100)] pt-10 pb-16">
        <div class="mx-auto max-w-lg px-6">

            {{-- Header --}}
            <header class="text-center mb-8">
                <p class="label-small text-[var(--marine-500)] mb-2">Acceso</p>
                <h1 class="display font-bold text-[var(--ink)]" style="font-size: clamp(1.75rem, 4vw, 2.25rem);">
                    Tu pase digital
                </h1>
                <p class="mt-2 text-[var(--muted)] text-sm">
                    Presenta este QR en la entrada.
                </p>
            </header>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-[var(--marine-300)] bg-[var(--marine-50)] px-4 py-3 text-sm text-[var(--marine-800)]">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ session('info') }}
                </div>
            @endif

            {{-- Evento + invitado --}}
            <div class="card p-6 mb-6">
                <h2 class="display font-semibold text-[var(--ink)] text-xl">{{ $event?->title ?? 'Evento' }}</h2>
                <p class="mt-1 text-sm text-[var(--muted)]">
                    {{ optional($event?->starts_at)->translatedFormat('d M Y H:i') ?? 'Fecha por definir' }}
                </p>
                @if ($event?->venue || $event?->city)
                    <p class="text-sm text-[var(--muted)]">{{ trim(implode(' ', array_filter([$event?->venue, $event?->city]))) }}</p>
                @endif
                <div class="mt-4 pt-4 border-t border-[var(--beige-300)] rounded-xl bg-[var(--beige-50)] p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.2em] text-[var(--marine-500)] font-semibold">Invitad@</p>
                    <p class="mt-1 font-semibold text-[var(--ink)] text-lg">{{ $attendee->name ?? 'Acceso confirmado' }}</p>
                </div>
            </div>

            {{-- QR + código manual --}}
            <div class="card p-6 text-center">
                <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--marine-500)]">QR de acceso</h3>
                <div class="mt-4 inline-flex items-center justify-center rounded-2xl border-2 border-[var(--card-border)] bg-white p-4 shadow-sm">
                    <img src="{{ $checkInQrUrl }}" alt="QR de acceso" class="h-52 w-52 rounded-xl" />
                </div>
                <p class="mt-4 text-sm text-[var(--muted)]">
                    Código manual: <span class="font-semibold text-[var(--ink)]">{{ $checkInCode }}</span>
                </p>
            </div>

            {{-- Acciones --}}
            <div class="mt-6 flex flex-col gap-3">
                <a href="{{ route('tickets.checkin.pdf', $attendee->invite_token) }}"
                   target="_blank"
                   class="btn btn-outline w-full justify-center border-[var(--marine-500)] text-[var(--marine-700)] hover:bg-[var(--marine-50)]">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Descargar pase (PDF)
                </a>
                <a href="{{ route('home') }}" class="btn btn-aggressive w-full justify-center">
                    Ver evento
                </a>
            </div>

        </div>
    </div>
@endsection
