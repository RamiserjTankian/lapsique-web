@extends('emails.layout')

@section('title', 'Sesión confirmada')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0; letter-spacing: 0.02em;">¡Tu sesión está confirmada!</h2>

    <p style="color: #e5e7eb;">Hola {{ $booking->client_name }},</p>

    <p style="color: #e5e7eb;">
        Recibimos tu reserva para <strong style="color: #ffffff;">{{ mb_strtolower($booking->service_name) }}</strong>.
        {{ $booking->service_description }}.
    </p>

    <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
        <h3 style="margin-top: 0; color: #ffffff;">Detalles de tu reserva</h3>
        <p style="color: #e5e7eb; margin: 6px 0;">
            <strong style="color: #ffffff;">Servicio:</strong> {{ $booking->service_name }}
        </p>
        @if ($slot)
            <p style="color: #e5e7eb; margin: 6px 0;">
                <strong style="color: #ffffff;">Fecha y hora:</strong>
                {{ $slot->date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }} · {{ $slot->time_label }}
            </p>
        @endif
        @if ($booking->shoot_location)
            <p style="color: #e5e7eb; margin: 6px 0;">
                <strong style="color: #ffffff;">Locación:</strong> {{ $booking->shoot_location }}
            </p>
        @endif
        <p style="color: #e5e7eb; margin: 6px 0;">
            <strong style="color: #ffffff;">Inversión:</strong>
            ${{ number_format($booking->amount, 0) }} {{ strtoupper($booking->currency) }}
        </p>
        <p style="color: #9ca3af; font-size: 12px; margin-top: 12px;">
            ID de reserva: {{ $booking->public_id }}
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $confirmUrl }}" class="button">Ver mi reserva</a>
    </div>

    <p style="color: #e5e7eb; font-size: 14px;">
        Revisa tu <a href="{{ $portalUrl }}" style="color: #ffffff;">portal de cliente</a>
        para ver el recibo, el estado de tu sesión y el enlace a tu material en Google Drive cuando esté publicado.
        Si es tu primera compra, recibirás un correo aparte con tus datos de acceso.
    </p>

    <p style="margin-top: 30px; color: #e5e7eb;">Nos vemos pronto en set.</p>
    <p style="color: #bdbdbd; font-size: 14px;">El equipo de Lapsique</p>
@endsection
