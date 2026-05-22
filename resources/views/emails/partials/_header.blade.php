@php
    use App\Support\EmailBrand;

    $profileImageUrl = $profileImageUrl ?? null;
@endphp
<div class="email-header" style="{{ EmailBrand::headerStyle() }}">
    @if($profileImageUrl)
        <img src="{{ $profileImageUrl }}" width="64" height="64" alt="Perfil" style="display:block;margin:0 auto 12px;border-radius:999px;border:1px solid {{ EmailBrand::BORDER }};" />
    @endif
    <h1 style="{{ EmailBrand::wordmarkStyle() }}">{{ EmailBrand::WORDMARK }}</h1>
</div>
