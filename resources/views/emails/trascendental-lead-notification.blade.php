@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Nuevo lead Trascendental')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Trascendental</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Nuevo lead calificado</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        {{ $customer->name }} envio una solicitud desde trascendentalby.mx.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Servicio:</strong> {{ $lead['service_type'] ?? 'n/a' }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Ciudad:</strong> {{ $lead['city'] ?? 'n/a' }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Fecha:</strong> {{ $lead['event_date'] ?? 'Por definir' }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Presupuesto:</strong> {{ $lead['budget'] ?? 'n/a' }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Email:</strong> {{ $customer->email }}</p>
        <p style="{{ EmailBrand::cardRowStyle() }}"><strong style="{{ EmailBrand::strongStyle() }}">Telefono:</strong> {{ $customer->phone ?: 'n/a' }}</p>
    </div>

    @if (! empty($lead['message']))
        <p style="{{ EmailBrand::paragraphStyle() }}">
            <strong style="{{ EmailBrand::strongStyle() }}">Mensaje:</strong><br>
            {{ $lead['message'] }}
        </p>
    @endif
@endsection
