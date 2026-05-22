@php
    use App\Support\EmailBrand;
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>@yield('title', EmailBrand::WORDMARK)</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="{{ EmailBrand::fontFamiliesUrl() }}" rel="stylesheet">
<style>
    body {
        font-family: {{ EmailBrand::FONT_SANS }};
        line-height: 1.6;
        color: {{ EmailBrand::FOREGROUND }};
        background-color: {{ EmailBrand::BACKGROUND }};
        margin: 0;
        padding: 0;
    }
    .email-wrapper {
        max-width: 600px;
        margin: 0 auto;
        background-color: {{ EmailBrand::BACKGROUND }};
    }
    .email-header {
        background-color: {{ EmailBrand::CARD }};
        padding: 28px 30px 22px;
        text-align: center;
        border-bottom: 1px solid {{ EmailBrand::BORDER }};
    }
    .email-body {
        padding: 30px;
    }
    .email-footer {
        background-color: {{ EmailBrand::CARD }};
        padding: 22px 20px;
        text-align: center;
        font-size: 12px;
        color: {{ EmailBrand::MUTED }};
        border-top: 1px solid {{ EmailBrand::BORDER }};
    }
    .button {
        display: inline-block;
        padding: 13px 28px;
        background: {{ EmailBrand::PRIMARY }};
        color: {{ EmailBrand::PRIMARY_FOREGROUND }} !important;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        margin: 20px 0;
        font-size: 13px;
        letter-spacing: 0.06em;
        box-shadow: 0 4px 20px rgba(212, 168, 74, 0.35);
    }
    .card {
        background-color: {{ EmailBrand::CARD }};
        padding: 20px;
        border-radius: 12px;
        margin: 20px 0;
        border: 1px solid {{ EmailBrand::BORDER }};
    }
    .eyebrow {
        margin: 0 0 16px;
        color: {{ EmailBrand::ACCENT }};
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.24em;
        text-transform: uppercase;
    }
    .social-links a {
        color: {{ EmailBrand::FOREGROUND }};
        text-decoration: none;
        margin: 0 10px;
        font-weight: 500;
    }
    @media only screen and (max-width: 600px) {
        .email-body {
            padding: 20px !important;
        }
    }
</style>
