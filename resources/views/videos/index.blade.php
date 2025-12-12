@extends('layouts.site')

@section('title', __('messages.videos_page.title') . ' | ' . __('messages.site.brand'))

@section('content')
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
                <a href="{{ route('videos.show', $video) }}" class="card card-animated overflow-hidden group">
                    <div class="h-48 w-full bg-gradient-to-br from-black to-zinc-900">
                        @if ($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @endif
                    </div>
                    <div class="px-5 py-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white line-clamp-2">{{ $video->title }}</h3>
                            @if ($video->is_featured)
                                <span class="pill">{{ __('messages.events_page.top') }}</span>
                            @endif
                        </div>
                        @if ($video->location)
                            <div class="flex items-center justify-between text-xs text-gray-300">
                                <span>{{ $video->location }}</span>
                                @if ($video->maps_url)
                                    <span class="pill">{{ __('messages.videos_page.maps') }}</span>
                                @endif
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
