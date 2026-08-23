<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pase Safe by Varuna</title>
<style>
@page { size: letter portrait; margin: 0; }
* { box-sizing: border-box; }
html, body {
    margin: 0;
    padding: 0;
    font-family: DejaVu Sans, Arial, sans-serif;
    color: #111111;
    background: #f3efe7;
}
.page {
    width: 8.5in;
    height: 11in;
    overflow: hidden;
    background: #f3efe7;
}
.page-break { page-break-after: always; }
.topbar {
    height: 0.68in;
    padding: 0.18in 0.38in;
    background: #0b0b0b;
    color: #ffffff;
}
.topbar table,
.hero,
.access {
    width: 100%;
    border-collapse: collapse;
}
.brand {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
.edition {
    text-align: right;
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #e36a16;
}
.body {
    padding: 0.34in 0.38in 0.3in;
}
.test-banner {
    margin-bottom: 0.18in;
    padding: 0.09in 0.14in;
    border: 1px solid #e36a16;
    color: #9a3f00;
    text-align: center;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.15em;
    text-transform: uppercase;
}
.hero td,
.access td {
    vertical-align: top;
}
.flyer-cell {
    width: 3.42in;
    padding-right: 0.28in;
}
.flyer-frame {
    width: 3.14in;
    height: 3.14in;
    overflow: hidden;
    background: #d8d3ca;
    border: 1px solid rgba(0, 0, 0, 0.12);
}
.flyer-frame img {
    display: block;
    width: 3.14in;
    height: 3.14in;
}
.flyer-empty {
    width: 3.14in;
    height: 3.14in;
    background: #111111;
}
.event-kicker {
    margin-top: 0.03in;
    color: #e36a16;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.event-title {
    margin-top: 0.13in;
    font-size: 27px;
    line-height: 1.04;
    font-weight: 800;
}
.event-line {
    margin-top: 0.13in;
    font-size: 12px;
    line-height: 1.55;
    color: #4b4b4b;
}
.fact {
    margin-top: 0.2in;
    padding-top: 0.13in;
    border-top: 1px solid #cac3b8;
}
.fact-label {
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #777067;
}
.fact-value {
    margin-top: 0.04in;
    font-size: 13px;
    font-weight: 700;
}
.rule {
    margin: 0.29in 0 0.25in;
    height: 3px;
    background: #e36a16;
}
.guest-cell {
    width: 4.65in;
    padding-right: 0.3in;
}
.section-label {
    color: #e36a16;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.guest-name {
    margin-top: 0.09in;
    font-size: 25px;
    line-height: 1.08;
    font-weight: 800;
}
.ticket-name {
    margin-top: 0.08in;
    font-size: 12px;
    color: #525252;
}
.notice {
    margin-top: 0.22in;
    padding: 0.16in 0.18in;
    background: #ffffff;
    font-size: 10px;
    line-height: 1.55;
    color: #404040;
}
.order-summary {
    margin-top: 0.2in;
    font-size: 10px;
    line-height: 1.55;
    color: #4d4d4d;
}
.order-summary strong { color: #111111; }
.qr-cell {
    width: 2.75in;
    text-align: center;
}
.qr-shell {
    padding: 0.16in;
    background: #ffffff;
}
.qr-shell img {
    width: 2.15in;
    height: 2.15in;
}
.scan-label {
    margin-bottom: 0.08in;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
.code-label {
    margin-top: 0.08in;
    font-size: 7px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #777067;
}
.code {
    margin-top: 0.04in;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: 0.22em;
}
.bottom {
    margin-top: 0.28in;
    padding: 0.2in 0.22in;
    background: #0b0b0b;
    color: #ffffff;
}
.bottom-title {
    color: #e36a16;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
.bottom-copy {
    margin-top: 0.07in;
    font-size: 9px;
    line-height: 1.5;
    color: #d7d7d7;
}
</style>
</head>
<body>
@foreach($attendees as $att)
    @php
        $locationText = collect([$event?->venue, $event?->city])->filter()->implode(', ');
        $isTest = $testMode ?? false;
    @endphp
    <div class="page {{ $loop->last ? '' : 'page-break' }}">
        <div class="topbar">
            <table><tr>
                <td class="brand">Lapsique Media</td>
                <td class="edition">Safe by Varuna / 1 edition</td>
            </tr></table>
        </div>

        <div class="body">
            @if($isTest)
                <div class="test-banner">Pase de prueba - No válido para ingreso</div>
            @endif

            <table class="hero"><tr>
                <td class="flyer-cell">
                    <div class="flyer-frame">
                        @if($flyerUrl)
                            <img src="{{ $flyerUrl }}" alt="Flyer Safe by Varuna">
                        @else
                            <div class="flyer-empty"></div>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="event-kicker">KAPI / Minimal house</div>
                    <div class="event-title">{{ $event?->title ?? 'Safe by Varuna 1 edition' }}</div>
                    <div class="event-line">Una noche de cupo limitado en Roma Norte.</div>

                    <div class="fact">
                        <div class="fact-label">Fecha y hora</div>
                        <div class="fact-value">27 agosto 2026 / 22:00 CDMX</div>
                    </div>
                    <div class="fact">
                        <div class="fact-label">Lugar</div>
                        <div class="fact-value">{{ $locationText ?: 'Casa Luma - Tonalá 145' }}</div>
                    </div>
                    <div class="fact">
                        <div class="fact-label">Condiciones</div>
                        <div class="fact-value">18+ / Sin reembolsos</div>
                    </div>
                </td>
            </tr></table>

            <div class="rule"></div>

            <table class="access"><tr>
                <td class="guest-cell">
                    <div class="section-label">Titular del acceso</div>
                    <div class="guest-name">{{ $att['name'] }}</div>
                    <div class="ticket-name">{{ $att['product'] }}</div>

                    @if($att['consumo_note'])
                        <div class="notice">{{ $att['consumo_note'] }}</div>
                    @endif

                    <div class="order-summary">
                        @if($order)
                            <strong>Orden:</strong> {{ $order->uuid ?? $order->id }}<br>
                            <strong>Total pagado:</strong> ${{ number_format((float) $order->total, 2) }} {{ $order->currency }}<br>
                        @endif
                        <strong>Acceso:</strong> 1 persona<br>
                        <strong>Uso:</strong> {{ $isTest ? 'Muestra no canjeable.' : 'Presenta este QR en la entrada.' }}
                    </div>
                </td>
                <td class="qr-cell">
                    <div class="qr-shell">
                        <div class="scan-label">{{ $isTest ? 'QR de prueba' : 'Escanear en puerta' }}</div>
                        <img src="{{ $att['qrUrl'] }}" alt="QR de acceso">
                        <div class="code-label">Código de respaldo</div>
                        <div class="code">{{ $att['code'] }}</div>
                    </div>
                </td>
            </tr></table>

            <div class="bottom">
                <div class="bottom-title">Información importante</div>
                <div class="bottom-copy">
                    {{ $isTest
                        ? 'Este documento es una muestra visual. No acredita pago, no reserva inventario y no permite el ingreso.'
                        : 'El QR es individual y será validado en puerta. No compartas este pase. El evento no admite reembolsos.' }}
                </div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
