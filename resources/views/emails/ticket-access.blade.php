@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Tu acceso')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Acceso al evento</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Tu acceso está listo</h2>

    @if($testMode ?? false)
        <div style="border:2px solid #b66a00;background:#fff1cc;color:#7a3f00;padding:14px 16px;margin:16px 0;font-weight:700;text-align:center;">
            PASE DE PRUEBA · NO VÁLIDO PARA INGRESO
        </div>
    @endif

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $attendee->name ?? 'amig@' }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">Este es tu acceso para <strong style="{{ EmailBrand::strongStyle() }}">{{ $event?->title }}</strong>.</p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Detalles del ticket</h3>
        @if($product)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Tipo:</strong> {{ $product->name }}</p>
        @endif
        @if($event?->starts_at)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Fecha:</strong> {{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        @endif
        @if($event?->venue)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Lugar:</strong> {{ $event->venue }}</p>
        @endif
    </div>

    <div style="{{ EmailBrand::qrWrapperStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Tu QR de acceso</h3>
        <p style="{{ EmailBrand::mutedStyle() }} margin-bottom:18px;">
            {{ ($testMode ?? false) ? 'Este QR permite revisar el flujo, pero no registra un ingreso real.' : 'Presenta este QR en la entrada para registrar tu acceso.' }}
        </p>
        <div style="{{ EmailBrand::qrFrameStyle() }}">
            <a href="{{ $checkInUrl }}" style="display:block;">
                <img src="{{ $checkInQrUrl }}" alt="QR de acceso" width="220" height="220" style="display:block;border-radius:12px;">
            </a>
        </div>
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:14px;font-size:12px;">
            Código manual: <strong style="{{ EmailBrand::strongStyle() }}">{{ $checkInCode }}</strong>
        </p>
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:8px;font-size:12px;">
            Si no ves el QR, <a href="{{ $checkInUrl }}" style="{{ EmailBrand::linkStyle() }}">abre tu pase</a>.
        </p>
        @include('emails.partials._button', ['url' => $checkInUrl, 'label' => 'Abrir pase de acceso'])
    </div>

    @include('emails.partials._button', ['url' => $eventUrl, 'label' => 'Ver detalles del evento'])

    <p style="{{ EmailBrand::paragraphStyle() }}">{{ ($testMode ?? false) ? 'Guarda este correo como evidencia de la prueba del workflow.' : 'Guarda este correo. Te servirá para ingresar al evento.' }}</p>
    <p style="{{ EmailBrand::mutedStyle() }}">Adjuntamos tu pase en PDF para que lo tengas siempre a mano.</p>
    <p style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</p>
@endsection
