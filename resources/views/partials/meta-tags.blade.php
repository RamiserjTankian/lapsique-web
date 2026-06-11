@php
    /** @var \App\Support\PageMetaData $meta */
    $ogImage = $meta->ogImage ?: \App\Support\PageMeta::defaultOgImageUrl();
    $twitterImage = $ogImage;
    $ogImageAlt = $meta->ogImageAlt ?: $meta->metaTitle;
    $isTrascendental = str_contains($meta->metaTitle, 'Trascendental') || str_contains($meta->canonicalUrl, 'trascendentalby');
    $siteName = $isTrascendental ? 'Trascendentalby' : 'Lapsique Media';
    $metaAppId = config('meta.marketing_api.app_id');
@endphp

<title>{{ $meta->metaTitle }}</title>
<meta name="title" content="{{ $meta->metaTitle }}">
<meta name="description" content="{{ $meta->description }}">
@if (filled($meta->keywords))
    <meta name="keywords" content="{{ $meta->keywords }}">
@endif
<meta name="author" content="{{ $siteName }}">
<meta name="application-name" content="{{ $siteName }}">
<meta name="thumbnail" content="{{ $ogImage }}">
<meta name="robots" content="{{ $meta->noindex ? 'noindex, nofollow' : 'index, follow' }}">

<link rel="canonical" href="{{ $meta->canonicalUrl }}">
<link rel="image_src" href="{{ $ogImage }}">

<meta property="og:type" content="{{ $meta->ogType }}">
<meta property="og:url" content="{{ $meta->canonicalUrl }}">
<meta property="og:title" content="{{ $meta->metaTitle }}">
<meta property="og:description" content="{{ $meta->description }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:url" content="{{ $ogImage }}">
<meta property="og:image:secure_url" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:alt" content="{{ $ogImageAlt }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="es_MX">
@if (filled($metaAppId))
    <meta property="fb:app_id" content="{{ $metaAppId }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $meta->canonicalUrl }}">
<meta name="twitter:title" content="{{ $meta->metaTitle }}">
<meta name="twitter:description" content="{{ $meta->description }}">
<meta name="twitter:image" content="{{ $twitterImage }}">
<meta name="twitter:image:alt" content="{{ $ogImageAlt }}">

@if ($meta->jsonLd)
    <script type="application/ld+json">{!! json_encode($meta->jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
