@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Prueba de correo')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Diagnóstico</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Mailtrap operativo</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Este correo confirma que la integración de Mailtrap en {{ EmailBrand::WORDMARK }} puede enviar mensajes con la plantilla de marca.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Fecha UTC:</strong> {{ $sentAt }}</p>
    </div>
@endsection
