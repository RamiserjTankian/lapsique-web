@extends('layouts.site')

@section('title', $dj->name . ' | lapsique.media')

@section('content')
    @php
        $profile = $dj->getFirstMediaUrl('profile', 'hero') ?: $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile');
        
        // Asegurar URL absoluta
        if ($profile && !str_starts_with($profile, 'http')) {
            $profile = url($profile);
        }

        $djMetaDesc = $dj->bio ? \Illuminate\Support\Str::limit(strip_tags($dj->bio), 155) : 'Conoce a ' . $dj->name . ', DJ de la escena electrónica en lapsique.media';
        $djOgImage = $profile ?: asset('images/og-default.jpg');
    @endphp

@section('meta_title', $dj->name . ' - DJ | lapsique.media')
@section('meta_description', $djMetaDesc)
@section('meta_keywords', $dj->name . ', DJ, música electrónica, techno, house, Riviera Maya, sets en vivo')

@section('og_type', 'profile')
@section('og_title', $dj->name . ' - DJ | lapsique.media')
@section('og_description', $djMetaDesc)
@section('og_image', $djOgImage)
@section('og_url', route('djs.show', $dj))

@section('twitter_title', $dj->name . ' - DJ | lapsique.media')
@section('twitter_description', $djMetaDesc)
@section('twitter_image', $djOgImage)
@section('twitter_url', route('djs.show', $dj))

