@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Bienvenido a '.EmailBrand::WORDMARK)

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Bienvenido a la órbita</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Ya estás dentro de {{ EmailBrand::WORDMARK }}</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola <strong style="{{ EmailBrand::strongStyle() }}">{{ $customer->name }}</strong>, gracias por sumarte a nuestra comunidad.</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Desde aquí te compartiremos lanzamientos, sesiones grabadas, eventos y referencias reales de producción audiovisual.
        La idea es que veas cómo se construye una pieza con intención antes de reservar, comprar o entrar a una experiencia.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Qué vas a recibir</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong>Producción audiovisual:</strong> ideas, referencias y paquetes para marcas que necesitan verse más premium.</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong>DJ sets y escena:</strong> sesiones, artistas y contenido pensado para crecer carrera, booking y presencia visual.</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong>Eventos:</strong> tickets, guest list y avisos de experiencias donde participa {{ EmailBrand::WORDMARK }}.</p>
    </div>

    @include('emails.partials._button', ['url' => route('djset.show'), 'label' => 'Ver sesiones DJ set'])

    <div style="{{ EmailBrand::tipBoxStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }} margin-bottom:10px;">Si tienes un proyecto en mente</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}">Responde este correo con tu marca, artista o evento. Te orientamos hacia sesión de contenido, DJ set grabado o acceso a próximos eventos.</p>
    </div>

    <p style="margin-top:30px;{{ EmailBrand::paragraphStyle() }}">
        <strong style="{{ EmailBrand::strongStyle() }}">Nos vemos en la siguiente producción.</strong><br>
        <span style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</span>
    </p>
@endsection
