@extends('layouts.site')

@php($en = app()->getLocale() === 'en')
@php($testing = (bool) config('mercadopago.embedded.testing', true))

@section('title', ($testing ? ($en ? 'Test payment' : 'Pago de prueba') : ($en ? 'Secure payment' : 'Pago seguro')).' · '.$event->title)
@section('hide_navbar', '1')

@section('content')
    <main class="mx-auto min-h-screen max-w-3xl px-4 py-8 sm:px-6 sm:py-12" id="main-content" tabindex="-1">
        <a href="{{ route('events.show', $event) }}#tickets" class="inline-flex min-h-11 items-center text-sm font-semibold text-[var(--marine-700)] underline decoration-1 underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4">
            {{ $en ? 'Return to the event' : 'Volver al evento' }}
        </a>

        <header class="mt-8 max-w-2xl">
            @if($testing)
                <p class="label-small text-[var(--marine-600)]">{{ $en ? 'TEST MODE · NO REAL CHARGE' : 'MODO TESTING · SIN CARGO REAL' }}</p>
            @endif
            <h1 class="display mt-3 text-4xl font-bold text-[var(--ink)] sm:text-5xl">{{ $testing ? ($en ? 'Secure test payment' : 'Pago seguro de prueba') : ($en ? 'Secure payment' : 'Pago seguro') }}</h1>
            <p class="mt-4 max-w-xl text-base leading-7 text-[var(--muted)]">
                {{ $en
                    ? 'Mercado Pago tokenizes the card inside its secure Brick. Lapsique never receives or stores the card number or CVV.'
                    : 'Mercado Pago tokeniza la tarjeta dentro de su Brick seguro. Lapsique nunca recibe ni almacena el número de tarjeta o el CVV.' }}
            </p>
        </header>

        <div class="mt-8 grid gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <section class="rounded-[24px] bg-[var(--beige-50)] p-5 shadow-[0_0_0_1px_oklch(0_0_0/0.08),0_12px_32px_oklch(0_0_0/0.06)] sm:p-6" aria-labelledby="order-summary-title">
                <p class="label-small text-[var(--marine-600)]">{{ $en ? 'ORDER SUMMARY' : 'RESUMEN DE ORDEN' }}</p>
                <h2 id="order-summary-title" class="mt-2 text-xl font-semibold text-[var(--ink)]">{{ $event->title }}</h2>
                <p class="mt-1 break-all text-xs text-[var(--muted)]">{{ $en ? 'Order' : 'Orden' }} <bdi>{{ $order->public_id }}</bdi></p>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[var(--muted)]">{{ $en ? 'Ticket subtotal' : 'Subtotal del boleto' }}</dt>
                        <dd class="font-medium tabular-nums text-[var(--ink)]">${{ number_format((float) $order->subtotal, 2) }} {{ $order->currency }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[var(--muted)]">{{ $en ? 'Service charge' : 'Cargo de servicio' }}</dt>
                        <dd class="font-medium tabular-nums text-[var(--ink)]">${{ number_format((float) $order->fee, 2) }} {{ $order->currency }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-t border-[var(--beige-300)] pt-4 text-base">
                        <dt class="font-semibold text-[var(--ink)]">Total</dt>
                        <dd class="font-bold tabular-nums text-[var(--ink)]">${{ number_format((float) $order->total, 2) }} {{ $order->currency }}</dd>
                    </div>
                </dl>

                @if($testing)
                    <div class="mt-6 rounded-2xl bg-amber-50 p-4 text-sm leading-6 text-amber-950" role="note">
                        <strong>{{ $en ? 'Testing only.' : 'Sólo pruebas.' }}</strong>
                        {{ $en ? 'Use a Mercado Pago test card. No real ticket will be valid for entry.' : 'Usa una tarjeta de prueba de Mercado Pago. Ningún boleto de prueba será válido para ingresar.' }}
                    </div>
                @endif
                <p class="mt-4 text-xs leading-5 text-[var(--muted)]">{{ $en ? '18+ · No refunds · Tickets are issued only after a verified webhook.' : '18+ · Sin reembolsos · Los accesos se emiten únicamente después del webhook verificado.' }}</p>
            </section>

            <section class="rounded-[24px] bg-white p-5 shadow-[0_0_0_1px_oklch(0_0_0/0.08),0_12px_32px_oklch(0_0_0/0.06)] sm:p-6" aria-labelledby="payment-heading" data-payment-state="loading">
                <h2 id="payment-heading" class="text-2xl font-semibold text-[var(--ink)]">{{ $en ? 'Credit or debit card' : 'Tarjeta de crédito o débito' }}</h2>
                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">{{ $en ? 'The secure fields below are provided by Mercado Pago.' : 'Los campos seguros son proporcionados por Mercado Pago.' }}</p>
                <div class="mt-5 min-h-80" id="mercadopago-card-form" aria-describedby="mercadopago-card-form-status" data-mercadopago-configuration-url="{{ $configurationUrl }}"></div>
                <p id="mercadopago-card-form-status" data-mercadopago-status class="mt-4 text-sm leading-6 text-[var(--muted)]" role="status" aria-live="polite">{{ $en ? 'Loading the secure form…' : 'Cargando el formulario seguro…' }}</p>
            </section>
        </div>

        <p class="mt-8 text-sm text-[var(--muted)]">
            <a class="inline-flex min-h-11 items-center underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4" href="{{ $resultUrl }}">{{ $en ? 'Check order status' : 'Consultar el estado de la orden' }}</a>
        </p>
    </main>
@endsection

@push('scripts')
    @vite('resources/js/mercadopago-embedded.js')
@endpush
