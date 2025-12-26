@extends('layouts.site')

@section('title', __('messages.djs_page.pill') . ' | ' . __('messages.site.brand'))

@section('meta_title', 'DJs y Artistas - lapsique.media')
@section('meta_description', 'Conoce los DJs que han grabado sets con Lapsique. Talento local e internacional de la escena electrónica en Riviera Maya.')
@section('meta_keywords', 'DJs, artistas electrónicos, techno DJs, house DJs, Riviera Maya, Playa del Carmen, talento local')

@section('og_type', 'website')
@section('og_title', 'DJs y Artistas - lapsique.media')
@section('og_description', 'Conoce los DJs que han grabado sets con Lapsique. Talento local e internacional de la escena electrónica.')
@php
    $djsOgImage = $djs->isNotEmpty() && $djs->first()->getFirstMediaUrl('profile', 'hero') ? 
        $djs->first()->getFirstMediaUrl('profile', 'hero') : 
        asset('images/og-default.jpg');
@endphp
@section('og_image', $djsOgImage)
@section('og_url', route('djs.index'))

@section('twitter_title', 'DJs y Artistas - lapsique.media')
@section('twitter_description', 'Conoce los DJs que han grabado sets con Lapsique.')
@section('twitter_image', $djsOgImage)
@section('twitter_url', route('djs.index'))

@section('canonical_url', route('djs.index'))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "DJs y Artistas",
  "description": "Conoce los DJs que han grabado sets con Lapsique",
  "url": "{{ route('djs.index') }}"
}
</script>
@endpush

@section('content')
    <div class="flex flex-col gap-3">
        <p class="pill">{{ __('messages.djs_page.pill') }}</p>
        <h1 class="text-3xl font-semibold text-white">{{ __('messages.djs_page.title') }}</h1>
        <p class="text-gray-300">DJs que han grabado sets con Lapsique.</p>
    </div>

    @if ($djs->isEmpty())
        <div class="card px-6 py-4 text-gray-300">{{ __('messages.djs_page.empty') }}</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($djs as $dj)
                @php
                    $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                    $isHighlighted = $dj->is_highlighted;
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
                        <!-- Tags flotantes en la imagen -->
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
                        <!-- Tags adicionales abajo -->
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

        <div class="mt-8">
            {{ $djs->links() }}
        </div>
    @endif
@endsection
