@extends('layouts.site')

@section('title', $post->title . ' | Blog | ' . __('messages.site.brand'))

@section('content')
    @php
        $cover = $post->getFirstMediaUrl('cover', 'large') ?: $post->getFirstMediaUrl('cover');
        
        // Asegurar URL absoluta
        if ($cover && !str_starts_with($cover, 'http')) {
            $cover = url($cover);
        }

        $gallery = $post->getMedia('gallery');
        $postMetaDesc = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 155);
        $postOgImage = $cover ?: asset('images/og-default.jpg');
        $authorName = $post->author->name ?? 'Trascendental';
    @endphp

@section('meta_title', $post->title . ' - Blog | Trascendental')
@section('meta_description', $postMetaDesc)
@section('meta_keywords', $post->title . ', blog, música electrónica, noticias, Trascendental')

@section('og_type', 'article')
@section('og_title', $post->title . ' - Blog | Trascendental')
@section('og_description', $postMetaDesc)
@section('og_image', $postOgImage)
@section('og_url', route('posts.show', $post))

@section('twitter_title', $post->title . ' - Blog | Trascendental')
@section('twitter_description', $postMetaDesc)
@section('twitter_image', $postOgImage)
@section('twitter_url', route('posts.show', $post))

@section('canonical_url', route('posts.show', $post))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{{ $post->title }}",
  "description": "{{ $postMetaDesc }}",
  "url": "{{ route('posts.show', $post) }}",
  @if($cover)
  "image": "{{ $cover }}",
  @endif
  @if($post->published_at)
  "datePublished": "{{ $post->published_at->toIso8601String() }}",
  @endif
  "dateModified": "{{ $post->updated_at->toIso8601String() }}",
  "author": {
    "@type": "Person",
    "name": "{{ $authorName }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Trascendental",
    "url": "{{ route('home') }}"
  }
}
</script>
@endpush

    <div class="mb-6">
        <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="font-medium">Volver al Blog</span>
        </a>
    </div>

    <div class="card relative overflow-hidden">
        @if ($cover)
            <div class="h-[420px] md:h-[520px] w-full bg-cover bg-center relative" style="background-image: url('{{ $cover }}')">
                <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/50 to-black/90"></div>
            </div>
        @else
            <div class="flex h-[420px] md:h-[520px] items-center justify-center bg-gradient-to-br from-black to-zinc-900 text-gray-600 relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/40 to-black/80"></div>
            </div>
        @endif
        <div class="absolute bottom-8 left-8 right-8 max-w-4xl">
            <p class="pill mb-3">Blog</p>
            <h1 class="text-3xl md:text-5xl font-bold text-white mb-4 leading-tight drop-shadow-lg">{{ $post->title }}</h1>
            <div class="flex flex-wrap items-center gap-3 md:gap-5">
                <div class="flex items-center gap-2 bg-black/50 backdrop-blur-sm px-4 py-2 rounded-full">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($post->author->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="text-sm font-medium text-white">{{ $post->author->name ?? 'Admin' }}</span>
                </div>
                <div class="flex items-center gap-2 bg-black/50 backdrop-blur-sm px-4 py-2 rounded-full text-sm text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ optional($post->published_at)->translatedFormat('d M Y') ?? 'Fecha' }}</span>
                </div>
                <div class="flex items-center gap-2 bg-black/50 backdrop-blur-sm px-4 py-2 rounded-full text-sm text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>{{ number_format($post->views) }} vistas</span>
                </div>
            </div>
        </div>
    </div>

    <article class="card p-8 space-y-6">
        @if ($post->excerpt)
            <div class="text-xl text-gray-200 leading-relaxed font-semibold border-l-4 border-primary-500 pl-6 py-2 bg-primary-500/5 rounded-r-lg">
                {!! nl2br(e($post->excerpt)) !!}
            </div>
        @endif

        <div class="prose prose-invert prose-lg max-w-none 
                    prose-headings:text-white prose-headings:font-bold
                    prose-p:text-gray-300 prose-p:leading-relaxed
                    prose-a:text-primary-400 prose-a:no-underline hover:prose-a:text-primary-300
                    prose-strong:text-white prose-strong:font-semibold
                    prose-ul:text-gray-300 prose-ol:text-gray-300
                    prose-blockquote:border-l-primary-500 prose-blockquote:bg-primary-500/5 prose-blockquote:text-gray-200
                    prose-code:text-primary-400 prose-code:bg-white/5 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded
                    prose-pre:bg-black/40 prose-pre:border prose-pre:border-white/10">
            {!! $post->content !!}
        </div>
    </article>

    @if ($gallery->isNotEmpty())
        <section class="space-y-6">
            <div>
                <p class="pill">Imágenes</p>
                <h2 class="mt-2 text-3xl font-bold text-white">Galería</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gallery as $image)
                    <div class="card overflow-hidden group cursor-pointer">
                        <img src="{{ $image->getUrl('thumb') }}" alt="{{ $post->title }}" class="h-64 w-full object-cover transition duration-500 group-hover:scale-110">
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($relatedPosts->isNotEmpty())
        <section class="space-y-6">
            <div>
                <p class="pill">Más contenido</p>
                <h2 class="mt-2 text-3xl font-bold text-white">Posts relacionados</h2>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedPosts as $relatedPost)
                    @php
                        $relatedCover = $relatedPost->getFirstMediaUrl('cover', 'thumb') ?: $relatedPost->getFirstMediaUrl('cover');
                    @endphp
                    <a href="{{ route('posts.show', $relatedPost) }}" class="card card-animated overflow-hidden group">
                        <div class="h-48 w-full bg-gradient-to-br from-black to-zinc-900 relative overflow-hidden">
                            @if ($relatedCover)
                                <img src="{{ $relatedCover }}" alt="{{ $relatedPost->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            @endif
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            <h3 class="text-lg font-bold text-white line-clamp-2 leading-tight group-hover:text-primary-400 transition-colors">
                                {{ $relatedPost->title }}
                            </h3>
                            <div class="flex items-center justify-between text-xs text-gray-400 pt-2 border-t border-white/5">
                                <span>{{ optional($relatedPost->published_at)->format('d M Y') ?? 'Fecha' }}</span>
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ number_format($relatedPost->views) }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
