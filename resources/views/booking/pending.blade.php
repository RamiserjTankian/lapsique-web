@extends('layouts.site')

@section('hide_navbar', '1')
@section('minimal_footer', '1')

@section('title', 'Pago en revisión — lapsique.media')

@section('content')
@php
    $whatsapp = app(\App\Models\SiteSetting::class)::current()?->booking_whatsapp ?: config('lapsique.whatsapp_number', '');
@endphp

<div class="-mx-6 -mt-10 mb-0 border-b border-white/10 bg-black/60 backdrop-blur sticky top-0 z-50">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <a href="{{ route('home') }}" class="text-sm font-semibold uppercase tracking-[0.3em] hover:text-white">lapsique.media</a>
    </div>
</div>

<div class="min-h-[70vh] flex items-center justify-center py-16">
    <div class="max-w-lg w-full space-y-8 text-center fade-up">
        <div class="mx-auto flex items-center justify-center w-24 h-24 rounded-full bg-yellow-500/20 border border-yellow-400/40">
            <svg class="w-12 h-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="space-y-3">
            <h1 class="text-3xl font-bold text-white">Pago en revisión</h1>
            <p class="text-gray-300">Tu pago está siendo procesado por Mercado Pago.</p>
            <p class="text-gray-400 text-sm">Esto puede tomar unos minutos. Te notificaremos cuando se confirme.</p>
        </div>

        <div class="card p-6 space-y-3 text-left">
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Cliente</span>
                <span class="text-white">{{ $booking->client_name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Servicio</span>
                <span class="text-white">Sesión de Contenido</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Monto</span>
                <span class="text-yellow-300 font-semibold">{{ $booking->formatted_amount }}</span>
            </div>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('customers.portal') }}" class="btn btn-primary inline-flex">
                Ver mi portal
            </a>
            <a href="{{ route('booking.show') }}" class="btn btn-ghost inline-flex">
                Volver a la landing
            </a>
        </div>

        @if($whatsapp)
        <p class="text-sm text-gray-400">
            ¿Tienes dudas sobre tu pago?
            <a href="https://wa.me/{{ $whatsapp }}?text=Hola%2C+hice+una+reserva+de+sesi%C3%B3n+de+contenido+y+mi+pago+est%C3%A1+en+revisi%C3%B3n.+Mi+nombre+es+{{ urlencode($booking->client_name) }}"
               target="_blank" rel="noopener" class="text-white underline">Contáctanos por WhatsApp</a>
        </p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const key = 'booking_pending_fired_{{ $booking->public_id }}';

    if (sessionStorage.getItem(key)) {
        return;
    }

    sessionStorage.setItem(key, '1');

    if (typeof window.LapsiqueTracker !== 'undefined') {
        window.LapsiqueTracker.track('booking_payment_pending', {
            value: {{ $booking->amount }},
            metadata: {
                booking_id: '{{ $booking->public_id }}',
                currency: '{{ $booking->currency }}',
            },
        });
    }
})();
</script>
@endpush
