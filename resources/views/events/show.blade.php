@extends('layouts.site')

@section('title', $event->title . ' | ' . __('messages.site.brand'))

@section('content')
    @php
        $cover = match ($event->featured_poster) {
            'vertical' => $event->getFirstMediaUrl('cover_vertical', 'poster_vertical') ?: $event->getFirstMediaUrl('cover_vertical'),
            'cover' => $event->getFirstMediaUrl('cover', 'cover_large') ?: $event->getFirstMediaUrl('cover'),
            default => $event->getFirstMediaUrl('cover_horizontal', 'poster_horizontal')
                ?: $event->getFirstMediaUrl('cover', 'cover_large')
                ?: $event->getFirstMediaUrl('cover'),
        };

        // Asegurar URL absoluta para WhatsApp/OpenGraph
        if ($cover && !str_starts_with($cover, 'http')) {
            $cover = url($cover);
        }

        $eventMetaDesc = $event->headline ?: ($event->description ? \Illuminate\Support\Str::limit(strip_tags($event->description), 155) : 'Evento de música electrónica en ' . ($event->city ?? 'Riviera Maya'));
        $eventOgImage = $cover ?: asset('images/og-default.jpg');
        $eventLocation = ($event->venue ? $event->venue . ', ' : '') . ($event->city ?? '');
    @endphp

@section('meta_title', $event->title . ' - Evento | lapsique.media')
@section('meta_description', $eventMetaDesc)
@section('meta_keywords', $event->title . ', evento electrónico, música electrónica, ' . ($event->city ?? 'Riviera Maya') . ', fiesta techno')

@section('og_type', 'event')
@section('og_title', $event->title . ' - Evento | lapsique.media')
@section('og_description', $eventMetaDesc)
@section('og_image', $eventOgImage)
@section('og_url', route('events.show', $event))

@section('twitter_title', $event->title . ' - Evento | lapsique.media')
@section('twitter_description', $eventMetaDesc)
@section('twitter_image', $eventOgImage)
@section('twitter_url', route('events.show', $event))

