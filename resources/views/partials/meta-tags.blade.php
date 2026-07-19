@php
    /** @var \App\Support\PageMetaData $meta */
    $ogImage = $meta->ogImage ?: \App\Support\PageMeta::defaultOgImageUrl();
    $twitterImage = $ogImage;
    $ogImageAlt = $meta->ogImageAlt ?: $meta->metaTitle;
    $isTrascendental = str_contains($meta->metaTitle, 'Trascendental') || str_contains($meta->canonicalUrl, 'trascendentalby');
    $siteName = $isTrascendental ? 'Trascendentalby' : 'Lapsique Media';
    $metaAppId = config('meta.marketing_api.app_id');
@endphp

<title data-inertia="">{{ $meta->metaTitle }}</title>
<meta data-inertia="meta-title" name="title" content="{{ $meta->metaTitle }}">
<meta data-inertia="description" name="description" content="{{ $meta->description }}">
@if (filled($meta->keywords))
    <meta data-inertia="keywords" name="keywords" content="{{ $meta->keywords }}">
@endif
<meta data-inertia="author" name="author" content="{{ $siteName }}">
<meta data-inertia="application-name" name="application-name" content="{{ $siteName }}">
<meta data-inertia="thumbnail" name="thumbnail" content="{{ $ogImage }}">
<meta data-inertia="robots" name="robots" content="{{ $meta->noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">

<link data-inertia="canonical" rel="canonical" href="{{ $meta->canonicalUrl }}">
<link data-inertia="image-src" rel="image_src" href="{{ $ogImage }}">

<meta data-inertia="og-type" property="og:type" content="{{ $meta->ogType }}">
<meta data-inertia="og-url" property="og:url" content="{{ $meta->canonicalUrl }}">
<meta data-inertia="og-title" property="og:title" content="{{ $meta->metaTitle }}">
<meta data-inertia="og-description" property="og:description" content="{{ $meta->description }}">
<meta data-inertia="og-image" property="og:image" content="{{ $ogImage }}">
<meta data-inertia="og-image-url" property="og:image:url" content="{{ $ogImage }}">
<meta data-inertia="og-image-secure-url" property="og:image:secure_url" content="{{ $ogImage }}">
<meta data-inertia="og-image-width" property="og:image:width" content="1200">
<meta data-inertia="og-image-height" property="og:image:height" content="630">
<meta data-inertia="og-image-type" property="og:image:type" content="image/jpeg">
<meta data-inertia="og-image-alt" property="og:image:alt" content="{{ $ogImageAlt }}">
<meta data-inertia="og-site-name" property="og:site_name" content="{{ $siteName }}">
<meta data-inertia="og-locale" property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'es_MX' }}">
<meta data-inertia="og-locale-alternate" property="og:locale:alternate" content="{{ app()->getLocale() === 'en' ? 'es_MX' : 'en_US' }}">
@if (filled($metaAppId))
    <meta property="fb:app_id" content="{{ $metaAppId }}">
@endif

<meta data-inertia="twitter-card" name="twitter:card" content="summary_large_image">
<meta data-inertia="twitter-url" name="twitter:url" content="{{ $meta->canonicalUrl }}">
<meta data-inertia="twitter-title" name="twitter:title" content="{{ $meta->metaTitle }}">
<meta data-inertia="twitter-description" name="twitter:description" content="{{ $meta->description }}">
<meta data-inertia="twitter-image" name="twitter:image" content="{{ $twitterImage }}">
<meta data-inertia="twitter-image-alt" name="twitter:image:alt" content="{{ $ogImageAlt }}">

@if ($meta->jsonLd)
    <script data-inertia="json-ld" type="application/ld+json">{!! json_encode($meta->jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
