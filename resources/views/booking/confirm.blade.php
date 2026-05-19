@extends('layouts.site')

@section('hide_navbar', '1')
@section('minimal_footer', '1')

@section('title', '¡Sesión Confirmada! — lapsique.media')
@section('meta_title', 'Reserva confirmada — lapsique.media')

@section('content')
@php
    $whatsapp = app(\App\Models\SiteSetting::class)::current()?->booking_whatsapp ?: config('lapsique.whatsapp_number', '');
    $slotDate = $booking->slot?->date;
    $slotTime = $booking->slot?->time_label;
    $dateStr = $slotDate ? $slotDate->translatedFormat('d \d\e F, Y') : null;
@endphp

{{-- Minimal nav --}}
<div class="-mx-6 -mt-10 mb-0 border-b border-white/10 bg-black/60 backdrop-blur sticky top-0 z-50">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <a href="{{ route('home') }}" class="text-sm font-semibold uppercase tracking-[0.3em] hover:text-white">lapsique.media</a>
    </div>
</div>

<div class="min-h-[70vh] flex items-center justify-center py-16">
    <div class="max-w-lg w-full space-y-8 text-center fade-up">

        {{-- Success icon --}}
        <div class="mx-auto flex items-center justify-center w-24 h-24 rounded-full bg-emerald-500/20 border border-emerald-400/40">
            <svg class="w-12 h-12 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="space-y-3">
            <h1 class="text-3xl font-bold text-white">¡Tu sesión está reservada!</h1>
            <p class="text-gray-300">Pago confirmado · Recibirás más detalles por email.</p>
        </div>

        <div class="card p-6 space-y-4 text-left">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Detalles de tu reserva</h3>

            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Servicio</span>
                    <span class="text-white font-medium">Sesión de Contenido</span>
                </div>
                @if($dateStr)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Fecha</span>
                    <span class="text-white font-medium">{{ $dateStr }}</span>
                </div>
                @endif
                @if($slotTime)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Hora</span>
                    <span class="text-white font-medium">{{ $slotTime }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Incluye</span>
                    <span class="text-white font-medium">2 Reels + 20 Fotos</span>
                </div>
                <div class="flex justify-between text-sm border-t border-white/10 pt-3">
                    <span class="text-gray-400">Total pagado</span>
                    <span class="text-emerald-300 font-bold text-base">{{ $booking->formatted_amount }}</span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <p class="text-gray-400 text-sm">
                Nos comunicaremos contigo por WhatsApp para confirmar la ubicación y los detalles finales de tu sesión.
            </p>

            <div class="rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-5 py-4 text-left">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Portal activado</p>
                <p class="mt-2 text-sm text-gray-200">Tu portal de cliente ya está listo para consultar la sesión, el estado del pago y los entregables cuando sean publicados.</p>
            </div>

            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('customers.portal') }}" class="btn btn-primary inline-flex">
                    Ir a mi portal
                </a>
                <a href="{{ route('booking.show') }}" class="btn btn-ghost inline-flex">
                    Reservar otra sesión
                </a>
            </div>

            @if($whatsapp)
            <a href="https://wa.me/{{ $whatsapp }}?text=Hola%21+Acabo+de+reservar+una+sesi%C3%B3n+de+contenido.+Mi+nombre+es+{{ urlencode($booking->client_name) }}"
               target="_blank" rel="noopener"
               class="btn btn-primary inline-flex">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 01-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.85-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14-.01-.3-.01-.47-.01z"/>
                </svg>
                Coordinar por WhatsApp
            </a>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Fire Purchase event once
(function() {
    const key = 'booking_purchase_fired_{{ $booking->public_id }}';
    if (!localStorage.getItem(key)) {
        localStorage.setItem(key, '1');

        window.trackMetaPixel && window.trackMetaPixel('Purchase', {
            content_name: 'Sesión de Contenido',
            value: {{ $booking->amount }},
            currency: '{{ $booking->currency }}',
            content_type: 'service'
        });

        window.trackMetaPixelCustom && window.trackMetaPixelCustom('Schedule', {
            content_name: 'Sesión de Contenido',
            value: {{ $booking->amount }},
            currency: '{{ $booking->currency }}'
        });

        if (typeof window.LapsiqueTracker !== 'undefined') {
            window.LapsiqueTracker.track('booking_confirmed', {
                booking_id: '{{ $booking->public_id }}',
                value: {{ $booking->amount }},
                currency: '{{ $booking->currency }}'
            });
        }
    }
})();
</script>
@endpush
