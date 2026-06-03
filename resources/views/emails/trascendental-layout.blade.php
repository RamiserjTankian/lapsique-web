<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('title', 'Trascendental')</title>
    <style>
        body { margin: 0; padding: 0; background: #f5f5f2; color: #050505; font-family: Arial, Helvetica, sans-serif; line-height: 1.55; }
        .tdl-wrapper { max-width: 640px; margin: 0 auto; background: #f5f5f2; color: #050505; }
        .tdl-header { padding: 30px 28px 22px; border-bottom: 1px solid #d8d8d2; }
        .tdl-wordmark { margin: 0; color: #050505; font-size: 20px; font-weight: 900; letter-spacing: 0; text-transform: uppercase; }
        .tdl-body { padding: 32px 28px; }
        .tdl-eyebrow { margin: 0 0 14px; color: #707070; font-size: 11px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; }
        .tdl-heading { margin: 0 0 18px; color: #050505; font-size: 34px; font-weight: 900; line-height: 0.98; text-transform: uppercase; }
        .tdl-copy { margin: 0 0 18px; color: #323232; font-size: 16px; }
        .tdl-card { margin: 24px 0; padding: 18px 0 0; border-top: 1px solid #050505; }
        .tdl-row { margin: 0; padding: 11px 0; border-bottom: 1px solid #d8d8d2; color: #323232; font-size: 15px; }
        .tdl-row strong { color: #050505; font-weight: 900; text-transform: uppercase; }
        .tdl-footer { padding: 22px 28px 30px; border-top: 1px solid #d8d8d2; color: #707070; font-size: 12px; }
        .tdl-footer a { color: #050505; text-decoration: none; font-weight: 800; text-transform: uppercase; }
        @media only screen and (max-width: 600px) {
            .tdl-header, .tdl-body, .tdl-footer { padding-left: 20px !important; padding-right: 20px !important; }
            .tdl-heading { font-size: 28px !important; }
        }
    </style>
</head>
<body>
    <div class="tdl-wrapper">
        <div class="tdl-header">
            <h1 class="tdl-wordmark">TRASCENDENTAL.</h1>
        </div>

        <div class="tdl-body">
            @yield('content')
        </div>

        <div class="tdl-footer">
            <p style="margin: 0 0 12px;">Artists / Events / Culture</p>
            <p style="margin: 0 0 12px;">
                <a href="https://www.instagram.com/trascendentalby/">Instagram</a>
                &nbsp;|&nbsp;
                <a href="https://chat.whatsapp.com/CNrxBxxfpUM7rKkYp3xIqA?s=sw&p=i&mlu=1">Comunidad</a>
            </p>
            <p style="margin: 0;">
                Este correo fue enviado por Trascendental.<br>
                © {{ date('Y') }} TRASCENDENTAL. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
