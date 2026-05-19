@extends('layouts.site')

@section('hide_navbar', '1')
@section('minimal_footer', '1')

@section('title', ($settings?->booking_title ?: 'Sesión de Contenido') . ' — lapsique.media')
@section('meta_title', $settings?->booking_title ?: 'Sesión de Contenido Profesional — lapsique.media')
@section('meta_description', $settings?->booking_subtitle ?: '2 Reels editados + 20 fotografías profesionales en una sola sesión. Agenda ahora por $' . number_format($price, 0, '.', ',') . ' MXN.')
@section('canonical_url', route('booking.show'))
@section('og_url', route('booking.show'))
@section('og_title', $settings?->booking_title ?: 'Sesión de Contenido Profesional')
@section('og_description', $settings?->booking_subtitle ?: '2 Reels + 20 Fotos editadas en una sola sesión.')
@section('og_image', url('/images/booking-og.jpg'))
@section('twitter_image', url('/images/booking-og.jpg'))
@section('og_image_alt', 'Landing de sesión de contenido de Lapsique Media')
@section('content_flush', '1')

@section('content')
@php
    $slotsJson = $slots->map(fn($s) => [
        'id' => $s->id,
        'date' => $s->date->format('Y-m-d'),
        'time_label' => $s->time_label,
        'time_value' => $s->time_value,
    ])->values()->toJson();
    $whatsapp = $settings?->booking_whatsapp ?: config('lapsique.whatsapp_number', '');
    $title = $settings?->booking_title ?: 'Sesión de Contenido Profesional';
    $subtitle = $settings?->booking_subtitle ?: '2 Reels editados + 20 fotografías en una sola sesión.';
    $nextSlot = $slots->first();
    $nextSlotSummary = $nextSlot
        ? $nextSlot->date->translatedFormat('d \d\e F') . ' · ' . $nextSlot->time_label
        : null;
    $portfolioFeed = collect($portfolioPhotos ?? [])
        ->take(5)
        ->values()
        ->map(function ($photo, $index) {
            return [
                'image' => $photo->asset_url,
                'title' => $photo->title ?: 'Trabajo real — lapsique.media',
                'caption' => $photo->caption ?: 'Producción, dirección y post con look listo para redes y campañas.',
                'eyebrow' => data_get($photo->tags, '0') ?: 'Portafolio',
                'time_label' => $index === 0 ? 'Reciente' : 'Selección',
            ];
        })
        ->all();
    $facebookPostPhoto = collect($portfolioFeed)
        ->map(fn ($item, $index) => $item + ['index' => $index])
        ->whenNotEmpty(fn ($items) => $items->random());
    $heroVideo = $portfolioVideo ?? null;
    $heroVideoMedia = $heroVideo?->getFirstMedia('asset');
    $heroVideoMime = $heroVideoMedia?->mime_type ?? 'video/mp4';
    $heroVideoOrientation = $heroVideo?->orientation === 'vertical' ? 'vertical' : 'horizontal';
    $heroVideoIsYoutube = $heroVideo?->source === 'youtube' && $heroVideo?->youtube_id;
    $heroVideoEmbedUrl = $heroVideoIsYoutube && $heroVideo?->youtube_id
        ? 'https://www.youtube-nocookie.com/embed/' . $heroVideo->youtube_id . '?autoplay=1&mute=1&controls=0&loop=1&playlist=' . $heroVideo->youtube_id . '&playsinline=1&rel=0&modestbranding=1&iv_load_policy=3'
        : null;
    $heroVideoTitle = $heroVideo?->title ?: 'Aftermovie real del portafolio';
    $heroVideoCaption = $heroVideo?->caption ?: 'Una muestra real de dirección, edición y ritmo visual aplicada al tipo de contenido que producimos para marcas.';
@endphp

<div class="booking-light-page booking-scene booking-layout-root">
<div class="booking-scene-bg" aria-hidden="true">
    <div class="booking-scene-orb booking-scene-orb-cyan"></div>
    <div class="booking-scene-orb booking-scene-orb-amber"></div>
    <div class="booking-scene-orb booking-scene-orb-rose"></div>
    <div class="booking-scene-grid booking-scene-grid--decor" aria-hidden="true">
        @foreach([
            ['label' => 'FOTO', 'caption' => '20 piezas'],
            ['label' => 'REEL', 'caption' => '×2 editados'],
            ['label' => '1', 'caption' => 'sesión'],
            ['label' => 'MP', 'caption' => 'pago seguro'],
        ] as $tile)
        <div class="booking-scene-tile">
            <span>{{ $tile['label'] }}</span>
            <small>{{ $tile['caption'] }}</small>
        </div>
        @endforeach
    </div>
</div>
<div class="booking-nav-shell sticky top-0 z-50 w-full">
    <div class="booking-nav-inner mx-auto max-w-6xl px-4 pb-3 pt-0 sm:px-6 sm:pb-4 sm:pt-0">
        <div class="booking-nav-bar">
            <a href="{{ route('home') }}" class="booking-nav-brand text-xs font-semibold uppercase tracking-[0.28em] text-black transition hover:text-black/70 sm:text-sm">
                lapsique.media
            </a>
            <div class="booking-nav-actions">
                <a href="#agenda"
                   class="btn btn-primary booking-nav-btn"
                   onclick="trackFunnelEvent('nav_agenda_click'); scrollToBookingAgenda('nav'); return false;">
                    Elegir horario
                </a>
                @if($whatsapp)
                <a href="https://wa.me/{{ $whatsapp }}?text=Hola%2C%20me%20interesa%20agendar%20una%20sesi%C3%B3n%20de%20contenido" target="_blank" rel="noopener"
                   class="btn btn-ghost booking-nav-btn"
                   data-meta-event="Contact"
                   data-meta-params='{"content_name":"Sesión de Contenido","contact_method":"WhatsApp"}'
                   onclick="trackFunnelEvent('nav_whatsapp_click')">
                    WhatsApp
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="booking-content-shell mx-auto max-w-6xl space-y-16 px-4 pb-28 sm:space-y-20 sm:px-6 md:pb-20 lg:px-8">

{{-- ─── HERO ────────────────────────────────────────────────────── --}}
<section class="booking-hero-section relative overflow-hidden pt-2 sm:pt-4" data-analytics-section="hero">
    <div class="pointer-events-none absolute inset-0 opacity-40" aria-hidden="true" style="background:radial-gradient(ellipse 80% 50% at 15% 0%, rgba(97,198,214,0.14), transparent 55%), radial-gradient(ellipse 55% 45% at 100% 15%, rgba(235,183,103,0.12), transparent 50%);"></div>

    <div class="relative py-10 md:py-16 lg:py-20">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center lg:gap-14">
            <div class="space-y-6 fade-up">
                <div class="flex flex-wrap gap-2">
                    <span class="pill border-cyan-400/30 bg-cyan-500/10 text-cyan-200">Paquete fijo — 1 sesión</span>
                    <span class="pill border-fuchsia-400/30 bg-fuchsia-500/10 text-fuchsia-200">Entrega en 5 días hábiles</span>
                </div>

                <h1 class="text-4xl font-bold leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ $title }}
                </h1>

                <p class="text-lg leading-relaxed text-gray-200 sm:text-xl">
                    {{ $subtitle }}
                </p>

                <p class="max-w-xl text-base leading-relaxed text-gray-400">
                    <strong class="font-semibold text-gray-300">Todo en una sesión:</strong> grabación y dirección, <strong class="text-gray-300">2 reels editados</strong> y <strong class="text-gray-300">20 fotos retocadas</strong>, listos para publicar y anunciar. Un solo precio, sin letra pequeña: eliges fecha y hora aquí, pagas con Mercado Pago y tu cupo queda confirmado al aprobar el pago.
                </p>

                <div class="rounded-2xl border border-white/10 bg-black/[0.03] p-4 sm:p-5">
                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <span class="text-4xl font-bold text-white sm:text-5xl">${{ number_format($price, 0, '.', ',') }}</span>
                        <span class="text-gray-300">MXN · sesión completa · <span class="text-gray-200">uso comercial incluido</span></span>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:flex-wrap sm:items-center">
                    <a href="#agenda"
                       class="btn btn-primary justify-center px-8 py-4 text-sm sm:inline-flex"
                       onclick="trackFunnelEvent('hero_cta_click'); scrollToBookingAgenda('hero_primary'); return false;">
                        Reservar fecha y hora — ir al calendario
                    </a>
                    @if($whatsapp)
                    <a href="https://wa.me/{{ $whatsapp }}?text=Hola%2C%20quiero%20info%20sobre%20la%20sesi%C3%B3n%20de%20contenido"
                       target="_blank" rel="noopener"
                       class="btn btn-ghost justify-center px-6 py-4 text-sm sm:inline-flex"
                       onclick="trackFunnelEvent('hero_whatsapp_click')">
                        Prefiero resolver dudas antes
                    </a>
                    @endif
                </div>

                <p class="text-xs text-gray-500">Mercado Pago · Cupos acotados por horario para cuidar cada sesión</p>
            </div>

            <div class="fade-up delay-1 w-full">
                    <div class="booking-hero-visual relative w-full max-w-xl lg:max-w-none lg:justify-self-end">
                        <div class="card booking-contrast-card booking-gallery-card p-4 sm:p-6">
                            <div class="mb-4 space-y-1">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">Prueba visual</p>
                                <p class="text-base font-medium text-white sm:text-lg">
                                    @if($heroVideo)
                                        Reel real del portafolio
                                    @else
                                        Así trabajamos tu tipo de contenido
                                    @endif
                                </p>
                            </div>
                        @if($heroVideo)
                        <div class="booking-showcase-video-wrap">
                            <div class="booking-showcase-video-frame is-{{ $heroVideoOrientation }}">
                                <div class="booking-showcase-video-ambient" style="background-image: url('{{ $heroVideo->poster_url }}');"></div>
                                <div class="booking-showcase-video-wash"></div>
                                <div class="booking-showcase-device is-{{ $heroVideoOrientation }}">
                                    <div class="booking-showcase-device-notch"></div>
                                    <div class="booking-showcase-device-screen">
                                @if($heroVideoIsYoutube && $heroVideoEmbedUrl)
                                <iframe
                                    src="{{ $heroVideoEmbedUrl }}"
                                    title="{{ $heroVideoTitle }}"
                                    class="booking-showcase-video booking-showcase-video--embed"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                                @else
                                <video
                                    class="booking-showcase-video"
                                    autoplay
                                    muted
                                    loop
                                    playsinline
                                    preload="metadata"
                                    poster="{{ $heroVideo->poster_url }}"
                                >
                                    <source src="{{ $heroVideo->asset_url }}" type="{{ $heroVideoMime }}">
                                </video>
                                @endif
                                    </div>
                                </div>
                                <div class="booking-showcase-video-badge">
                                    <span class="booking-showcase-video-dot"></span>
                                    Auto preview
                                </div>
                            </div>
                            <div class="booking-showcase-video-meta">
                                <div class="booking-showcase-video-copy">
                                    <p class="booking-showcase-video-label">Aftermovie real</p>
                                    <h3 class="booking-showcase-video-title">{{ $heroVideoTitle }}</h3>
                                </div>
                                <a href="#agenda" class="btn btn-primary booking-showcase-video-cta inline-flex text-xs" onclick="trackFunnelEvent('hero_video_agenda_click'); scrollToBookingAgenda('hero_video'); return false;">
                                    Quiero algo así
                                </a>
                            </div>
                        </div>
                        @elseif(!empty($portfolioFeed))
                        <div class="booking-gallery-grid">
                            @foreach($portfolioFeed as $index => $photo)
                            <button
                                type="button"
                                class="booking-gallery-tile"
                                data-gallery-index="{{ $index }}"
                                onclick="openPortfolioModal({{ $index }})"
                            >
                                <img
                                    src="{{ $photo['image'] }}"
                                    alt="{{ $photo['title'] }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-500"
                                >
                                <span class="booking-gallery-overlay">
                                    <strong>{{ $photo['title'] }}</strong>
                                    <small>Ampliar</small>
                                </span>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="rounded-[28px] border border-black/10 bg-white/85 p-6 text-center">
                            <p class="text-sm text-gray-600">Pronto verás aquí trabajos recientes.</p>
                            <a href="#agenda" class="btn btn-primary mt-4 inline-flex text-xs" onclick="trackFunnelEvent('hero_gallery_empty_cta'); return false;">Ir al calendario</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ─── Embudo: pasos (calendario → datos → pago) ─────────────── --}}
