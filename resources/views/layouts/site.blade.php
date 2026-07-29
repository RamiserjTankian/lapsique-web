<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $defaultMetaImage = url('/images/og-default.jpg');
        $pageTitle = trim($__env->yieldContent('title', __('messages.site.brand')));
        $noindex = request()->routeIs('tickets.*', 'guestlist.*', 'customers.*', 'customer.unsubscribe');
        $metaTitle = trim($__env->yieldContent('meta_title', $pageTitle));
        $metaDescription = trim($__env->yieldContent('meta_description', __('messages.site.brand_tagline')));
        $metaKeywords = trim($__env->yieldContent('meta_keywords', 'música electrónica, DJ sets, techno, house, Riviera Maya, Playa del Carmen, Tulum, eventos electrónicos, sets en vivo'));
        $canonicalUrl = trim($__env->yieldContent('canonical_url', url()->current()));
        $ogType = trim($__env->yieldContent('og_type', 'website'));
        $ogUrl = trim($__env->yieldContent('og_url', $canonicalUrl));
        $ogTitle = trim($__env->yieldContent('og_title', $metaTitle));
        $ogDescription = trim($__env->yieldContent('og_description', $metaDescription));
        $ogImage = trim($__env->yieldContent('og_image', $defaultMetaImage));
        $twitterTitle = trim($__env->yieldContent('twitter_title', $ogTitle));
        $twitterDescription = trim($__env->yieldContent('twitter_description', $ogDescription));
        $twitterImage = trim($__env->yieldContent('twitter_image', $ogImage));
        $ogImageAlt = trim($__env->yieldContent('og_image_alt', $metaTitle));

        if (! str_starts_with($ogImage, 'http://') && ! str_starts_with($ogImage, 'https://')) {
            $ogImage = url($ogImage);
        }

        if (! str_starts_with($twitterImage, 'http://') && ! str_starts_with($twitterImage, 'https://')) {
            $twitterImage = url($twitterImage);
        }
    @endphp
    
    {{-- Primary Meta Tags --}}
    <title>{{ $pageTitle }}</title>
    <meta name="title" content="{{ $metaTitle }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    
    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $canonicalUrl }}">
    
    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $ogUrl }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:url" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">
    <meta property="og:site_name" content="{{ __('messages.site.brand') }}">
    <meta property="og:locale" content="es_MX">
    
    {{-- Additional Open Graph Meta Tags (for events, articles, etc.) --}}
    @stack('og_meta')
    
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ trim($__env->yieldContent('twitter_url', $canonicalUrl)) }}">
    <meta name="twitter:title" content="{{ $twitterTitle }}">
    <meta name="twitter:description" content="{{ $twitterDescription }}">
    <meta name="twitter:image" content="{{ $twitterImage }}">
    <meta name="twitter:image:alt" content="{{ $ogImageAlt }}">
    
    {{-- Additional Meta Tags --}}
    <meta name="author" content="{{ __('messages.site.brand') }}">
    <meta name="robots" content="{{ $noindex ? 'noindex, nofollow, noarchive' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
    <meta name="theme-color" content="#000000">
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @php
        $routeName = request()->route()?->getName() ?? 'unknown';
        $pageType = match (true) {
            request()->routeIs('home') => 'home',
            request()->routeIs('events.show') => 'event',
            request()->routeIs('customers.login') => 'customer_login',
            request()->routeIs('customers.portal') => 'customer_portal',
            request()->routeIs('guestlist.*') => 'guest_list',
            request()->routeIs('tickets.success') => 'purchase_success',
            request()->routeIs('tickets.pending') => 'purchase_pending',
            request()->routeIs('tickets.failure') => 'purchase_failure',
            request()->routeIs('tickets.*') => 'ticketing',
            request()->routeIs('booking.show') => 'content_booking',
            request()->routeIs('booking.confirm') => 'booking_success',
            request()->routeIs('booking.pending') => 'booking_pending',
            request()->routeIs('booking.failure') => 'booking_failure',
            default => 'site',
        };
        $analyticsConfig = [
            'enabled' => config('analytics.enabled'),
            'endpoint' => route('analytics.collect'),
            'sampleRate' => config('analytics.sample_rate'),
            'sessionTimeout' => config('analytics.session_timeout'),
            'trackClicks' => config('analytics.track_clicks'),
            'trackForms' => config('analytics.track_forms'),
            'trackEngagement' => config('analytics.track_engagement'),
            'presence' => [
                'heartbeatIntervalSeconds' => config('analytics.presence.heartbeat_interval_seconds'),
                'activeWindowSeconds' => config('analytics.presence.active_window_seconds'),
            ],
        ];
        $metaPixelConfig = \App\Support\Meta::pixelClientConfig();
        $metaPixelId = $metaPixelConfig['id'];
        $metaPixelEnabled = $metaPixelConfig['enabled'];
        $metaPixelTrackPageView = $metaPixelConfig['trackPageView'] ?? true;
        $pageConfig = [
            'type' => $pageType,
            'name' => $routeName,
            'title' => trim($__env->yieldContent('title', __('messages.site.brand'))),
            'path' => request()->getPathInfo(),
            'url' => url()->current(),
        ];
    @endphp
    <script>
        window.LapsiqueAnalytics = @json($analyticsConfig);
        window.LapsiquePixel = @json($metaPixelConfig);
        window.LapsiquePage = @json($pageConfig);
        window.__lapsiqueTrackerQueue = window.__lapsiqueTrackerQueue || [];
        window.__lapsiquePixelQueue = window.__lapsiquePixelQueue || [];

        window.LapsiqueTracker = window.LapsiqueTracker || {
            getContext: function () {
                return window.LapsiqueTrackingContext || {};
            },
            track: function (name, options) {
                window.__lapsiqueTrackerQueue.push({
                    method: 'track',
                    name: name,
                    options: options || {},
                });
            },
            trackPageview: function (options) {
                window.__lapsiqueTrackerQueue.push({
                    method: 'trackPageview',
                    options: options || {},
                });
            },
            syncForms: function () {
                window.__lapsiqueTrackerQueue.push({
                    method: 'syncForms',
                    options: {},
                });
            },
        };

        window.trackMetaPixel = window.trackMetaPixel || function (eventName, payload, options) {
            window.__lapsiquePixelQueue.push({
                method: 'track',
                eventName: eventName,
                payload: payload || {},
                options: options || null,
            });
        };

        window.trackMetaPixelCustom = window.trackMetaPixelCustom || function (eventName, payload, options) {
            window.__lapsiquePixelQueue.push({
                method: 'trackCustom',
                eventName: eventName,
                payload: payload || {},
                options: options || null,
            });
        };
    </script>
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($metaPixelEnabled)
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $metaPixelId }}');
            @if ($metaPixelTrackPageView)
            fbq('track', 'PageView');
            @endif
        </script>
        @if ($metaPixelTrackPageView)
        <noscript>
            <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" />
        </noscript>
        @endif
    @endif
    
    {{-- JSON-LD Structured Data --}}
    @stack('structured_data')
