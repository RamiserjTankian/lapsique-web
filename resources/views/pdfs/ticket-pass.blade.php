<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pase de acceso — {{ $event?->title ?? 'Evento' }}</title>
<style>
@page { size: letter portrait; margin: 0; }
* { box-sizing: border-box; }
html, body {
    margin: 0;
    padding: 0;
    font-family: DejaVu Sans, Arial, sans-serif;
    color: #102534;
    background: #ffffff;
}
.page {
    position: relative;
    width: 8.5in;
    height: 11in;
    overflow: hidden;
    background: #f8f5ef;
}
.page-break { page-break-after: always; }

.layout {
    width: 100%;
    height: 100%;
}
.aside {
    position: absolute;
    left: 0;
    top: 0;
    width: 2.68in;
    height: 11in;
    background: #072030;
    color: #ffffff;
    overflow: hidden;
}
.main {
    position: absolute;
    left: 2.68in;
    top: 0;
    width: 5.82in;
    height: 11in;
    background: #f8f5ef;
    overflow: hidden;
}

.flyer {
    width: 100%;
    height: 4.7in;
    background: #0c3347;
}
.flyer img {
    display: block;
    width: 100%;
    height: 4.7in;
    object-fit: cover;
}
.flyer-empty {
    width: 100%;
    height: 4.7in;
    background: linear-gradient(160deg, #0c3347, #071e2a);
}

.aside-block {
    padding: 0.24in 0.22in;
    border-top: 1px solid rgba(255,255,255,0.08);
}
.eyebrow {
    font-size: 10px;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.52);
    margin-bottom: 0.14in;
}
.aside-title {
    font-size: 21px;
    line-height: 1.05;
    font-weight: 700;
    margin: 0 0 0.12in;
}
.aside-copy {
    font-size: 11px;
    line-height: 1.6;
    color: rgba(255,255,255,0.72);
}
.venue-card {
    margin-top: 0.2in;
    padding: 0.16in;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
}
.venue-photo {
    width: 100%;
    height: 1.28in;
    display: block;
    margin-bottom: 0.14in;
    object-fit: cover;
    border-radius: 10px;
}

.lineup-item {
    display: table;
    width: 100%;
    margin-bottom: 0.1in;
}
.lineup-photo,
.lineup-info,
.lineup-role {
    display: table-cell;
    vertical-align: middle;
}
.lineup-photo {
    width: 0.56in;
    padding-right: 0.08in;
}
.lineup-info {
    width: auto;
}
.lineup-role {
    width: 0.84in;
    text-align: right;
}
.artist-photo {
    width: 0.42in;
    height: 0.42in;
    border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,0.7);
    object-fit: cover;
}
.artist-ph {
    width: 0.42in;
    height: 0.42in;
    border-radius: 50%;
    background: #c8dcea;
    color: #18425b;
    text-align: center;
    line-height: 0.42in;
    font-weight: 700;
    font-size: 12px;
}
.b2b {
    position: relative;
    width: 0.54in;
    height: 0.42in;
}
.b2b img,
.b2b .artist-ph {
    position: absolute;
    top: 0;
}
.b2b .front { left: 0; z-index: 2; }
.b2b .back { left: 0.16in; z-index: 1; }
.artist-name {
    font-size: 12px;
    font-weight: 700;
    line-height: 1.25;
    color: #ffffff;
}
.artist-time {
    font-size: 10px;
    color: rgba(255,255,255,0.62);
}
.role-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    background: #e9f6fb;
    color: #176688;
    border: 1px solid #8fc6df;
}
.role-pill.hl {
    background: #fff6d9;
    color: #7a5a10;
    border-color: #d0ad54;
}
.b2b-pill {
    display: inline-block;
    margin-left: 6px;
    padding: 2px 6px;
    border-radius: 999px;
    font-size: 7px;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    background: #1b82a4;
    color: #ffffff;
}

