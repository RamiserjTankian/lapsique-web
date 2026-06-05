@extends('layouts.site')

@section('title', 'Check-in | ' . ($event?->title ?? __('messages.site.brand')))

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'attended' => 'Asistió',
            'cancelled' => 'Cancelado',
            'no_show' => 'No asistió',
        ];

        $statusLabel = $statusLabels[$entry->status] ?? ucfirst($entry->status ?? 'pendiente');
        $statusClass = match ($entry->status) {
            'confirmed' => 'bg-emerald-500/15 text-emerald-200 border-emerald-400/30',
            'attended' => 'bg-sky-500/15 text-sky-200 border-sky-400/30',
            'pending' => 'bg-amber-500/15 text-amber-200 border-amber-400/30',
            'cancelled', 'no_show' => 'bg-rose-500/15 text-rose-200 border-rose-400/30',
            default => 'bg-white/10 text-gray-200 border-white/20',
        };

        $invitedBy = $entry->dj?->name
            ?? $entry->rp?->name
            ?? $entry->inviteLink?->name
            ?? 'Trascendental';

        $checkInLimit = $entry->getCheckInLimit();
        $checkInCount = $entry->getCheckInCount();
        $remainingUses = $entry->getRemainingCheckIns();
    @endphp

    <div class="grid gap-8 lg:grid-cols-[1.3fr_0.7fr]">
        <section class="space-y-6">
            <div class="card card-animated p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Check-in</p>
                        <h1 class="mt-3 text-3xl font-semibold text-white">{{ $event?->title ?? 'Evento' }}</h1>
                        <p class="mt-2 text-sm text-gray-300">
                            Presenta este pase en la entrada para registrar tu acceso.
                        </p>
                    </div>
                    <span class="pill border {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Invitado</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $customer?->name ?? 'Invitado' }}</p>
                        <p class="text-sm text-gray-400">{{ $customer?->email ?? 'Email no disponible' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Invitado por</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $invitedBy }}</p>
                        <p class="text-sm text-gray-400">Guest list</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Fecha y hora</p>
                        <p class="mt-2 text-lg font-semibold text-white">
                            {{ $event?->starts_at?->format('d/m/Y H:i') ?? 'Por confirmar' }}
                        </p>
                        <p class="text-sm text-gray-400">{{ $event?->city ?? 'Ciudad por confirmar' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Venue</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $event?->venue ?? 'Por confirmar' }}</p>
                        <p class="text-sm text-gray-400">+{{ $entry->plus_ones ?? 0 }} acompañantes</p>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Estado del acceso</p>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-2xl font-semibold text-white">
                            {{ $remainingUses === 0 ? 'Consumos agotados' : ($checkInCount > 0 ? 'Check-in confirmado' : 'Pendiente de confirmación') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-400">
                            {{ $remainingUses === 0 ? 'Este QR ya alcanzó el límite de accesos.' : ($checkInCount > 0 ? 'Registro completado en el sistema.' : 'Confirma el acceso cuando la persona esté presente.') }}
                        </p>
                        <p class="mt-2 text-xs text-gray-400">
                            Usos restantes: {{ $remainingUses }} de {{ $checkInLimit }}
                        </p>
                    </div>
                    @if($entry->check_in_at)
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-500/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100">
                            {{ $entry->check_in_at->format('d/m/Y H:i') }}
                        </span>
                    @endif
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Acción</p>
                    <span class="text-xs text-gray-400">Código {{ $checkInCode }}</span>
                </div>

                @if(session('info'))
                    <div class="mt-4 rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                        {{ session('info') }}
                    </div>
                @endif

                @if($remainingUses === 0)
                    <div class="mt-4 rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                        Este QR ya agotó sus consumos permitidos.
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-300">
                        Confirma el check-in solo cuando el invitado esté presente.
                    </p>
                    <form method="POST" action="{{ $checkInConfirmUrl }}" class="mt-5">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full justify-center">
                            Confirmar check-in
                        </button>
                    </form>
                @endif
            </div>

            <div class="card p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">QR de acceso</p>
                <p class="mt-2 text-sm text-gray-300">
                    Muestra este QR en la entrada para registrar el acceso.
                </p>
                <div class="mt-4 flex justify-center">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <img src="{{ $checkInQrUrl }}" alt="QR de check-in" class="h-56 w-56 object-contain">
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Instrucciones rápidas</p>
                <ul class="mt-4 space-y-3 text-sm text-gray-300">
                    <li>1. Verifica nombre y email con el invitado.</li>
                    <li>2. Confirma el número de acompañantes.</li>
                    <li>3. Presiona “Confirmar check-in” para registrar acceso.</li>
                </ul>
                <div class="mt-5 rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Código manual</p>
                    <p class="mt-2 text-2xl font-semibold text-white tracking-[0.3em]">{{ $checkInCode }}</p>
                    <p class="mt-2 text-xs text-gray-400">Usa este código si el QR falla.</p>
                </div>
            </div>
        </aside>
    </div>
@endsection
