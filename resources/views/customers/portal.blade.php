@extends('layouts.site')

@section('title', 'Mi portal | ' . __('messages.site.brand'))
@section('hide_navbar', '1')
@section('minimal_footer', '1')

@section('content')
@php
    $bookings = $customer->contentBookings->sortByDesc('created_at');
    $readyBookings = $bookings->filter(fn ($booking) => $booking->deliverables_ready_at && $booking->getMedia('deliverables')->isNotEmpty());
    $bookingCount = $bookings->count();
    $paymentCount = $payments->count();
    $deliverablesCount = $readyBookings->sum(fn ($booking) => $booking->getMedia('deliverables')->count());
    $statusStyles = [
        'confirmed' => 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300',
        'pending_payment' => 'border-amber-400/30 bg-amber-500/10 text-amber-300',
        'pending' => 'border-amber-400/30 bg-amber-500/10 text-amber-300',
        'failed' => 'border-red-400/30 bg-red-500/10 text-red-300',
        'cancelled' => 'border-zinc-500/40 bg-zinc-500/10 text-zinc-300',
        'paid' => 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300',
        'registered' => 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300',
        'checked_in' => 'border-cyan-400/30 bg-cyan-500/10 text-cyan-300',
        'rejected' => 'border-red-400/30 bg-red-500/10 text-red-300',
    ];
@endphp

<div class="-mx-6 -mt-10 mb-8 border-b border-white/10 bg-black/70 backdrop-blur sticky top-0 z-50">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <a href="{{ route('home') }}" class="text-sm font-semibold uppercase tracking-[0.3em] text-white/90 hover:text-white">lapsique.media</a>
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-ghost text-[11px]">Inicio</a>
            <form method="POST" action="{{ route('customers.logout') }}">
                @csrf
                <button type="submit" class="btn btn-primary text-[11px]">Cerrar sesión</button>
            </form>
        </div>
    </div>
</div>

