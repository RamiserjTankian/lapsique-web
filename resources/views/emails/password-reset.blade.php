@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', 'Restablecer contraseña')

@section('content')
    <p class="eyebrow" style="{{ EmailBrand::eyebrowStyle() }}">Portal de cliente</p>
    <h2 style="{{ EmailBrand::headingStyle() }}">Restablecer contraseña</h2>

    <p style="{{ EmailBrand::paragraphStyle() }}">Hola {{ $customer->name ?? '' }},</p>

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Recibimos una solicitud para restablecer la contraseña de tu portal en {{ EmailBrand::WORDMARK }}.
    </p>

    @include('emails.partials._button', ['url' => $resetUrl, 'label' => 'Restablecer contraseña'])

    <p style="{{ EmailBrand::paragraphStyle() }}">
        Este enlace expira en 60 minutos. Si no solicitaste este cambio, ignora este correo.
    </p>

    <p style="{{ EmailBrand::mutedStyle() }}">
        Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
        <a href="{{ $resetUrl }}" style="{{ EmailBrand::linkStyle() }} word-break:break-all;">{{ $resetUrl }}</a>
    </p>
@endsection