<section class="space-y-6" data-analytics-section="funnel_steps" aria-labelledby="booking-funnel-heading">
    <div class="rounded-3xl border border-black/8 bg-white/90 px-5 py-8 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:px-8 sm:py-10">
        <h2 id="booking-funnel-heading" class="text-center text-2xl font-bold text-white sm:text-3xl">
            Cómo agendar (3 pasos en esta misma página)
        </h2>
        <p class="mx-auto mt-2 max-w-2xl text-center text-sm text-gray-600 sm:text-base">
            Todo ocurre en esta página: <strong class="text-gray-800">eliges momento</strong>, <strong class="text-gray-800">dejas tus datos</strong> y <strong class="text-gray-800">pagas</strong>. Abajo está el calendario y el formulario, en ese orden.
        </p>
        <ol class="mt-8 grid gap-4 sm:grid-cols-3 sm:gap-5">
            <li class="booking-funnel-step">
                <span class="booking-funnel-step-num">1</span>
                <h3 class="booking-funnel-step-title">Elige día y hora</h3>
                <p class="booking-funnel-step-desc">Marca un día con cupo y la hora que te acomode. Así apartas tu lugar antes de que el bloque se llene.</p>
            </li>
            <li class="booking-funnel-step">
                <span class="booking-funnel-step-num">2</span>
                <h3 class="booking-funnel-step-title">Completa tus datos</h3>
                <p class="booking-funnel-step-desc">Nombre, email y WhatsApp para confirmarte y coordinar la sesión.</p>
            </li>
            <li class="booking-funnel-step">
                <span class="booking-funnel-step-num">3</span>
                <h3 class="booking-funnel-step-title">Paga con Mercado Pago</h3>
                <p class="booking-funnel-step-desc">Un solo cobro con Mercado Pago. Cuando se aprueba, tu reserva queda confirmada y recibes la confirmación.</p>
            </li>
        </ol>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="#agenda" class="btn btn-primary px-8 py-3.5 text-xs sm:text-sm" onclick="trackFunnelEvent('funnel_inline_agenda'); scrollToBookingAgenda('funnel_steps'); return false;">
                Ir al calendario ahora
            </a>
        </div>
    </div>
</section>

{{-- ─── INCLUDES ────────────────────────────────────────────────── --}}
<section class="space-y-6" data-analytics-section="includes">
    <div class="text-center space-y-3">
        <span class="pill border-cyan-400/20 bg-cyan-500/10 text-cyan-200">Tu paquete cerrado</span>
        <h2 class="mt-3 text-3xl font-bold text-white md:text-4xl">Qué recibes al agendar (sin sorpresas)</h2>
        <p class="mx-auto max-w-2xl text-sm text-gray-500 md:text-base">Es el mismo producto para todos: una sesión, entrega definida y archivo listo para publicar.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['icon' => '🎬', 'title' => '2 Reels editados', 'desc' => 'Historias verticales con ritmo, sonido limpio y color de cine. Listos para Meta Ads, TikTok e Instagram.'],
            ['icon' => '📸', 'title' => '20 fotos editadas', 'desc' => 'Retoque de piel natural, color coherente con tu marca y archivos en alta resolución.'],
            ['icon' => '🎨', 'title' => 'Dirección en set', 'desc' => 'Te guiamos en poses, ángulos y mensaje visual para que no pierdas tiempo improvisando.'],
            ['icon' => '⏱️', 'title' => 'Sesión de 2–3 h', 'desc' => 'Ventana concentrada para sacar foto y video con buena variedad en un solo día.'],
            ['icon' => '🚀', 'title' => 'Entrega en 5 días hábiles', 'desc' => 'Todo por enlace de descarga (Drive u otro canal acordado).'],
            ['icon' => '✅', 'title' => 'Licencia comercial', 'desc' => 'Usa el material en campañas, web y redes sin líos de derechos para este paquete.'],
        ] as $item)
        <div class="card booking-contrast-card booking-feature-card card-animated p-6 space-y-3">
            <div class="text-3xl">{{ $item['icon'] }}</div>
            <h3 class="text-xl font-semibold text-white">{{ $item['title'] }}</h3>
            <p class="text-sm leading-relaxed text-gray-300">{{ $item['desc'] }}</p>
        </div>
        @endforeach
    </div>
</section>

@if($facebookPostPhoto)
<section class="space-y-6" data-analytics-section="social_proof">
    <div class="mx-auto max-w-3xl">
        <div class="booking-facebook-card">
            <div class="booking-facebook-card__header">
                <div class="booking-facebook-card__identity">
                    <div class="booking-facebook-card__avatar">LM</div>
                    <div>
                        <p class="booking-facebook-card__author">Lapsique Media</p>
                        <p class="booking-facebook-card__meta">Patrocinado · Ahora</p>
                    </div>
                </div>
                <button type="button" class="booking-facebook-card__menu" aria-label="Más opciones">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

            <p class="booking-facebook-card__caption">Así puede verse una creative lista para anuncios, retargeting y redes.</p>

            <button
                type="button"
                class="booking-facebook-card__media"
                onclick="openPortfolioModal({{ $facebookPostPhoto['index'] }})"
            >
                <img
                    src="{{ $facebookPostPhoto['image'] }}"
                    alt="{{ $facebookPostPhoto['title'] }}"
                    loading="lazy"
                    class="booking-facebook-card__image"
                >
            </button>

            <div class="booking-facebook-card__stats">
                <span>1.2 mil</span>
                <span>84 comentarios</span>
                <span>12 compartidos</span>
            </div>

            <div class="booking-facebook-card__actions">
                <button type="button" class="booking-facebook-card__action">
                    <span>👍</span>
                    Me gusta
                </button>
                <button type="button" class="booking-facebook-card__action" onclick="openPortfolioModal({{ $facebookPostPhoto['index'] }})">
                    <span>💬</span>
                    Ver foto
                </button>
                <a href="#agenda" class="booking-facebook-card__action" onclick="trackFunnelEvent('facebook_mockup_agenda_click'); scrollToBookingAgenda('facebook_mockup'); return false;">
                    <span>↗</span>
                    Reservar
                </a>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ─── BOOKING WIDGET ─────────────────────────────────────────── --}}
