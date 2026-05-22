@php
    use App\Support\EmailBrand;

    $url = $url ?? '#';
    $label = $label ?? 'Continuar';
    $align = $align ?? 'center';
@endphp
<div style="text-align:{{ $align }};margin:28px 0;">
    <a href="{{ $url }}" class="button" style="{{ EmailBrand::buttonStyle() }}">{{ $label }}</a>
</div>
