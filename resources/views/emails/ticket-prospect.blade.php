@extends('emails.layout')

@section('title', 'Completa tu compra en lapsique.media')

@section('content')
    <div style="text-align: center; padding: 10px 0 24px;">
        <h2 style="color: #071E2A; margin: 0 0 8px; font-size: 26px; letter-spacing: 0.04em; font-weight: 700;">
            Completa tu compra
        </h2>
        <p style="color: #1B82A4; margin: 0; font-size: 13px; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600;">
            Registro confirmado y compra pendiente
        </p>
    </div>

    <p style="color: #1A2D3D; font-size: 16px; margin: 0 0 16px;">
        Hola <strong style="color: #071E2A;">{{ $order->buyer_name ?? 'amig@' }}</strong>,
    </p>

    <p style="color: #1A2D3D; margin: 0 0 16px;">
        Gracias por registrarte en <strong style="color: #0B3749;">lapsique.media</strong>. Al hacerlo, quedaste
        suscrito a nuestro newsletter para recibir noticias de eventos, lanzamientos y contenido nuevo.
    </p>

    <p style="color: #1A2D3D; margin: 0 0 20px;">
        También vimos que dejaste una compra pendiente{{ $event?->title ? ' para ' : '' }}
        @if($event?->title)
            <strong style="color: #0B3749;">{{ $event->title }}</strong>
        @endif.
        Si estabas apartando una mesa o comprando tickets, puedes retomarlo cuando quieras.
    </p>

    <div class="card" style="background-color: #EEF7FB; border: 1px solid #A3D4EA; border-radius: 12px; padding: 24px; margin: 24px 0;">
        <h3 style="margin: 0 0 16px; color: #0B3749; font-size: 15px; letter-spacing: 0.08em; text-transform: uppercase;">
            Tu compra pendiente
        </h3>
        @foreach ($items as $item)
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(163, 212, 234, 0.4);">
                <span style="color: #1A2D3D; font-weight: 600;">{{ $item->quantity }} × {{ $item->name }}</span>
                <span style="color: #6B7F8E;">{{ number_format((float) $item->unit_price, 2) }} {{ $order->currency }}</span>
            </div>
        @endforeach
        <div style="margin-top: 14px; padding-top: 10px; border-top: 2px solid #1B82A4;">
            <p style="margin: 0 0 6px; color: #1A2D3D;">Subtotal: <strong style="color: #0B3749;">{{ number_format((float) $order->subtotal, 2) }} {{ $order->currency }}</strong></p>
            <p style="margin: 0 0 6px; color: #1A2D3D;">Cargo por servicio: <strong style="color: #0B3749;">{{ number_format((float) $order->fee, 2) }} {{ $order->currency }}</strong></p>
            <p style="margin: 0; color: #071E2A; font-size: 16px;">Total pendiente: <strong>{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</strong></p>
        </div>
    </div>

    <div class="card-beige" style="background-color: #FDFAF6; border: 1px solid #DED2BB; border-radius: 12px; padding: 24px; margin: 24px 0;">
        <h3 style="margin: 0 0 10px; color: #0B3749; font-size: 15px; letter-spacing: 0.08em; text-transform: uppercase;">
            Qué sigue
        </h3>
        <p style="color: #1A2D3D; margin: 8px 0; padding-left: 12px; border-left: 3px solid #3BA0C5;">
            Puedes completar tu compra en cualquier momento desde el enlace de abajo.
        </p>
        <p style="color: #1A2D3D; margin: 8px 0; padding-left: 12px; border-left: 3px solid #3BA0C5;">
            Cuando el pago se apruebe, te enviaremos la confirmación y los accesos correspondientes.
        </p>
    </div>

    @if($manageUrl)
        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ $manageUrl }}" style="display: inline-block; padding: 13px 32px; background: #1B82A4; color: #ffffff; text-decoration: none; border-radius: 999px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; font-size: 12px;">
                Completar compra
            </a>
        </div>
    @endif

    <p style="margin-top: 28px; color: #1A2D3D;">
        <strong style="color: #0B3749;">Nos vemos en la pista.</strong><br>
        <span style="color: #6B7F8E; font-size: 14px;">El equipo de lapsique.media</span>
    </p>
@endsection