<section id="agenda" class="space-y-6 scroll-mt-20" data-analytics-section="booking_widget">
    <div class="text-center space-y-3">
        <span class="pill border-cyan-400/20 bg-cyan-500/10 text-cyan-200">Paso 1 — calendario</span>
        <h2 class="mt-3 text-3xl font-bold text-white md:text-4xl">Elige fecha y hora: aquí conviertes clic en reserva</h2>
        <p class="mx-auto max-w-2xl text-sm text-gray-500 md:text-base">Los cupos son en vivo. Siguiente paso después de elegir hora: tus datos y pago con Mercado Pago.</p>
    </div>

    @if($slots->isEmpty())
    <div class="card booking-contrast-card booking-empty-state p-10 text-center space-y-4">
        <div class="text-5xl">🗓️</div>
        <h3 class="text-lg font-semibold text-white">No hay horarios disponibles en este momento</h3>
        <p class="text-gray-400">Contáctanos por WhatsApp para coordinar una fecha personalizada.</p>
        @if($whatsapp)
        <a href="https://wa.me/{{ $whatsapp }}?text=Hola%2C%20me%20interesa%20una%20sesi%C3%B3n%20de%20contenido%20y%20no%20vi%20horarios%20disponibles"
           target="_blank" rel="noopener"
           class="btn btn-primary inline-flex">
            Contactar por WhatsApp
        </a>
        @endif
    </div>
    @else

    {{-- Main Booking Widget --}}
    <div class="card booking-contrast-card booking-widget-card overflow-hidden" id="booking-widget">
        <div class="grid md:grid-cols-[280px_1fr]">
            {{-- Left Panel: Service Summary --}}
            <div class="booking-widget-side border-b border-white/10 md:border-b-0 md:border-r p-6 space-y-5">
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-widest text-gray-500">lapsique.media</p>
                    <h3 class="font-bold text-white text-lg">Sesión de Contenido</h3>
                </div>

                <div class="space-y-2">
                    <div class="flex items-start gap-2 text-sm text-gray-300">
                        <span class="mt-0.5 text-base">🎬</span>
                        <span>2 Reels con edición profesional</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm text-gray-300">
                        <span class="mt-0.5 text-base">📸</span>
                        <span>20 fotografías editadas</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm text-gray-300">
                        <span class="mt-0.5 text-base">⏱️</span>
                        <span>Sesión de 2–3 horas</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm text-gray-300">
                        <span class="mt-0.5 text-base">🚀</span>
                        <span>Entrega en 5 días hábiles</span>
                    </div>
                </div>

                <div class="border-t border-white/10 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Precio</p>
                    <p class="text-3xl font-bold text-white">${{ number_format($price, 0, '.', ',') }}</p>
                    <p class="text-xs text-gray-400">MXN · todo incluido</p>
                </div>

                {{-- Selected slot indicator --}}
                <div id="selected-slot-summary" class="hidden border-t border-white/10 pt-4 space-y-1">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Sesión elegida</p>
                    <p id="selected-slot-text" class="text-sm font-medium text-emerald-300"></p>
                </div>
            </div>

            {{-- Right Panel: Calendar --}}
            <div class="p-6 space-y-5">
                {{-- Calendar header --}}
                <div class="flex items-center justify-between">
                    <button id="cal-prev" class="flex items-center justify-center w-9 h-9 rounded-full hover:bg-white/10 transition text-gray-300 hover:text-white" aria-label="Mes anterior">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h4 id="cal-header" class="text-sm font-semibold text-white uppercase tracking-wider"></h4>
                    <button id="cal-next" class="flex items-center justify-center w-9 h-9 rounded-full hover:bg-white/10 transition text-gray-300 hover:text-white" aria-label="Mes siguiente">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                {{-- Day names --}}
                <div class="grid grid-cols-7 gap-1">
                    @foreach(['L','M','X','J','V','S','D'] as $d)
                    <div class="text-center text-xs font-medium text-gray-500 py-1">{{ $d }}</div>
                    @endforeach
                </div>

                {{-- Calendar days grid --}}
                <div id="cal-grid" class="grid grid-cols-7 gap-1"></div>

                {{-- Time slots --}}
                <div id="time-slots-panel" class="hidden space-y-3">
                    <div class="border-t border-white/10 pt-4">
                        <p id="time-slots-date-label" class="text-xs text-gray-400 mb-3"></p>
                        <div id="time-slots-grid" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Booking Form (slides in after slot selection) ─── --}}
    <div id="booking-form-wrapper" class="booking-form-shell" aria-hidden="true">
        <div class="card booking-contrast-card booking-form-card p-6 md:p-8 space-y-6">
            {{-- Form header --}}
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500">Paso 2 — datos y pago</p>
                    <h3 class="text-lg font-bold text-white">Completa y paga para confirmar</h3>
                    <p id="form-slot-label" class="text-sm font-medium text-emerald-300"></p>
                </div>
                <button onclick="clearSlotSelection()" class="text-gray-500 hover:text-white transition text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cambiar
                </button>
            </div>

            @if($errors->any())
            <div class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-300 space-y-1">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form id="booking-form" action="{{ route('booking.checkout') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="booking_slot_id" id="form-slot-id">

                {{-- Tracking fields auto-injected by analytics.js --}}

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Nombre completo *</label>
                        <input type="text" name="client_name" class="field" placeholder="Tu nombre" required value="{{ old('client_name') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Email *</label>
                        <input type="email" name="client_email" class="field" placeholder="tu@email.com" required value="{{ old('client_email') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">WhatsApp *</label>
                        <input type="tel" name="client_phone" class="field" placeholder="+52 984 123 4567" required value="{{ old('client_phone') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Instagram</label>
                        <input type="text" name="client_instagram" class="field" placeholder="@tuusuario" value="{{ old('client_instagram') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">¿Algo que debamos saber de tu marca o sesión? <span class="text-gray-600 normal-case">(opcional)</span></label>
                    <textarea name="notes" class="field" rows="2" placeholder="Estilo, colores, tipo de contenido, ubicación preferida...">{{ old('notes') }}</textarea>
                </div>

                <div class="border-t border-white/10 pt-5">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div class="text-sm text-gray-300">
                            <p class="font-medium text-white">Sesión de Contenido</p>
                            <p class="text-xs text-gray-400">2 Reels + 20 Fotos editadas</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-white">${{ number_format($price, 0, '.', ',') }} MXN</p>
                            <p class="text-xs text-gray-500">Pago único</p>
                        </div>
                    </div>

                    <button type="submit" id="booking-submit-btn"
                        class="btn btn-primary w-full justify-center text-sm py-4">
                        <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.074 4 8.442 16.889h2.518l.968-4.775h2.985c2.354 0 3.85-1.264 3.85-3.378 0-1.7-1.158-2.956-3.185-2.956H10.39l.684-2.78H11.074zM13.5 8.105h1.97c1.092 0 1.727.523 1.727 1.48 0 1.121-.77 1.77-2.036 1.77H13.04l.46-3.25z"/>
                            <path d="M3 18l1.5-6h1.728L5.009 17h5.025l-.243 1H3z"/>
                        </svg>
                        Pagar ${{ number_format($price, 0, '.', ',') }} MXN con Mercado Pago
                    </button>

                    <p class="text-center text-xs text-gray-500 mt-3">
                        🔒 Pago 100% seguro procesado por Mercado Pago
                    </p>
                </div>
            </form>
        </div>
    </div>
    @endif
</section>

{{-- ─── FAQ ─────────────────────────────────────────────────────── --}}
<section class="space-y-6" data-analytics-section="faq">
    <h2 class="text-center text-2xl font-bold text-white md:text-3xl">Preguntas frecuentes</h2>
    <div class="space-y-3 max-w-2xl mx-auto">
        @foreach([
            ['q' => '¿Dónde se realiza la sesión?', 'a' => 'Podemos ir a tu negocio, locación exterior o coordinar en estudio. Lo definimos cuando confirmes tu reserva por WhatsApp.'],
            ['q' => '¿Puedo usar el contenido para Meta Ads?', 'a' => 'Sí, incluye licencia de uso comercial completo. Puedes usar todas las fotos y reels en tus campañas de Instagram, Facebook y cualquier otro canal.'],
            ['q' => '¿En qué formato entrego los archivos?', 'a' => 'Los reels en formato MP4 (1080×1920) y las fotos en JPG alta resolución, entregados por Google Drive.'],
            ['q' => '¿Qué pasa si necesito reprogramar mi sesión?', 'a' => 'Puedes cambiar tu cita con al menos 48 horas de anticipación sin costo adicional. Contáctanos por WhatsApp.'],
        ] as $faq)
        <details class="card booking-contrast-card booking-faq-card p-5 group open:ring-1 open:ring-white/20" >
            <summary class="flex items-center justify-between cursor-pointer font-medium text-white list-none select-none">
                {{ $faq['q'] }}
                <svg class="w-4 h-4 text-gray-400 transition group-open:rotate-180 shrink-0 ml-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>
            <p class="text-sm text-gray-400 leading-relaxed mt-3">{{ $faq['a'] }}</p>
        </details>
        @endforeach
    </div>
</section>

{{-- ─── FINAL CTA ───────────────────────────────────────────────── --}}
<section class="booking-final-cta rounded-3xl border border-black/8 bg-white/[0.92] px-5 py-10 text-center shadow-[0_20px_50px_rgba(15,23,42,0.07)] sm:px-10 sm:py-12" data-analytics-section="final_cta">
    <h2 class="text-2xl font-bold text-white sm:text-3xl md:text-4xl">Asegura tu sesión: fecha, datos y pago en un solo flujo</h2>
    <p class="mx-auto mt-3 max-w-2xl text-base text-gray-600 md:text-lg">El siguiente clic te lleva al calendario. Si ya viste el portafolio y el paquete te encaja, el momento de convertir es <strong class="text-gray-800">reservar el horario</strong>.</p>
    <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="#agenda" class="btn btn-primary px-10 py-4 text-sm inline-flex"
           onclick="trackFunnelEvent('final_cta_click'); scrollToBookingAgenda('final_cta'); return false;">
            Ir al calendario y reservar
        </a>
        @if($whatsapp)
        <a href="https://wa.me/{{ $whatsapp }}?text=Hola%2C%20quiero%20agendar%20el%20paquete%202%20reels%20%2B%2020%20fotos" target="_blank" rel="noopener" class="btn btn-ghost px-8 py-4 text-sm inline-flex" onclick="trackFunnelEvent('final_whatsapp_click')">
            Resolver últimas dudas por WhatsApp
        </a>
        @endif
    </div>
</section>
</div>{{-- /.booking-content-shell --}}

{{-- Modales fuera del ancho de contenido (evita recortes / stacking raro) --}}
<div id="booking-popup" class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/75 px-4 backdrop-blur-sm">
    <div class="card booking-contrast-card booking-popup-card relative w-full max-w-lg overflow-hidden p-6 md:p-7">
        <button type="button" class="absolute right-4 top-4 text-gray-400 transition hover:text-white" onclick="closeBookingPopup('close_button')" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="space-y-5">
            <div class="space-y-3">
                <h3 class="text-2xl font-bold leading-tight text-white sm:text-3xl">Siguiente paso: elegir tu horario</h3>
                <p class="text-base leading-relaxed text-gray-300">
                    Vas al calendario, eliges día y bloque, luego completas tus datos y pagas con Mercado Pago. En menos de dos minutos puedes tener la reserva lista.
                </p>
            </div>

            <div class="booking-popup-slot rounded-2xl border border-white/12 bg-black/30 p-4">
                <p class="text-xs uppercase tracking-[0.22em] text-gray-500">Primer cupo visible</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ $nextSlotSummary ?: 'Horarios en tiempo real abajo en la página' }}</p>
                <p class="mt-2 text-sm text-gray-400">Te posicionamos en el bloque de agenda para que no pierdas el funnel.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <button type="button" class="btn btn-primary w-full justify-center text-[11px]" onclick="launchAgendaFromPopup()">
                    Ver horarios disponibles
                </button>
                @if($whatsapp)
                    <a href="https://wa.me/{{ $whatsapp }}?text=Hola%2C%20quiero%20agendar%20una%20sesi%C3%B3n%20de%20contenido" target="_blank" rel="noopener" class="btn btn-ghost w-full justify-center text-[11px]" onclick="trackFunnelEvent('booking_popup_whatsapp_clicked', { metadata: { source: 'popup' } })">
                        WhatsApp
                    </a>
                @else
                    <button type="button" class="btn btn-ghost w-full justify-center text-[11px]" onclick="closeBookingPopup('secondary_close')">
                        Seguir leyendo
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="portfolio-modal" class="fixed inset-0 z-[130] hidden items-center justify-center bg-black/70 px-4 py-8 backdrop-blur-md">
    <div class="portfolio-modal-card relative w-full max-w-5xl overflow-hidden">
        <button type="button" class="portfolio-modal-close" onclick="closePortfolioModal()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="grid md:grid-cols-[1.12fr_0.88fr]">
            <div class="portfolio-modal-media">
                <img id="portfolio-modal-image" src="" alt="" class="h-full w-full object-cover">
            </div>
            <div class="portfolio-modal-panel">
                <div class="portfolio-modal-author">
                    <div class="portfolio-modal-avatar">LM</div>
                    <div>
                        <p class="portfolio-modal-brand">Lapsique Media</p>
                        <p id="portfolio-modal-time" class="portfolio-modal-time">Portafolio</p>
                    </div>
                </div>

                <div class="portfolio-modal-copy">
                    <p id="portfolio-modal-eyebrow" class="portfolio-modal-eyebrow">Referencia creativa</p>
                    <h3 id="portfolio-modal-title" class="portfolio-modal-title">Contenido premium para marcas con presencia</h3>
                    <p id="portfolio-modal-caption" class="portfolio-modal-caption">Visuales pensados para campañas, redes sociales y una presencia de marca mucho más sólida.</p>
                </div>

                <div class="portfolio-modal-stats">
                    <p class="text-sm font-medium text-gray-200">Mismo estándar de edición y dirección que aplicamos a tu paquete de sesión.</p>
                </div>

                <div class="portfolio-modal-actions">
                    <button type="button" class="portfolio-action-btn is-primary" onclick="closePortfolioModal(); scrollToBookingAgenda('gallery_modal'); trackFunnelEvent('booking_gallery_cta_clicked');">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        Quiero agendar con este look
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /.booking-light-page --}}

