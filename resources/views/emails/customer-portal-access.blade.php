@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Bienvenida a tu portal')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Portal de cliente</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">¡Bienvenido a {{ EmailBrand::WORDMARK }}!</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $customer->name ?? 'amig@' }},</p>

    @if ($booking)
        <p style="{{ EmailBrand::paragraphStyle() }}">
            Tu pago quedó confirmado. Creamos tu <strong style="{{ EmailBrand::strongStyle() }}">portal de cliente</strong>
            para que revises tu sesión, recibos y el material de Drive cuando esté listo.
        </p>
    @elseif ($order?->event)
        <p style="{{ EmailBrand::paragraphStyle() }}">Tu compra de <strong style="{{ EmailBrand::strongStyle() }}">{{ $order->event->title }}</strong> ya está confirmada.</p>
    @else
        <p style="{{ EmailBrand::paragraphStyle() }}">Ya puedes entrar a tu portal para revisar tus compras y servicios.</p>
    @endif

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Datos de acceso</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}">Usuario: <strong style="{{ EmailBrand::strongStyle() }}">{{ $customer->email }}</strong></p>
        <p style="{{ EmailBrand::cardRowStyle() }}">Contraseña temporal: <strong style="{{ EmailBrand::strongStyle() }}">{{ $temporaryPassword }}</strong></p>
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:12px;font-size:12px;">Guarda estos datos. Te recomendamos cambiar la contraseña después de ingresar.</p>
    </div>

    @include('emails.partials._button', ['url' => $loginUrl, 'label' => 'Ingresar a mi portal'])

    <p style="{{ EmailBrand::mutedStyle() }}">
        Desde tu portal puedes ver sesiones confirmadas, historial de pagos y enlaces a tu material en Google Drive.
    </p>
@endsection
