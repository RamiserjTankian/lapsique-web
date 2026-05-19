@extends('layouts.site')

@php
    // Calcular la URL de la imagen del evento destacado
    $coverUrl = $featuredEvent ? match ($featuredEvent->featured_poster) {
        'vertical' => $featuredEvent->getFirstMediaUrl('cover_vertical', 'poster_vertical') ?: $featuredEvent->getFirstMediaUrl('cover_vertical'),
        'cover' => $featuredEvent->getFirstMediaUrl('cover', 'cover_large') ?: $featuredEvent->getFirstMediaUrl('cover'),
        default => $featuredEvent->getFirstMediaUrl('cover_horizontal', 'poster_horizontal')
            ?: $featuredEvent->getFirstMediaUrl('cover', 'cover_large')
            ?: $featuredEvent->getFirstMediaUrl('cover'),
    } : null;

    // Meta tags dinámicas basadas en evento destacado
    $defaultTitle = 'lapsique.media - Música electrónica en Riviera Maya';
    $defaultDescription = 'Proyecto audiovisual que documenta la escena electrónica en Riviera Maya. DJ sets, aftermovies y fotografía con estética monocromática.';
    
    // Asegurar URL absoluta para WhatsApp/OpenGraph
    if ($coverUrl && !str_starts_with($coverUrl, 'http')) {
        $coverUrl = url($coverUrl);
    }
    
    // Asegurar que defaultImage también sea URL absoluta
    $defaultImage = url(asset('images/og-default.jpg'));
    
    $metaTitle = $featuredEvent ? $featuredEvent->title . ' | lapsique.media' : $defaultTitle;
    $metaDescription = $featuredEvent 
        ? ($featuredEvent->headline ?: $featuredEvent->title . ' - ' . $defaultDescription)
        : $defaultDescription;
    
    $metaImage = $coverUrl ?: $defaultImage;
    
    // Asegurar que la imagen siempre tenga protocolo https
    if ($metaImage && !str_starts_with($metaImage, 'http')) {
        $metaImage = url($metaImage);
    }
    if ($metaImage && str_starts_with($metaImage, 'http://')) {
        $metaImage = str_replace('http://', 'https://', $metaImage);
    }
    
    $ogType = $featuredEvent ? 'event' : 'website';
@endphp

@section('title', $featuredEvent ? $featuredEvent->title . ' | lapsique.media' : 'lapsique.media')

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', 'música electrónica, DJ sets, techno, house, Riviera Maya, Playa del Carmen, Tulum, eventos electrónicos, sets en vivo, aftermovies')

@section('og_type', $ogType)
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@section('og_image', $metaImage)
@section('og_url', route('home'))

@section('twitter_title', $metaTitle)
@section('twitter_description', $metaDescription)
@section('twitter_image', $metaImage)
@section('twitter_url', route('home'))

@section('canonical_url', route('home'))

@if($featuredEvent)
@push('og_meta')
    @if($featuredEvent->starts_at)
    <meta property="event:start_time" content="{{ $featuredEvent->starts_at->toIso8601String() }}">
    @endif
    @if($featuredEvent->ends_at)
    <meta property="event:end_time" content="{{ $featuredEvent->ends_at->toIso8601String() }}">
    @endif
    @if($featuredEvent->venue || $featuredEvent->city)
    <meta property="event:location" content="{{ trim(($featuredEvent->venue ? $featuredEvent->venue . ', ' : '') . ($featuredEvent->city ?? ''), ', ') }}">
    @endif
