@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Join The List Trascendental')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Trascendental.</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Estas en la lista</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        {{ $customer->name ?: 'Hola' }}, tu registro quedo confirmado. Este codigo valida un 20% de descuento para nuestro siguiente evento Trascendental.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Codigo:</strong> {{ $discountCode }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Descuento:</strong> 20%</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Uso:</strong> valido para el siguiente evento anunciado por Trascendental.</p>
    </div>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Conserva este correo. Cuando se anuncie la siguiente fecha, usa el codigo en el canal de compra o escribenos para validarlo.
    </p>
@endsection
