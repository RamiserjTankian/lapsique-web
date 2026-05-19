@extends('emails.layout')

@section('title', 'Tu Acceso')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0; letter-spacing: 0.02em;">Tu acceso está listo 🎟️</h2>

    <p style="color: #e5e7eb;">Hola {{ $attendee->name ?? 'amig@' }},</p>

    <p style="color: #e5e7eb;">Este es tu acceso para <strong style="color: #ffffff;">{{ $event?->title }}</strong>.</p>

    <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
        <h3 style="margin-top: 0; color: #ffffff;">🎫 Detalles del ticket</h3>
        @if($product)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">Tipo:</strong> {{ $product->name }}</p>
        @endif
        @if($event?->starts_at)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">Fecha:</strong> {{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        @endif
        @if($event?->venue)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">Lugar:</strong> {{ $event->venue }}</p>
        @endif
    </div>

    <div style="background-color: #0b0b0b; border: 1px solid rgba(255, 255, 255, 0.12); padding: 20px; border-radius: 12px; margin: 24px 0; text-align: center;">
        <h3 style="margin: 0 0 10px; color: #ffffff;">✅ Tu QR de acceso</h3>
        <p style="margin: 0 0 18px; color: #d1d5db; font-size: 14px;">
            Presenta este QR en la entrada para registrar tu acceso.
        </p>
        <div style="display: inline-block; padding: 12px; background-color: #111111; border-radius: 14px; border: 1px solid rgba(255, 255, 255, 0.12);">
            <a href="{{ $checkInUrl }}" style="display: block;">
                <img src="{{ $checkInQrUrl }}" alt="QR de Acceso" style="display: block; width: 220px; height: 220px; border-radius: 12px;">
            </a>
        </div>
        <p style="margin: 14px 0 0; font-size: 12px; color: #d1d5db;">
            Código manual: <strong style="color: #ffffff;">{{ $checkInCode }}</strong>
        </p>
        <p style="margin: 8px 0 0; font-size: 12px; color: #bdbdbd;">
            Si no ves el QR, abre el pase aquí:
            <a href="{{ $checkInUrl }}" style="color: #ffffff; text-decoration: underline;">Abrir pase</a>
        </p>
        <div style="margin-top: 18px;">
            <a href="{{ $checkInUrl }}" class="button">Abrir Pase de Acceso</a>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $eventUrl }}" class="button">Ver Detalles del Evento</a>
    </div>

    <p style="margin-top: 30px; color: #e5e7eb;">Guarda este email. Te servirá para ingresar al evento.</p>
    <p style="color: #bdbdbd; font-size: 14px;">Adjuntamos tu pase en PDF para que lo tengas siempre a mano.</p>
    <p style="color: #bdbdbd; font-size: 14px;">El equipo de Lapsique</p>
@endsection
