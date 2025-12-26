@extends('emails.layout')

@section('title', 'Bienvenido a Lapsique')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0;">¡Hola {{ $customer->name }}! 👋</h2>
    
    <p style="color: #e5e7eb;">Gracias por unirte a la familia Lapsique. Estamos emocionados de tenerte con nosotros.</p>
    
    <p style="color: #e5e7eb;">Somos una comunidad apasionada por la música electrónica, especialmente el techno. Aquí encontrarás:</p>
    
    <ul style="line-height: 2; color: #e5e7eb;">
        <li>🎉 Información sobre nuestros próximos eventos</li>
        <li>🎧 Los mejores DJs y artistas de la escena</li>
        <li>🎟️ Acceso anticipado a tickets</li>
        <li>💌 Contenido exclusivo y noticias</li>
    </ul>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('events.index') }}" class="button">Ver Próximos Eventos</a>
    </div>
    
    <p style="color: #e5e7eb;">¿Quieres estar en nuestra guest list? Regístrate en nuestros eventos y recibe confirmación directa.</p>
    
    <p style="margin-top: 30px; color: #e5e7eb;">
        <strong style="color: #ffffff;">¡Nos vemos en la pista! 🔊</strong><br>
        <span style="color: #bdbdbd;">El equipo de Lapsique</span>
    </p>
@endsection