@endpush
@endif

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Organization",
  "name": "lapsique.media",
  "url": "{{ route('home') }}",
  "logo": "{{ asset('images/logo.png') }}",
  "description": "Proyecto audiovisual que documenta la escena electrónica en Riviera Maya",
  "sameAs": [
    "https://www.youtube.com/@LAPSIQUEMEDIA",
    "{{ config('lapsique.instagram_url') }}"
  ]
}
</script>
@if($featuredEvent)
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Event",
  "name": "{{ $featuredEvent->title }}",
  "description": "{{ $featuredEvent->headline ?: $featuredEvent->title }}",
  "image": "{{ $metaImage }}",
  "url": "{{ route('events.show', $featuredEvent) }}",
  @if($featuredEvent->starts_at)
  "startDate": "{{ $featuredEvent->starts_at->toIso8601String() }}",
  @endif
  @if($featuredEvent->ends_at)
  "endDate": "{{ $featuredEvent->ends_at->toIso8601String() }}",
  @endif
  @if($featuredEvent->venue || $featuredEvent->city)
  "location": {
    "@type": "Place",
    "name": "{{ $featuredEvent->venue ?? $featuredEvent->city }}",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "{{ $featuredEvent->city ?? '' }}",
      "addressCountry": "MX"
    }
  },
  @endif
  "organizer": {
    "@type": "Organization",
    "name": "lapsique.media",
    "url": "{{ route('home') }}"
  }
}
</script>
@endif
@endpush