{{-- Barra fija móvil: insistir en agendar --}}
<div class="booking-sticky-cta safe-area-btm md:hidden" aria-hidden="false">
    <div class="booking-sticky-cta-inner">
        <a href="#agenda" class="btn btn-primary booking-sticky-btn" onclick="trackFunnelEvent('sticky_cta_click'); scrollToBookingAgenda('sticky_bar'); return false;">
            Reservar horario
        </a>
    </div>
</div>

@endsection

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Service",
    "name": "{{ $title }}",
    "description": "{{ $subtitle }}",
    "provider": {
        "@@type": "Organization",
        "name": "lapsique.media"
    },
    "offers": {
        "@@type": "Offer",
        "price": "{{ $price }}",
        "priceCurrency": "MXN",
        "availability": "https://schema.org/InStock"
    }
}
</script>
@endpush

@push('scripts')
<script>
// ─── Booking Page Data ───────────────────────────────────────────
window.BookingData = {
    slots: {!! $slotsJson !!},
    price: {{ $price }},
    currency: 'MXN'
};
window.BookingGalleryItems = @json($portfolioFeed);

// ─── Funnel Tracking ─────────────────────────────────────────────
let leadFired = false;

function trackFunnelEvent(eventName, extra) {
    if (typeof window.LapsiqueTracker !== 'undefined') {
        window.LapsiqueTracker.track(eventName, extra || {});
    }
}

function trackBookingFormSubmit() {
    trackFunnelEvent('booking_form_submitted', { value: window.BookingData.price, currency: 'MXN' });
    trackFunnelEvent('booking_payment_cta_clicked', {
        value: window.BookingData.price,
        currency: 'MXN',
    });
    window.trackMetaPixel && window.trackMetaPixel('AddPaymentInfo', {
        content_name: 'Sesión de Contenido',
        value: window.BookingData.price,
        currency: 'MXN'
    });
}

function syncOverlayBodyLock() {
    const portfolioOpen = document.getElementById('portfolio-modal')?.classList.contains('flex');
    const bookingPopupOpen = document.getElementById('booking-popup')?.classList.contains('flex');

    document.body.classList.toggle('overflow-hidden', Boolean(portfolioOpen || bookingPopupOpen));
}

// Track ViewContent on load
document.addEventListener('DOMContentLoaded', function () {
    window.trackMetaPixel && window.trackMetaPixel('ViewContent', {
        content_type: 'service',
        content_name: 'Sesión de Contenido',
        value: window.BookingData.price,
        currency: 'MXN'
    });
    trackFunnelEvent('booking_page_viewed', { value: window.BookingData.price });
});

function renderPortfolioModalItem(index) {
    const item = window.BookingGalleryItems?.[index];

    if (!item) {
        return;
    }

    const image = document.getElementById('portfolio-modal-image');
    const title = document.getElementById('portfolio-modal-title');
    const caption = document.getElementById('portfolio-modal-caption');
    const eyebrow = document.getElementById('portfolio-modal-eyebrow');
    const time = document.getElementById('portfolio-modal-time');

    if (image) {
        image.src = item.image;
        image.alt = item.title || 'Portafolio Lapsique Media';
    }

    if (title) title.textContent = item.title || 'Contenido premium para marcas con presencia';
    if (caption) caption.textContent = item.caption || 'Visuales pensados para campañas, redes sociales y una presencia de marca mucho más sólida.';
    if (eyebrow) eyebrow.textContent = item.eyebrow || 'Portafolio';
    if (time) time.textContent = item.time_label || 'Selección';
}

window.openPortfolioModal = function(index) {
    renderPortfolioModalItem(index);

    const modal = document.getElementById('portfolio-modal');
    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    syncOverlayBodyLock();

    trackFunnelEvent('booking_gallery_opened', {
        metadata: { index },
    });
};

window.closePortfolioModal = function() {
    const modal = document.getElementById('portfolio-modal');
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    syncOverlayBodyLock();
};

