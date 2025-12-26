@extends('layouts.site')

@section('title', $video->title . ' | ' . __('messages.site.brand'))

@section('content')
    @php
        $embedUrl = 'https://www.youtube.com/embed/' . $video->youtube_id;
        $djs = $video->djs;
        $videoMetaDesc = $video->description ?: 'Mira este DJ set en vivo desde ' . ($video->location ?? 'Riviera Maya');
        $videoOgImage = $video->thumbnail_url ?: asset('images/og-default.jpg');
        
        // Asegurar URL absoluta
        if ($videoOgImage && !str_starts_with($videoOgImage, 'http')) {
            $videoOgImage = url($videoOgImage);
        }

        $djNames = $djs->pluck('name')->join(', ');
        
        $tagConfig = [
            'psique-originals' => ['emoji' => '🎬', 'label' => 'PSIQUE ORIGINALS', 'class' => 'bg-purple-500/90 text-white border-purple-400'],
            'youtube' => ['emoji' => '📺', 'label' => 'YOUTUBE', 'class' => 'bg-red-500/90 text-white border-red-400'],
        ];
    @endphp

@section('meta_title', $video->title . ' - Video | lapsique.media')
@section('meta_description', $videoMetaDesc)
@section('meta_keywords', $video->title . ', DJ set, video en vivo, ' . ($video->location ?? 'Riviera Maya') . ', música electrónica' . ($djNames ? ', ' . $djNames : ''))

@section('og_type', 'video.other')
@section('og_title', $video->title . ' - Video | lapsique.media')
@section('og_description', $videoMetaDesc)
@section('og_image', $videoOgImage)
@section('og_url', route('videos.show', $video))

@section('twitter_title', $video->title . ' - Video | lapsique.media')
@section('twitter_description', $videoMetaDesc)
@section('twitter_image', $videoOgImage)
@section('twitter_url', route('videos.show', $video))
@section('twitter_card', 'player')

@section('canonical_url', route('videos.show', $video))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "{{ $video->title }}",
  "description": "{{ $videoMetaDesc }}",
  "url": "{{ $video->youtube_url }}",
  "thumbnailUrl": "{{ $video->thumbnail_url }}",
  "embedUrl": "{{ $embedUrl }}",
  @if($video->published_at)
  "uploadDate": "{{ $video->published_at->toIso8601String() }}",
  @endif
  @if($video->location)
  "contentLocation": {
    "@type": "Place",
    "name": "{{ $video->location }}"
  },
  @endif
  "publisher": {
    "@type": "Organization",
    "name": "lapsique.media",
    "url": "{{ route('home') }}"
  }
}
</script>
@endpush

    <div class="card overflow-hidden">
        <div class="aspect-video w-full">
            <iframe
                src="{{ $embedUrl }}"
                title="{{ $video->title }}"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                class="h-full w-full"
            ></iframe>
        </div>
        <div class="flex flex-col gap-3 px-6 py-5 md:flex-row md:items-center md:justify-between">
            <div class="space-y-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="pill">{{ __('messages.videos_page.title') }}</p>
                    @if (isset($video->tags) && $video->tags && count($video->tags) > 0)
                        @foreach ($video->tags as $tag)
                            @php
                                $config = $tagConfig[$tag] ?? ['emoji' => '', 'label' => strtoupper($tag), 'class' => 'bg-white/90 text-black border-white'];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold tracking-wider border backdrop-blur-sm {{ $config['class'] }} shadow-lg">
                                <span>{{ $config['emoji'] }}</span>
                                <span>{{ $config['label'] }}</span>
                            </span>
                        @endforeach
                    @endif
                </div>
                <h1 class="text-2xl font-semibold text-white">{{ $video->title }}</h1>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">
                    {{ optional($video->published_at)->format('d M Y') ?? __('messages.event.date') }}
                </p>
                @if ($video->location)
                    <p class="text-sm text-gray-300">{{ $video->location }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ $video->youtube_url }}" target="_blank" class="btn btn-primary">{{ __('messages.hero.youtube') }}</a>
                @if ($video->maps_url)
                    <a href="{{ $video->maps_url }}" target="_blank" class="btn btn-ghost">{{ __('messages.videos_page.view_maps') }}</a>
                @endif
                <a href="{{ $instagramUrl }}" target="_blank" class="btn btn-ghost">{{ __('messages.hero.instagram') }}</a>
            </div>
        </div>
    </div>

    <section class="card p-6 space-y-3">
        <p class="pill">{{ __('messages.videos_page.title') }}</p>
        <p class="text-gray-200 leading-relaxed">{{ $video->description ?: __('messages.videos_page.subtitle') }}</p>
    </section>

    @if ($djs->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="pill">Line up</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">DJs en este video</h2>
                </div>
                <a href="{{ route('djs.index') }}" class="btn btn-ghost">Ver lineup completo</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($djs as $dj)
                    @php
                        $cover = $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                    @endphp
                    <a href="{{ route('djs.show', $dj) }}" class="card card-animated overflow-hidden group">
                        <div class="h-40 w-full bg-gradient-to-br from-black to-zinc-900">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="px-5 py-4 space-y-1">
                            <h3 class="text-base font-semibold text-white group-hover:text-primary-400 transition-colors">{{ $dj->name }}</h3>
                            @if ($dj->instagram_handle)
                                <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ '@' . $dj->instagram_handle }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
