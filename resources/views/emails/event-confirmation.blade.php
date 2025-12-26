@extends('emails.layout')

@section('title', 'Confirmación de Evento')

@section('content')
    @php
        $arrivalOptions = ['12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00'];
        $arrivalTime = $arrivalOptions[array_rand($arrivalOptions)];
    @endphp

    <h2 style="color: #ffffff; margin-top: 0; letter-spacing: 0.02em;">¡Estás en la lista! ✅</h2>
    
    <p style="color: #e5e7eb;">Hola {{ $customer->name }},</p>
    
    <p style="color: #e5e7eb;">Tu registro para <strong style="color: #ffffff;">{{ $event->title }}</strong> ha sido confirmado.</p>
    
    <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
        <h3 style="margin-top: 0; color: #ffffff;">📅 Detalles del Evento</h3>
        
        @if($event->headline)
            <p style="font-size: 16px; color: #d1d5db; font-style: italic;">{{ $event->headline }}</p>
        @endif
        
        @if($event->starts_at)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">📆 Fecha:</strong> {{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">🕐 Hora:</strong> 12:00</p>
        @endif
        
        @if($event->venue)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">📍 Lugar:</strong> {{ $event->venue }}</p>
        @endif
        
        @if($event->city)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">🌆 Ciudad:</strong> {{ $event->city }}</p>
        @endif
        
        <p style="color: #e5e7eb;"><strong style="color: #ffffff;">✨ Estado:</strong> <span style="color: #ffffff; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">CONFIRMADO ✅</span></p>

        <p style="margin-top: 16px; color: #f3f4f6;">
            Te esperamos en este evento a las <strong style="color: #ffffff;">{{ $arrivalTime }}</strong>
            confiando en que nos aportarás tu energía y tiempo para crear una experiencia inolvidable.
        </p>
        <p style="margin-top: 8px; color: #f3f4f6;">
            La guest list caduca a las <strong style="color: #ffffff;">6 pm</strong>.
        </p>
    </div>

    <div style="background-color: #0b0b0b; border: 1px solid rgba(255, 255, 255, 0.12); padding: 20px; border-radius: 12px; margin: 24px 0; text-align: center;">
        <h3 style="margin: 0 0 10px; color: #ffffff;">🎟️ Tu QR de Check-in</h3>
        <p style="margin: 0 0 18px; color: #d1d5db; font-size: 14px;">
            Presenta este QR en la entrada para registrar tu acceso.
        </p>
        <div style="display: inline-block; padding: 12px; background-color: #111111; border-radius: 14px; border: 1px solid rgba(255, 255, 255, 0.12);">
            <a href="{{ $checkInUrl }}" style="display: block;">
                <img src="{{ $checkInQrUrl }}" alt="QR de Check-in" style="display: block; width: 220px; height: 220px; border-radius: 12px;">
            </a>
        </div>
        <p style="margin: 14px 0 0; font-size: 12px; color: #d1d5db;">
            Código manual: <strong style="color: #ffffff;">{{ $checkInCode }}</strong>
        </p>
        <p style="margin: 8px 0 0; font-size: 12px; color: #bdbdbd;">
            El staff validará este código en la entrada.
        </p>
        <p style="margin: 10px 0 0; font-size: 12px; color: #d1d5db;">
            Si no ves el QR, abre el pase aquí:
            <a href="{{ $checkInUrl }}" style="color: #ffffff; text-decoration: underline;">Abrir pase</a>
        </p>
        <div style="margin-top: 18px;">
            <a href="{{ $checkInUrl }}" class="button">Abrir Pase de Check-in</a>
        </div>
    </div>
    
    @if($event->description)
        <div style="margin: 20px 0;">
            <h4 style="color: #ffffff;">Acerca del Evento</h4>
            <p style="color: #e5e7eb;">{{ Str::limit($event->description, 200) }}</p>
        </div>
    @endif
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $eventUrl }}" class="button">Ver Detalles Completos</a>
    </div>
    
    <div style="background-color: #0b0b0b; padding: 15px; border-left: 3px solid #ffffff; margin: 20px 0;">
        <p style="margin: 0; color: #e5e7eb;"><strong>💡 Tip:</strong> Guarda este email. Te servirá para tu check-in en el acceso.</p>
    </div>
    
    <p style="margin-top: 30px; color: #e5e7eb;">
        ¿Tienes alguna pregunta? No dudes en contactarnos.<br>
        <strong style="color: #ffffff;">¡Nos vemos en la pista! 🎉</strong>
    </p>
    
    <p style="color: #bdbdbd; font-size: 14px;">
        El equipo de Lapsique
    </p>
@endsection
