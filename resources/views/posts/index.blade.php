@extends('layouts.site')

@section('title', 'Blog | ' . __('messages.site.brand'))

@section('meta_title', 'Blog - ' . __('messages.site.brand'))
@section('meta_description', 'Mantente al día con las últimas noticias de la escena electrónica. Artículos, entrevistas y contenido exclusivo.')
@section('meta_keywords', 'blog electrónico, noticias música electrónica, artículos DJ, escena electrónica, Riviera Maya')

@section('og_type', 'website')
@section('og_title', 'Blog - ' . __('messages.site.brand'))
@section('og_description', 'Mantente al día con las últimas noticias de la escena electrónica.')
@php
    // Intentar obtener imagen del evento destacado probando todas las opciones disponibles
    $postsOgImage = null;
    if ($featuredEvent) {
        // Probar cover con conversión cover_large
        $postsOgImage = $featuredEvent->getFirstMediaUrl('cover', 'cover_large');
        
        // Si no existe, probar cover sin conversión
        if (!$postsOgImage) {
            $postsOgImage = $featuredEvent->getFirstMediaUrl('cover');
        }
        
        // Si no existe, probar cover_horizontal con conversión
        if (!$postsOgImage) {
            $postsOgImage = $featuredEvent->getFirstMediaUrl('cover_horizontal', 'poster_horizontal');
        }
        
        // Si no existe, probar cover_horizontal sin conversión
        if (!$postsOgImage) {
            $postsOgImage = $featuredEvent->getFirstMediaUrl('cover_horizontal');
        }
        
        // Si no existe, probar cover_vertical con conversión
        if (!$postsOgImage) {
            $postsOgImage = $featuredEvent->getFirstMediaUrl('cover_vertical', 'poster_vertical');
        }
        
        // Si no existe, probar cover_vertical sin conversión
        if (!$postsOgImage) {
            $postsOgImage = $featuredEvent->getFirstMediaUrl('cover_vertical');
        }
    }
    
    // Si no hay imagen del evento destacado, usar el primer post
    if (!$postsOgImage && $posts->isNotEmpty()) {
        $postsOgImage = $posts->first()->getFirstMediaUrl('cover', 'large') 
            ?: $posts->first()->getFirstMediaUrl('cover');
    }
    
    // Fallback a imagen por defecto
    $postsOgImage = $postsOgImage ?: asset('images/og-default.jpg');
@endphp
@section('og_image', $postsOgImage)
@section('og_url', route('posts.index'))

@section('twitter_title', 'Blog - ' . __('messages.site.brand'))
@section('twitter_description', 'Mantente al día con las últimas noticias de la escena electrónica.')
@section('twitter_image', $postsOgImage)
@section('twitter_url', route('posts.index'))

@section('canonical_url', route('posts.index'))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Blog",
  "name": "Blog Lapsique Media",
  "description": "Noticias y contenido sobre música electrónica",
  "url": "{{ route('posts.index') }}",
  "publisher": {
    "@type": "Organization",
    "name": "Lapsique Media",
    "url": "{{ route('home') }}"
  }
}
</script>
@endpush

@section('content')
    <div class="flex flex-col gap-3 mb-8">
        <p class="pill">Blog</p>
        <h1 class="text-4xl md:text-5xl font-bold text-white">Últimas noticias y contenido</h1>
        <p class="text-lg text-gray-300">Mantente al día con las últimas noticias de la escena electrónica.</p>
    </div>

    @if ($posts->isEmpty())
        <div class="card px-8 py-12 text-center">
            <div class="flex flex-col items-center gap-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <div>
                    <p class="text-xl font-semibold text-white mb-2">No hay posts publicados todavía</p>
                    <p class="text-gray-400">¡Pronto habrá contenido increíble!</p>
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                @php
                    $cover = $post->getFirstMediaUrl('cover', 'thumb') ?: $post->getFirstMediaUrl('cover');
                @endphp
                <a href="{{ route('posts.show', $post) }}" class="card card-animated overflow-hidden group relative flex flex-col">
                    <div class="h-56 w-full bg-gradient-to-br from-black to-zinc-900 relative overflow-hidden">
                        @if ($cover)
                            <img src="{{ $cover }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                        @else
                            <div class="flex h-full items-center justify-center text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                        @endif
                        <div class="absolute top-3 right-3 bg-black/70 backdrop-blur-sm px-3 py-1 rounded-full text-xs text-white flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ number_format($post->views) }}
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-3 flex-1 flex flex-col">
                        <h3 class="text-xl font-bold text-white line-clamp-2 leading-tight transition-colors group-hover:text-primary">
                            {{ $post->title }}
                        </h3>
                        @if ($post->excerpt)
                            <p class="text-sm text-gray-400 line-clamp-3 leading-relaxed flex-1">{!! nl2br(e($post->excerpt)) !!}</p>
                        @endif
                        <div class="pt-3 border-t border-white/5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center bg-primary text-xs font-bold text-white">
                                    {{ strtoupper(substr($post->author->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-gray-300">{{ $post->author->name ?? 'Admin' }}</span>
                                    <span class="text-xs text-gray-500">{{ optional($post->published_at)->format('d M Y') ?? 'Fecha' }}</span>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 transition-[color,transform] group-hover:translate-x-1 group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
