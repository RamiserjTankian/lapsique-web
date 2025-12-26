<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lapsique')</title>
    <style>
        body {
            font-family: 'Space Grotesk', 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #f6f6f6;
            background-color: #050505;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #050505;
        }
        .email-header {
            background-color: #050505;
            padding: 28px 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            letter-spacing: 0.18em;
        }
        .email-body {
            padding: 30px;
        }
        .email-footer {
            background-color: #0b0b0b;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #bdbdbd;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #ffffff;
            color: #050505 !important;
            text-decoration: none;
            border-radius: 999px;
            font-weight: 600;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 12px;
        }
        .button:hover {
            opacity: 0.9;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            color: #ffffff;
            text-decoration: none;
            margin: 0 10px;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #050505; color: #f6f6f6; font-family: 'Space Grotesk', 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6;">
    <div class="email-wrapper" style="max-width: 600px; margin: 0 auto; background-color: #050505; color: #f6f6f6;">
        <div class="email-header" style="background-color: #050505; padding: 28px 30px 20px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.12);">
            <h1 style="color: #ffffff; margin: 0; font-size: 28px; letter-spacing: 0.18em;">🎬 LAPSIQUE.MEDIA</h1>
        </div>
        
        <div class="email-body" style="padding: 30px; color: #f6f6f6;">
            @yield('content')
        </div>
        
        <div class="email-footer" style="background-color: #0b0b0b; padding: 20px; text-align: center; font-size: 12px; color: #bdbdbd; border-top: 1px solid rgba(255, 255, 255, 0.12);">
            <div class="social-links" style="margin: 20px 0;">
                <a href="https://instagram.com/lapsique.media" style="color: #ffffff; text-decoration: none; margin: 0 10px;">Instagram</a> |
                <a href="https://www.youtube.com/@LAPSIQUEMEDIA" style="color: #ffffff; text-decoration: none; margin: 0 10px;">YouTube</a>
            </div>
            
            <p style="color: #bdbdbd; margin: 0 0 12px;">
                Lapsique - Techno & Electronic Music<br>
                <a href="{{ $unsubscribeUrl ?? '#' }}" style="color: #bdbdbd;">Cancelar suscripción</a>
            </p>
            
            <p style="font-size: 11px; color: #8f8f8f; margin: 0;">
                Este email fue enviado a {{ $customer->email }}<br>
                © {{ date('Y') }} Lapsique. Todos los derechos reservados.
            </p>
            <div style="text-align: right; font-size: 18px; margin-top: 8px;">🇲🇽</div>
        </div>
    </div>
    
    {{-- Tracking Pixel --}}
    @if(isset($trackingPixelUrl))
        <img src="{{ $trackingPixelUrl }}" width="1" height="1" style="display:none;" alt="">
    @endif
</body>
</html>
