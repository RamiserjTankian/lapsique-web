@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Join The List Trascendental')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Trascendental.</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Estas en la lista</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        {{ $customer->name ?: 'Hola' }}, tu registro quedo confirmado. Te enviaremos acceso anticipado a lanzamientos de tickets, eventos de cupo limitado, anuncios de artistas y oportunidades de guest list.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Tickets:</strong> lanzamientos y preventas seleccionadas.</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Eventos:</strong> fechas de capacidad limitada.</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Comunidad:</strong> oportunidades y anuncios para la lista.</p>
    </div>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Conserva este correo. Cuando se anuncie una nueva fecha, recibiras el siguiente paso por los canales oficiales de Trascendental.
    </p>
@endsection
