@extends('emails.layout')

@section('title', 'Confirmación de Compra')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0; letter-spacing: 0.02em;">¡Compra confirmada! ✅</h2>

    <p style="color: #e5e7eb;">Hola {{ $order->buyer_name ?? 'amig@' }},</p>

    <p style="color: #e5e7eb;">Tu pago se registró correctamente para <strong style="color: #ffffff;">{{ $event?->title }}</strong>.</p>

    <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
        <h3 style="margin-top: 0; color: #ffffff;">🧾 Resumen de compra</h3>
        @foreach ($items as $item)
            <p style="color: #e5e7eb; margin: 6px 0;">
                <strong style="color: #ffffff;">{{ $item->name }}</strong>
                — {{ $item->quantity }} x {{ number_format($item->unit_price, 2) }} {{ $order->currency }}
            </p>
        @endforeach
        <p style="color: #ffffff; margin-top: 12px; font-weight: 600;">Total: {{ number_format($order->total, 2) }} {{ $order->currency }}</p>
    </div>

    <div style="background-color: #0b0b0b; border: 1px solid rgba(255, 255, 255, 0.12); padding: 20px; border-radius: 12px; margin: 24px 0;">
        <h3 style="margin: 0 0 10px; color: #ffffff;">👥 Registra a cada asistente</h3>
        <p style="margin: 0 0 18px; color: #d1d5db; font-size: 14px;">
            Para enviar los accesos con QR necesitamos el nombre, correo, WhatsApp e Instagram de cada persona.
        </p>
        <div style="margin-top: 18px;">
            <a href="{{ $orderUrl }}" class="button">Completar registros</a>
        </div>
    </div>

    @if($event && $event->starts_at)
        <p style="color: #e5e7eb;">
            Fecha del evento: <strong style="color: #ffffff;">{{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</strong>
        </p>
    @endif

    <p style="margin-top: 30px; color: #e5e7eb;">Gracias por ser parte de la experiencia Lapsique.</p>
    <p style="color: #bdbdbd; font-size: 14px;">El equipo de Lapsique</p>
@endsection