</head>
@php
    $hideNavbar = trim($__env->yieldContent('hide_navbar')) === '1';
    $minimalFooter = trim($__env->yieldContent('minimal_footer')) === '1';
    $contentFlush = trim($__env->yieldContent('content_flush', '')) === '1';
    $siteSettings = \App\Models\SiteSetting::current();
    $contactWhatsappNumber = preg_replace('/\D+/', '', (string) ($siteSettings?->booking_whatsapp ?: config('lapsique.whatsapp_number', '')));
    $contactWhatsappMessage = 'Hola, vengo de Lapsique Media. Quiero platicar sobre un proyecto.';
    $contactWhatsappUrl = $contactWhatsappNumber !== '' ? 'https://wa.me/' . $contactWhatsappNumber . '?text=' . urlencode($contactWhatsappMessage) : null;
    $bookingCtaUrl = route('home') . '#agenda';
    $showWhatsappWidget = (bool) $contactWhatsappUrl;
@endphp
<body class="bg-ink text-gray-100 min-h-screen antialiased" data-page-type="{{ $pageType }}" data-route-name="{{ $routeName }}">
    <div class="bg-grid min-h-screen">
        <a
            href="#main-content"
            class="fixed left-4 top-4 z-[100] -translate-y-24 border border-white/40 bg-black px-4 py-3 text-sm font-bold uppercase tracking-[0.08em] text-white opacity-0 transition-[transform,opacity] duration-150 focus:translate-y-0 focus:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black motion-reduce:transition-none"
        >
            {{ app()->getLocale() === 'en' ? 'Skip to main content' : 'Saltar al contenido principal' }}
        </a>
        @if (! $hideNavbar)
        <header class="sticky top-0 z-50 border-b border-white/10 bg-[#07090b]/95 backdrop-blur-xl">
            <div class="mx-auto flex min-h-[4.5rem] max-w-7xl items-center justify-between gap-4 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex min-h-11 items-center gap-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <span class="flex size-9 items-center justify-center border border-white/15 bg-white/[0.04] text-lg font-bold text-primary" aria-hidden>Ι</span>
                    <span class="leading-none">
                        <span class="block font-display text-lg font-bold lowercase tracking-tight text-white">lapsique</span>
                        <span class="mt-1 block font-mono text-[0.55rem] uppercase tracking-[0.35em] text-primary">media</span>
                    </span>
                </a>
                <nav class="hidden items-center gap-1 text-xs uppercase tracking-[0.14em] md:flex" aria-label="{{ app()->getLocale() === 'en' ? 'Primary navigation' : 'Navegación principal' }}">
                    <a href="{{ route('portfolio.index') }}" class="nav-link px-3">{{ __('messages.site.nav.portfolio') }}</a>

                    <details class="group relative">
                        <summary role="button" aria-haspopup="menu" class="nav-link cursor-pointer list-none gap-2 px-3 [&::-webkit-details-marker]:hidden">
                            {{ app()->getLocale() === 'en' ? 'Scene' : 'Escena' }}
                            <span aria-hidden class="transition-transform group-open:rotate-45">+</span>
                        </summary>
                        <div class="absolute left-1/2 top-[calc(100%+0.75rem)] grid w-[21rem] -translate-x-1/2 divide-y divide-white/10 border border-white/15 bg-[#07090b] p-1 shadow-2xl">
                            <a href="{{ route('djs.index') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">DJs</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Artists documented by Lapsique.' : 'Artistas documentados por Lapsique.' }}</span>
                            </a>
                            <a href="{{ route('events.index') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ __('messages.site.nav.events') }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Shows, collaborations, and archive.' : 'Shows, colaboraciones y archivo.' }}</span>
                            </a>
                            <a href="{{ route('videos.index') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">Psique Sessions</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Complete DJ sets produced by Lapsique.' : 'DJ sets completos producidos por Lapsique.' }}</span>
                            </a>
                            <a href="{{ route('videos.index') }}#aftermovies" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">Aftermovies</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Events and nightlife in motion.' : 'Eventos y nightlife en movimiento.' }}</span>
                            </a>
                            <a href="{{ route('posts.index') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">Blog</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Editorial notes from the scene.' : 'Notas editoriales de la escena.' }}</span>
                            </a>
                        </div>
                    </details>

                    <details class="group relative">
                        <summary role="button" aria-haspopup="menu" class="nav-link cursor-pointer list-none gap-2 px-3 [&::-webkit-details-marker]:hidden">
                            {{ app()->getLocale() === 'en' ? 'Services' : 'Servicios' }}
                            <span aria-hidden class="transition-transform group-open:rotate-45">+</span>
                        </summary>
                        <div class="absolute right-0 top-[calc(100%+0.75rem)] grid max-h-[calc(100vh-5.5rem)] w-[22rem] overflow-y-auto divide-y divide-white/10 border border-white/15 bg-[#07090b] p-1 shadow-2xl">
                            <a href="{{ route('content-creation.show') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ app()->getLocale() === 'en' ? 'Social media content' : 'Contenido para redes' }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Reels and photography for social media and ads.' : 'Reels y fotografía para redes y anuncios.' }}</span>
                            </a>
                            <a href="{{ route('food-reels.show') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ app()->getLocale() === 'en' ? 'Restaurant reels' : 'Reels para restaurantes' }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Food, atmosphere, and service made to sell.' : 'Comida, ambiente y servicio listos para vender.' }}</span>
                            </a>
                            <a href="{{ route('djset.show') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ app()->getLocale() === 'en' ? 'Record a DJ set' : 'Grabar un DJ set' }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'A cinematic record ready to publish.' : 'Un registro cinematográfico listo para publicar.' }}</span>
                            </a>
                            <a href="{{ route('electronic-event-coverage.show') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ app()->getLocale() === 'en' ? 'Event coverage' : 'Cobertura de eventos' }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Aftermovie, drone, and edited photography.' : 'Aftermovie, dron y fotografía editada.' }}</span>
                            </a>
                            <a href="{{ route('multi-camera.show') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ app()->getLocale() === 'en' ? 'Multicamera production' : 'Producción multicámara' }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Continuous Log video, drops, audio, and photos.' : 'Video continuo en Log, drops, audio y fotos.' }}</span>
                            </a>
                            <a href="{{ route('drone-sessions.show') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ app()->getLocale() === 'en' ? 'Drone flights' : 'Vuelos con dron' }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Aerial footage for properties and venues.' : 'Tomas aéreas para propiedades y venues.' }}</span>
                            </a>
                            <a href="{{ route('construction-progress.show') }}" class="group/menu-item px-4 py-3 hover:bg-white/[0.06]">
                                <span class="block font-bold text-white group-hover/menu-item:text-primary">{{ app()->getLocale() === 'en' ? 'Construction progress' : 'Avances de obra' }}</span>
                                <span class="mt-1 block normal-case tracking-normal text-white/55">{{ app()->getLocale() === 'en' ? 'Progress reports with photo, video, and drone.' : 'Reportes con foto, video y dron.' }}</span>
                            </a>
                        </div>
                    </details>
                </nav>
                <div class="flex items-center gap-3">
                    @auth('customer')
                        <a href="{{ route('customers.portal') }}" class="btn btn-ghost hidden md:inline-flex">Mi portal</a>
                        <form method="POST" action="{{ route('customers.logout') }}" class="hidden md:block">
                            @csrf
                            <button type="submit" class="btn btn-ghost">Salir</button>
                        </form>
                    @else
                        <a href="{{ route('customers.login') }}" class="btn btn-ghost hidden md:inline-flex">Mi portal</a>
                    @endauth
                    @php
                        $locale = app()->getLocale();
                        $nextLocale = $locale === 'es' ? 'en' : 'es';
                        $langEmoji = $nextLocale === 'es' ? '🇲🇽' : '🇬🇧';
                    @endphp
                    <a href="{{ route('locale.switch', $nextLocale) }}" class="btn btn-ghost hidden md:inline-flex text-2xl leading-none" title="{{ $nextLocale === 'es' ? 'Cambiar a Español' : 'Switch to English' }}">{{ $langEmoji }}</a>
                    <a href="{{ $bookingCtaUrl }}" class="btn btn-primary hidden lg:inline-flex">
                        {{ app()->getLocale() === 'en' ? 'Book session' : 'Agendar sesión' }}
                    </a>
                    <details class="group relative md:hidden">
                        <summary role="button" aria-haspopup="menu" class="btn btn-outline cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                            {{ app()->getLocale() === 'en' ? 'Menu' : 'Menú' }}
                            <span aria-hidden class="transition-transform group-open:rotate-45">+</span>
                        </summary>
                        <nav
                            class="absolute right-0 top-[calc(100%+0.75rem)] z-50 grid w-[min(82vw,19rem)] border border-white/20 bg-[#07090b] p-2 shadow-2xl"
                            aria-label="{{ app()->getLocale() === 'en' ? 'Mobile navigation' : 'Navegación móvil' }}"
                        >
                            <a href="{{ route('home') }}" class="nav-link px-3">{{ __('messages.site.nav.home') }}</a>
                            <a href="{{ route('djs.index') }}" class="nav-link px-3">{{ __('messages.site.nav.djs') }}</a>
                            <a href="{{ route('events.index') }}" class="nav-link px-3">{{ __('messages.site.nav.events') }}</a>
                            <a href="{{ route('videos.index') }}" class="nav-link px-3">{{ __('messages.site.nav.videos') }}</a>
                            <a href="{{ route('portfolio.index') }}" class="nav-link px-3">{{ __('messages.site.nav.portfolio') }}</a>
                            <a href="{{ route('posts.index') }}" class="nav-link px-3">Blog</a>
                            @auth('customer')
                                <a href="{{ route('customers.portal') }}" class="nav-link border-t border-white/15 px-3">Mi portal</a>
                            @else
                                <a href="{{ route('customers.login') }}" class="nav-link border-t border-white/15 px-3">Mi portal</a>
                            @endauth
                            <a href="{{ route('locale.switch', $nextLocale) }}" class="nav-link px-3">
                                {{ $nextLocale === 'es' ? 'Español' : 'English' }}
                            </a>
                        </nav>
                    </details>
                </div>
            </div>
        </header>
        @endif

        @if (session('success'))
            <div class="mx-auto max-w-6xl px-6 pt-6">
                <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <main id="main-content" tabindex="-1" class="outline-none @if ($contentFlush) w-full py-6 sm:py-8 @else mx-auto max-w-6xl px-6 py-10 space-y-14 @endif">
            @yield('content')
        </main>

        @if ($minimalFooter)
        <footer class="border-t border-white/10">
            <div class="mx-auto max-w-6xl px-6 py-5 text-center text-sm text-gray-400">
                {{ __('messages.site.brand') }}
            </div>
        </footer>
        @else
        <footer class="border-t border-white/10">
            <div class="mx-auto max-w-6xl px-6 py-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    {{-- Brand --}}
                    <div>
                        <p class="font-semibold uppercase tracking-[0.2em] text-gray-200">{{ __('messages.site.brand') }}</p>
                        <p class="text-sm text-gray-400">{{ __('messages.site.brand_tagline') }}</p>
                    </div>
                    
                    {{-- Navigation Links --}}
                    <div class="flex items-center gap-6 text-sm">
                        <a href="{{ route('djs.index') }}" class="nav-link">DJs</a>
                        <a href="{{ route('events.index') }}" class="nav-link">{{ __('messages.site.footer_links.events') }}</a>
                        <a href="{{ route('videos.index') }}" class="nav-link">{{ __('messages.site.footer_links.videos') }}</a>
                        <a href="{{ route('portfolio.index') }}" class="nav-link">{{ __('messages.site.footer_links.portfolio') }}</a>
                        <a href="{{ route('posts.index') }}" class="nav-link">Blog</a>
                    </div>
                    
                    {{-- Social Media Icons --}}
                    <div class="flex items-center gap-4">
                        <a href="https://www.youtube.com/{{ config('lapsique.youtube_handle') }}" target="_blank" rel="noopener noreferrer" class="inline-flex size-11 items-center justify-center text-gray-400 transition-[color,transform] duration-150 hover:text-white active:scale-[0.96] motion-reduce:transition-none" title="YouTube" aria-label="YouTube">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21.58 7.2c-.12-.86-.9-1.54-1.78-1.65C17.34 5.25 14 5.25 12 5.25s-5.34 0-7.8.3c-.88.11-1.66.79-1.78 1.65C2.25 8.77 2.25 10.13 2.25 12s0 3.23.17 4.8c.12.86.9 1.54 1.78 1.65 2.46.3 5.8.3 7.8.3s5.34 0 7.8-.3c.88-.11 1.66-.79 1.78-1.65.17-1.57.17-2.93.17-4.8s0-3.23-.17-4.8ZM10.5 14.7V9.3l4.1 2.7-4.1 2.7Z"/>
                            </svg>
                        </a>
                        <a href="{{ config('lapsique.instagram_url') }}" target="_blank" rel="noopener noreferrer" class="inline-flex size-11 items-center justify-center text-gray-400 transition-[color,transform] duration-150 hover:text-white active:scale-[0.96] motion-reduce:transition-none" title="Instagram" aria-label="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7.5 2h9A5.5 5.5 0 0122 7.5v9a5.5 5.5 0 01-5.5 5.5h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2zm0 1.5A4 4 0 003.5 7.5v9a4 4 0 004 4h9a4 4 0 004-4v-9a4 4 0 00-4-4h-9zM17.25 6a1.25 1.25 0 110 2.5 1.25 1.25 0 010-2.5zM12 7.5a4.5 4.5 0 110 9 4.5 4.5 0 010-9zm0 1.5a3 3 0 100 6 3 3 0 000-6z"/>
                            </svg>
                        </a>
                        @if ($contactWhatsappUrl)
                        <a href="{{ $contactWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex size-11 items-center justify-center text-gray-400 transition-[color,transform] duration-150 hover:text-white active:scale-[0.96] motion-reduce:transition-none" title="WhatsApp" aria-label="WhatsApp">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 01-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.85-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14-.01-.3-.01-.47-.01z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </footer>
        @endif
    </div>

    @if ($showWhatsappWidget)
    <div class="whatsapp-widget" data-whatsapp-widget>
        <div id="whatsapp-popup" class="whatsapp-popup hidden" role="dialog" aria-modal="false" aria-labelledby="whatsapp-popup-title">
            <div class="whatsapp-popup__window">
                <div class="whatsapp-popup__header">
                    <div>
                        <span class="whatsapp-popup__eyebrow">WhatsApp</span>
                        <strong id="whatsapp-popup-title" class="whatsapp-popup__title">{{ __('messages.site.brand') }}</strong>
                        <span class="whatsapp-popup__status">Normalmente respondemos rápido</span>
                    </div>
                    <button type="button" class="whatsapp-popup__close" aria-label="Cerrar popup de WhatsApp" onclick="closeWhatsAppWidget()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="whatsapp-popup__body">
                    <div class="whatsapp-popup__bubble">
                        <span class="whatsapp-popup__sender">De parte de Lapsique Media</span>
                        <p class="whatsapp-popup__message">Podemos ayudarte a producir contenido con intención.</p>
                        <div class="whatsapp-popup__meta">
                            <span>{{ now()->format('H:i') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-sky-500" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M1.73 12.91 8.1 19.28a1 1 0 0 0 1.41 0L22.27 6.52l-1.41-1.41L8.8 17.17l-5.66-5.67-1.41 1.41Zm6 0 6.37 6.37a1 1 0 0 0 1.41 0L24 10.79l-1.41-1.41-7.45 7.44-5.66-5.67-1.41 1.41Z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="whatsapp-popup__actions">
                    <a href="{{ $contactWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="whatsapp-cta whatsapp-cta--primary" onclick="trackWhatsAppWidget('contact_click')">
                        Contactar
                    </a>
                    <a href="{{ $bookingCtaUrl }}" class="whatsapp-cta whatsapp-cta--secondary" onclick="trackWhatsAppWidget('schedule_click')">
                        Agendar sesión
                    </a>
                </div>
            </div>
        </div>

        <button type="button" id="whatsapp-toggle" class="whatsapp-fab" aria-expanded="false" aria-controls="whatsapp-popup" aria-label="Abrir contacto por WhatsApp" onclick="toggleWhatsAppWidget()">
            <span class="whatsapp-fab__icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 01-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.85-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14-.01-.3-.01-.47-.01z"/>
                </svg>
            </span>
            <span class="whatsapp-fab__text">
                <span class="whatsapp-fab__label">WhatsApp</span>
                <span class="whatsapp-fab__action">Hablemos</span>
            </span>
        </button>
    </div>
    @endif

    <!-- Lead Capture Popup Modal -->
    @if (! $hideNavbar && ! $minimalFooter || $showWhatsappWidget)
    @if (! $hideNavbar && ! $minimalFooter)
    <div id="lead-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/70 backdrop-blur-sm px-4" style="display: none;">
        <div class="card relative w-full max-w-md animate-modal-in">
            <button onclick="closeLeadModal()" class="absolute right-4 top-4 text-gray-400 transition hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="px-8 py-8 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-2xl font-semibold text-white">¡Únete a la comunidad!</h3>
                    <p class="text-gray-300">Sé el primero en enterarte de próximos eventos, lanzamientos exclusivos y contenido especial.</p>
                </div>
                <form id="lead-form" class="space-y-4" onsubmit="submitLeadForm(event)">
                    <div>
                        <input type="text" name="name" id="lead-name" placeholder="Tu nombre" class="field" required>
                    </div>
                    <div>
                        <input type="email" name="email" id="lead-email" placeholder="Tu email" class="field" required>
                    </div>
                    <div>
                        <input type="tel" name="phone" id="lead-phone" placeholder="WhatsApp (opcional)" class="field">
                    </div>
                    <div>
                        <input type="text" name="instagram_handle" id="lead-instagram" placeholder="Instagram @usuario (opcional)" class="field">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="subscribed_newsletter" id="lead-newsletter" checked class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-white focus:ring-white">
                        <label for="lead-newsletter" class="text-sm text-gray-300">Quiero recibir el newsletter</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-full justify-center">
                        <span id="lead-btn-text">Suscribirme</span>
                        <span id="lead-btn-loading" class="hidden">Enviando...</span>
                    </button>
                    <p id="lead-error" class="text-red-400 text-sm hidden"></p>
                    <p id="lead-success" class="text-emerald-400 text-sm hidden"></p>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        let leadModalShown = false;

        function showLeadModal() {
            if (leadModalShown || localStorage.getItem('leadModalShown')) {
                return;
            }
            const modal = document.getElementById('lead-modal');
            if (! modal) {
                return;
            }
            modal.style.display = 'flex';
            leadModalShown = true;
        }

        function closeLeadModal() {
            const modal = document.getElementById('lead-modal');
            if (! modal) {
                return;
            }
            modal.style.display = 'none';
            localStorage.setItem('leadModalShown', 'true');
        }

        async function submitLeadForm(e) {
            e.preventDefault();
            
            const form = e.target;
            const btnText = document.getElementById('lead-btn-text');
            const btnLoading = document.getElementById('lead-btn-loading');
            const errorEl = document.getElementById('lead-error');
            const successEl = document.getElementById('lead-success');

            if (! btnText || ! btnLoading || ! errorEl || ! successEl) {
                return;
            }
            
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                instagram_handle: formData.get('instagram_handle'),
                subscribed_newsletter: formData.get('subscribed_newsletter') === 'on',
                source: 'popup'
            };

            try {
                const response = await fetch('{{ route("customers.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    successEl.textContent = result.message;
                    successEl.classList.remove('hidden');
                    form.reset();
                    setTimeout(() => {
                        closeLeadModal();
                    }, 2000);
                } else {
                    errorEl.textContent = result.message || 'Error al enviar el formulario';
                    errorEl.classList.remove('hidden');
                }
            } catch (error) {
                errorEl.textContent = 'Error de conexión. Inténtalo de nuevo.';
                errorEl.classList.remove('hidden');
            } finally {
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            }
        }

        // Show modal after 10 seconds on homepage
        if (window.location.pathname === '/' && document.getElementById('lead-modal')) {
            setTimeout(showLeadModal, 10000);
        }

        // Show modal on exit intent
        document.addEventListener('mouseout', function(e) {
            if (!e.toElement && !e.relatedTarget && document.getElementById('lead-modal')) {
                showLeadModal();
            }
        });

        function trackWhatsAppWidget(action) {
            if (window.LapsiqueTracker && typeof window.LapsiqueTracker.track === 'function') {
                window.LapsiqueTracker.track('whatsapp_widget_interaction', {
                    action: action,
                    page_type: document.body?.dataset?.pageType || 'site',
                });
            }
        }

        function setWhatsAppWidgetState(isOpen) {
            const popup = document.getElementById('whatsapp-popup');
            const toggle = document.getElementById('whatsapp-toggle');

            if (!popup || !toggle) {
                return;
            }

            popup.classList.toggle('hidden', !isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function toggleWhatsAppWidget(forceState) {
            const popup = document.getElementById('whatsapp-popup');

            if (!popup) {
                return;
            }

            const isOpen = typeof forceState === 'boolean' ? forceState : popup.classList.contains('hidden');
            setWhatsAppWidgetState(isOpen);

            if (isOpen) {
                trackWhatsAppWidget('popup_open');
            }
        }

        function closeWhatsAppWidget() {
            setWhatsAppWidgetState(false);
        }

        document.addEventListener('click', function (event) {
            const widget = document.querySelector('[data-whatsapp-widget]');
            const popup = document.getElementById('whatsapp-popup');

            if (!widget || !popup || popup.classList.contains('hidden')) {
                return;
            }

            if (!widget.contains(event.target)) {
                closeWhatsAppWidget();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeWhatsAppWidget();
            }
        });

        window.trackWhatsAppWidget = trackWhatsAppWidget;
        window.toggleWhatsAppWidget = toggleWhatsAppWidget;
        window.closeWhatsAppWidget = closeWhatsAppWidget;
    </script>

    <style>
        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-modal-in {
            animation: modalIn 0.3s ease-out forwards;
        }
    </style>
    @endif
    @stack('scripts')
</body>
</html>