// ─── Calendar Component ───────────────────────────────────────────
(function() {
    const MONTHS_ES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const DAYS_FULL_ES = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    const POPUP_STORAGE_KEY = 'lapsique_booking_popup_seen';

    let currentYear, currentMonth;
    let selectedSlotId = null;
    let selectedSlotData = null;
    let bookingFormViewed = false;
    let bookingFormStarted = false;
    let bookingIntentStarted = false;
    let bookingFormFocusTimer = null;
    let popupVisible = false;
    let popupAutoLaunched = false;
    const today = new Date();
    today.setHours(0,0,0,0);

    // Group slots by date
    function groupSlotsByDate(slots) {
        const map = {};
        slots.forEach(s => {
            if (!map[s.date]) map[s.date] = [];
            map[s.date].push(s);
        });
        return map;
    }

    const slotsByDate = groupSlotsByDate(window.BookingData.slots);

    function hasBookingIntent() {
        const panel = document.getElementById('time-slots-panel');
        const formWrapper = document.getElementById('booking-form-wrapper');

        return Boolean(
            bookingIntentStarted ||
            selectedSlotId ||
            (panel && !panel.classList.contains('hidden')) ||
            (formWrapper && formWrapper.classList.contains('is-visible'))
        );
    }

    function showBookingForm(options = {}) {
        const {
            animate = true,
            focus = true,
        } = options;

        const formWrapper = document.getElementById('booking-form-wrapper');

        if (!formWrapper) {
            return;
        }

        if (bookingFormFocusTimer) {
            clearTimeout(bookingFormFocusTimer);
            bookingFormFocusTimer = null;
        }

        formWrapper.classList.remove('is-instant');
        formWrapper.classList.add('is-visible');
        formWrapper.setAttribute('aria-hidden', 'false');

        if (!animate) {
            formWrapper.classList.add('is-instant');
        }

        requestAnimationFrame(() => {
            formWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        if (focus) {
            bookingFormFocusTimer = window.setTimeout(() => {
                const firstField = formWrapper.querySelector('input, textarea, select');
                firstField?.focus({ preventScroll: true });
            }, animate ? 320 : 120);
        }
    }

    function hideBookingForm() {
        const formWrapper = document.getElementById('booking-form-wrapper');

        if (!formWrapper) {
            return;
        }

        if (bookingFormFocusTimer) {
            clearTimeout(bookingFormFocusTimer);
            bookingFormFocusTimer = null;
        }

        formWrapper.classList.remove('is-visible', 'is-instant');
        formWrapper.setAttribute('aria-hidden', 'true');
    }

    function initCalendar() {
        // Find the first month with available slots
        const firstSlotDate = window.BookingData.slots.length > 0
            ? new Date(window.BookingData.slots[0].date + 'T12:00:00')
            : new Date();

        currentYear = firstSlotDate.getFullYear();
        currentMonth = firstSlotDate.getMonth();
        renderCalendar();
    }

    function renderCalendar() {
        const header = document.getElementById('cal-header');
        const grid = document.getElementById('cal-grid');
        if (!header || !grid) return;

        header.textContent = MONTHS_ES[currentMonth] + ' ' + currentYear;

        const firstDay = new Date(currentYear, currentMonth, 1);
        // Monday-based: 0=Mon, 6=Sun
        let startDow = firstDay.getDay(); // 0=Sun
        startDow = (startDow + 6) % 7; // convert to Mon-based

        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        let html = '';

        // Empty cells before first day
        for (let i = 0; i < startDow; i++) {
            html += '<div></div>';
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const dateObj = new Date(currentYear, currentMonth, d);
            dateObj.setHours(0,0,0,0);
            const isPast = dateObj < today;
            const hasSlots = !!slotsByDate[dateStr] && slotsByDate[dateStr].length > 0;
            const isSelected = selectedSlotData && selectedSlotData.date === dateStr;

            let cls = 'booking-calendar-day';

            if (isPast) {
                cls += ' is-disabled';
            } else if (isSelected) {
                cls += ' is-selected';
            } else if (hasSlots) {
                cls += ' is-available';
            } else {
                cls += ' is-muted';
            }

            // Today indicator
            const isToday = dateObj.getTime() === today.getTime();

            html += `<div class="text-center py-0.5">
                <button type="button" class="${cls}" ${(isPast || !hasSlots) ? 'disabled' : `onclick="selectDate('${dateStr}')"`} data-date="${dateStr}">
                    ${d}
                    ${isToday ? '<span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-emerald-400"></span>' : ''}
                </button>
            </div>`;
        }

        grid.innerHTML = html;

        // Update prev button state
        const prevBtn = document.getElementById('cal-prev');
        const nowMonth = new Date();
        const isCurrentOrPast = (currentYear < nowMonth.getFullYear()) ||
            (currentYear === nowMonth.getFullYear() && currentMonth <= nowMonth.getMonth());
        if (prevBtn) prevBtn.disabled = isCurrentOrPast;
        if (prevBtn) prevBtn.style.opacity = isCurrentOrPast ? '0.3' : '1';
    }

    window.selectDate = function(dateStr) {
        const slots = slotsByDate[dateStr];
        if (!slots || slots.length === 0) return;
        bookingIntentStarted = true;

        // Fire Lead event on first date selection
        if (!leadFired) {
            leadFired = true;
            window.trackMetaPixel && window.trackMetaPixel('Lead', {
                content_name: 'Sesión de Contenido',
                value: window.BookingData.price,
                currency: 'MXN'
            });
            trackFunnelEvent('booking_date_selected', { date: dateStr });
        }

        const panel = document.getElementById('time-slots-panel');
        const dateLabel = document.getElementById('time-slots-date-label');
        const slotsGrid = document.getElementById('time-slots-grid');
        if (!panel) return;

        // Format date label
        const dt = new Date(dateStr + 'T12:00:00');
        const dayName = DAYS_FULL_ES[dt.getDay()];
        const dayNum = dt.getDate();
        const monthName = MONTHS_ES[dt.getMonth()].toLowerCase();
        const yearNum = dt.getFullYear();
        dateLabel.textContent = `${dayName.charAt(0).toUpperCase() + dayName.slice(1)}, ${dayNum} de ${monthName} de ${yearNum}`;

        // Render time slots
        let slotsHtml = '';
        slots.forEach(s => {
            const isSelected = selectedSlotId === s.id;
            const cls = isSelected
                ? 'booking-time-slot is-selected'
                : 'booking-time-slot';
            slotsHtml += `<button type="button" class="${cls}" onclick="selectSlot(${s.id}, '${s.date}', '${s.time_label}', '${s.time_value}')">${s.time_label}</button>`;
        });
        slotsGrid.innerHTML = slotsHtml;

        panel.classList.remove('hidden');

        // If slot was from this date, keep form open; else hide form
        if (!selectedSlotData || selectedSlotData.date !== dateStr) {
            hideBookingForm();
        }

        // Re-render calendar to update selection visual
        renderCalendar();

        // Scroll to time slots on mobile
        if (window.innerWidth < 768) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    window.selectSlot = function(id, date, timeLabel, timeValue) {
        selectedSlotId = id;
        selectedSlotData = { id, date, time_label: timeLabel, time_value: timeValue };

        // Update summary in left panel
        const dt = new Date(date + 'T12:00:00');
        const formatted = `${MONTHS_ES[dt.getMonth()].slice(0,3)} ${dt.getDate()}, ${dt.getFullYear()} — ${timeLabel}`;
        const summary = document.getElementById('selected-slot-summary');
        const summaryText = document.getElementById('selected-slot-text');
        if (summary) summary.classList.remove('hidden');
        if (summaryText) summaryText.textContent = formatted;

        // Update form
        const formSlotId = document.getElementById('form-slot-id');
        const formSlotLabel = document.getElementById('form-slot-label');
        if (formSlotId) formSlotId.value = id;
        if (formSlotLabel) formSlotLabel.textContent = `📅 ${formatted}`;

        // Show booking form
        showBookingForm();

        if (!bookingFormViewed) {
            bookingFormViewed = true;
            trackFunnelEvent('booking_form_viewed', {
                metadata: { slot_id: id, source: 'slot_selection' },
            });
        }

        // Re-render time slots to reflect selection
        selectDate(date);

        // Track
        window.trackMetaPixel && window.trackMetaPixel('AddToCart', {
            content_name: 'Sesión de Contenido',
            value: window.BookingData.price,
            currency: 'MXN'
        });
        trackFunnelEvent('booking_slot_selected', { slot_id: id, date, time: timeLabel });

        // Track InitiateCheckout when form becomes visible
        window.trackMetaPixel && window.trackMetaPixel('InitiateCheckout', {
            content_name: 'Sesión de Contenido',
            value: window.BookingData.price,
            currency: 'MXN',
            num_items: 1
        });
        trackFunnelEvent('booking_checkout_started', { value: window.BookingData.price });
    };

    window.clearSlotSelection = function() {
        selectedSlotId = null;
        selectedSlotData = null;
        hideBookingForm();
        document.getElementById('selected-slot-summary')?.classList.add('hidden');
        trackFunnelEvent('booking_slot_cleared');
        renderCalendar();
    };

    window.scrollToBookingAgenda = function(source = 'direct') {
        bookingIntentStarted = true;
        document.getElementById('agenda')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        trackFunnelEvent('booking_calendar_opened', {
            metadata: { source },
        });
    };

    window.openBookingPopup = function(trigger = 'manual', source = 'ui') {
        const popup = document.getElementById('booking-popup');
        if (!popup || popupVisible) return;

        popup.classList.remove('hidden');
        popup.classList.add('flex');
        popupVisible = true;
        sessionStorage.setItem(POPUP_STORAGE_KEY, '1');
        syncOverlayBodyLock();

        trackFunnelEvent('booking_popup_shown', {
            metadata: { trigger, source },
        });
    };

    window.closeBookingPopup = function(reason = 'dismiss') {
        const popup = document.getElementById('booking-popup');
        if (!popup || !popupVisible) return;

        popup.classList.add('hidden');
        popup.classList.remove('flex');
        popupVisible = false;
        syncOverlayBodyLock();

        trackFunnelEvent('booking_popup_dismissed', {
            metadata: { reason },
        });
    };

    window.launchAgendaFromPopup = function() {
        trackFunnelEvent('booking_popup_cta_clicked', {
            metadata: { target: 'agenda' },
        });
        closeBookingPopup('primary_cta');
        scrollToBookingAgenda('popup');
    };

    function maybeAutoLaunchPopup(trigger) {
        if (
            popupAutoLaunched ||
            popupVisible ||
            hasBookingIntent() ||
            sessionStorage.getItem(POPUP_STORAGE_KEY) === '1'
        ) {
            return;
        }

        popupAutoLaunched = true;
        openBookingPopup(trigger, 'auto');
    }

    function initPopupLaunchers() {
        if (!window.BookingData.slots.length) {
            return;
        }

        window.setTimeout(() => maybeAutoLaunchPopup('time_12s'), 12000);

        window.addEventListener('scroll', () => {
            const doc = document.documentElement;
            const scrollable = doc.scrollHeight - window.innerHeight;

            if (scrollable <= 0) {
                return;
            }

            const progress = (window.scrollY / scrollable) * 100;

            if (progress >= 45) {
                maybeAutoLaunchPopup('scroll_45');
            }
        }, { passive: true });

        document.addEventListener('mouseout', (event) => {
            if (window.innerWidth < 1024 || event.relatedTarget || event.toElement || popupVisible) {
                return;
            }

            if (typeof event.clientY === 'number' && event.clientY > 12) {
                return;
            }

            maybeAutoLaunchPopup('exit_intent');
        });

        document.getElementById('booking-popup')?.addEventListener('click', (event) => {
            if (event.target?.id === 'booking-popup') {
                closeBookingPopup('backdrop');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && popupVisible) {
                closeBookingPopup('escape');
            }

            if (event.key === 'Escape') {
                closePortfolioModal();
            }
        });

        document.getElementById('portfolio-modal')?.addEventListener('click', (event) => {
            if (event.target?.id === 'portfolio-modal') {
                closePortfolioModal();
            }
        });
    }

    function initBookingFormTracking() {
        const bookingForm = document.getElementById('booking-form');

        if (!bookingForm) {
            return;
        }

        bookingForm.addEventListener('submit', () => {
            trackBookingFormSubmit();
        });

        bookingForm.querySelectorAll('input, textarea, select').forEach((field) => {
            field.addEventListener('focus', () => {
                if (bookingFormStarted) {
                    return;
                }

                bookingFormStarted = true;
                trackFunnelEvent('booking_form_started', {
                    metadata: {
                        field: field.name || field.id || 'unknown',
                    },
                });
            }, { once: true });
        });
    }

    // Calendar navigation
    document.getElementById('cal-prev')?.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    });

    document.getElementById('cal-next')?.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar();
    });

    // If there are validation errors, restore the slot selection from old input
    @if(old('booking_slot_id'))
    document.addEventListener('DOMContentLoaded', function() {
        const slotId = parseInt('{{ old("booking_slot_id") }}');
        const slot = window.BookingData.slots.find(s => s.id === slotId);
        if (slot) {
            selectSlot(slot.id, slot.date, slot.time_label, slot.time_value);
        }
        showBookingForm({ animate: false, focus: false });
    });
    @endif

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initCalendar();
            initPopupLaunchers();
            initBookingFormTracking();
        });
    } else {
        initCalendar();
        initPopupLaunchers();
        initBookingFormTracking();
    }
})();
</script>

