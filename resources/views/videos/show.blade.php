@extends('layouts.site')

@section('title', $video->title . ' | ' . __('messages.site.brand'))

@section('content')
    @php
        $embedUrl = 'https://www.youtube.com/embed/' . $video->youtube_id;
    @endphp

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
            <div>
                <p class="pill mb-2">{{ __('messages.videos_page.title') }}</p>
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
@endsection
