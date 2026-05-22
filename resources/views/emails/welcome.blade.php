@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Bienvenido a '.EmailBrand::WORDMARK)

@section('content')
    <h2 style="{{ EmailBrand::headingStyle() }}">¡Hola {{ $customer->name }}!</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Gracias por unirte a {{ EmailBrand::WORDMARK }}. Estamos emocionados de tenerte con nosotros.</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">Aquí encontrarás información sobre eventos, producción de contenido y experiencias exclusivas:</p>

    <ul style="line-height:2;color:{{ EmailBrand::FOREGROUND }};padding-left:20px;">
        <li>Próximos eventos y lanzamientos</li>
        <li>Artistas y colaboraciones</li>
        <li>Acceso anticipado a tickets</li>
        <li>Contenido y noticias del estudio</li>
    </ul>

    @include('emails.partials._button', ['url' => route('events.index'), 'label' => 'Ver próximos eventos'])

    <p style="{{ EmailBrand::paragraphStyle() }}">¿Quieres estar en nuestra guest list? Regístrate en nuestros eventos y recibe confirmación directa.</p>

    <p style="margin-top:30px;{{ EmailBrand::paragraphStyle() }}">
        <strong style="{{ EmailBrand::strongStyle() }}">¡Nos vemos pronto!</strong><br>
        <span style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</span>
    </p>
@endsection
