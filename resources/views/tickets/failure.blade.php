@extends('layouts.site')

@section('title', 'Pago rechazado | ' . $event->title)
@section('hide_navbar', '1')
@section('minimal_footer', '1')

@section('content')
    <nav class="sticky top-0 z-50 flex items-center justify-between border-b border-[var(--beige-300)] bg-[var(--beige-50)] px-6 py-4">
        <a href="{{ route('home') }}" class="text-[var(--ink)] font-semibold tracking-tight">{{ __('messages.site.brand') }}</a>
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('customers.login') }}" class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[var(--ink)] transition hover:bg-white/70">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                <span class="hidden sm:inline">Mis tickets</span>
            </a>
            <a href="{{ route('events.show', $event) }}" class="btn btn-primary py-2 px-5 text-[0.65rem]">Ver evento</a>
        </div>
    </nav>

    <div class="min-h-screen bg-[var(--beige-100)] pt-10 pb-16">
        <div class="mx-auto max-w-3xl px-6">
            <header class="mb-8 text-center">
                <p class="label-small mb-2 text-[var(--marine-500)]">Pago rechazado</p>
                <h1 class="display font-bold text-[var(--ink)]" style="font-size: clamp(1.9rem, 4vw, 3rem);">
                    No pudimos completar el pago
                </h1>
                <p class="mx-auto mt-3 max-w-xl text-sm text-[var(--muted)] sm:text-base">
                    Intenta nuevamente o usa otro método de pago.
                </p>
            </header>

            <div class="card overflow-hidden">
                <div class="border-b border-[var(--beige-300)] bg-[var(--beige-50)] px-6 py-5">
                    <p class="label-small text-[var(--marine-500)]">Orden de compra</p>
                    <h2 class="display mt-2 text-2xl font-semibold text-[var(--ink)]">{{ $event->title }}</h2>
                    <p class="mt-2 text-sm text-[var(--muted)]">Orden #{{ $order->public_id }}</p>
                </div>

                <div class="space-y-6 px-6 py-6">
                    <div class="rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                        El cobro no se confirmó. Tu lugar no quedó reservado hasta completar el pago.
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-[var(--card-border)] bg-white px-5 py-5">
                            <p class="label-small text-[var(--marine-500)]">Siguiente paso</p>
                            <p class="mt-3 text-sm leading-6 text-[var(--muted)]">
                                Reintenta la compra desde la landing actual para volver a generar tu checkout.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-[var(--card-border)] bg-white px-5 py-5">
                            <p class="label-small text-[var(--marine-500)]">Si el problema continúa</p>
                            <p class="mt-3 text-sm leading-6 text-[var(--muted)]">
                                Prueba con otro método de pago o revisa tus tickets desde tu portal para continuar después.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('events.show', $event) }}#tickets" class="btn btn-aggressive justify-center sm:flex-1">
                            Reintentar compra
                        </a>
                        <a href="{{ route('events.show', $event) }}" class="btn btn-outline justify-center border-[var(--beige-400)] text-[var(--ink)] hover:bg-[var(--beige-100)] sm:flex-1">
                            Volver al evento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const orderTotal = @json((float) $order->total);
    const orderCurrency = @json($order->currency);

    window.LapsiqueTracker.track('payment_failed', {
        category: 'commerce',
        label: @json($event->title),
        value: orderTotal,
        metadata: {
            order_id: '{{ $order->public_id }}',
            event_id: '{{ $event->id }}',
            currency: orderCurrency,
        },
    });

    window.trackMetaPixelCustom('PaymentFailed', {
        content_name: @json($event->title),
        content_ids: @json($order->items->pluck('ticket_product_id')),
        value: orderTotal,
        currency: orderCurrency,
    });
</script>
@endpush
