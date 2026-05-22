@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Tu contenido está listo')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Entrega lista</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">¡Tu contenido ya está listo!</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $booking->client_name }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Publicamos nuevo material de tu <strong style="{{ EmailBrand::strongStyle() }}">sesión de contenido</strong>.
        @if ($deliverableLink->displayLabel())
            <br><span style="{{ EmailBrand::mutedStyle() }}">{{ $deliverableLink->displayLabel() }}</span>
        @endif
    </p>

    @if ($slot)
        <p style="{{ EmailBrand::mutedStyle() }} margin-bottom:24px;">
            Sesión: {{ $slot->date->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }} · {{ $slot->time_label }}
        </p>
    @endif

    @include('emails.partials._button', ['url' => $driveUrl, 'label' => 'Abrir en Google Drive'])

    @if ($allLinks->count() > 1)
        <div class="card" style="{{ EmailBrand::cardStyle() }}">
            <h3 style="{{ EmailBrand::cardTitleStyle() }} font-size:16px;">Todos tus enlaces</h3>
            <ul style="margin:0;padding-left:18px;color:{{ EmailBrand::FOREGROUND }};">
                @foreach ($allLinks as $link)
                    <li style="margin-bottom:10px;">
                        <a href="{{ $link->url }}" style="{{ EmailBrand::linkStyle() }}">{{ $link->displayLabel() }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <p style="text-align:center;margin:24px 0;">
        <a href="{{ $portalUrl }}" style="{{ EmailBrand::linkStyle() }} font-size:14px;">Ver también en mi portal</a>
    </p>

    <p style="{{ EmailBrand::paragraphStyle() }} font-size:14px;">
        En tu portal puedes consultar el historial de entregas y el estado de tu sesión cuando quieras.
    </p>

    <p style="{{ EmailBrand::paragraphStyle() }} margin-top:30px;">¡Gracias por confiar en {{ EmailBrand::WORDMARK }}!</p>
    <p style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</p>
@endsection
