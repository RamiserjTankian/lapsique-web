@extends('emails.layout')

@section('title', 'Recibo de compra')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0; letter-spacing: 0.02em;">Recibo de pago</h2>

    <p style="color: #e5e7eb;">Hola {{ $booking->client_name }},</p>

    <p style="color: #e5e7eb;">
        Este es el comprobante de tu pago por la <strong style="color: #ffffff;">sesión de contenido profesional</strong> en Lapsique.
    </p>

    <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
        <h3 style="margin-top: 0; color: #ffffff;">Detalle del recibo</h3>
        <p style="color: #e5e7eb; margin: 6px 0;">
            <strong style="color: #ffffff;">Concepto:</strong> Sesión de contenido (2 reels + 20 fotografías)
        </p>
        @if ($slot)
            <p style="color: #e5e7eb; margin: 6px 0;">
                <strong style="color: #ffffff;">Fecha programada:</strong>
                {{ $slot->date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }} · {{ $slot->time_label }}
            </p>
        @endif
        <p style="color: #e5e7eb; margin: 6px 0;">
            <strong style="color: #ffffff;">Monto pagado:</strong>
            ${{ number_format($booking->amount, 0) }} {{ strtoupper($booking->currency) }}
        </p>
        @if ($booking->paid_at)
            <p style="color: #e5e7eb; margin: 6px 0;">
                <strong style="color: #ffffff;">Fecha de pago:</strong>
                {{ $booking->paid_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm') }}
            </p>
        @endif
        <p style="color: #e5e7eb; margin: 6px 0;">
            <strong style="color: #ffffff;">Método de pago:</strong>
            {{ $booking->payment_provider === 'stripe' ? 'Stripe' : ($booking->payment_provider === 'mercadopago' ? 'Mercado Pago' : '—') }}
        </p>
        <p style="color: #9ca3af; font-size: 12px; margin-top: 12px;">
            Folio: {{ $booking->public_id }}
        </p>
    </div>

    @if ($customer && ($customer->fiscal_legal_name || $customer->fiscal_rfc))
        <div style="background-color: #0b0b0b; padding: 20px; border-radius: 12px; margin: 20px 0; border: 1px solid rgba(255, 255, 255, 0.12);">
            <h3 style="margin-top: 0; color: #ffffff;">Datos fiscales</h3>
            @if ($customer->fiscal_legal_name)
                <p style="color: #e5e7eb; margin: 6px 0;">{{ $customer->fiscal_legal_name }}</p>
            @endif
            @if ($customer->fiscal_rfc)
                <p style="color: #e5e7eb; margin: 6px 0;">RFC: {{ $customer->fiscal_rfc }}</p>
            @endif
        </div>
    @endif

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $portalUrl }}" class="button">Ir a mi portal</a>
    </div>

    <p style="color: #bdbdbd; font-size: 14px;">
        Guarda este correo como comprobante. Si necesitas factura, contáctanos con tu folio.
    </p>
@endsection
