@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Recibo de pago')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Recibo de pago</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Comprobante de tu sesión</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $booking->client_name }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Este es el comprobante de tu pago por <strong style="{{ EmailBrand::strongStyle() }}">{{ mb_strtolower($booking->service_name) }}</strong> en {{ EmailBrand::WORDMARK }}.
    </p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Detalle del recibo</h3>
        <p style="{{ EmailBrand::cardRowStyle() }}">
            <strong style="{{ EmailBrand::strongStyle() }}">Concepto:</strong> {{ $booking->service_name }} ({{ $booking->service_description }})
        </p>
        @if ($slot)
            <p style="{{ EmailBrand::cardRowStyle() }}">
                <strong style="{{ EmailBrand::strongStyle() }}">Fecha programada:</strong>
                {{ $slot->date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }} · {{ $slot->time_label }}
            </p>
        @endif
        <p style="{{ EmailBrand::cardRowStyle() }}">
            <strong style="{{ EmailBrand::strongStyle() }}">Monto pagado:</strong>
            ${{ number_format($booking->amount, 0) }} {{ strtoupper($booking->currency) }}
        </p>
        @if ($booking->paid_at)
            <p style="{{ EmailBrand::cardRowStyle() }}">
                <strong style="{{ EmailBrand::strongStyle() }}">Fecha de pago:</strong>
                {{ $booking->paid_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm') }}
            </p>
        @endif
        <p style="{{ EmailBrand::cardRowStyle() }}">
            <strong style="{{ EmailBrand::strongStyle() }}">Método de pago:</strong>
            {{ $booking->payment_provider === 'stripe' ? 'Stripe' : ($booking->payment_provider === 'mercadopago' ? 'Mercado Pago' : '—') }}
        </p>
        <p style="{{ EmailBrand::mutedStyle() }} margin-top:12px;font-size:12px;">Folio: {{ $booking->public_id }}</p>
    </div>

    @if ($customer && ($customer->fiscal_legal_name || $customer->fiscal_rfc))
        <div class="card" style="{{ EmailBrand::cardStyle() }}">
            <h3 style="{{ EmailBrand::cardTitleStyle() }}">Datos fiscales</h3>
            @if ($customer->fiscal_legal_name)
                <p style="{{ EmailBrand::cardRowStyle() }}">{{ $customer->fiscal_legal_name }}</p>
            @endif
            @if ($customer->fiscal_rfc)
                <p style="{{ EmailBrand::cardRowStyle() }}">RFC: {{ $customer->fiscal_rfc }}</p>
            @endif
        </div>
    @endif

    @include('emails.partials._button', ['url' => $portalUrl, 'label' => 'Ir a mi portal'])

    <p style="{{ EmailBrand::mutedStyle() }}">
        Guarda este correo como comprobante. Si necesitas factura, contáctanos con tu folio.
    </p>
@endsection
