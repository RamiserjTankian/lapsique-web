@extends('emails.layout')
@php
    use App\Support\EmailBrand;

    $eventTime = $event->starts_at?->format('H:i');
    $arrivalHint = $event->starts_at
        ? $event->starts_at->copy()->subMinutes(30)->format('H:i')
        : null;
@endphp

@section('title', 'Confirmación de evento')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Guest list confirmada</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">¡Estás en la lista!</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $customer->name }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">Tu registro para <strong style="{{ EmailBrand::strongStyle() }}">{{ $event->title }}</strong> ha sido confirmado.</p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Detalles del evento</h3>

        @if($event->headline)
            <p style="{{ EmailBrand::cardRowStyle() }} font-style:italic;color:{{ EmailBrand::MUTED }};">{{ $event->headline }}</p>
        @endif

        @if($event->starts_at)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Fecha:</strong> {{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Hora:</strong> {{ $eventTime }}</p>
        @endif

        @if($event->venue)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Lugar:</strong> {{ $event->venue }}</p>
        @endif

        @if($event->city)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Ciudad:</strong> {{ $event->city }}</p>
        @endif

        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Estado:</strong> <span style="color:{{ EmailBrand::ACCENT }};font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">Confirmado</span></p>

        @if($arrivalHint && $eventTime)
            <p style="{{ EmailBrand::cardRowStyle() }} margin-top:16px;">
                Te recomendamos llegar a las <strong style="{{ EmailBrand::strongStyle() }}">{{ $arrivalHint }}</strong>
                (el evento inicia a las {{ $eventTime }}).
            </p>
        @endif
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:8px;font-size:13px;">
            La guest list caduca a las 18:00 del día del evento.
        </p>
    </div>

    <div style="{{ EmailBrand::qrWrapperStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Tu QR de check-in</h3>
        <p style="{{ EmailBrand::mutedStyle() }} margin-bottom:18px;">
            Presenta este QR en la entrada para registrar tu acceso.
        </p>
        <div style="{{ EmailBrand::qrFrameStyle() }}">
            <a href="{{ $checkInUrl }}" style="display:block;">
                <img src="{{ $checkInQrUrl }}" alt="QR de check-in" width="220" height="220" style="display:block;border-radius:12px;">
            </a>
        </div>
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:14px;font-size:12px;">
            Código manual: <strong style="{{ EmailBrand::strongStyle() }}">{{ $checkInCode }}</strong>
        </p>
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:8px;font-size:12px;">
            Si no ves el QR, <a href="{{ $checkInUrl }}" style="{{ EmailBrand::linkStyle() }}">abre tu pase</a>.
        </p>
        @include('emails.partials._button', ['url' => $checkInUrl, 'label' => 'Abrir pase de check-in'])
    </div>

    @if($event->description)
        <div style="margin:20px 0;">
            <h4 style="{{ EmailBrand::cardTitleStyle() }}">Acerca del evento</h4>
            <p style="{{ EmailBrand::paragraphStyle() }}">{{ Str::limit($event->description, 200) }}</p>
        </div>
    @endif

    @include('emails.partials._button', ['url' => $eventUrl, 'label' => 'Ver detalles completos'])

    <div style="{{ EmailBrand::tipBoxStyle() }}">
        <p style="margin:0;{{ EmailBrand::paragraphStyle() }}"><strong>Tip:</strong> Guarda este correo. Te servirá para tu check-in en el acceso.</p>
    </div>

    <p style="{{ EmailBrand::paragraphStyle() }} margin-top:30px;">
        ¿Tienes alguna pregunta? No dudes en contactarnos.<br>
        <strong style="{{ EmailBrand::strongStyle() }}">¡Nos vemos pronto!</strong>
    </p>

    <p style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</p>
@endsection
