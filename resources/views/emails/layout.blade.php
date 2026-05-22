@php
    use App\Support\EmailBrand;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    @include('emails.partials._head')
</head>
<body style="{{ EmailBrand::bodyStyle() }}">
    <div class="email-wrapper" style="{{ EmailBrand::wrapperStyle() }}">
        @include('emails.partials._header')

        <div class="email-body" style="{{ EmailBrand::bodyPaddingStyle() }}">
            @yield('content')
        </div>

        @include('emails.partials._footer')
    </div>

    @if(isset($trackingPixelUrl) && filled($trackingPixelUrl))
        <img src="{{ $trackingPixelUrl }}" width="1" height="1" style="display:none;" alt="">
    @endif
</body>
</html>
