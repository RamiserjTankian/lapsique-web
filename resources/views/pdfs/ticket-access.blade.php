<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pase de acceso</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            font-size: 12px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 8px 0;
        }
        .subtitle {
            color: #6b7280;
            margin: 0 0 12px 0;
        }
        .grid {
            display: table;
            width: 100%;
        }
        .col {
            display: table-cell;
            vertical-align: top;
        }
        .qr {
            text-align: right;
        }
        .label {
            color: #6b7280;
            font-size: 11px;
        }
        .value {
            font-weight: bold;
            margin-bottom: 6px;
        }
        .code {
            font-size: 16px;
            letter-spacing: 2px;
        }
        .test-banner { border: 2px solid #b66a00; background: #fff1cc; color: #7a3f00; padding: 12px; margin-bottom: 16px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    @if($testMode ?? false)
        <div class="test-banner">PASE DE PRUEBA · NO VÁLIDO PARA INGRESO</div>
    @endif
    <div class="card">
        <div class="title">Pase de acceso 🎟️</div>
        <div class="subtitle">{{ $event?->title ?? 'Evento' }}</div>
        <div class="grid">
            <div class="col">
                <div class="label">Nombre</div>
                <div class="value">{{ $attendee->name ?? 'Invitado' }}</div>
                @if($product)
                    <div class="label">Ticket</div>
                    <div class="value">{{ $product->name }}</div>
                @endif
                @if($event?->starts_at)
                    <div class="label">Fecha</div>
                    <div class="value">{{ $event->starts_at->locale('es')->isoFormat('D [de] MMMM YYYY, HH:mm') }}</div>
                @endif
                @if($event?->venue)
                    <div class="label">Lugar</div>
                    <div class="value">{{ $event->venue }}</div>
                @endif
                <div class="label">Código manual</div>
                <div class="value code">{{ $checkInCode }}</div>
            </div>
            <div class="col qr">
                <img src="{{ $checkInQrUrl }}" alt="QR" width="180" height="180">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="label">Link de acceso</div>
        <div class="value">{{ $checkInUrl }}</div>
        <p class="label">{{ ($testMode ?? false) ? 'Documento de prueba. No permite ingreso ni check-in.' : 'Presenta este pase en la entrada. Guarda este PDF en tu telefono.' }}</p>
    </div>
</body>
</html>
