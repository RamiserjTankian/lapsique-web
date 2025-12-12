@extends('layouts.site')

@section('title', $dj->name . ' | lapsique.media')

@section('content')
    @php
        $profile = $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
        $gallery = $dj->getMedia('gallery');
        $rawVideo = $dj->youtube_url;
        $embedVideo = null;
        if ($rawVideo) {
            $embedVideo = str_replace('watch?v=', 'embed/', $rawVideo);
            $embedVideo = str_replace('youtu.be/', 'www.youtube.com/embed/', $embedVideo);
        }
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card relative overflow-hidden lg:col-span-2">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/40 to-black/80"></div>
            @if ($profile)
                <div class="h-[360px] w-full bg-cover bg-center" style="background-image: url('{{ $profile }}')"></div>
            @else
                <div class="flex h-[360px] items-center justify-center bg-gradient-to-br from-black to-zinc-900 text-gray-500">
                    Sube una foto de perfil para este DJ.
                </div>
            @endif
            <div class="absolute bottom-6 left-6 right-6 flex items-center justify-between">
                <div>
                    <p class="pill mb-2">DJ</p>
                    <h1 class="text-3xl font-semibold text-white">{{ $dj->name }}</h1>
                </div>
                @if ($dj->is_featured)
                    <span class="pill">Destacado</span>
                @endif
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <p class="pill">Contacto</p>
            <div class="space-y-2 text-sm text-gray-300">
                <div class="flex items-center justify-between">
                    <span>Instagram</span>
                    <a href="{{ $dj->instagram_handle ? 'https://instagram.com/' . ltrim($dj->instagram_handle, '@') : '#' }}"
                        target="_blank"
                        class="text-white hover:underline">
                        {{ $dj->instagram_handle ? '@' . ltrim($dj->instagram_handle, '@') : 'Pendiente' }}
                    </a>
                </div>
                <div class="flex items-center justify-between">
                    <span>SoundCloud</span>
                    <a href="{{ $dj->soundcloud_url ?? '#' }}" target="_blank" class="text-white hover:underline">
                        {{ $dj->soundcloud_url ? 'SoundCloud' : 'Pendiente' }}
                    </a>
                </div>
                <div class="flex items-center justify-between">
                    <span>Website</span>
                    <a href="{{ $dj->website_url ?? '#' }}" target="_blank" class="text-white hover:underline">
                        {{ $dj->website_url ? 'Visitar' : 'Pendiente' }}
                    </a>
                </div>
                @if ($rawVideo)
                    <div class="pt-2">
                        <a href="{{ $rawVideo }}" target="_blank" class="btn btn-ghost w-full justify-center">Ver set en YouTube</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <section class="grid gap-6 lg:grid-cols-3">
        <div class="card p-6 space-y-3 lg:col-span-2">
            <p class="pill">Bio</p>
            <p class="text-gray-200 leading-relaxed">{{ $dj->bio ?? 'Agrega la biografía en el dashboard para contar la historia de este DJ.' }}</p>
        </div>

        @if ($embedVideo)
            <div class="card overflow-hidden">
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
                    <p class="text-sm uppercase tracking-[0.18em] text-gray-400">Set grabado</p>
                    <p class="text-lg font-semibold text-white">{{ $dj->name }}</p>
                </div>
            </div>
        @endif
    </section>

    @if ($gallery->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-white">Galería</h2>
                <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ $gallery->count() }} fotos</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gallery as $image)
                    <div class="card overflow-hidden">
                        <img src="{{ $image->getUrl('thumb') }}" alt="{{ $dj->name }}" class="h-52 w-full object-cover">
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
