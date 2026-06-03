@extends('emails.trascendental-layout')

@section('title', 'Join The List Trascendental')

@section('content')
    <p class="tdl-eyebrow">Trascendental.</p>
    <h2 class="tdl-heading">Estas en la lista</h2>

    <p class="tdl-copy">
        {{ $customer->name ?: 'Hola' }}, tu registro quedo confirmado. Tendras acceso anticipado a eventos, anuncios de artistas y proyectos seleccionados antes de su lanzamiento publico.
    </p>

    <div class="tdl-card">
        <p class="tdl-row"><strong>Eventos:</strong> acceso temprano a fechas seleccionadas.</p>
        <p class="tdl-row"><strong>Artistas:</strong> anuncios del roster y showcases.</p>
        <p class="tdl-row"><strong>Proyectos:</strong> novedades antes del lanzamiento publico.</p>
    </div>

    <p class="tdl-copy">
        Conserva este correo. Cuando se anuncie una nueva fecha, recibiras el siguiente paso por los canales oficiales de Trascendental.
    </p>
@endsection
