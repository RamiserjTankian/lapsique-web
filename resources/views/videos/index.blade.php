@extends('layouts.site')

@section('title', __('messages.videos_page.title') . ' | ' . __('messages.site.brand'))

@section('meta_title', 'Videos y Sets - lapsique.media')
@section('meta_description', 'Toda la librería audiovisual de lapsique.media. DJ sets en vivo, aftermovies y contenido exclusivo de la escena electrónica en Riviera Maya.')
@section('meta_keywords', 'videos electrónicos, DJ sets, aftermovies, sets en vivo, YouTube, música electrónica, Riviera Maya')

@section('og_type', 'website')
@section('og_title', 'Videos y Sets - lapsique.media')
@section('og_description', 'Toda la librería audiovisual de lapsique.media. DJ sets en vivo, aftermovies y contenido exclusivo.')
@php
    $videosOgImage = $videos->isNotEmpty() && $videos->first()->thumbnail_url ? 
        $videos->first()->thumbnail_url : 
        asset('images/og-default.jpg');
@endphp
@section('og_image', $videosOgImage)
@section('og_url', route('videos.index'))

@section('twitter_title', 'Videos y Sets - lapsique.media')
@section('twitter_description', 'Toda la librería audiovisual de lapsique.media. DJ sets en vivo, aftermovies y contenido exclusivo.')
@section('twitter_image', $videosOgImage)
@section('twitter_url', route('videos.index'))

@section('canonical_url', route('videos.index'))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Videos y Sets",
  "description": "Librería audiovisual completa de DJ sets y aftermovies",
  "url": "{{ route('videos.index') }}"
}
</script>
@endpush

@section('content')
    @php
        $tagConfig = [
            'psique-originals' => ['emoji' => '🎬', 'label' => 'PSIQUE', 'class' => 'bg-purple-500/90 text-white border-purple-400'],
            'youtube' => ['emoji' => '📺', 'label' => 'YOUTUBE', 'class' => 'bg-red-500/90 text-white border-red-400'],
        ];
    @endphp

    <div class="flex flex-col gap-3">
        <p class="pill">{{ __('messages.videos_page.title') }}</p>
        <h1 class="text-3xl font-semibold text-white">{{ __('messages.videos_page.title') }}</h1>
        <p class="text-gray-300">{{ __('messages.videos_page.subtitle') }}</p>
        <div class="flex gap-3">
            <a href="https://www.youtube.com/@LAPSIQUEMEDIA" target="_blank" class="btn btn-primary">{{ __('messages.hero.youtube') }}</a>
            <a href="{{ config('lapsique.instagram_url') }}" target="_blank" class="btn btn-ghost">{{ __('messages.hero.instagram') }}</a>
        </div>
    </div>

    @if ($videos->isEmpty())
        <div class="card px-6 py-4 text-gray-300">{{ __('messages.videos_page.empty') }}</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($videos as $video)
                <a href="{{ route('videos.show', $video) }}" class="card card-animated overflow-hidden group relative">
                    <div class="h-48 w-full bg-gradient-to-br from-black to-zinc-900 relative">
                        @if ($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @endif
                        @if ($video->tags && count($video->tags) > 0)
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                @foreach ($video->tags as $tag)
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
                    </div>
                    <div class="px-5 py-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white line-clamp-2">{{ $video->title }}</h3>
                            @if ($video->is_featured)
                                <span class="pill">{{ __('messages.events_page.top') }}</span>
                            @endif
                        </div>
                        @if ($video->djs->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($video->djs as $dj)
                                    <span class="pill border-white text-white bg-white/10 backdrop-blur text-[11px] px-3 py-1">
                                        {{ $dj->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @if ($video->location)
                            <div class="flex items-center justify-between text-xs text-gray-300">
                                <span>{{ $video->location }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between text-xs uppercase tracking-[0.18em] text-gray-400">
                            <span>{{ optional($video->published_at)->format('d M Y') ?? __('messages.event.date') }}</span>
                            <span>{{ __('messages.videos_section.yt') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
