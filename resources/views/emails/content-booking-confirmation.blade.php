@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Sesión confirmada')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Reserva confirmada</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">¡Tu sesión está confirmada!</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $booking->client_name }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Recibimos tu reserva para <strong style="{{ EmailBrand::strongStyle() }}">{{ mb_strtolower($booking->service_name) }}</strong>.
        {{ $booking->service_description }}.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Detalles de tu reserva</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Servicio:</strong> {{ $booking->service_name }}</p>
        @if ($slot)
            <p style="{{ EmailBrand::cardRowStyle() }}">
                <strong style="{{ EmailBrand::strongStyle() }}">Fecha y hora:</strong>
                {{ $slot->date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }} · {{ $slot->time_label }}
            </p>
        @endif
        @if ($booking->shoot_location)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Locación:</strong> {{ $booking->shoot_location }}</p>
        @endif
        <p style="{{ EmailBrand::cardRowStyle() }}">
            <strong style="{{ EmailBrand::strongStyle() }}">Inversión:</strong>
            ${{ number_format($booking->amount, 0) }} {{ strtoupper($booking->currency) }}
        </p>
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:12px;font-size:12px;">ID de reserva: {{ $booking->public_id }}</p>
    </div>

    @include('emails.partials._button', ['url' => $confirmUrl, 'label' => 'Ver mi reserva'])

    <p style="{{ EmailBrand::paragraphStyle() }} font-size:14px;">
        Revisa tu <a href="{{ $portalUrl }}" style="{{ EmailBrand::linkStyle() }}">portal de cliente</a>
        para ver el recibo, el estado de tu sesión y el enlace a tu material en Google Drive cuando esté publicado.
        Si es tu primera compra, recibirás un correo aparte con tus datos de acceso.
    </p>

    <p style="{{ EmailBrand::paragraphStyle() }} margin-top:30px;">Nos vemos pronto en set.</p>
    <p style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</p>
@endsection
