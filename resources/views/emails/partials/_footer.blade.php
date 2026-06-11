@php
    use App\Support\EmailBrand;
@endphp
<div class="email-footer" style="{{ EmailBrand::footerStyle() }}">
    <div class="social-links" style="margin:16px 0;">
        <a href="{{ EmailBrand::INSTAGRAM_URL }}" style="color:{{ EmailBrand::FOREGROUND }};text-decoration:none;margin:0 10px;font-weight:500;">Instagram</a>
        |
        <a href="{{ EmailBrand::WEBSITE_URL }}" style="color:{{ EmailBrand::FOREGROUND }};text-decoration:none;margin:0 10px;font-weight:500;">Web</a>
    </div>

    <p style="color:{{ EmailBrand::MUTED }};margin:0 0 12px;font-size:13px;">
        {{ EmailBrand::TAGLINE }}<br>
        @if(isset($unsubscribeUrl) && $unsubscribeUrl !== '#')
            <a href="{{ $unsubscribeUrl }}" style="color:{{ EmailBrand::ACCENT }};text-decoration:underline;">Cancelar suscripción</a>
        @endif
    </p>

    <p style="font-size:11px;color:{{ EmailBrand::MUTED }};margin:0;">
        Este correo fue enviado a {{ $recipientEmail ?? (isset($customer) ? $customer->email : '') }}<br>
        © {{ date('Y') }} {{ EmailBrand::WORDMARK }}. Todos los derechos reservados.
    </p>
</div>
