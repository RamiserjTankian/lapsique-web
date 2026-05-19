@extends('emails.layout')

@section('title', 'Tu contenido está listo')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0; letter-spacing: 0.02em;">¡Tu contenido ya está listo!</h2>

    <p style="color: #e5e7eb;">Hola {{ $booking->client_name }},</p>

    <p style="color: #e5e7eb;">
        Publicamos nuevo material de tu <strong style="color: #ffffff;">sesión de contenido</strong>.
        @if ($deliverableLink->displayLabel())
            <br><span style="color: #9ca3af;">{{ $deliverableLink->displayLabel() }}</span>
        @endif
    </p>

    @if ($slot)
        <p style="color: #9ca3af; font-size: 14px; margin-bottom: 24px;">
            Sesión: {{ $slot->date->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }} · {{ $slot->time_label }}
        </p>
    @endif

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $driveUrl }}" class="button" style="margin-bottom: 12px;">Abrir en Google Drive</a>
    </div>

    @if ($allLinks->count() > 1)
        <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
            <h3 style="margin-top: 0; color: #ffffff; font-size: 16px;">Todos tus enlaces</h3>
            <ul style="margin: 0; padding-left: 18px; color: #e5e7eb;">
                @foreach ($allLinks as $link)
                    <li style="margin-bottom: 10px;">
                        <a href="{{ $link->url }}" style="color: #ffffff;">{{ $link->displayLabel() }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="text-align: center; margin: 24px 0;">
        <a href="{{ $portalUrl }}" style="color: #ffffff; font-size: 14px; text-decoration: underline;">Ver también en mi portal</a>
    </div>

    <p style="color: #e5e7eb; font-size: 14px;">
        En tu portal puedes consultar el historial de entregas y el estado de tu sesión cuando quieras.
    </p>

    <p style="margin-top: 30px; color: #e5e7eb;">¡Gracias por confiar en Lapsique!</p>
    <p style="color: #bdbdbd; font-size: 14px;">El equipo de Lapsique</p>
@endsection
