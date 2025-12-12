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
        $gallery = $event->getMedia('gallery');
        $rawVideo = $event->youtube_url;
        $embedVideo = null;
        if ($rawVideo) {
            $embedVideo = str_replace('watch?v=', 'embed/', $rawVideo);
            $embedVideo = str_replace('youtu.be/', 'www.youtube.com/embed/', $embedVideo);
        }
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
        <div class="absolute bottom-6 left-6 right-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="pill mb-2">{{ __('messages.event.pill') }}</p>
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
            <div class="flex flex-wrap gap-3">
                @if ($event->ticket_url)
                    <a href="{{ $event->ticket_url }}" target="_blank" class="btn btn-primary">Tickets / RSVP</a>
                @endif
                @if ($rawVideo)
                    <a href="{{ $rawVideo }}" target="_blank" class="btn btn-ghost">Ver en YouTube</a>
                @endif
            </div>
        </div>
    </div>

    <section class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3 p-6 lg:col-span-2">
            <p class="pill">Descripción</p>
            <p class="text-gray-200 leading-relaxed">{{ $event->description ?? 'Añade una descripción para este show desde el panel.' }}</p>
        </div>
        <div class="card space-y-3 p-6">
            <p class="pill">Logística</p>
            <div class="text-sm text-gray-300 space-y-2">
                <p><span class="text-gray-400">Venue: </span>{{ $event->venue ?? 'Por definir' }}</p>
                <p><span class="text-gray-400">Ciudad: </span>{{ $event->city ?? 'Por definir' }}</p>
                <p><span class="text-gray-400">Fecha: </span>{{ optional($event->starts_at)->format('d M Y H:i') ?? 'Por definir' }}</p>
                <p><span class="text-gray-400">Registros: </span>{{ $event->guests->count() }} invitados en lista</p>
            </div>
        </div>
    </section>

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

    @if ($gallery->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-white">Galería</h2>
                <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ $gallery->count() }} fotos</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gallery as $image)
                    <div class="card overflow-hidden">
                        <img src="{{ $image->getUrl('thumb') }}" alt="{{ $event->title }}" class="h-52 w-full object-cover">
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
    @if ($event->djs->isNotEmpty())
        <section class="card p-6 space-y-3">
            <p class="pill">Line up</p>
            <div class="flex flex-wrap gap-3">
                @foreach ($event->djs as $dj)
                    <a href="{{ route('djs.show', $dj) }}" class="btn btn-ghost">{{ $dj->name }}</a>
                @endforeach
            </div>
        </section>
    @endif
