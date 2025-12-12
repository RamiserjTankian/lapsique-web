@extends('layouts.site')

@section('title', 'lapsique.media')

@section('content')
    @php
        $coverUrl = match ($featuredEvent?->featured_poster) {
            'vertical' => $featuredEvent?->getFirstMediaUrl('cover_vertical', 'poster_vertical') ?: $featuredEvent?->getFirstMediaUrl('cover_vertical'),
            'cover' => $featuredEvent?->getFirstMediaUrl('cover', 'cover_large') ?: $featuredEvent?->getFirstMediaUrl('cover'),
            default => $featuredEvent?->getFirstMediaUrl('cover_horizontal', 'poster_horizontal')
                ?: $featuredEvent?->getFirstMediaUrl('cover', 'cover_large')
                ?: $featuredEvent?->getFirstMediaUrl('cover'),
        };
        $rawVideo = $featuredEvent?->youtube_url ?? $highlightDj?->youtube_url ?? null;
    @endphp

    <section class="relative grid items-start gap-10 lg:grid-cols-2 fade-up">
        <span class="beam"></span>
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <span class="pill">{{ __('messages.hero.pill') }}</span>
                @if ($featuredEvent?->is_featured)
                    <span class="pill">{{ __('messages.events_page.top') }}</span>
                @endif
            </div>
            <div class="space-y-3">
                <h1 class="text-3xl font-semibold leading-tight text-white sm:text-4xl lg:text-5xl">
                    {{ __('messages.hero.headline') }}
                </h1>
                <p class="text-lg text-gray-300">
                    {{ __('messages.hero.description') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="https://www.youtube.com/@LAPSIQUEMEDIA" target="_blank" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M21.8 8.001s-.2-1.4-.8-2c-.7-.8-1.6-.8-2-0.9-2.8-.2-7-.2-7-.2h-.1s-4.2 0-7 .2c-.4.1-1.3.1-2 .9-.6.6-.8 2-.8 2S2 9.6 2 11.2v1.6c0 1.6.2 3.2.2 3.2s.2 1.4.8 2c.7.8 1.6.8 2 .9 1.4.1 5.8.2 7 .2s7-.1 7-.1.4 0 1.1-.1c.4-.1 1.3-.1 2-.9.6-.6.8-2 .8-2s.2-1.6.2-3.2v-1.6c0-1.6-.2-3.2-.2-3.2Z"/><path d="m10 14.5 4.8-2.8L10 8.9v5.6Z" fill="#000"/></svg>
                        {{ __('messages.hero.youtube') }}
                    </a>
                    <a href="{{ $instagramUrl }}" target="_blank" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
                        {{ __('messages.hero.instagram') }}
                    </a>
                    <a href="{{ route('djs.index') }}" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 17.5A4.5 4.5 0 0 1 8.5 13H12"/><path d="M15.5 13H12"/><circle cx="12" cy="7" r="3"/><circle cx="18.5" cy="15.5" r="2.5"/><circle cx="5.5" cy="15.5" r="2.5"/></svg>
                        {{ __('messages.hero.view_djs') }}
                    </a>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($featuredEvent)
                    <div class="space-y-1">
                        <p class="text-sm uppercase tracking-[0.3em] text-gray-400">{{ __('messages.hero.next_highlight') }}</p>
                        <p class="text-lg font-semibold text-white">{{ $featuredEvent->title }}</p>
                        <p class="text-sm text-gray-400">
                            {{ optional($featuredEvent->starts_at)->translatedFormat('d M Y H:i') ?? 'Próximamente' }}
                            @if ($featuredEvent->venue)
                                — {{ $featuredEvent->venue }} {{ $featuredEvent->city }}
                            @elseif($featuredEvent->city)
                                — {{ $featuredEvent->city }}
                            @endif
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('events.show', $featuredEvent) }}" class="btn btn-primary">{{ __('messages.hero.see_event') }}</a>
                            @if ($featuredEvent->ticket_url)
                                <a href="{{ $featuredEvent->ticket_url }}" target="_blank" class="btn btn-ghost">{{ __('messages.hero.tickets') }}</a>
                            @endif
                            @if ($rawVideo)
                                <a href="{{ $rawVideo }}" target="_blank" class="btn btn-ghost">{{ __('messages.hero.watch_set') }}</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/30 to-black/70"></div>
            @if ($coverUrl)
                <div class="h-[320px] w-full bg-cover bg-center" style="background-image: url('{{ $coverUrl }}')"></div>
            @else
                <div class="flex h-[320px] items-center justify-center bg-gradient-to-br from-black to-zinc-900 text-gray-500">
                    {{ __('messages.home.cover_placeholder') }}
                </div>
            @endif
            @if ($featuredEvent)
                <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-sm text-gray-200">
                    <div>
                        <p class="font-semibold">{{ $featuredEvent->venue ?? 'Venue por definir' }}</p>
                        <p class="text-gray-300">{{ $featuredEvent->city ?? 'Ciudad' }}</p>
                    </div>
                    <span class="pill">{{ optional($featuredEvent->starts_at)->format('d M') ?? 'Fecha' }}</span>
                </div>
            @endif
        </div>
    </section>

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
                                    @if ($video->maps_url)
                                        <a href="{{ $video->maps_url }}" target="_blank" class="text-white underline">{{ __('messages.videos_section.maps') }}</a>
                                    @endif
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
                    <a href="{{ route('djs.show', $dj) }}" class="card overflow-hidden group">
                        @php
                            $profile = $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                        @endphp
                        <div class="h-48 w-full bg-gradient-to-br from-black to-zinc-900">
                            @if ($profile)
                                <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="space-y-2 px-5 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-white">{{ $dj->name }}</h3>
                                @if ($dj->is_featured)
                                    <span class="pill">{{ __('messages.events_page.top') }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($dj->bio, 110) }}</p>
                            <div class="text-xs uppercase tracking-[0.18em] text-gray-400">
                                {{ $dj->instagram_handle ? '@' . $dj->instagram_handle : __('messages.djs_page.instagram_pending') }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section id="eventos" class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="pill">{{ __('messages.events_page.pill') }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">{{ __('messages.home.events_title') }}</h2>
            </div>
            <a href="{{ route('events.index') }}" class="btn btn-ghost">{{ __('messages.home.cta_view_calendar') }}</a>
        </div>
        @if ($events->isEmpty())
            <div class="card px-6 py-4 text-gray-300">{{ __('messages.home.events_empty') }}</div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($events as $event)
                    @php
                        $eventCover = $event->getFirstMediaUrl('cover', 'thumb') ?: $event->getFirstMediaUrl('cover');
                    @endphp
                    <a href="{{ route('events.show', $event) }}" class="card overflow-hidden group">
                        <div class="h-44 w-full bg-gradient-to-br from-black to-zinc-900">
                            @if ($eventCover)
                                <img src="{{ $eventCover }}" alt="{{ $event->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="px-5 py-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-white">{{ $event->title }}</h3>
                                @if ($event->is_featured)
                                    <span class="pill">Top</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>
                            <div class="flex items-center justify-between text-xs uppercase tracking-[0.18em] text-gray-400">
                                <span>{{ optional($event->starts_at)->format('d M') ?? 'Fecha' }}</span>
                                <span>{{ $event->city ?? 'Ciudad' }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
