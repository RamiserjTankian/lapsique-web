@extends('layouts.site')

@section('title', __('messages.portfolio_page.title') . ' | ' . __('messages.site.brand'))
@section('meta_title', __('messages.portfolio_page.title') . ' - lapsique.media')
@section('meta_description', __('messages.portfolio_page.subtitle'))
@section('meta_keywords', 'portafolio audiovisual, fotografía, aftermovies, reels, galería, Riviera Maya')

@php
    $ogImage = asset('images/og-default.jpg');
    
    if (isset($featuredItem)) {
        // Priorizar poster, luego asset
        $imageUrl = $featuredItem->poster_url ?: $featuredItem->asset_url;
        
        if ($imageUrl) {
            // Si ya es una URL absoluta (http/https), usarla directamente
            if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
                $ogImage = $imageUrl;
            } else {
                // Convertir URL relativa a absoluta
                $ogImage = url($imageUrl);
            }
        }
    }
@endphp

@section('og_type', 'website')
@section('og_title', __('messages.portfolio_page.title') . ' - lapsique.media')
@section('og_description', __('messages.portfolio_page.subtitle'))
@section('og_image', $ogImage)
@section('og_url', route('portfolio.index'))

@section('twitter_title', __('messages.portfolio_page.title') . ' - lapsique.media')
@section('twitter_description', __('messages.portfolio_page.subtitle'))
@section('twitter_image', $ogImage)
@section('twitter_url', route('portfolio.index'))