<style>
#booking-widget details summary::-webkit-details-marker { display: none; }

.booking-form-shell {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    pointer-events: none;
    transform: translateY(18px);
    transition:
        max-height 0.45s cubic-bezier(0.22, 1, 0.36, 1),
        opacity 0.28s ease,
        transform 0.38s cubic-bezier(0.22, 1, 0.36, 1),
        margin-top 0.38s ease;
}

.booking-form-shell.is-visible {
    max-height: 1200px;
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
    margin-top: 1.25rem;
}

.booking-form-shell.is-instant {
    transition: none;
}

.booking-form-shell .booking-form-card {
    transform-origin: top center;
}

#time-slots-panel {
    animation: fadeUp 0.3s ease forwards;
}

#booking-popup .card,
#portfolio-modal .portfolio-modal-card {
    animation: fadeUp 0.28s ease forwards;
}

body[data-page-type="content_booking"] {
    background: #f5f1eb !important;
    color: #151515 !important;
    color-scheme: light;
}

body[data-page-type="content_booking"] .bg-grid {
    background:
        radial-gradient(circle at 10% 10%, rgba(239, 170, 66, 0.08), transparent 24%),
        radial-gradient(circle at 88% 16%, rgba(63, 166, 184, 0.1), transparent 22%),
        radial-gradient(circle at 50% 58%, rgba(0, 0, 0, 0.03), transparent 36%),
        linear-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(15, 23, 42, 0.03) 1px, transparent 1px),
        #f5f1eb !important;
    background-size: auto, auto, auto, 32px 32px, 32px 32px, auto;
    background-position: 0 0, 0 0, 0 0, -8px -8px, -8px -8px, 0 0;
}

.booking-light-page {
    position: relative;
    isolation: isolate;
    color: #151515;
}

