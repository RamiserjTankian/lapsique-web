@extends('emails.layout')

@section('title', 'Bienvenida a tu portal')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0; letter-spacing: 0.02em;">¡Bienvenido a Lapsique!</h2>

    <p style="color: #e5e7eb;">Hola {{ $customer->name ?? 'amig@' }},</p>

    @if ($booking)
        <p style="color: #e5e7eb;">
            Tu pago quedó confirmado. Creamos tu <strong style="color: #ffffff;">portal de cliente</strong>
            para que revises tu sesión, recibos y el material de Drive cuando esté listo.
        </p>
    @elseif ($order?->event)
        <p style="color: #e5e7eb;">Tu compra de <strong style="color: #ffffff;">{{ $order->event->title }}</strong> ya está confirmada.</p>
    @else
        <p style="color: #e5e7eb;">Ya puedes entrar a tu portal para revisar tus compras y servicios.</p>
    @endif

    <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
        <h3 style="margin-top: 0; color: #ffffff;">Datos de acceso</h3>
        <p style="color: #e5e7eb; margin: 6px 0;">Usuario: <strong style="color: #ffffff;">{{ $customer->email }}</strong></p>
        <p style="color: #e5e7eb; margin: 6px 0;">Contraseña temporal: <strong style="color: #ffffff;">{{ $temporaryPassword }}</strong></p>
        <p style="color: #9ca3af; font-size: 12px; margin-top: 12px;">Guarda estos datos. Te recomendamos cambiar la contraseña después de ingresar.</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $loginUrl }}" class="button">Ingresar a mi portal</a>
    </div>

    <p style="color: #bdbdbd; font-size: 14px;">
        Desde tu portal puedes ver sesiones confirmadas, historial de pagos y enlaces a tu material en Google Drive.
    </p>
@endsection
