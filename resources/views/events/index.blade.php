@extends('layouts.site')

@section('title', __('messages.events_page.pill') . ' | ' . __('messages.site.brand'))

@section('meta_title', 'Eventos - lapsique.media')
@section('meta_description', 'Shows, colaboraciones y residencias de música electrónica en Riviera Maya. Visuales monocromáticos y producción de alto nivel.')
@section('meta_keywords', 'eventos electrónicos, shows, música electrónica, Riviera Maya, Playa del Carmen, Tulum, fiestas techno')

@section('og_type', 'website')
@section('og_title', 'Eventos - lapsique.media')
@section('og_description', 'Shows, colaboraciones y residencias de música electrónica en Riviera Maya.')
@php
    $eventsOgImage = $events->isNotEmpty() && $events->first()->getFirstMediaUrl('cover', 'cover_large') ? 
        $events->first()->getFirstMediaUrl('cover', 'cover_large') : 
        asset('images/og-default.jpg');
@endphp
@section('og_image', $eventsOgImage)
@section('og_url', route('events.index'))

@section('twitter_title', 'Eventos - lapsique.media')
@section('twitter_description', 'Shows, colaboraciones y residencias de música electrónica en Riviera Maya.')
@section('twitter_image', $eventsOgImage)
@section('twitter_url', route('events.index'))

@section('canonical_url', route('events.index'))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Eventos",
  "description": "Shows, colaboraciones y residencias de música electrónica",
  "url": "{{ route('events.index') }}"
}
</script>
@endpush

@section('content')
    <div class="flex flex-col gap-3">
        <p class="pill">{{ __('messages.events_page.pill') }}</p>
        <h1 class="text-3xl font-semibold text-white">{{ __('messages.events_page.title') }}</h1>
        <p class="text-gray-300">{{ __('messages.events_page.subtitle') }}</p>
    </div>

    @if ($events->isEmpty())
        <div class="card px-6 py-4 text-gray-300">{{ __('messages.events_page.empty') }}</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
                @php
                    $cover = $event->getFirstMediaUrl('cover', 'thumb') ?: $event->getFirstMediaUrl('cover');
                @endphp
                <a href="{{ route('events.show', $event) }}" class="card overflow-hidden group">
                    <div class="h-48 w-full bg-gradient-to-br from-black to-zinc-900">
                        @if ($cover)
                            <img src="{{ $cover }}" alt="{{ $event->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @endif
                    </div>
                    <div class="px-5 py-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">{{ $event->title }}</h3>
                            @if ($event->is_featured)
                                <span class="pill">{{ __('messages.events_page.top') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($event->description, 120) }}</p>
                        <div class="flex items-center justify-between text-xs uppercase tracking-[0.18em] text-gray-400">
                            <span>{{ optional($event->starts_at)->format('d M') ?? __('messages.event.date') }}</span>
                            <span>{{ $event->city ?? __('messages.event.city') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