.booking-light-page .text-white { color: #101010 !important; }
.booking-light-page .text-gray-200 { color: #2c2c2c !important; }
.booking-light-page .text-gray-300 { color: #494949 !important; }
.booking-light-page .text-gray-400 { color: #686868 !important; }
.booking-light-page .text-gray-500 { color: #8a8a8a !important; }
.booking-light-page .text-cyan-200 { color: #0f7d8c !important; }
.booking-light-page .text-fuchsia-200 { color: #92508a !important; }
.booking-light-page .text-emerald-200 { color: #14735f !important; }
.booking-light-page .text-emerald-300 { color: #0f7a5b !important; }

.booking-scene-bg {
    position: absolute;
    inset: -2rem -2rem 0;
    overflow: hidden;
    pointer-events: none;
    z-index: -1;
}

.booking-scene-orb {
    position: absolute;
    border-radius: 999px;
    filter: blur(90px);
    opacity: 0.55;
}

.booking-scene-orb-cyan {
    top: 6rem;
    left: -7rem;
    width: 18rem;
    height: 18rem;
    background: rgba(97, 198, 214, 0.22);
}

.booking-scene-orb-amber {
    top: 22rem;
    right: -6rem;
    width: 17rem;
    height: 17rem;
    background: rgba(235, 183, 103, 0.22);
}

.booking-scene-orb-rose {
    bottom: 18rem;
    left: 50%;
    width: 22rem;
    height: 22rem;
    transform: translateX(-50%);
    background: rgba(217, 132, 182, 0.14);
}

.booking-scene-grid {
    position: absolute;
    top: 8rem;
    right: clamp(-2rem, 3vw, 2rem);
    display: grid;
    grid-template-columns: repeat(4, minmax(90px, 1fr));
    gap: 0.9rem;
    width: min(560px, 48vw);
    opacity: 0.62;
    transform: rotate(-6deg);
}

.booking-scene-tile {
    border: 1px solid rgba(17, 24, 39, 0.08);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.52);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    backdrop-filter: blur(12px);
    padding: 0.9rem 1rem;
}

.booking-scene-tile span,
.booking-scene-tile small {
    display: block;
}

.booking-scene-tile span {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.24em;
    color: #171717;
}

.booking-scene-tile small {
    margin-top: 0.35rem;
    font-size: 0.68rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #8a8a8a;
}

.booking-nav-shell {
    padding-top: 0;
    background: linear-gradient(180deg, rgba(244, 248, 252, 0.88), rgba(244, 248, 252, 0.2));
}

.booking-nav-inner {
    position: relative;
}

.booking-nav-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.8rem 1rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1.35rem;
    background: rgba(255, 255, 255, 0.78);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
}

.booking-nav-brand {
    flex: 0 0 auto;
    letter-spacing: 0.34em !important;
    white-space: nowrap;
}

.booking-nav-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.7rem;
    margin-left: auto;
}

.booking-nav-btn {
    min-height: 2.85rem;
    padding: 0.8rem 1.15rem;
    justify-content: center;
    font-size: 0.68rem;
    letter-spacing: 0.17em;
    white-space: nowrap;
}

.booking-hero-section {
    padding-top: 1rem;
}

/* Decoración de fondo: esquina inferior, no compite con el hero */
.booking-scene-grid--decor {
    top: auto !important;
    bottom: 4rem;
    right: clamp(0rem, 2vw, 1.5rem);
    width: min(320px, 34vw) !important;
    grid-template-columns: repeat(2, minmax(88px, 1fr)) !important;
    gap: 0.55rem !important;
    opacity: 0.28;
    transform: rotate(-4deg);
}

.booking-funnel-step {
    position: relative;
    border-radius: 1.25rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(252, 250, 246, 0.94));
    padding: 1.25rem 1.25rem 1.25rem 3.5rem;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
}

.booking-funnel-step-num {
    position: absolute;
    left: 1rem;
    top: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.85rem;
    height: 1.85rem;
    border-radius: 999px;
    background: #111111;
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 800;
}

.booking-funnel-step-title {
    font-size: 1rem;
    font-weight: 700;
    color: #111111;
}

.booking-funnel-step-desc {
    margin-top: 0.45rem;
    font-size: 0.85rem;
    line-height: 1.55;
    color: #5c5c5c;
}

.booking-sticky-cta {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 90;
    padding: 0.65rem 1rem;
    padding-bottom: max(0.65rem, env(safe-area-inset-bottom, 0.65rem));
    background: rgba(255, 255, 255, 0.94);
    border-top: 1px solid rgba(15, 23, 42, 0.1);
    box-shadow: 0 -12px 40px rgba(15, 23, 42, 0.12);
    backdrop-filter: blur(14px);
}

.booking-sticky-cta-inner {
    margin: 0 auto;
    max-width: 32rem;
}

.booking-sticky-btn {
    width: 100%;
    justify-content: center;
    text-align: center;
}

.booking-light-page .pill {
    border-color: rgba(15, 23, 42, 0.1) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.booking-light-page .btn-primary {
    background: #111111 !important;
    border-color: #111111 !important;
    color: #ffffff !important;
    box-shadow: 0 14px 34px rgba(17, 17, 17, 0.16) !important;
    transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
}

.booking-light-page .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 20px 40px rgba(17, 17, 17, 0.2) !important;
}

.booking-light-page .btn-ghost {
    background: rgba(255, 255, 255, 0.88) !important;
    color: #111111 !important;
    border-color: rgba(15, 23, 42, 0.12) !important;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
}

.booking-light-page .btn-ghost:hover {
    background: #f3efe9 !important;
}

.booking-light-page .card,
.booking-contrast-card,
.portfolio-modal-card {
    border: 1px solid rgba(15, 23, 42, 0.09);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(250, 247, 242, 0.94));
    box-shadow:
        0 20px 48px rgba(15, 23, 42, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
}

.booking-feature-card,
.booking-rider-mini-card,
.booking-faq-card,
.booking-popup-slot,
.booking-empty-state,
.booking-gallery-card {
    box-shadow:
        0 18px 38px rgba(15, 23, 42, 0.07),
        inset 0 1px 0 rgba(255, 255, 255, 0.72);
}

.booking-feature-card:hover,
.booking-faq-card:hover {
    transform: translateY(-3px);
    transition: transform 180ms ease, box-shadow 180ms ease;
    box-shadow:
        0 26px 48px rgba(15, 23, 42, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.76);
}

.booking-hero-visual {
    padding-top: 2.2rem;
}

.booking-camera-mosaic {
    position: absolute;
    inset: -1.8rem auto auto -1.4rem;
    display: grid;
    grid-template-columns: repeat(2, minmax(130px, 1fr));
    gap: 0.7rem;
    width: min(290px, 72%);
    z-index: 0;
}

.booking-camera-tile {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    background: rgba(253, 251, 247, 0.78);
    box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(14px);
    padding: 0.85rem 1rem;
}

.booking-camera-tile span,
.booking-camera-tile small {
    display: block;
}

.booking-camera-tile span {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.24em;
    color: #161616;
}

.booking-camera-tile small {
    margin-top: 0.35rem;
    font-size: 0.65rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #8a8a8a;
}

.booking-gallery-card {
    position: relative;
    z-index: 1;
    border-radius: 30px;
}

.booking-showcase-video-wrap {
    display: grid;
    gap: 0.8rem;
}

.booking-showcase-video-frame {
    position: relative;
    overflow: hidden;
    border-radius: 28px;
    background: transparent;
    aspect-ratio: 16 / 10;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.35rem;
}

.booking-showcase-video-frame.is-vertical {
    aspect-ratio: 1 / 1;
}

.booking-showcase-video-ambient,
.booking-showcase-video-wash {
    display: none;
}

.booking-showcase-device {
    position: relative;
    z-index: 1;
    border-radius: 32px;
    background: linear-gradient(180deg, #0a0a0b, #17181b);
    padding: 10px;
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.08),
        0 22px 38px rgba(15, 23, 42, 0.18),
        0 34px 60px rgba(0, 0, 0, 0.22);
    max-width: 100%;
    max-height: 100%;
}

.booking-showcase-device.is-vertical {
    height: calc(100% - 0.75rem);
    aspect-ratio: 9 / 16;
}

.booking-showcase-device.is-horizontal {
    width: min(100%, 520px);
}

.booking-showcase-device-notch {
    position: absolute;
    top: 10px;
    left: 50%;
    width: 34%;
    height: 18px;
    border-radius: 0 0 14px 14px;
    background: rgba(0, 0, 0, 0.88);
    transform: translateX(-50%);
    z-index: 3;
}

.booking-showcase-device-screen {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    background: #000000;
}

.booking-showcase-device.is-vertical .booking-showcase-device-screen {
    aspect-ratio: 9 / 16;
}

.booking-showcase-device.is-horizontal .booking-showcase-device-screen {
    aspect-ratio: 16 / 9;
}

.booking-showcase-video-badge {
    position: absolute;
    left: 1rem;
    bottom: 1rem;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    background: rgba(8, 10, 14, 0.56);
    padding: 0.55rem 0.8rem;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(14px);
}

.booking-showcase-video-dot {
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 999px;
    background: #34d399;
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.4);
    animation: bookingVideoPulse 1.8s ease-out infinite;
}

.booking-showcase-video {
    width: 100%;
    height: 100%;
    border: 0;
    object-fit: cover;
    background: #050505;
}

.booking-showcase-video-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(246, 240, 231, 0.92));
    padding: 0.9rem 0.95rem 0.9rem 1.1rem;
}

.booking-showcase-video-copy {
    min-width: 0;
}

.booking-showcase-video-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #6b7280;
}

.booking-showcase-video-title {
    margin-top: 0.18rem;
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.1;
}

.booking-showcase-video-cta {
    flex-shrink: 0;
    padding-inline: 1.35rem;
}

.booking-facebook-card {
    overflow: hidden;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 28px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(249, 245, 238, 0.96));
    box-shadow:
        0 24px 46px rgba(15, 23, 42, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.82);
}

.booking-facebook-card__header,
.booking-facebook-card__stats,
.booking-facebook-card__actions {
    padding-inline: 1.25rem;
}

.booking-facebook-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 1.15rem;
}

.booking-facebook-card__identity {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.booking-facebook-card__avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.8rem;
    height: 2.8rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #1877f2, #4f46e5);
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.12em;
}

.booking-facebook-card__author {
    font-size: 0.95rem;
    font-weight: 700;
    color: #111827;
}

.booking-facebook-card__meta {
    margin-top: 0.1rem;
    font-size: 0.8rem;
    color: #6b7280;
}

.booking-facebook-card__menu {
    display: inline-flex;
    align-items: center;
    gap: 0.22rem;
    border: 0;
    padding: 0.35rem;
    color: #6b7280;
    cursor: pointer;
}

.booking-facebook-card__menu span {
    width: 0.28rem;
    height: 0.28rem;
    border-radius: 999px;
    background: currentColor;
}

.booking-facebook-card__caption {
    padding: 0.85rem 1.25rem 1rem;
    font-size: 0.97rem;
    line-height: 1.55;
    color: #1f2937;
}

.booking-facebook-card__media {
    display: block;
    width: 100%;
    border: 0;
    background: #e5e7eb;
    cursor: pointer;
}

.booking-facebook-card__image {
    width: 100%;
    aspect-ratio: 4 / 5;
    object-fit: cover;
}

.booking-facebook-card__stats {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding-top: 0.9rem;
    padding-bottom: 0.85rem;
    font-size: 0.83rem;
    color: #6b7280;
}

.booking-facebook-card__actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.35rem;
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    padding-top: 0.45rem;
    padding-bottom: 0.6rem;
}

.booking-facebook-card__action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    min-height: 2.7rem;
    border-radius: 12px;
    border: 0;
    background: transparent;
    font-size: 0.9rem;
    font-weight: 600;
    color: #4b5563;
    transition: background 140ms ease, color 140ms ease;
}

.booking-facebook-card__action:hover {
    background: rgba(15, 23, 42, 0.05);
    color: #111827;
}

@keyframes bookingVideoPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.42);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(52, 211, 153, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(52, 211, 153, 0);
    }
}

.booking-gallery-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-auto-rows: 118px;
    gap: 0.8rem;
}

.booking-gallery-tile {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    box-shadow: 0 14px 26px rgba(15, 23, 42, 0.1);
    cursor: pointer;
    isolation: isolate;
}

.booking-gallery-tile:first-child {
    grid-column: span 2;
    grid-row: span 2;
}

.booking-gallery-tile:nth-child(5) {
    grid-column: span 2;
}

.booking-gallery-tile img {
    transform: scale(1.01);
}

.booking-gallery-tile:hover img {
    transform: scale(1.06);
}

.booking-gallery-overlay {
    position: absolute;
    inset: auto 0 0;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 1rem;
    background: linear-gradient(180deg, transparent, rgba(15, 15, 15, 0.82));
    text-align: left;
}

.booking-gallery-overlay strong {
    font-size: 0.9rem;
    line-height: 1.15;
    color: #fff;
}

.booking-gallery-overlay small {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.72rem;
}

.booking-rider-mini-card,
.booking-widget-side,
.booking-popup-slot {
    background: linear-gradient(180deg, rgba(251, 248, 242, 0.96), rgba(246, 240, 231, 0.92)) !important;
    border-color: rgba(15, 23, 42, 0.08) !important;
}