@section('canonical_url', route('portfolio.index'))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "{{ __('messages.portfolio_page.title') }}",
  "description": "{{ __('messages.portfolio_page.subtitle') }}",
  "url": "{{ route('portfolio.index') }}"
}
</script>
@endpush

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-3">
            <p class="pill">{{ __('messages.portfolio_page.pill') }}</p>
            <h1 class="text-3xl font-semibold text-white">{{ __('messages.portfolio_page.title') }}</h1>
            <p class="text-gray-300">{{ __('messages.portfolio_page.subtitle') }}</p>
        </div>

        <div class="flex flex-wrap gap-2 text-xs uppercase tracking-[0.2em]">
            <button class="btn btn-primary" data-filter="all">{{ __('messages.portfolio_page.filters.all') }}</button>
            <button class="btn btn-ghost" data-filter="photo">{{ __('messages.portfolio_page.filters.photo') }}</button>
            <button class="btn btn-ghost" data-filter="video">{{ __('messages.portfolio_page.filters.video') }}</button>
            @if (!empty($availableTags))
                @php
                    $tagLabels = [
                        'barbershop' => '💈',
                        'food' => '🍔',
                        'nightlife' => '🌃',
                        'events' => '🎉',
                        'fitness' => '💪',
                        'beauty' => '💄',
                        'fashion' => '👔',
                        'music' => '🎵',
                        'art' => '🎨',
                        'travel' => '✈️',
                        'lifestyle' => '🌟',
                        'automotive' => '🚗',
                        'real-estate' => '🏠',
                        'tech' => '💻',
                        'sports' => '⚽',
                        'wedding' => '💍',
                        'corporate' => '🏢',
                        'hospitality' => '🏨',
                    ];
                @endphp
                @foreach ($availableTags as $tag)
                    @php
                        $emoji = $tagLabels[$tag] ?? '🏷️';
                        $label = ucfirst(str_replace('-', ' ', $tag));
                    @endphp
                    <button class="btn btn-ghost" data-filter-tag="{{ $tag }}">
                        <span>{{ $emoji }}</span>
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
            @endif
        </div>

        @if ($items->isEmpty())
            <div class="card px-6 py-4 text-gray-300">{{ __('messages.portfolio_page.empty') }}</div>
        @else
            <div id="portfolio-grid" class="grid auto-rows-[minmax(12rem,auto)] gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:grid-flow-row-dense">
                @foreach ($items as $item)
                    @php
                        $isVideo = $item->type === 'video';
                        $orientation = $item->orientation === 'vertical' ? 'vertical' : 'horizontal';
                        $spanClass = $orientation === 'vertical' ? 'row-span-2' : 'row-span-1';
                        $spanClass .= $orientation === 'horizontal' && $isVideo ? ' lg:col-span-2' : '';
                        $assetUrl = $item->asset_url;
                        $posterUrl = $item->poster_url;
                        $media = $item->getFirstMedia('asset');
                        $videoMime = $media?->mime_type ?? 'video/mp4';
                        $isYoutube = $item->source === 'youtube' && $item->youtube_id;
                        $embedUrl = $item->embed_url;
                    @endphp

                    <button
                        type="button"
                        class="portfolio-card card-animated group relative {{ $spanClass }} {{ $orientation === 'vertical' ? 'min-h-[28rem]' : '' }} overflow-hidden rounded-3xl border border-white/10 bg-black/30 text-left"
                        data-portfolio-card
                        data-type="{{ $item->type }}"
                        data-orientation="{{ $orientation }}"
                        data-provider="{{ $isYoutube ? 'youtube' : 'upload' }}"
                        data-title="{{ $item->title ?? '' }}"
                        data-caption="{{ $item->caption ?? '' }}"
                        data-tags="{{ json_encode($item->tags ?? []) }}"
                        data-src="{{ $isYoutube ? '' : $assetUrl }}"
                        data-embed="{{ $embedUrl ?? '' }}"
                        data-poster="{{ $posterUrl }}"
                    >
                        <div class="absolute inset-0">
                            @if ($isVideo)
                                @if ($isYoutube)
                                    <img src="{{ $posterUrl }}" alt="{{ $item->title ?? 'Video' }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <video
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        muted
                                        loop
                                        playsinline
                                        preload="metadata"
                                        poster="{{ $posterUrl }}"
                                        data-portfolio-video
                                    >
                                        <source src="{{ $assetUrl }}" type="{{ $videoMime }}">
                                    </video>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 via-black/10 to-transparent"></div>
                                <span class="absolute top-3 left-3 rounded-full border border-white/20 bg-black/60 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.2em] text-white backdrop-blur-sm">
                                    Reel
                                </span>
                                @if ($orientation === 'horizontal')
                                    <div class="absolute bottom-4 left-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/30 bg-black/50">▶</span>
                                        <span>{{ __('messages.portfolio_page.cta_play') }}</span>
                                    </div>
                                @else
                                    <div class="absolute top-3 right-3 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-white">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-white/30 bg-black/50 text-[8px]">▶</span>
                                    </div>
                                @endif
                            @else
                                <img src="{{ $assetUrl }}" alt="{{ $item->title ?? 'Portafolio' }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 via-black/10 to-transparent"></div>
                                <span class="absolute top-3 left-3 rounded-full border border-white/20 bg-black/60 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.2em] text-white backdrop-blur-sm">
                                    Foto
                                </span>
                            @endif
                        </div>

                        <div class="absolute inset-x-0 bottom-0 flex flex-col gap-2 px-4 pb-4 pt-8">
                            @if ($item->title)
                                <p class="text-base font-semibold text-white leading-tight">{{ $item->title }}</p>
                            @endif
                            @if ($item->caption)
                                <p class="text-xs text-gray-300 line-clamp-{{ $orientation === 'vertical' ? '3' : '2' }} leading-relaxed">{{ $item->caption }}</p>
                            @endif
                            @if ($item->tags && count($item->tags) > 0)
                                <div class="flex flex-wrap gap-1 mt-auto">
                                    @foreach ($item->tags as $tag)
                                        @php
                                            $tagLabels = [
                                                'barbershop' => '💈',
                                                'food' => '🍔',
                                                'nightlife' => '🌃',
                                                'events' => '🎉',
                                                'fitness' => '💪',
                                                'beauty' => '💄',
                                                'fashion' => '👔',
                                                'music' => '🎵',
                                                'art' => '🎨',
                                                'travel' => '✈️',
                                                'lifestyle' => '🌟',
                                                'automotive' => '🚗',
                                                'real-estate' => '🏠',
                                                'tech' => '💻',
                                                'sports' => '⚽',
                                                'wedding' => '💍',
                                                'corporate' => '🏢',
                                                'hospitality' => '🏨',
                                            ];
                                            $emoji = $tagLabels[$tag] ?? '🏷️';
                                        @endphp
                                        <span class="inline-flex items-center gap-0.5 rounded-full border border-white/20 bg-black/60 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.1em] text-white">
                                            <span class="text-[10px]">{{ $emoji }}</span>
                                            <span class="truncate max-w-[80px]">{{ ucfirst(str_replace('-', ' ', $tag)) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div id="portfolio-modal" class="fixed inset-0 z-[70] hidden flex items-center justify-center bg-black/80 px-4 py-10 backdrop-blur">
        <div class="relative w-full max-w-6xl overflow-hidden rounded-3xl border border-white/10 bg-black/90">
            <button id="portfolio-modal-close" class="absolute right-4 top-4 z-10 rounded-full border border-white/20 bg-black/60 px-3 py-2 text-xs uppercase tracking-[0.2em] text-white">
                {{ __('messages.portfolio_page.close') }}
            </button>
            <div class="flex flex-col">
                <div class="relative flex items-center justify-center bg-black" id="portfolio-modal-media-container">
                    <img id="portfolio-modal-image" src="" alt="" class="hidden max-h-[70vh] w-full object-contain">
                    <video id="portfolio-modal-video" class="hidden max-h-[70vh] w-full" controls playsinline></video>
                    <iframe id="portfolio-modal-embed" class="hidden" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
                <div class="space-y-3 border-t border-white/10 p-6">
                    <p id="portfolio-modal-title" class="text-2xl font-semibold text-white"></p>
                    <p id="portfolio-modal-caption" class="text-sm text-gray-300"></p>
                    <div id="portfolio-modal-tags" class="flex flex-wrap gap-1.5"></div>
                    <div class="flex flex-wrap gap-2 text-xs uppercase tracking-[0.2em] text-gray-400">
                        <span id="portfolio-modal-type"></span>
                    </div>
                    <p class="text-xs text-gray-500">
                        {{ __('messages.portfolio_page.modal_hint') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const cards = Array.from(document.querySelectorAll('[data-portfolio-card]'));
        const modal = document.getElementById('portfolio-modal');
        const modalImage = document.getElementById('portfolio-modal-image');
        const modalVideo = document.getElementById('portfolio-modal-video');
        const modalEmbed = document.getElementById('portfolio-modal-embed');
        const modalTitle = document.getElementById('portfolio-modal-title');
        const modalCaption = document.getElementById('portfolio-modal-caption');
        const modalTags = document.getElementById('portfolio-modal-tags');
        const modalType = document.getElementById('portfolio-modal-type');
        const modalClose = document.getElementById('portfolio-modal-close');
        
        const tagLabels = {
            'barbershop': '💈',
            'food': '🍔',
            'nightlife': '🌃',
            'events': '🎉',
            'fitness': '💪',
            'beauty': '💄',
            'fashion': '👔',
            'music': '🎵',
            'art': '🎨',
            'travel': '✈️',
            'lifestyle': '🌟',
            'automotive': '🚗',
            'real-estate': '🏠',
            'tech': '💻',
            'sports': '⚽',
            'wedding': '💍',
            'corporate': '🏢',
            'hospitality': '🏨',
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modalImage.classList.add('hidden');
            modalVideo.classList.add('hidden');
            modalEmbed.classList.add('hidden');
            modalTags.innerHTML = '';
            modalVideo.pause();
            modalVideo.removeAttribute('src');
            modalVideo.load();
            modalEmbed.removeAttribute('src');
            
            // Reset container
            const container = document.getElementById('portfolio-modal-media-container');
            if (container) {
                container.className = 'relative flex items-center justify-center bg-black';
            }
        };

        const openModal = (card) => {
            const type = card.dataset.type;
            const orientation = card.dataset.orientation;
            const src = card.dataset.src;
            const poster = card.dataset.poster;
            const provider = card.dataset.provider;
            const embedUrl = card.dataset.embed;
            const title = card.dataset.title || '{{ __('messages.portfolio_page.modal_fallback_title') }}';
            const caption = card.dataset.caption || '';
            const tags = JSON.parse(card.dataset.tags || '[]');

            modalTitle.textContent = title;
            modalCaption.textContent = caption;
            
            // Render tags
            modalTags.innerHTML = '';
            if (tags && tags.length > 0) {
                tags.forEach(tag => {
                    const emoji = tagLabels[tag] || '🏷️';
                    const label = tag.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    const tagEl = document.createElement('span');
                    tagEl.className = 'inline-flex items-center gap-1 rounded-full border border-white/20 bg-black/50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-white';
                    tagEl.innerHTML = `<span>${emoji}</span><span>${label}</span>`;
                    modalTags.appendChild(tagEl);
                });
            }
            
            modalType.textContent = type === 'video' ? '{{ __('messages.portfolio_page.labels.video') }}' : '{{ __('messages.portfolio_page.labels.photo') }}';

            if (type === 'video' && provider === 'youtube') {
                modalEmbed.classList.remove('hidden');
                modalEmbed.setAttribute('src', embedUrl);
                
                // Ajustar tamaño según orientación
                const container = document.getElementById('portfolio-modal-media-container');
                if (orientation === 'vertical') {
                    modalEmbed.className = 'hidden max-w-md mx-auto aspect-[9/16] w-full';
                    container.className = 'relative flex items-center justify-center bg-black py-4';
                } else {
                    modalEmbed.className = 'hidden w-full aspect-video';
                    container.className = 'relative flex items-center justify-center bg-black';
                }
                modalEmbed.classList.remove('hidden');
            } else if (type === 'video') {
                modalVideo.classList.remove('hidden');
                modalVideo.setAttribute('src', src);
                if (poster) {
                    modalVideo.setAttribute('poster', poster);
                }
                modalVideo.play().catch(() => {});
            } else {
                modalImage.classList.remove('hidden');
                modalImage.setAttribute('src', src);
                modalImage.setAttribute('alt', title);
            }

            modal.classList.remove('hidden');
        };

        cards.forEach((card) => {
            card.addEventListener('click', () => openModal(card));
        });

        modalClose?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        const filterButtons = Array.from(document.querySelectorAll('[data-filter]'));
        const tagFilterButtons = Array.from(document.querySelectorAll('[data-filter-tag]'));
        const allFilterButtons = [...filterButtons, ...tagFilterButtons];

        allFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                // Reset all buttons
                allFilterButtons.forEach((btn) => {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-ghost');
                });
                
                // Activate clicked button
                button.classList.add('btn-primary');
                button.classList.remove('btn-ghost');

                const filter = button.dataset.filter;
                const filterTag = button.dataset.filterTag;

                cards.forEach((card) => {
                    let matches = false;
                    
                    if (filter === 'all') {
                        matches = true;
                    } else if (filter === 'photo' || filter === 'video') {
                        matches = card.dataset.type === filter;
                    } else if (filterTag) {
                        // Filter by tag
                        const cardTags = JSON.parse(card.dataset.tags || '[]');
                        matches = cardTags.includes(filterTag);
                    }
                    
                    card.classList.toggle('hidden', !matches);
                });
            });
        });

        const videos = Array.from(document.querySelectorAll('[data-portfolio-video]'));
        if ('IntersectionObserver' in window && videos.length) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    const video = entry.target;
                    if (entry.isIntersecting) {
                        video.play().catch(() => {});
                    } else {
                        video.pause();
                    }
                });
            }, { threshold: 0.35 });

            videos.forEach((video) => observer.observe(video));
        }
    })();
</script>
@endpush
