@extends('layouts.site')

@section('title', 'Pago en proceso | ' . $event->title)
@section('hide_navbar', '1')
@section('minimal_footer', '1')

@section('content')
    @php
        $providerLabel = match ($order->payment_provider) {
            'stripe' => 'Tarjeta · Stripe',
            default => 'Mercado Pago',
        };
    @endphp

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
        <div class="mx-auto max-w-5xl px-6">
            <header class="mb-8 text-center">
                <p class="label-small mb-2 text-[var(--marine-500)]">Pago en proceso</p>
                <h1 class="display font-bold text-[var(--ink)]" style="font-size: clamp(2rem, 4vw, 3.4rem);">
                    Estamos validando tu pago
                </h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm text-[var(--muted)] sm:text-base">
                    En cuanto el pago sea aprobado podrás registrar a cada asistente, descargar los accesos y continuar con tu compra.
                </p>
            </header>

            @if ($errors->has('checkout'))
                <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                    {{ $errors->first('checkout') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[1.35fr_0.9fr]">
                <section class="card overflow-hidden">
                    <div class="border-b border-[var(--beige-300)] bg-[var(--beige-50)] px-6 py-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="label-small text-[var(--marine-500)]">Orden de compra</p>
                                <h2 class="display mt-2 text-2xl font-semibold text-[var(--ink)]">{{ $event->title }}</h2>
                                <p class="mt-2 text-sm text-[var(--muted)]">Orden #{{ $order->public_id }}</p>
                            </div>
                            <span class="pill border-amber-300 bg-amber-50 text-xs text-amber-700">
                                {{ $providerLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-6 px-6 py-6">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-[var(--card-border)] bg-white px-5 py-5">
                                <p class="label-small text-[var(--marine-500)]">Total</p>
                                <p class="mt-3 text-2xl font-semibold text-[var(--ink)]">{{ number_format($order->total, 2) }} {{ $order->currency }}</p>
                            </div>
                            <div class="rounded-2xl border border-[var(--card-border)] bg-white px-5 py-5">
                                <p class="label-small text-[var(--marine-500)]">Comprador</p>
                                <p class="mt-3 text-sm font-medium text-[var(--ink)]">{{ $order->buyer_name }}</p>
                                <p class="mt-1 text-sm text-[var(--muted)]">{{ $order->buyer_email }}</p>
                            </div>
                            <div class="rounded-2xl border border-[var(--card-border)] bg-white px-5 py-5">
                                <p class="label-small text-[var(--marine-500)]">Estado</p>
                                <p class="mt-3 text-sm font-medium text-[var(--ink)]">Pendiente de aprobación</p>
                                <p class="mt-1 text-sm text-[var(--muted)]">Tu lugar se confirma al acreditarse el pago.</p>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-[var(--marine-200)] bg-[linear-gradient(135deg,rgba(27,130,164,0.10),rgba(255,255,255,0.95))] px-6 py-6">
                            <p class="label-small text-[var(--marine-600)]">Siguiente paso</p>
                            <h3 class="mt-2 display text-2xl font-semibold text-[var(--ink)]">Puedes revisar o volver a pagar ahora</h3>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-[var(--muted)]">
                                Si el pago ya se reflejó, vuelve a abrir este link en unos minutos. Si la transacción quedó inconclusa,
                                puedes reabrir el checkout y completar el pago sin crear una orden nueva.
                            </p>

                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ route('tickets.success', $order) }}" class="btn btn-primary justify-center sm:flex-1">
                                    Ver estado de mi compra
                                </a>
                                <form method="POST" action="{{ route('tickets.retry', $order) }}" class="sm:flex-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline w-full justify-center border-[var(--marine-400)] text-[var(--marine-700)] hover:bg-[var(--marine-50)]">
                                        Volver a pagar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-[var(--card-border)] bg-white px-5 py-5">
                                <p class="label-small text-[var(--marine-500)]">Qué recibirás al aprobarse</p>
                                <p class="mt-3 text-sm leading-6 text-[var(--muted)]">
                                    Confirmación por correo, acceso al portal y los QR correspondientes para cada integrante registrado.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-[var(--card-border)] bg-white px-5 py-5">
                                <p class="label-small text-[var(--marine-500)]">Si pagaste hace unos minutos</p>
                                <p class="mt-3 text-sm leading-6 text-[var(--muted)]">
                                    Los procesadores pueden tardar un poco en confirmar. No cierres este enlace; puedes volver aquí cuando quieras.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="card p-6">
                        <p class="label-small text-[var(--marine-500)]">Resumen rápido</p>
                        <div class="mt-4 space-y-3">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-[var(--card-border)] bg-white px-4 py-4">
                                    <div>
                                        <p class="text-sm font-semibold text-[var(--ink)]">{{ $item->name }}</p>
                                        <p class="text-xs text-[var(--muted)]">{{ $item->quantity }} × {{ number_format((float) $item->unit_price, 2) }} {{ $order->currency }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-[var(--ink)]">{{ number_format((float) $item->total_price, 2) }} {{ $order->currency }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card p-6">
                        <p class="label-small text-[var(--marine-500)]">Acciones</p>
                        <div class="mt-4 space-y-3">
                            <a href="{{ route('events.show', $event) }}" class="btn btn-outline w-full justify-center border-[var(--beige-400)] text-[var(--ink)] hover:bg-[var(--beige-100)]">
                                Volver al evento
                            </a>
                            <a href="{{ route('customers.login') }}" class="btn btn-outline w-full justify-center border-[var(--beige-400)] text-[var(--ink)] hover:bg-[var(--beige-100)]">
                                Ir a mis tickets
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const orderTotal = @json((float) $order->total);
    const orderCurrency = @json($order->currency);

    window.LapsiqueTracker.track('payment_pending', {
        category: 'commerce',
        label: @json($event->title),
        value: orderTotal,
        metadata: {
            order_id: '{{ $order->public_id }}',
            event_id: '{{ $event->id }}',
            currency: orderCurrency,
        },
    });

    window.trackMetaPixelCustom('PaymentPending', {
        content_name: @json($event->title),
        content_ids: @json($order->items->pluck('ticket_product_id')),
        value: orderTotal,
        currency: orderCurrency,
    });
</script>
@endpush