.main-wrap {
    padding: 0.18in 0.2in 0.12in;
}
.main-top {
    display: table;
    width: 100%;
    margin-bottom: 0.08in;
}
.main-title {
    display: table-cell;
    vertical-align: top;
    padding-right: 0.12in;
}
.main-counter {
    display: table-cell;
    width: 0.92in;
    text-align: right;
    vertical-align: top;
}
.pass-label {
    font-size: 11px;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: #6a8799;
    margin-bottom: 0.06in;
}
.event-title {
    font-size: 28px;
    line-height: 0.98;
    font-weight: 800;
    color: #072030;
    margin-bottom: 0.04in;
}
.event-meta {
    font-size: 12.5px;
    line-height: 1.32;
    color: #4a6678;
}
.counter-box {
    border: 1px solid #d8cbb3;
    border-radius: 18px;
    padding: 0.1in 0.08in;
    background: #ffffff;
}
.counter-box .num {
    font-size: 28px;
    line-height: 1;
    font-weight: 800;
    color: #072030;
}
.counter-box .meta {
    font-size: 8px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #6a8799;
    line-height: 1.35;
}

.guest-card {
    background: #ffffff;
    border: 1px solid #d8cbb3;
    border-radius: 18px;
    padding: 0.14in 0.18in;
    margin-bottom: 0.07in;
}
.guest-name {
    font-size: 22px;
    line-height: 1.05;
    font-weight: 800;
    color: #102534;
    margin-bottom: 0.06in;
}
.guest-type {
    font-size: 13px;
    color: #4a6678;
    margin-bottom: 0.06in;
}
.pending-pill {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #8e5b12;
    background: #fff1cc;
    border: 1px solid #e8c36c;
    margin-bottom: 0.12in;
}
.consumo-box {
    background: #eef7fb;
    border: 1px solid #a7d3e5;
    border-radius: 14px;
    padding: 0.1in 0.14in;
    font-size: 12px;
    line-height: 1.35;
    color: #155d79;
}

.qr-shell {
    width: 100%;
    background: #ffffff;
    border: 1px solid #d8cbb3;
    border-radius: 22px;
    padding: 0.1in 0.1in 0.08in;
    text-align: center;
    margin-bottom: 0.07in;
    height: 3.78in;
    overflow: hidden;
}
.scan-label {
    font-size: 11px;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: #718a99;
    margin-bottom: 0.06in;
}
.qr-shell img {
    display: block;
    margin: 0 auto;
    width: 2.28in;
    height: 2.28in;
}
.code-label {
    margin-top: 0.03in;
    font-size: 10px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: #718a99;
}
.code-value {
    margin-top: 0.03in;
    font-size: 23px;
    letter-spacing: 0.08in;
    font-weight: 800;
    color: #072030;
    font-family: DejaVu Sans Mono, monospace;
}

.info-grid {
    display: table;
    width: 100%;
    table-layout: fixed;
}
.info-col,
.buy-box,
.info-box {
    display: table-cell;
    vertical-align: top;
}
.info-col { width: 50%; padding-right: 0.06in; }
.buy-box { padding-right: 0.08in; }
.info-box { padding-left: 0.08in; }
.panel {
    border-radius: 20px;
    border: 1px solid #d8cbb3;
    background: #ffffff;
    padding: 0.14in 0.14in;
    height: 1.5in;
    overflow: hidden;
}
.panel-title {
    font-size: 11px;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: #6a8799;
    margin-bottom: 0.06in;
}
.row {
    display: table;
    width: 100%;
    margin-bottom: 0.05in;
}
.row:last-child { margin-bottom: 0; }
.row-label,
.row-value {
    display: table-cell;
    vertical-align: top;
    font-size: 13px;
    color: #415e70;
}
.row-value {
    text-align: right;
    font-weight: 700;
    color: #102534;
}
.total-row {
    margin-top: 0.05in;
    padding-top: 0.06in;
    border-top: 1px solid #eadcc5;
}
.total-row .row-label,
.total-row .row-value {
    font-size: 16px;
    font-weight: 800;
    color: #072030;
}
.info-copy {
    font-size: 11px;
    line-height: 1.52;
    color: #415e70;
}
.info-copy strong {
    color: #072030;
}
.deep-panel {
    background: #072030;
    color: #ffffff;
    border-color: #072030;
}
.deep-panel .panel-title {
    color: rgba(255,255,255,0.58);
}
.deep-copy {
    font-size: 11px;
    line-height: 1.5;
    color: rgba(255,255,255,0.8);
}
.deep-copy strong {
    color: #ffffff;
}