@section('content')
    @php
        $rawVideo = $featuredEvent?->youtube_url ?? $highlightDj?->youtube_url ?? null;

        $tagConfig = [
            'new' => ['emoji' => '🆕', 'label' => 'NEW', 'class' => 'bg-emerald-500/90 text-white border-emerald-400'],
            'trending' => ['emoji' => '📈', 'label' => 'TREND', 'class' => 'bg-purple-500/90 text-white border-purple-400'],
            'hot' => ['emoji' => '🔥', 'label' => 'HOT', 'class' => 'bg-red-500/90 text-white border-red-400'],
            'star' => ['emoji' => '⭐', 'label' => 'STAR', 'class' => 'bg-yellow-500/90 text-black border-yellow-400 font-black'],
            'producer' => ['emoji' => '🎛️', 'label' => 'PROD', 'class' => 'bg-blue-500/90 text-white border-blue-400'],
            'resident' => ['emoji' => '🏠', 'label' => 'PSIQUE', 'class' => 'bg-indigo-500/90 text-white border-indigo-400'],
            'international' => ['emoji' => '🌎', 'label' => 'INTL', 'class' => 'bg-cyan-500/90 text-white border-cyan-400'],
            'local' => ['emoji' => '📍', 'label' => 'LOCAL', 'class' => 'bg-pink-500/90 text-white border-pink-400'],
            'dj' => ['emoji' => '🎧', 'label' => 'DJ', 'class' => 'bg-gray-500/90 text-white border-gray-400'],
            'live' => ['emoji' => '🎹', 'label' => 'LIVE', 'class' => 'bg-orange-500/90 text-white border-orange-400'],
        ];
    @endphp

    @if ($featuredEvent)
        <!-- EPIC FEATURED EVENT HERO -->
        <section class="relative -mx-6 mb-10 overflow-hidden">
            <span class="beam"></span>
            
            <!-- Background Image with Parallax Effect -->
            <div class="absolute inset-0 h-full w-full">
                @if ($coverUrl)
                    <div class="h-full w-full bg-cover bg-center blur-2xl opacity-20 scale-110" style="background-image: url('{{ $coverUrl }}')"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/80 to-black"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
                    <!-- Left Content -->
                    <div class="flex flex-col justify-center space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="pill border-white text-white bg-white/10 backdrop-blur">🔥 Evento Destacado</span>
                                @if ($featuredEvent->starts_at && $featuredEvent->starts_at > now())
                                    <span class="pill border-emerald-400 text-emerald-400 bg-emerald-400/10">Próximamente</span>
                                @endif
                            </div>
                            
                            <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl lg:text-6xl">
                                {{ $featuredEvent->title }}
                            </h1>
                            
                            @if ($featuredEvent->headline)
                                <p class="text-xl text-gray-300 leading-relaxed">
                                    {{ $featuredEvent->headline }}
                                </p>
                            @endif

                            <!-- Event Details -->
                            <div class="space-y-3 pt-4">
                                <div class="flex items-center gap-3 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-lg font-semibold text-white">
                                        {{ optional($featuredEvent->starts_at)->translatedFormat('l, d \d\e F \d\e Y - H:i') ?? 'Fecha por confirmar' }}
                                    </span>
                                </div>
                                @if ($featuredEvent->venue || $featuredEvent->city)
                                    <div class="flex items-center gap-3 text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-lg">
                                            {{ $featuredEvent->venue ? $featuredEvent->venue . ', ' : '' }}{{ $featuredEvent->city ?? 'Ubicación por confirmar' }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            @php
                                $hasInternalTickets = $featuredEvent->ticketProducts && $featuredEvent->ticketProducts->isNotEmpty();
                                $eventTicketsUrl = $hasInternalTickets
                                    ? route('events.show', $featuredEvent) . '#tickets'
                                    : $featuredEvent->ticket_url;
                            @endphp

                            <!-- CTA Buttons -->
                            <div class="flex flex-wrap gap-4 pt-4">
                                <a href="{{ route('events.show', $featuredEvent) }}" class="btn btn-primary text-base px-8 py-4">
                                    Ver Detalles del Evento
                                </a>
                                @if ($eventTicketsUrl)
                                    <a href="{{ $eventTicketsUrl }}" @if (! $hasInternalTickets) target="_blank" @endif class="btn btn-ghost text-base px-8 py-4 border-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                        </svg>
                                        Comprar tickets / mesas
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right: Event Poster -->
                    <div class="relative">
                        <div class="card overflow-hidden relative group transform transition duration-500 hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-60 z-10"></div>
                            @if ($coverUrl)
                                <img src="{{ $coverUrl }}" alt="{{ $featuredEvent->title }}" class="h-[600px] w-full object-cover">
                            @else
                                <div class="flex h-[600px] items-center justify-center bg-gradient-to-br from-purple-900/20 to-black text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Floating Info Badge -->
                            <div class="absolute bottom-6 left-6 right-6 z-20 flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-xs uppercase tracking-[0.2em] text-gray-300">Evento</p>
                                    <p class="text-xl font-bold text-white">{{ optional($featuredEvent->starts_at)->format('d M Y') ?? 'Próximamente' }}</p>
                                </div>
                                @if ($hasInternalTickets || $featuredEvent->ticket_url)
                                    <span class="pill border-white text-white bg-white/20 backdrop-blur">Tickets y mesas disponibles</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
        <!-- Fallback Hero without Featured Event -->
        <section class="relative py-16 lg:py-24 text-center space-y-6 fade-up">
            <span class="beam"></span>
            <div class="space-y-4">
                <span class="pill">{{ __('messages.hero.pill') }}</span>
                <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl lg:text-6xl max-w-4xl mx-auto">
                    {{ __('messages.hero.headline') }}
                </h1>
                <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                    {{ __('messages.hero.description') }}
                </p>
                <div class="flex flex-wrap justify-center gap-4 pt-6">
                    <a href="https://www.youtube.com/@LAPSIQUEMEDIA" target="_blank" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M21.58 7.2c-.12-.86-.9-1.54-1.78-1.65C17.34 5.25 14 5.25 12 5.25s-5.34 0-7.8.3c-.88.11-1.66.79-1.78 1.65C2.25 8.77 2.25 10.13 2.25 12s0 3.23.17 4.8c.12.86.9 1.54 1.78 1.65 2.46.3 5.8.3 7.8.3s5.34 0 7.8-.3c.88-.11 1.66-.79 1.78-1.65.17-1.57.17-2.93.17-4.8s0-3.23-.17-4.8ZM10.5 14.7V9.3l4.1 2.7-4.1 2.7Z"/></svg>
                        {{ __('messages.hero.youtube') }}
                    </a>
                    <a href="{{ $instagramUrl }}" target="_blank" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
                        {{ __('messages.hero.instagram') }}
                    </a>
                    <a href="{{ route('events.index') }}" class="btn btn-ghost">Ver Eventos</a>
                </div>
            </div>
        </section>
    @endif

    @if ($videos->isNotEmpty())
        <section class="space-y-5 fade-up">
            <div class="flex items-center justify-between">
                <div>
                    <p class="pill">{{ __('messages.videos_section.pill') }}</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">
                        {{ __('messages.videos_section.title', ['handle' => $youtubeHandle]) }}
                    </h2>
                </div>
                <a href="https://www.youtube.com/{{ ltrim($youtubeHandle, '@') }}/videos" target="_blank" class="btn btn-ghost">{{ __('messages.videos_section.cta') }}</a>
            </div>
            <div class="carousel">
                @foreach ($videos as $video)
                    <a href="{{ route('videos.show', $video) }}" class="card card-animated carousel-card overflow-hidden group">
                        <div class="h-40 w-full bg-gradient-to-br from-black to-zinc-900">
                            @if ($video->thumbnail_url)
                                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="px-5 py-4 space-y-2">
                            <h3 class="text-base font-semibold text-white line-clamp-2">{{ $video->title }}</h3>
                            <div class="flex items-center justify-between text-xs uppercase tracking-[0.18em] text-gray-400">
                                <span>{{ optional($video->published_at)->format('d M Y') ?? 'Fecha' }}</span>
                                <span>{{ __('messages.videos_section.yt') }}</span>
                            </div>
                            @if ($video->location)
                                <div class="flex items-center justify-between text-xs text-gray-300">
                                    <span>{{ $video->location }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif


    <section id="lineup" class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="pill">{{ __('messages.djs_page.pill') }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">{{ __('messages.home.lineup_title') }}</h2>
            </div>
            <a href="{{ route('djs.index') }}" class="btn btn-ghost">{{ __('messages.home.cta_view_all') }}</a>
        </div>
        @if ($djs->isEmpty())
            <div class="card px-6 py-4 text-gray-300">{{ __('messages.home.lineup_empty') }}</div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($djs as $dj)
                    @php
                        $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                        $isHighlighted = $dj->is_highlighted;
                    @endphp
                    <a href="{{ route('djs.show', $dj) }}" class="card card-animated overflow-hidden group relative {{ $isHighlighted ? 'card-highlighted' : '' }}">
                        <div class="h-48 w-full bg-gradient-to-br from-black to-zinc-900 relative">
                            @if ($profile)
                                <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                            @auth
                                <a href="{{ route('filament.admin.resources.djs.edit', $dj) }}" class="absolute top-3 right-3 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white shadow-lg backdrop-blur hover:bg-black/80 transition" title="Editar portada">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2.5 2.5 0 113.536 3.536L12.536 14.5 9 15.5l1-3.536z" />
                                    </svg>
                                </a>
                            @endauth
                            @if ($dj->tags && count($dj->tags) > 0)
                                <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                    @foreach (array_slice($dj->tags, 0, 2) as $tag)
                                        @php
                                            $config = $tagConfig[$tag] ?? ['emoji' => '', 'label' => strtoupper($tag), 'class' => 'bg-white/90 text-black border-white'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider border backdrop-blur-sm {{ $config['class'] }} shadow-lg">
                                            <span>{{ $config['emoji'] }}</span>
                                            <span>{{ $config['label'] }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if ($isHighlighted)
                                <div class="absolute top-3 right-3">
                                    <span class="pill border-yellow-400 text-yellow-400 bg-yellow-400/20 backdrop-blur shadow-lg">⭐ DESTACADO</span>
                                </div>
                            @elseif ($dj->is_featured)
                                <div class="absolute top-3 right-3">
                                    <span class="pill border-white text-white bg-white/20 backdrop-blur shadow-lg">TOP</span>
                                </div>
                            @endif
                        </div>
                        <div class="space-y-2 px-5 py-4">
                            <h3 class="text-lg font-semibold text-white">{{ $dj->name }}</h3>
                            <p class="text-sm text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($dj->bio, 110) }}</p>
                            @if ($dj->tags && count($dj->tags) > 2)
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    @foreach (array_slice($dj->tags, 2) as $tag)
                                        @php
                                            $config = $tagConfig[$tag] ?? ['emoji' => '', 'label' => strtoupper($tag)];
                                        @endphp
                                        <span class="text-[9px] text-gray-500 tracking-wider">{{ $config['emoji'] }} {{ $config['label'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="text-xs uppercase tracking-[0.18em] text-gray-400 pt-1">
                                {{ $dj->instagram_handle ? '@' . $dj->instagram_handle : __('messages.djs_page.instagram_pending') }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@endsection
