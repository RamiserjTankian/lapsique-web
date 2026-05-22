@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Recordatorio de evento')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Recordatorio</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">¡El evento es pronto!</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $customer->name }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }} font-size:18px;">
        <strong style="{{ EmailBrand::strongStyle() }}">{{ $event->title }}</strong> comienza en
        <strong style="color:{{ EmailBrand::ACCENT }};">{{ $hoursBeforeEvent }} horas</strong>.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Información del evento</h3>

        @if($event->starts_at)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Cuándo:</strong> {{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM') }}</p>
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Hora:</strong> {{ $event->starts_at->format('H:i') }}</p>
        @endif

        @if($event->venue)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Dónde:</strong> {{ $event->venue }}</p>
        @endif

        @if($event->city)
            <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Ciudad:</strong> {{ $event->city }}</p>
        @endif
    </div>

    @include('emails.partials._button', ['url' => $eventUrl, 'label' => 'Ver mapa y detalles'])

    <h4 style="{{ EmailBrand::cardTitleStyle() }}">Antes de salir, recuerda:</h4>
    <ul style="line-height:2;color:{{ EmailBrand::FOREGROUND }};padding-left:20px;">
        <li>Llegar con 30 minutos de anticipación</li>
        <li>Traer identificación oficial</li>
        <li>Llevar efectivo para consumos</li>
        <li>Cargar tu celular</li>
        <li>Respetar las medidas del venue</li>
    </ul>

    <div style="{{ EmailBrand::tipBoxStyle() }}">
        <p style="margin:0;{{ EmailBrand::paragraphStyle() }}">
            <strong>Tu estado en guest list:</strong>
            <span style="color:{{ EmailBrand::ACCENT }};font-weight:600;">{{ strtoupper($guestListEntry->status ?? 'CONFIRMADO') }}</span>
        </p>
    </div>

    <p style="{{ EmailBrand::paragraphStyle() }} margin-top:30px;">
        <strong style="{{ EmailBrand::strongStyle() }}">¡Nos vemos muy pronto!</strong><br>
        Prepárate para una gran experiencia.
    </p>

    <p style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</p>
@endsection