</style>
</head>
<body>

@php $totalAccesos = count($attendees); @endphp

@foreach($attendees as $att)
    @php
        $headline = $event?->headline ?: ($event?->description ? \Illuminate\Support\Str::limit(strip_tags($event->description), 210) : null);
        $descText = $event?->description ? \Illuminate\Support\Str::limit(strip_tags($event->description), 280) : null;
        $locationText = collect([$event?->venue, $event?->city])->filter()->implode(', ');
    @endphp
    <div class="page">
        <div class="layout">
            <div class="aside">
                <div class="flyer">
                    @if($flyerUrl)
                        <img src="{{ $flyerUrl }}" alt="{{ $event?->title }}">
                    @else
                        <div class="flyer-empty"></div>
                    @endif
                </div>

                <div class="aside-block">
                    <div class="eyebrow">Evento</div>
                    <div class="aside-title">{{ $event?->title ?? 'Evento' }}</div>
                    <div class="aside-copy">
                        @if($event?->starts_at)
                            {{ $event->starts_at->locale('es')->isoFormat('dddd D [de] MMMM YYYY') }}<br>
                            Apertura {{ $event->starts_at->format('H:i') }} hrs
                        @endif
                    </div>

                    @if($venueUrl)
                        <div class="venue-card">
                            <img src="{{ $venueUrl }}" alt="Venue" class="venue-photo">
                            <div class="aside-copy">
                                {{ $locationText ?: ($event?->venue ?: 'Trascendental') }}
                            </div>
                        </div>
                    @endif
                </div>

                @if($lineup->isNotEmpty())
                    <div class="aside-block">
                        <div class="eyebrow">Lineup</div>
                        @foreach($lineup as $entry)
                            <div class="lineup-item">
                                <div class="lineup-photo">
                                    @if($entry['is_b2b'])
                                        <div class="b2b">
                                            @php $bp = $entry['photos']; @endphp
                                            @if($bp[1]['url'] ?? null)
                                                <img class="artist-photo back" src="{{ $bp[1]['url'] }}" alt="">
                                            @else
                                                <div class="artist-ph back">{{ mb_substr($bp[1]['name'] ?? 'B', 0, 1) }}</div>
                                            @endif
                                            @if($bp[0]['url'] ?? null)
                                                <img class="artist-photo front" src="{{ $bp[0]['url'] }}" alt="">
                                            @else
                                                <div class="artist-ph front">{{ mb_substr($bp[0]['name'] ?? 'A', 0, 1) }}</div>
                                            @endif
                                        </div>
                                    @elseif($entry['photos'][0]['url'] ?? null)
                                        <img class="artist-photo" src="{{ $entry['photos'][0]['url'] }}" alt="{{ $entry['name'] }}">
                                    @else
                                        <div class="artist-ph">{{ mb_substr($entry['name'], 0, 1) }}</div>
                                    @endif
                                </div>
                                <div class="lineup-info">
                                    <div class="artist-name">
                                        {{ $entry['name'] }}
                                        @if($entry['is_b2b'])<span class="b2b-pill">B2B</span>@endif
                                    </div>
                                    @if($entry['time_slot'])
                                        <div class="artist-time">{{ $entry['time_slot'] }}</div>
                                    @endif
                                </div>
                                <div class="lineup-role">
                                    <span class="role-pill {{ $entry['role_label'] === 'Headliner' ? 'hl' : '' }}">{{ $entry['role_label'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="aside-block">
                    <div class="eyebrow">Acceso digital</div>
                    <div class="aside-copy">
                        {{ $digitalAccessCopy ?? 'Este QR funciona para acceso, relecturas y consumos permitidos del evento. Guardalo en tu telefono.' }}
                    </div>
                </div>
            </div>

            <div class="main">
                <div class="main-wrap">
                    <div class="main-top">
                        <div class="main-title">
                            <div class="pass-label">Pase de acceso · Trascendental</div>
                            <div class="event-title">{{ $event?->title ?? 'Evento' }}</div>
                            <div class="event-meta">
                                @if($event?->starts_at)
                                    {{ $event->starts_at->locale('es')->isoFormat('dddd D [de] MMMM YYYY · HH:mm') }}<br>
                                @endif
                                {{ $locationText ?: trim(implode(', ', array_filter([$event?->venue, $event?->city, 'México']))) }}
                            </div>
                        </div>
                        <div class="main-counter">
                            <div class="counter-box">
                                <div class="num">{{ $att['index'] }}</div>
                                <div class="meta">de {{ $totalAccesos }}<br>accesos</div>
                            </div>
                        </div>
                    </div>

                    <div class="guest-card">
                        @if($att['is_unassigned'] ?? false)
                            <div class="pending-pill">Pendiente de asignar</div>
                        @endif
                        <div class="guest-name">{{ $att['name'] }}</div>
                        <div class="guest-type">{{ $att['product'] }}</div>
                        @if($att['consumo_note'])
                            <div class="consumo-box">{{ $att['consumo_note'] }}</div>
                        @endif
                    </div>

                    <div class="qr-shell">
                        <div class="scan-label">Escanear en entrada</div>
                        <img src="{{ $att['qrUrl'] }}" alt="QR de acceso">
                        <div class="code-label">Código de respaldo</div>
                        <div class="code-value">{{ $att['code'] }}</div>
                    </div>

                    <div class="info-grid">
                        <div class="info-col">
                            <div class="panel">
                                <div class="panel-title">Información clave</div>
                                <div class="info-copy">
                                    <strong>Acceso:</strong> {{ $att['product'] }}<br>
                                    <strong>Venue:</strong> {{ $locationText ?: trim(implode(', ', array_filter([$event?->venue, $event?->city, 'México']))) }}<br>
                                    <strong>Uso del QR:</strong> {{ $usageCopy ?? 'Presenta este pase en la entrada y conservalo para accesos, relecturas y consumos permitidos.' }}
                                </div>
                            </div>
                        </div>
                        <div class="info-col">
                            <div class="panel deep-panel">
                                <div class="panel-title">Experiencia</div>
                                <div class="deep-copy">
                                    {!! nl2br(e(\Illuminate\Support\Str::limit($headline ?: $descText ?: 'Una experiencia de música electrónica, visuales y comunidad curada por Trascendental.', 170))) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-grid" style="margin-top: 0.08in;">
                        <div class="buy-box">
                            <div class="panel">
                                <div class="panel-title">Resumen de compra</div>
                                @if($order)
                                    @foreach($order->items as $item)
                                        <div class="row">
                                            <div class="row-label">{{ $item->quantity }} × {{ $item->name }}</div>
                                            <div class="row-value">${{ number_format((float)($item->total_price ?? $item->unit_price * $item->quantity), 0) }} {{ $order->currency }}</div>
                                        </div>
                                    @endforeach
                                    @if((float) $order->fee > 0)
                                        <div class="row">
                                            <div class="row-label">Cargo por servicio</div>
                                            <div class="row-value">${{ number_format((float)$order->fee, 0) }} {{ $order->currency }}</div>
                                        </div>
                                    @endif
                                    <div class="row total-row">
                                        <div class="row-label">Total pagado</div>
                                        <div class="row-value">${{ number_format((float)$order->total, 0) }} {{ $order->currency }}</div>
                                    </div>
                                @else
                                    <div class="info-copy">{{ $summaryCopy ?? 'Pase individual emitido para acceso y control de consumo en el evento.' }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="info-box">
                            <div class="panel">
                                <div class="panel-title">Indicaciones</div>
                                <div class="info-copy">
                                    <strong>Llega con tu PDF o QR listo.</strong><br>
                                    Este acceso es personal y se valida contra el código mostrado en esta hoja.<br><br>
                                    {!! nl2br(e(\Illuminate\Support\Str::limit($descText ?: 'Una experiencia de música electrónica, visuales y comunidad curada por Trascendental.', 150))) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (! $loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