<div class="space-y-8">
    <section class="card overflow-hidden border-white/15 bg-white/[0.04]">
        <div class="grid gap-8 px-6 py-8 md:grid-cols-[1.3fr_0.7fr] md:px-8">
            <div class="space-y-5">
                <div class="flex flex-wrap gap-2">
                    <span class="pill border-cyan-400/30 bg-cyan-500/10 text-cyan-200">Portal de cliente</span>
                    <span class="pill border-fuchsia-400/30 bg-fuchsia-500/10 text-fuchsia-200">Sesiones y entregables</span>
                </div>
                <div class="space-y-2">
                    <h1 class="text-4xl font-bold tracking-tight text-white md:text-5xl">{{ $customer->name }}</h1>
                    <p class="max-w-2xl text-base leading-relaxed text-gray-300">
                        Aquí puedes revisar tus sesiones registradas, materiales entregados, pagos realizados y la información principal de tu cuenta.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/12 bg-white/[0.04] p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-gray-500">Sesiones</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $bookingCount }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/12 bg-white/[0.04] p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-gray-500">Pagos</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $paymentCount }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/12 bg-white/[0.04] p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-gray-500">Archivos listos</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $deliverablesCount }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[28px] border border-white/12 bg-gradient-to-br from-cyan-500/10 via-white/[0.04] to-fuchsia-500/10 p-6">
                <p class="text-xs uppercase tracking-[0.24em] text-gray-500">Información</p>
                <div class="mt-5 space-y-4 text-sm text-gray-200">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Email</p>
                        <p class="mt-1 text-base text-white">{{ $customer->email }}</p>
                    </div>
                    @if ($customer->phone)
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">WhatsApp</p>
                            <p class="mt-1 text-base text-white">{{ $customer->phone }}</p>
                        </div>
                    @endif
                    @if ($customer->instagram_handle)
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Instagram</p>
                            <p class="mt-1 text-base text-white">{{ '@' . ltrim($customer->instagram_handle, '@') }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Última actividad</p>
                        <p class="mt-1 text-base text-white">{{ optional($customer->last_interaction_at)->translatedFormat('d M Y · H:i') ?? 'Ahora' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-gray-500">Contenido</p>
                <h2 class="mt-2 text-2xl font-bold text-white md:text-3xl">Mis sesiones de contenido</h2>
            </div>
            <a href="{{ route('booking.show') }}" class="btn btn-ghost text-[11px]">Reservar otra sesión</a>
        </div>

        @if ($bookings->isEmpty())
            <div class="card border-white/12 bg-white/[0.04] p-8 text-center">
                <p class="text-lg font-medium text-white">Todavía no tienes sesiones registradas.</p>
                <p class="mt-2 text-sm text-gray-400">Cuando reserves una sesión de contenido aparecerá aquí junto con sus pagos y entregables.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($bookings as $booking)
                    @php
                        $deliverables = $booking->deliverables_ready_at ? $booking->getMedia('deliverables') : collect();
                    @endphp
                    <article class="card overflow-hidden border-white/12 bg-white/[0.04]">
                        <div class="grid gap-0 lg:grid-cols-[0.95fr_1.05fr]">
                            <div class="border-b border-white/10 p-6 lg:border-b-0 lg:border-r">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-xl font-semibold text-white">{{ $booking->slot?->date?->translatedFormat('d \d\e F, Y') ?? 'Sesión sin fecha' }}</h3>
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $statusStyles[$booking->status] ?? 'border-white/20 bg-white/10 text-white' }}">
                                        {{ $booking->status_label }}
                                    </span>
                                </div>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Horario</p>
                                        <p class="mt-1 text-base text-white">{{ $booking->slot?->time_label ?? 'Por confirmar' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Pago</p>
                                        <p class="mt-1 text-base text-white">{{ $booking->formatted_amount }}</p>
                                        <p class="mt-1 text-xs text-gray-400">{{ $booking->payment_status_label }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Instagram</p>
                                        <p class="mt-1 text-base text-white">{{ $booking->client_instagram ?: 'No registrado' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Locación</p>
                                        <p class="mt-1 text-base text-white">{{ $booking->shoot_location ?: 'Se define con el equipo' }}</p>
                                    </div>
                                </div>
                                @if ($booking->notes)
                                    <div class="mt-5 rounded-2xl border border-white/10 bg-black/30 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Brief compartido</p>
                                        <p class="mt-2 text-sm leading-relaxed text-gray-300">{{ $booking->notes }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="p-6">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Entregables</p>
                                        <h4 class="mt-1 text-lg font-semibold text-white">
                                            @if ($deliverables->isNotEmpty())
                                                Material disponible
                                            @elseif ($booking->getMedia('deliverables')->isNotEmpty())
                                                Material cargado, pendiente de publicación
                                            @else
                                                Material en preparación
                                            @endif
                                        </h4>
                                    </div>
                                    @if ($booking->deliverables_ready_at)
                                        <span class="rounded-full border border-cyan-400/30 bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-200">
                                            Publicado {{ $booking->deliverables_ready_at->translatedFormat('d M') }}
                                        </span>
                                    @endif
                                </div>

                                @if ($deliverables->isNotEmpty())
                                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                        @foreach ($deliverables as $media)
                                            @php
                                                $mime = (string) $media->mime_type;
                                                $isImage = str_starts_with($mime, 'image/');
                                                $isVideo = str_starts_with($mime, 'video/');
                                                $previewUrl = $isImage && $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl();
                                            @endphp
                                            <div class="overflow-hidden rounded-2xl border border-white/10 bg-black/30">
                                                @if ($isImage)
                                                    <img src="{{ $previewUrl }}" alt="{{ $media->name }}" class="h-44 w-full object-cover">
                                                @elseif ($isVideo)
                                                    <video class="h-44 w-full object-cover" preload="metadata" controls>
                                                        <source src="{{ $media->getUrl() }}" type="{{ $mime }}">
                                                    </video>
                                                @else
                                                    <div class="flex h-44 flex-col items-center justify-center gap-3 bg-white/[0.03] px-4 text-center">
                                                        <span class="rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-200">
                                                            {{ strtoupper(pathinfo($media->file_name, PATHINFO_EXTENSION) ?: 'FILE') }}
                                                        </span>
                                                        <p class="text-sm text-gray-300">{{ $media->name }}</p>
                                                    </div>
                                                @endif
                                                <div class="flex items-center justify-between gap-3 p-4">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-medium text-white">{{ $media->name }}</p>
                                                        <p class="mt-1 text-xs text-gray-500">{{ number_format($media->size / 1024 / 1024, 2) }} MB</p>
                                                    </div>
                                                    <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener" download class="btn btn-primary text-[11px]">
                                                        Descargar
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-5 rounded-2xl border border-dashed border-white/12 bg-black/30 p-5">
                                        <p class="text-sm text-gray-300">
                                            @if ($booking->getMedia('deliverables')->isNotEmpty())
                                                El equipo ya cargó archivos, pero todavía no los publica en tu portal.
                                            @else
                                                Tu material final aparecerá aquí apenas el equipo lo suba y lo publique.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="space-y-4">
        <div>
            <p class="text-xs uppercase tracking-[0.24em] text-gray-500">Finanzas</p>
            <h2 class="mt-2 text-2xl font-bold text-white md:text-3xl">Pagos recientes</h2>
        </div>

        @if ($payments->isEmpty())
            <div class="card border-white/12 bg-white/[0.04] p-6">
                <p class="text-sm text-gray-400">Aún no hay pagos registrados en tu portal.</p>
            </div>
        @else
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($payments as $payment)
                    <div class="card border-white/12 bg-white/[0.04] p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-base font-semibold text-white">{{ $payment->label }}</p>
                                <p class="mt-1 text-sm text-gray-400">{{ $payment->detail }}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $statusStyles[$payment->status_key] ?? 'border-white/20 bg-white/10 text-white' }}">
                                {{ $payment->status }}
                            </span>
                        </div>
                        <div class="mt-4 flex items-end justify-between gap-3">
                            <p class="text-2xl font-bold text-white">{{ $payment->amount }}</p>
                            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">{{ optional($payment->date)->translatedFormat('d M Y · H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    @if ($customer->ticketAttendees->isNotEmpty())
        <section class="space-y-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-gray-500">Eventos</p>
                <h2 class="mt-2 text-2xl font-bold text-white md:text-3xl">Mis tickets</h2>
            </div>
            <div class="space-y-3">
                @foreach ($customer->ticketAttendees->sortByDesc('created_at') as $attendee)
                    @php
                        $isActive = in_array($attendee->status, ['registered', 'checked_in']);
                    @endphp
                    <div class="card border-white/12 bg-white/[0.04] p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-lg font-semibold text-white">{{ $attendee->event?->title ?? 'Evento' }}</p>
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $isActive ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300' : 'border-amber-400/30 bg-amber-500/10 text-amber-300' }}">
                                        {{ $isActive ? 'Activo' : 'Pendiente' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-400">{{ $attendee->product?->name ?? 'Ticket' }}</p>
                                @if ($attendee->event?->starts_at)
                                    <p class="mt-1 text-xs text-gray-500">{{ $attendee->event->starts_at->translatedFormat('d M Y · H:i') }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ $attendee->getCheckInUrl() }}" class="btn btn-primary text-[11px]">Ver QR</a>
                                @if ($attendee->event)
                                    <a href="{{ route('events.show', $attendee->event) }}" class="btn btn-ghost text-[11px]">Ver evento</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($customer->guestListEntries->isNotEmpty())
        <section class="space-y-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-gray-500">Eventos</p>
                <h2 class="mt-2 text-2xl font-bold text-white md:text-3xl">Mis guest lists</h2>
            </div>
            <div class="space-y-3">
                @foreach ($customer->guestListEntries->sortByDesc('created_at') as $entry)
                    <div class="card border-white/12 bg-white/[0.04] p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-lg font-semibold text-white">{{ $entry->event?->title ?? 'Guest List General' }}</p>
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $statusStyles[$entry->status] ?? 'border-white/20 bg-white/10 text-white' }}">
                                        {{ ucfirst($entry->status) }}
                                    </span>
                                </div>
                                @if ($entry->event)
                                    <p class="mt-2 text-xs text-gray-500">{{ optional($entry->event->starts_at)->translatedFormat('d M Y') ?? 'Fecha por definir' }}</p>
                                @endif
                            </div>
                            @if ($entry->status === 'confirmed' && $entry->event)
                                <a href="{{ route('events.show', $entry->event) }}" class="btn btn-ghost text-[11px]">Ver evento</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
