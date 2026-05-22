@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Completa tu compra — '.EmailBrand::WORDMARK)

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Compra pendiente</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Completa tu compra</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola <strong style="{{ EmailBrand::strongStyle() }}">{{ $order->buyer_name ?? 'amig@' }}</strong>,</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Gracias por registrarte en <strong style="{{ EmailBrand::strongStyle() }}">{{ EmailBrand::WORDMARK }}</strong>.
        Quedaste suscrito a nuestro newsletter para recibir noticias de eventos, lanzamientos y contenido nuevo.
    </p>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        También dejaste una compra pendiente
        @if($event?->title)
            para <strong style="{{ EmailBrand::strongStyle() }}">{{ $event->title }}</strong>
        @endif
        . Si estabas apartando una mesa o comprando tickets, puedes retomarlo cuando quieras.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Tu compra pendiente</h3>
        @foreach ($items as $item)
            <p style="{{ EmailBrand::cardRowStyle() }} display:flex;justify-content:space-between;">
                <span><strong>{{ $item->quantity }} × {{ $item->name }}</strong></span>
                <span style="color:{{ EmailBrand::MUTED }};">{{ number_format((float) $item->unit_price, 2) }} {{ $order->currency }}</span>
            </p>
        @endforeach
        <div style="margin-top:14px;padding-top:12px;border-top:2px solid {{ EmailBrand::PRIMARY }};">
            <p style="{{ EmailBrand::cardRowStyle() }}">Subtotal: <strong>{{ number_format((float) $order->subtotal, 2) }} {{ $order->currency }}</strong></p>
            <p style="{{ EmailBrand::cardRowStyle() }}">Cargo por servicio: <strong>{{ number_format((float) $order->fee, 2) }} {{ $order->currency }}</strong></p>
            <p style="{{ EmailBrand::cardRowStyle() }} font-size:16px;font-weight:600;">Total pendiente: {{ number_format((float) $order->total, 2) }} {{ $order->currency }}</p>
        </div>
    </div>

    <div style="{{ EmailBrand::tipBoxStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }} margin-bottom:10px;">Qué sigue</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}">Puedes completar tu compra en cualquier momento desde el enlace de abajo.</p>
        <p style="{{ EmailBrand::cardRowStyle() }}">Cuando el pago se apruebe, te enviaremos la confirmación y los accesos correspondientes.</p>
    </div>

    @if($manageUrl)
        @include('emails.partials._button', ['url' => $manageUrl, 'label' => 'Completar compra'])
    @endif

    <p style="{{ EmailBrand::paragraphStyle() }} margin-top:28px;">
        <strong style="{{ EmailBrand::strongStyle() }}">Nos vemos pronto.</strong><br>
        <span style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</span>
    </p>
@endsection
