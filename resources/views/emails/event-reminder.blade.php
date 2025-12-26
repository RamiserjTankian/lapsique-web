@extends('emails.layout')

@section('title', 'Recordatorio de Evento')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0;">¡El evento es pronto! 🔔</h2>
    
    <p style="color: #e5e7eb;">Hola {{ $customer->name }},</p>
    
    <p style="font-size: 18px; color: #e5e7eb;">
        <strong style="color: #ffffff;">{{ $event->title }}</strong> comienza en
        <span style="color: #ffffff; font-weight: 600;">{{ $hoursBeforeEvent }} horas</span>!
    </p>
    
    <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
        <h3 style="margin-top: 0; color: #ffffff;">📍 Información del Evento</h3>
        
        @if($event->starts_at)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">📆 Cuándo:</strong> {{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM') }}</p>
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">🕐 Hora:</strong> {{ $event->starts_at->format('H:i') }}</p>
        @endif
        
        @if($event->venue)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">📍 Dónde:</strong> {{ $event->venue }}</p>
        @endif
        
        @if($event->city)
            <p style="color: #e5e7eb;"><strong style="color: #ffffff;">🌆 Ciudad:</strong> {{ $event->city }}</p>
        @endif
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $eventUrl }}" class="button">Ver Mapa y Detalles</a>
    </div>
    
    <h4 style="color: #ffffff;">💡 Antes de salir, recuerda:</h4>
    <ul style="line-height: 2; color: #e5e7eb;">
        <li>✅ Llegar con 30 minutos de anticipación</li>
        <li>🆔 Traer identificación oficial</li>
        <li>💳 Llevar efectivo para bebidas</li>
        <li>📱 Cargar tu celular</li>
        <li>😷 Respetar las medidas de seguridad del venue</li>
    </ul>
    
    <div style="background-color: #0b0b0b; padding: 15px; border-left: 3px solid #ffffff; margin: 20px 0;">
        <p style="margin: 0; color: #e5e7eb;">
            <strong style="color: #ffffff;">Tu estado en guest list:</strong>
            <span style="color: #ffffff; font-weight: 600;">{{ strtoupper($guestListEntry->status ?? 'CONFIRMADO') }}</span>
        </p>
    </div>
    
    <p style="margin-top: 30px; color: #e5e7eb;">
        <strong style="color: #ffffff;">¡Nos vemos muy pronto! 🎊🎧</strong><br>
        Prepárate para una noche increíble.
    </p>
    
    <p style="color: #bdbdbd; font-size: 14px;">
        El equipo de Lapsique
    </p>
@endsection