.booking-rider-note {
    background: linear-gradient(180deg, rgba(209, 244, 247, 0.62), rgba(234, 249, 250, 0.82)) !important;
    border-color: rgba(53, 151, 167, 0.18) !important;
}

.booking-widget-card {
    overflow: hidden;
}

.booking-widget-side {
    border-right-color: rgba(15, 23, 42, 0.08) !important;
}

.booking-widget-card .border-t,
.booking-widget-card .border-b,
.booking-form-card .border-t,
#time-slots-panel > div,
#selected-slot-summary {
    border-color: rgba(15, 23, 42, 0.08) !important;
}

.booking-light-page .field {
    width: 100%;
    border: 1px solid rgba(15, 23, 42, 0.13) !important;
    background: rgba(255, 255, 255, 0.92) !important;
    color: #131313 !important;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
}

.booking-light-page .field::placeholder {
    color: #9a9a9a !important;
}

.booking-light-page .field:focus {
    border-color: rgba(17, 17, 17, 0.24) !important;
    box-shadow: 0 0 0 4px rgba(17, 17, 17, 0.05) !important;
}

.tech-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.1);
    background: linear-gradient(135deg, rgba(224, 246, 249, 0.95), rgba(251, 235, 246, 0.95));
    padding: 0.75rem 1rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    color: #111;
    text-transform: uppercase;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

#booking-widget button[aria-label] {
    color: #686868 !important;
}

#booking-widget button[aria-label]:hover {
    background: rgba(15, 23, 42, 0.05) !important;
    color: #101010 !important;
}

.booking-calendar-day {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.4rem;
    height: 2.4rem;
    margin: 0 auto;
    border-radius: 999px;
    border: 1px solid transparent;
    font-size: 0.9rem;
    font-weight: 600;
    transition: transform 140ms ease, background 140ms ease, border-color 140ms ease, color 140ms ease, box-shadow 140ms ease;
}

.booking-calendar-day.is-available {
    background: rgba(255, 255, 255, 0.92);
    border-color: rgba(15, 23, 42, 0.12);
    color: #141414;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    cursor: pointer;
}

.booking-calendar-day.is-available:hover {
    transform: translateY(-1px);
    background: #f8f3ec;
}

.booking-calendar-day.is-selected {
    background: #101010;
    border-color: #101010;
    color: #fff;
    box-shadow: 0 12px 24px rgba(15, 15, 15, 0.16);
    cursor: pointer;
}

.booking-calendar-day.is-disabled,
.booking-calendar-day.is-muted {
    color: #b5b5b5;
    cursor: not-allowed;
}

.booking-time-slot {
    padding: 0.72rem 1rem;
    border-radius: 16px;
    border: 1px solid rgba(15, 23, 42, 0.12);
    background: rgba(255, 255, 255, 0.92);
    color: #151515;
    font-size: 0.88rem;
    font-weight: 600;
    box-shadow: 0 10px 18px rgba(15, 23, 42, 0.05);
    transition: transform 140ms ease, box-shadow 140ms ease, background 140ms ease;
}

.booking-time-slot:hover {
    transform: translateY(-1px);
    background: #f7f2ea;
    box-shadow: 0 14px 22px rgba(15, 23, 42, 0.08);
}

.booking-time-slot.is-selected {
    background: #101010;
    color: #fff;
    border-color: #101010;
    box-shadow: 0 16px 24px rgba(15, 15, 15, 0.14);
}

.booking-faq-card[open] {
    box-shadow:
        0 24px 46px rgba(15, 23, 42, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.booking-final-cta {
    padding-top: 1.5rem;
}

#booking-popup {
    background: rgba(9, 11, 16, 0.58) !important;
}

.booking-popup-card {
    border-radius: 28px;
}

.booking-popup-slot p:last-child {
    max-width: 28rem;
}

#booking-popup button.absolute,
.portfolio-modal-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 999px;
    color: #6a6a6a !important;
}

#booking-popup button.absolute:hover,
.portfolio-modal-close:hover {
    background: rgba(15, 23, 42, 0.06);
    color: #101010 !important;
}

#portfolio-modal {
    background: rgba(6, 8, 12, 0.68);
    align-items: flex-start !important;
    overflow-y: auto;
    overscroll-behavior: contain;
}

.portfolio-modal-card {
    position: relative;
    width: min(1120px, 100%);
    margin: auto;
    max-height: calc(100vh - 2.5rem);
    border-radius: 32px;
    overflow: hidden;
}

.portfolio-modal-card > .grid {
    height: 100%;
}

.portfolio-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 2;
    background: rgba(255, 255, 255, 0.88);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
}

.portfolio-modal-media {
    min-height: 340px;
    max-height: calc(100vh - 2.5rem);
    background: #0b0b0b;
}

.portfolio-modal-panel {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-height: calc(100vh - 2.5rem);
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 2rem;
    background: linear-gradient(180deg, #ffffff, #f8f4ee);
}

.portfolio-modal-author {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.portfolio-modal-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.8rem;
    height: 2.8rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #111111, #333333);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.18em;
}

.portfolio-modal-brand {
    font-size: 0.96rem;
    font-weight: 700;
    color: #111111;
}

.portfolio-modal-time,
.portfolio-modal-eyebrow {
    font-size: 0.72rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #8a8a8a;
}

.portfolio-modal-title {
    font-size: clamp(1.5rem, 2vw, 2rem);
    line-height: 1.05;
    font-weight: 800;
    color: #111111;
}

.portfolio-modal-caption {
    margin-top: 0.9rem;
    font-size: 1rem;
    line-height: 1.7;
    color: #464646;
}

.portfolio-modal-stats {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0;
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    color: #666666;
    font-size: 0.92rem;
}

.portfolio-modal-likes {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    color: #111111;
    font-weight: 600;
}

.portfolio-like-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.7rem;
    height: 1.7rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #0f7d8c, #8f4dd5);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
}

.portfolio-modal-actions {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: 1fr;
}

.portfolio-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    border: 1px solid rgba(15, 23, 42, 0.1);
    border-radius: 16px;
    padding: 0.85rem 1rem;
    background: rgba(255, 255, 255, 0.88);
    color: #1a1a1a;
    font-size: 0.88rem;
    font-weight: 600;
    transition: background 140ms ease, transform 140ms ease, box-shadow 140ms ease;
}

.portfolio-action-btn:hover {
    transform: translateY(-1px);
    background: #f4eee5;
    box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
}

.portfolio-action-btn.is-primary {
    background: #111111;
    border-color: #111111;
    color: #ffffff;
}

.portfolio-action-btn.is-primary:hover {
    background: #1d1d1d;
}

@media (max-width: 1024px) {
    .booking-scene-grid {
        width: min(420px, 52vw);
        grid-template-columns: repeat(3, minmax(90px, 1fr));
    }
}

@media (max-width: 768px) {
    .booking-nav-shell {
        padding-top: 0;
    }

    .booking-nav-inner {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    .booking-nav-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
        padding: 0.8rem;
        border-radius: 1.1rem;
    }

    .booking-nav-brand {
        text-align: center;
        font-size: 0.72rem;
        letter-spacing: 0.3em !important;
    }

    .booking-nav-actions {
        width: 100%;
        gap: 0.65rem;
    }

    .booking-nav-btn {
        flex: 1 1 0;
        min-width: 0;
        min-height: 2.7rem;
        padding: 0.82rem 0.75rem;
        font-size: 0.64rem;
        letter-spacing: 0.14em;
    }

    .booking-scene-grid {
        display: none;
    }

    .booking-camera-mosaic {
        position: static;
        width: 100%;
        margin-bottom: 1rem;
    }

    .booking-hero-visual {
        padding-top: 0;
    }

    .booking-showcase-video-frame {
        padding: 0.8rem;
    }

    .booking-showcase-video-frame.is-vertical {
        aspect-ratio: 4 / 5.4;
    }

    .booking-showcase-device {
        padding: 8px;
        border-radius: 26px;
    }

    .booking-showcase-device-notch {
        top: 8px;
        height: 14px;
    }

    .booking-showcase-video-badge {
        left: 0.8rem;
        bottom: 0.8rem;
        padding: 0.48rem 0.72rem;
        font-size: 0.6rem;
    }

    .booking-showcase-video-meta {
        border-radius: 24px;
        align-items: stretch;
        flex-direction: column;
        padding: 0.95rem;
    }

    .booking-showcase-video-cta {
        width: 100%;
        justify-content: center;
    }

    .booking-facebook-card__header,
    .booking-facebook-card__stats,
    .booking-facebook-card__actions,
    .booking-facebook-card__caption {
        padding-inline: 0.95rem;
    }

    .booking-facebook-card__stats {
        justify-content: flex-start;
    }

    .booking-facebook-card__actions {
        grid-template-columns: 1fr;
    }

    .booking-gallery-grid {
        grid-auto-rows: 110px;
    }

    .booking-gallery-tile:first-child,
    .booking-gallery-tile:nth-child(5) {
        grid-column: span 3;
    }

    .portfolio-modal-panel {
        max-height: none;
        overflow: visible;
        padding: 1.4rem;
    }

    .portfolio-modal-card {
        max-height: none;
    }

    .portfolio-modal-card > .grid {
        height: auto;
    }

    .portfolio-modal-media {
        min-height: 240px;
        max-height: 40vh;
    }

    .portfolio-modal-stats {
        flex-direction: column;
        align-items: flex-start;
    }

    .portfolio-modal-actions {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