@section('canonical_url', route('djs.show', $dj))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Person",
  "name": "{{ $dj->name }}",
  "description": "{{ $djMetaDesc }}",
  "url": "{{ route('djs.show', $dj) }}",
  @if($profile)
  "image": "{{ $profile }}",
  @endif
  @if($dj->instagram_handle)
  "sameAs": [
    "https://www.instagram.com/{{ $dj->instagram_handle }}"
  ],
  @endif
  "jobTitle": "DJ"
}
</script>
@endpush

    @php
        $gallery = $dj->getMedia('gallery');
        $rawVideo = $dj->youtube_url;
        $embedVideo = null;
        $videos = $dj->videos;
        if ($rawVideo) {
            $embedVideo = str_replace('watch?v=', 'embed/', $rawVideo);
            $embedVideo = str_replace('youtu.be/', 'www.youtube.com/embed/', $embedVideo);
        }
        
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

    {{-- Card con imagen y nombre - 12 columnas (ancho completo) --}}
    <div class="card relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/40 to-black/80"></div>
        @if ($profile)
            <div class="h-[360px] w-full bg-cover bg-center" style="background-image: url('{{ $profile }}')"></div>
        @else
            <div class="flex h-[360px] items-center justify-center bg-gradient-to-br from-black to-zinc-900 text-gray-500">
                Sube una foto de perfil para este DJ.
            </div>
        @endif
        <div class="absolute bottom-6 left-6 right-6 flex items-center justify-between">
            <div class="space-y-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="pill">DJ</p>
                    @if ($dj->is_featured)
                        <span class="pill border-white text-white bg-white/20 backdrop-blur">Destacado</span>
                    @endif
                </div>
                <h1 class="text-3xl font-semibold text-white">{{ $dj->name }}</h1>
                @if ($dj->tags && count($dj->tags) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($dj->tags as $tag)
                            @php
                                $config = $tagConfig[$tag] ?? ['emoji' => '', 'label' => strtoupper($tag), 'class' => 'bg-white/90 text-black border-white'];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold tracking-wider border backdrop-blur-sm {{ $config['class'] }} shadow-lg">
                                <span>{{ $config['emoji'] }}</span>
                                <span>{{ $config['label'] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            @auth
                <a href="{{ route('filament.admin.resources.djs.edit', $dj) }}" class="btn btn-ghost">
                    Ajustar portada
                </a>
            @endauth
        </div>
        @auth
            <a href="{{ route('filament.admin.resources.djs.edit', $dj) }}" class="absolute top-3 right-3 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white shadow-lg backdrop-blur hover:bg-black/80 transition" title="Editar portada">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2.5 2.5 0 113.536 3.536L12.536 14.5 9 15.5l1-3.536z" />
                </svg>
            </a>
        @endauth
    </div>

    {{-- Videos relacionados - PRIORIDAD --}}
    @if ($videos->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="pill">DJ Sets</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Videos relacionados</h2>
                </div>
                <a href="{{ route('videos.index') }}" class="btn btn-ghost">Ver todos los videos</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($videos as $video)
                    <a href="{{ route('videos.show', $video) }}" class="card card-animated overflow-hidden group">
                        <div class="h-44 w-full bg-gradient-to-br from-black to-zinc-900 relative">
                            @if ($video->thumbnail_url)
                                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute top-3 left-3">
                                <span class="pill border-white text-white bg-white/10 backdrop-blur">{{ optional($video->published_at)->format('d M Y') ?? 'Próximamente' }}</span>
                            </div>
                        </div>
                        <div class="px-5 py-4 space-y-2">
                            <h3 class="text-lg font-semibold text-white line-clamp-2 group-hover:text-primary-400 transition-colors">
                                {{ $video->title }}
                            </h3>
                            @if ($video->location)
                                <p class="text-sm text-gray-400">{{ $video->location }}</p>
                            @endif
                            <div class="flex items-center justify-between text-xs uppercase tracking-[0.18em] text-gray-500 pt-1">
                                <span>Video</span>
                                <span>YouTube</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Bio y Contacto optimizados --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Bio - Ocupa 2 columnas --}}
        <div class="card p-6 space-y-3 md:col-span-2">
            <p class="pill">Bio</p>
            @php
                $bioText = $dj->bio ?? 'Agrega la biografía en el dashboard para contar la historia de este DJ.';
                $bioLength = strlen($bioText);
                $showReadMore = $bioLength > 500;
                $shortBio = $showReadMore ? substr($bioText, 0, 500) . '...' : $bioText;
            @endphp
            <p class="text-gray-200 leading-relaxed" id="bio-text">{!! nl2br(e($shortBio)) !!}</p>
            @if ($showReadMore)
                <button onclick="openBioModal()" class="text-primary-400 hover:text-primary-300 text-sm font-semibold flex items-center gap-1">
                    <span>Leer más</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @endif
        </div>

        {{-- Contacto - Ocupa 1 columna, más compacto --}}
        <div class="card p-6 space-y-3">
            <p class="pill">Contacto</p>
            <div class="space-y-3 text-sm">
                @if ($dj->instagram_handle)
                    @php
                        $instagramHandle = ltrim($dj->instagram_handle, '@');
                    @endphp
                    <a href="https://instagram.com/{{ $instagramHandle }}" target="_blank" class="flex items-center gap-2 text-gray-300 hover:text-white transition group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="4" y="4" width="16" height="16" rx="4"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/>
                        </svg>
                        <span class="text-xs">{{ '@' . $instagramHandle }}</span>
                    </a>
                @endif
                
                @if ($dj->soundcloud_url)
                    <a href="{{ $dj->soundcloud_url }}" target="_blank" class="flex items-center gap-2 text-gray-300 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1.5 12.5v2l.5 2 .5-2v-2l-.5-2zm1.5 0v2l.5 2 .5-2v-2l-.5-2zm1.5-2v4l.5 2 .5-2v-4l-.5-2zm1.5 0v4l.5 2 .5-2v-4l-.5-2zm1.5 0v4l.5 2 .5-2v-4l-.5-2zm1.5-2v6l.5 2 .5-2v-6l-.5-2zm1.5 0v6l.5 2 .5-2v-6l-.5-2zm1.5 0v6l.5 2 .5-2v-6l-.5-2zm1.5-2v8l.5 2 .5-2v-8l-.5-2zm1.5 2v6l.5 2 .5-2v-6l-.5-2zm1.5 0v6h6c1.1 0 2-.9 2-2s-.9-2-2-2c-.2-3.3-2.9-6-6.2-6-.4 0-.8 0-1.2.1l.4 2z"/>
                        </svg>
                        <span class="text-xs">SoundCloud</span>
                    </a>
                @endif
                
                @if ($dj->website_url)
                    <a href="{{ $dj->website_url }}" target="_blank" class="flex items-center gap-2 text-gray-300 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span class="text-xs">Website</span>
                    </a>
                @endif
                
                @if ($rawVideo)
                    <a href="{{ $rawVideo }}" target="_blank" class="btn btn-primary w-full justify-center text-xs py-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21.58 7.2c-.12-.86-.9-1.54-1.78-1.65C17.34 5.25 14 5.25 12 5.25s-5.34 0-7.8.3c-.88.11-1.66.79-1.78 1.65C2.25 8.77 2.25 10.13 2.25 12s0 3.23.17 4.8c.12.86.9 1.54 1.78 1.65 2.46.3 5.8.3 7.8.3s5.34 0 7.8-.3c.88-.11 1.66-.79 1.78-1.65.17-1.57.17-2.93.17-4.8s0-3.23-.17-4.8ZM10.5 14.7V9.3l4.1 2.7-4.1 2.7Z"/>
                        </svg>
                        Ver Set
                    </a>
                @endif
            </div>
        </div>
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

    {{-- Modal de Bio Completa --}}
    <div id="bio-modal" class="fixed inset-0 z-[100] items-center justify-center bg-black/70 backdrop-blur-sm px-4" style="display: none;">
        <div class="card relative w-full max-w-2xl max-h-[80vh] overflow-y-auto">
            <div class="sticky top-0 right-0 flex justify-end p-4 bg-gradient-to-b from-black/50 to-transparent z-10">
                <button onclick="closeBioModal()" class="flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80 transition backdrop-blur">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-8 pb-8 space-y-4">
                <div class="space-y-2">
                    <p class="pill">Bio Completa</p>
                    <h3 class="text-2xl font-semibold text-white">{{ $dj->name }}</h3>
                </div>
                <div class="text-gray-200 leading-relaxed whitespace-pre-line">
                    {{ $dj->bio ?? '' }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function openBioModal() {
            const modal = document.getElementById('bio-modal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeBioModal() {
            const modal = document.getElementById('bio-modal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('bio-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeBioModal();
            }
        });

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBioModal();
            }
        });
    </script>
@endsection
