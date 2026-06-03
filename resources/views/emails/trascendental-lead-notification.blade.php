@extends('emails.trascendental-layout')

@section('title', 'Nuevo lead Trascendental')

@section('content')
    <p class="tdl-eyebrow">Trascendental</p>
    <h2 class="tdl-heading">Nuevo lead calificado</h2>

    <p class="tdl-copy">
        {{ $customer->name }} envio una solicitud desde trascendentalby.mx.
    </p>

    <div class="tdl-card">
        <p class="tdl-row"><strong>Servicio:</strong> {{ $lead['service_type'] ?? 'n/a' }}</p>
        <p class="tdl-row"><strong>Ciudad:</strong> {{ $lead['city'] ?? 'n/a' }}</p>
        <p class="tdl-row"><strong>Fecha:</strong> {{ $lead['event_date'] ?? 'Por definir' }}</p>
        <p class="tdl-row"><strong>Presupuesto:</strong> {{ $lead['budget'] ?? 'n/a' }}</p>
        <p class="tdl-row"><strong>Email:</strong> {{ $customer->email }}</p>
        <p class="tdl-row"><strong>Telefono:</strong> {{ $customer->phone ?: 'n/a' }}</p>
    </div>

    @if (! empty($lead['message']))
        <p class="tdl-copy">
            <strong>Mensaje:</strong><br>
            {{ $lead['message'] }}
        </p>
    @endif
@endsection
