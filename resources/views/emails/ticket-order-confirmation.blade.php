@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Confirmación de compra')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Compra confirmada</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">¡Pago registrado!</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $order->buyer_name ?? 'amig@' }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">Tu pago se registró correctamente para <strong style="{{ EmailBrand::strongStyle() }}">{{ $event?->title }}</strong>.</p>

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Resumen de compra</h3>
        @foreach ($items as $item)
            <p style="{{ EmailBrand::cardRowStyle() }}">
                <strong style="{{ EmailBrand::strongStyle() }}">{{ $item->name }}</strong>
                — {{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} {{ $order->currency }}
            </p>
        @endforeach
        <p style="{{ EmailBrand::cardRowStyle() }} margin-top:12px;font-weight:600;">
            Total: {{ number_format($order->total, 2) }} {{ $order->currency }}
        </p>
    </div>

    @if($order->status === 'paid')
        <div class="card" style="{{ EmailBrand::cardStyle() }}">
            <h3 style="{{ EmailBrand::cardTitleStyle() }}">Tus accesos en PDF</h3>
            <p style="{{ EmailBrand::paragraphStyle() }}">
                Los accesos se encuentran dentro del PDF adjunto y cada uno incluye un QR individual.
            </p>
            <p style="{{ EmailBrand::mutedStyle() }}">
                Guarda el PDF adjunto: ahí encontrarás los QR individuales de la mesa y podrás compartirlos con tus invitados.
            </p>
        </div>
    @endif

    <div class="card" style="{{ EmailBrand::cardStyle() }}">
        <h3 style="{{ EmailBrand::cardTitleStyle() }}">Registra a cada asistente</h3>
        <p style="{{ EmailBrand::mutedStyle() }} margin-bottom:18px;">
            Para enviar los accesos con QR por correo necesitamos el nombre, correo, WhatsApp e Instagram de cada persona.
        </p>
        @include('emails.partials._button', ['url' => $orderUrl, 'label' => 'Completar registros', 'align' => 'left'])
    </div>

    @if($event && $event->starts_at)
        <p style="{{ EmailBrand::paragraphStyle() }}">
            Fecha del evento: <strong style="{{ EmailBrand::strongStyle() }}">{{ $event->starts_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</strong>
        </p>
    @endif

    <p style="{{ EmailBrand::paragraphStyle() }}">Gracias por ser parte de la experiencia {{ EmailBrand::WORDMARK }}.</p>
    <p style="{{ EmailBrand::mutedStyle() }}">El equipo de {{ EmailBrand::WORDMARK }}</p>
@endsection
