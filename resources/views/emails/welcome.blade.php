@extends('emails.layout')
@php
    use App\Support\EmailBrand;

    $language = $language ?? 'es';
    $variant = $variant ?? 'production';
    $ctaUrl = $ctaUrl ?? route('booking.show');
    $ctaLabel = $ctaLabel ?? ($language === 'en' ? 'Book a content session' : 'Agendar sesión de contenido');
@endphp

@section('title', 'Bienvenido a '.EmailBrand::WORDMARK)

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">{{ $language === 'en' ? 'Welcome to the orbit' : 'Bienvenido a la órbita' }}</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">{{ $language === 'en' ? "You're inside " : 'Ya estás dentro de ' }}{{ EmailBrand::WORDMARK }}</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">{{ $language === 'en' ? 'Hi' : 'Hola' }} <strong style="{{ EmailBrand::strongStyle() }}">{{ $customer->name }}</strong>, {{ $language === 'en' ? 'thanks for joining our community.' : 'gracias por sumarte a nuestra comunidad.' }}</p>

    @if($variant === 'dj_set')
        <p style="{{ EmailBrand::paragraphStyle() }}">{{ $language === 'en' ? 'We will send you recorded sessions, artist references and production ideas for turning a set into a career asset.' : 'Te vamos a compartir sesiones grabadas, referencias de artistas e ideas de producción para convertir un set en una pieza que te ayude a crecer carrera.' }}</p>
    @elseif($variant === 'events')
        <p style="{{ EmailBrand::paragraphStyle() }}">{{ $language === 'en' ? 'We will send you event updates, ticket access, guest list alerts and visual references from the Lapsique scene.' : 'Te vamos a compartir eventos, tickets, guest list y referencias visuales de la escena que documenta Lapsique.' }}</p>
    @else
        <p style="{{ EmailBrand::paragraphStyle() }}">{{ $language === 'en' ? 'We will send you production references, business content ideas and ways to make your offer look more cinematic before booking.' : 'Te vamos a compartir referencias de producción, ideas de contenido para negocio y formas de hacer que tu oferta se vea más cinematográfica antes de agendar.' }}</p>
    @endif

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">{{ $language === 'en' ? 'What you will receive' : 'Qué vas a recibir' }}</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong>{{ $language === 'en' ? 'Audiovisual production:' : 'Producción audiovisual:' }}</strong> {{ $language === 'en' ? 'references and packages for brands that need to look premium.' : 'ideas, referencias y paquetes para marcas que necesitan verse más premium.' }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong>DJ sets:</strong> {{ $language === 'en' ? 'sessions, artists and content built for bookings and visual presence.' : 'sesiones, artistas y contenido pensado para crecer carrera, booking y presencia visual.' }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong>{{ $language === 'en' ? 'Events:' : 'Eventos:' }}</strong> {{ $language === 'en' ? 'tickets, guest list and Lapsique experience updates.' : 'tickets, guest list y avisos de experiencias donde participa ' . EmailBrand::WORDMARK . '.' }}</p>
    </div>

    @include('emails.partials._button', ['url' => $ctaUrl, 'label' => $ctaLabel])

    <div style="{{ EmailBrand::tipBoxStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }} margin-bottom:10px;">{{ $language === 'en' ? 'If you have a project in mind' : 'Si tienes un proyecto en mente' }}</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}">{{ $language === 'en' ? 'Reply with your brand, artist or event. We will point you to content production, a recorded DJ set or the next event access.' : 'Responde este correo con tu marca, artista o evento. Te orientamos hacia sesión de contenido, DJ set grabado o acceso a próximos eventos.' }}</p>
    </div>

    <p style="margin-top:30px;{{ EmailBrand::paragraphStyle() }}">
        <strong style="{{ EmailBrand::strongStyle() }}">{{ $language === 'en' ? 'See you in the next production.' : 'Nos vemos en la siguiente producción.' }}</strong><br>
        <span style="{{ EmailBrand::mutedStyle() }}">{{ $language === 'en' ? 'The ' . EmailBrand::WORDMARK . ' team' : 'El equipo de ' . EmailBrand::WORDMARK }}</span>
    </p>
@endsection