@section('canonical_url', route('events.show', $event))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Event",
  "name": "{{ $event->title }}",
  "description": "{{ $eventMetaDesc }}",
  "url": "{{ route('events.show', $event) }}",
  @if($cover)
  "image": "{{ $cover }}",
  @endif
  @if($event->starts_at)
  "startDate": "{{ $event->starts_at->toIso8601String() }}",
  @endif
  @if($eventLocation)
  "location": {
    "@type": "Place",
    "name": "{{ $eventLocation }}",
    "address": "{{ $event->city ?? 'Riviera Maya, México' }}"
  },
  @endif
  "organizer": {
    "@type": "Organization",
    "name": "lapsique.media",
    "url": "{{ route('home') }}"
  }
}
</script>
@endpush

    @php
        $gallery = $event->getMedia('gallery');
        $venueGallery = $event->getMedia('venue_gallery');
        $rawVideo = $event->youtube_url;
        $embedVideo = null;
        if ($rawVideo) {
            $embedVideo = str_replace('watch?v=', 'embed/', $rawVideo);
            $embedVideo = str_replace('youtu.be/', 'www.youtube.com/embed/', $embedVideo);
        }
        $technicalRider = $event->technical_rider ?? [];
        $eventTags = $event->tags ?? [];
    @endphp

    <div class="card relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/40 to-black/80"></div>
        @if ($cover)
            <div class="h-[380px] w-full bg-cover bg-center" style="background-image: url('{{ $cover }}')"></div>
        @else
            <div class="flex h-[380px] items-center justify-center bg-gradient-to-br from-black to-zinc-900 text-gray-500">
                Sube un cover del evento desde el dashboard.
            </div>
        @endif
        <div class="absolute bottom-6 left-6 right-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="space-y-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="pill">{{ __('messages.event.pill') }}</p>
                    @if (!empty($eventTags))
                        @foreach ($eventTags as $tag)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-purple-500/90 to-pink-500/90 text-white border border-white/30 backdrop-blur-sm shadow-lg">
                                {{ $tag }}
                            </span>
                        @endforeach
                    @endif
                </div>
                <h1 class="text-3xl font-semibold text-white">{{ $event->title }}</h1>
                <p class="text-sm uppercase tracking-[0.2em] text-gray-300">
                    {{ optional($event->starts_at)->translatedFormat('d M Y H:i') ?? 'Fecha por definir' }}
                    @if ($event->venue)
                        — {{ $event->venue }} {{ $event->city }}
                    @elseif ($event->city)
                        — {{ $event->city }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($event->ticket_url)
                    <a href="{{ $event->ticket_url }}" target="_blank" class="group relative inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-white text-black font-bold text-sm hover:bg-black hover:text-white transition-all duration-300 shadow-lg hover:shadow-2xl animate-pulse hover:animate-none border-2 border-black hover:border-white overflow-hidden">
                        <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-200%] group-hover:translate-x-[200%] transition-transform duration-1000"></span>
                        <span class="relative z-10">🎫</span>
                        <span class="relative z-10 uppercase tracking-wider">Tickets / RSVP</span>
                    </a>
                @endif
                @if ($rawVideo)
                    <a href="{{ $rawVideo }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/20 text-white font-semibold text-sm hover:bg-white/10 transition">
                        <span>▶️</span>
                        <span>YouTube</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- DESCRIPCIÓN Y LOGÍSTICA -->
    <section class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3 p-6 lg:col-span-2">
            <p class="pill">Descripción</p>
            <p class="text-gray-200 leading-relaxed">{!! nl2br(e($event->description ?? 'Añade una descripción para este show desde el panel.')) !!}</p>
        </div>
        <div class="card space-y-3 p-6">
            <p class="pill">Logística</p>
            <div class="text-sm text-gray-300 space-y-2">
                @if ($event->location)
                    <p><span class="text-gray-400">Venue: </span>{{ $event->location->name }}</p>
                    @if ($event->location->address)
                        <p><span class="text-gray-400">Dirección: </span>{{ $event->location->address }}</p>
                    @endif
                    <p><span class="text-gray-400">Ciudad: </span>{{ $event->city ?? $event->location->city ?? 'Por definir' }}</p>
                    @if ($event->location->maps_url)
                        <a href="{{ $event->location->maps_url }}" target="_blank" class="text-blue-400 hover:text-blue-300 flex items-center gap-1">
                            <span>📍 Ver en Google Maps</span>
                        </a>
                    @endif
                @else
                    <p><span class="text-gray-400">Venue: </span>{{ $event->venue ?? 'Por definir' }}</p>
                    <p><span class="text-gray-400">Ciudad: </span>{{ $event->city ?? 'Por definir' }}</p>
                @endif
                <p><span class="text-gray-400">Fecha: </span>{{ optional($event->starts_at)->format('d M Y H:i') ?? 'Por definir' }}</p>
            </div>
        </div>
    </section>

    <!-- LINEUP -->
    @if ($event->djs->isNotEmpty())
        @php
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
            
            // Agrupar DJs por rol
            $headliners = $event->djs->where('pivot.role', 'headliner');
            $warmups = $event->djs->where('pivot.role', 'warmup');
            $locals = $event->djs->where('pivot.role', 'local');
            
            // Determinar si headliners y warmups van en la misma fila
            $headlinersCount = $headliners->count();
            $sameRow = $headlinersCount < 3;
        @endphp
        <section class="space-y-8">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="pill">Line up</p>
                    <h2 class="text-2xl font-semibold text-white">Artistas confirmados</h2>
                </div>
            </div>

            @if ($sameRow && ($headliners->isNotEmpty() || $warmups->isNotEmpty()))
                <!-- HEADLINERS Y WARMUPS EN LA MISMA FILA -->
                <div class="space-y-4">
                    <div class="flex items-center gap-6">
                        @if ($headliners->isNotEmpty())
                            <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                                <span>⭐</span>
                                <span>Headliners</span>
                            </h3>
                        @endif
                        @if ($warmups->isNotEmpty())
                            <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                                <span>🎵</span>
                                <span>Warmup / Support</span>
                            </h3>
                        @endif
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($headliners as $dj)
                            @php
                                $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                            @endphp
                            <a href="{{ route('djs.show', $dj) }}" class="card card-animated overflow-hidden group relative shadow-lg shadow-yellow-500/20 hover:shadow-yellow-500/40 transition-shadow duration-300">
                                <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/10 via-orange-500/10 to-yellow-500/10 rounded-lg blur-xl"></div>
                                <div class="relative h-64 w-full bg-gradient-to-br from-black to-zinc-900">
                                    @if ($profile)
                                        <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    @endif
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
                                    <div class="absolute top-3 right-3">
                                        <span class="pill border-yellow-400 text-yellow-400 bg-black/50 backdrop-blur text-xs font-bold flex items-center gap-1">
                                            <span>⭐</span>
                                            <span>HEADLINER</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="relative px-4 py-3 space-y-1.5">
                                    <h3 class="text-base font-bold text-white">{{ $dj->name }}</h3>
                                    @if ($dj->pivot->time_slot)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">🕒</span>
                                            <p class="text-xs font-medium text-gray-300">{{ $dj->pivot->time_slot }}</p>
                                        </div>
                                    @endif
                                    @if ($dj->instagram_handle)
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ '@' . $dj->instagram_handle }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                        
                        @foreach ($warmups as $dj)
                            @php
                                $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                            @endphp
                            <a href="{{ route('djs.show', $dj) }}" class="card card-animated overflow-hidden group relative">
                                <div class="h-64 w-full bg-gradient-to-br from-black to-zinc-900 relative">
                                    @if ($profile)
                                        <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    @endif
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
                                    <div class="absolute top-3 right-3">
                                        <span class="pill border-white text-white bg-black/50 backdrop-blur text-xs">WARMUP</span>
                                    </div>
                                </div>
                                <div class="px-4 py-3 space-y-1.5">
                                    <h3 class="text-base font-bold text-white">{{ $dj->name }}</h3>
                                    @if ($dj->pivot->time_slot)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">🕒</span>
                                            <p class="text-xs font-medium text-gray-300">{{ $dj->pivot->time_slot }}</p>
                                        </div>
                                    @endif
                                    @if ($dj->instagram_handle)
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ '@' . $dj->instagram_handle }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- HEADLINERS EN SU PROPIA FILA (3 o más) -->
                @if ($headliners->isNotEmpty())
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                            <span>⭐</span>
                            <span>Headliners</span>
                        </h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($headliners as $dj)
                                @php
                                    $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                                @endphp
                                <a href="{{ route('djs.show', $dj) }}" class="card card-animated overflow-hidden group relative shadow-lg shadow-yellow-500/20 hover:shadow-yellow-500/40 transition-shadow duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/10 via-orange-500/10 to-yellow-500/10 rounded-lg blur-xl"></div>
                                    <div class="relative h-64 w-full bg-gradient-to-br from-black to-zinc-900">
                                        @if ($profile)
                                            <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        @endif
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
                                        <div class="absolute top-3 right-3">
                                            <span class="pill border-yellow-400 text-yellow-400 bg-black/50 backdrop-blur text-xs font-bold flex items-center gap-1">
                                                <span>⭐</span>
                                                <span>HEADLINER</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="relative px-4 py-3 space-y-1.5">
                                        <h3 class="text-base font-bold text-white">{{ $dj->name }}</h3>
                                        @if ($dj->pivot->time_slot)
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs">🕒</span>
                                                <p class="text-xs font-medium text-gray-300">{{ $dj->pivot->time_slot }}</p>
                                            </div>
                                        @endif
                                        @if ($dj->instagram_handle)
                                            <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ '@' . $dj->instagram_handle }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- WARMUPS EN SU PROPIA FILA -->
                @if ($warmups->isNotEmpty())
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                            <span>🎵</span>
                            <span>Warmup / Support</span>
                        </h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($warmups as $dj)
                                @php
                                    $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                                @endphp
                                <a href="{{ route('djs.show', $dj) }}" class="card card-animated overflow-hidden group relative">
                                    <div class="h-64 w-full bg-gradient-to-br from-black to-zinc-900 relative">
                                        @if ($profile)
                                            <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        @endif
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
                                        <div class="absolute top-3 right-3">
                                            <span class="pill border-white text-white bg-black/50 backdrop-blur text-xs">WARMUP</span>
                                        </div>
                                    </div>
                                    <div class="px-4 py-3 space-y-1.5">
                                        <h3 class="text-base font-bold text-white">{{ $dj->name }}</h3>
                                        @if ($dj->pivot->time_slot)
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs">🕒</span>
                                                <p class="text-xs font-medium text-gray-300">{{ $dj->pivot->time_slot }}</p>
                                            </div>
                                        @endif
                                        @if ($dj->instagram_handle)
                                            <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ '@' . $dj->instagram_handle }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <!-- LOCALS SIEMPRE EN SU PROPIA FILA (ABAJO) -->
            @if ($locals->isNotEmpty())
                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                        <span>📍</span>
                        <span>Local Artists</span>
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($locals as $dj)
                            @php
                                $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                            @endphp
                            <a href="{{ route('djs.show', $dj) }}" class="card card-animated overflow-hidden group relative">
                                <div class="h-64 w-full bg-gradient-to-br from-black to-zinc-900 relative">
                                    @if ($profile)
                                        <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    @endif
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
                                    <div class="absolute top-3 right-3">
                                        <span class="pill border-pink-400 text-pink-400 bg-black/50 backdrop-blur text-xs">LOCAL</span>
                                    </div>
                                </div>
                                <div class="px-4 py-3 space-y-1.5">
                                    <h3 class="text-base font-bold text-white">{{ $dj->name }}</h3>
                                    @if ($dj->pivot->time_slot)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">🕒</span>
                                            <p class="text-xs font-medium text-gray-300">{{ $dj->pivot->time_slot }}</p>
                                        </div>
                                    @endif
                                    @if ($dj->instagram_handle)
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ '@' . $dj->instagram_handle }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    <!-- TECHNICAL RIDER -->
    @if (!empty($technicalRider))
        <section class="card p-6 space-y-4 bg-gradient-to-br from-white/5 to-white/0 border-white/20">
            <div class="space-y-2">
                <p class="pill border-cyan-400 text-cyan-400">🎛️ Technical Rider</p>
                <h2 class="text-2xl font-semibold text-white">Equipamiento disponible</h2>
                <p class="text-gray-400 text-sm">Equipos profesionales de audio para garantizar la mejor experiencia</p>
            </div>
            
            @php
                $categorizedRider = collect($technicalRider)->groupBy('category');
            @endphp

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @if ($categorizedRider->has('cdj'))
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span>💿</span>
                            <span>CDJs / Media Players</span>
                        </h3>
                        <div class="space-y-2">
                            @foreach ($categorizedRider['cdj'] as $item)
                                <div class="flex items-center justify-between bg-white/5 rounded-lg px-4 py-3 border border-white/10">
                                    <span class="text-gray-200 font-medium">{{ str_replace(['💿 ', 'Pioneer ', 'Denon '], '', $item['brand_model'] ?? 'N/A') }}</span>
                                    @if (($item['quantity'] ?? 1) > 1)
                                        <span class="bg-cyan-500/20 text-cyan-300 text-xs font-bold px-2 py-1 rounded-full">x{{ $item['quantity'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($categorizedRider->has('mixer'))
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span>🎚️</span>
                            <span>Mixer</span>
                        </h3>
                        <div class="space-y-2">
                            @foreach ($categorizedRider['mixer'] as $item)
                                <div class="flex items-center justify-between bg-white/5 rounded-lg px-4 py-3 border border-white/10">
                                    <span class="text-gray-200 font-medium">{{ str_replace(['🎚️ ', 'Pioneer ', 'Allen & Heath ', 'Denon ', 'Richie Hawtin '], '', $item['brand_model'] ?? 'N/A') }}</span>
                                    @if (($item['quantity'] ?? 1) > 1)
                                        <span class="bg-cyan-500/20 text-cyan-300 text-xs font-bold px-2 py-1 rounded-full">x{{ $item['quantity'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($categorizedRider->has('sound_system'))
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span>🔊</span>
                            <span>Sound System</span>
                        </h3>
                        <div class="space-y-2">
                            @foreach ($categorizedRider['sound_system'] as $item)
                                <div class="flex items-center justify-between bg-white/5 rounded-lg px-4 py-3 border border-white/10">
                                    <span class="text-gray-200 font-medium">{{ str_replace('🔊 ', '', $item['brand_model'] ?? 'N/A') }}</span>
                                    @if (($item['quantity'] ?? 1) > 1)
                                        <span class="bg-cyan-500/20 text-cyan-300 text-xs font-bold px-2 py-1 rounded-full">x{{ $item['quantity'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    <!-- VENUE GALLERY -->
    @if ($venueGallery->isNotEmpty())
        <section class="space-y-4">
            <div class="space-y-2">
                <p class="pill">📍 Venue</p>
                <h2 class="text-2xl font-semibold text-white">Conoce el lugar</h2>
                <p class="text-gray-400">Galería del venue donde será la fiesta</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($venueGallery as $image)
                    <div class="card overflow-hidden group">
                        <img src="{{ $image->getUrl('thumb') }}" alt="Venue" class="h-52 w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- YOUTUBE VIDEO -->
    @if ($embedVideo)
        <section class="card overflow-hidden">
            <div class="aspect-video w-full">
                <iframe
                    src="{{ $embedVideo }}"
                    title="YouTube player"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="h-full w-full"
                ></iframe>
            </div>
            <div class="px-5 py-4">
                <p class="text-sm uppercase tracking-[0.18em] text-gray-400">Set / aftermovie</p>
                <p class="text-lg font-semibold text-white">{{ $event->title }}</p>
            </div>
        </section>
    @endif

    <!-- GUEST LIST FORM -->
    @if ($event->starts_at && $event->starts_at > now())
        <section class="card p-8 bg-gradient-to-br from-white/5 to-white/0 border-white/20">
            <div class="grid gap-8 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <p class="pill border-emerald-400 text-emerald-400">✨ Guest List</p>
                        <h2 class="text-3xl font-bold text-white">¡Regístrate aquí!</h2>
                        <p class="text-gray-300 text-lg">Únete a la lista de invitados para este evento. Sortearemos 10 accesos gratuitos.</p>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-gray-400">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Entrada garantizada a grabaciones de LaPsique.Media</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Sortearemos 10 accesos gratuitos para nuestro evento mensual</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('guestlist.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <input type="text" name="full_name" placeholder="Nombre completo *" class="field" required>
                        <input type="email" name="email" placeholder="Email *" class="field" required>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <input type="tel" name="whatsapp" placeholder="WhatsApp (opcional)" class="field">
                        <input type="text" name="instagram_handle" placeholder="Instagram @usuario" class="field">
                    </div>
                    <div>
                        <select name="gender" class="field">
                            <option value="">Género (opcional)</option>
                            <option value="femenino">Femenino</option>
                            <option value="masculino">Masculino</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <textarea name="notes" placeholder="Notas adicionales (opcional)" class="field" rows="3"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="accepts_emails" id="accepts_emails" required class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-white focus:ring-white">
                        <label for="accepts_emails" class="text-sm text-gray-300">Acepto recibir confirmación y actualizaciones del evento por email *</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-full justify-center text-base py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Registrarme en el Guest List
                    </button>
                </form>
            </div>
        </section>
    @endif

    @if ($gallery->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <p class="pill">📸 Galería</p>
                    <h2 class="text-2xl font-semibold text-white">Fotos del evento</h2>
                </div>
                <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ $gallery->count() }} fotos</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gallery as $image)
                    <div class="card overflow-hidden group">
                        <img src="{{ $image->getUrl('thumb') }}" alt="{{ $event->title }}" class="h-52 w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
