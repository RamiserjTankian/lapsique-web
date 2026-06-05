@php
    $eventTitle = $event->title;
    $djName = $dj ? $dj->name : null;
    $rpName = $rp ? $rp->name : null;
    $invitedBy = $djName ?? $rpName ?? 'Trascendental';
    $metaTitle = "Guest List {$eventTitle}" . ($invitedBy ? " - Invitación de {$invitedBy}" : '') . " | " . __('messages.site.brand');
    $metaDescription = "Regístrate en la guest list para {$eventTitle}" . ($invitedBy ? " con invitación de {$invitedBy}" : '') . ". " . ($event->headline ?: ($event->description ? \Illuminate\Support\Str::limit(strip_tags($event->description), 120) : 'Evento de música electrónica'));
    $eventImage = $event->getFirstMediaUrl('cover', 'cover_large') ?: $event->getFirstMediaUrl('cover');
    if ($eventImage && !str_starts_with($eventImage, 'http')) {
        $eventImage = url($eventImage);
    }
    $metaImage = $eventImage ?: asset('images/og-default.jpg');
    $canonicalUrl = route('guestlist.register', ['token' => $inviteLink->token]);
@endphp

@extends('layouts.site')

@section('title', $metaTitle)

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', "guest list, {$eventTitle}, " . ($djName ? "{$djName}, " : '') . ($rpName ? "{$rpName}, " : '') . "registro, invitación, música electrónica, evento")

@section('og_type', 'event')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@section('og_image', $metaImage)
@section('og_url', $canonicalUrl)

@section('twitter_title', $metaTitle)
@section('twitter_description', $metaDescription)
@section('twitter_image', $metaImage)
@section('twitter_url', $canonicalUrl)

@section('canonical_url', $canonicalUrl)

@push('og_meta')
    @if($event->starts_at)
    <meta property="event:start_time" content="{{ $event->starts_at->toIso8601String() }}">
    @endif
    @if($event->venue || $event->city)
    <meta property="event:location" content="{{ trim(($event->venue ? $event->venue . ', ' : '') . ($event->city ?? ''), ', ') }}">
    @endif
@endpush

@push('structured_data')
@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $eventTitle,
        'description' => $metaDescription,
        'url' => route('events.show', $event),
    ];
    
    if ($eventImage) {
        $structuredData['image'] = $eventImage;
    }
    
    if ($event->starts_at) {
        $structuredData['startDate'] = $event->starts_at->toIso8601String();
    }
    
    if ($event->venue || $event->city) {
        $location = [
            '@type' => 'Place',
            'name' => $event->venue ?? $event->city,
        ];
        
        if ($event->city) {
            $location['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $event->city,
            ];
        }
        
        $structuredData['location'] = $location;
    }
    
    $structuredData['organizer'] = [
        '@type' => 'Organization',
        'name' => __('messages.site.brand'),
        'url' => route('home'),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black py-3 sm:py-6 lg:py-8 px-2 sm:px-4 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            {{-- Header con imagen del evento --}}
            @if($event->getFirstMediaUrl('cover'))
            <div class="h-48 sm:h-64 bg-cover bg-center" style="background-image: url('{{ $event->getFirstMediaUrl('cover', 'cover_large') }}')"></div>
            @endif
            
            <div class="p-5 sm:p-6 lg:p-10">
                <div class="text-center mb-6 sm:mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                        Registro Guest List
                    </h1>
                    <p class="text-gray-700 text-base sm:text-lg font-medium">
                        {{ $event->title }}
                    </p>
                    @if($event->headline)
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">
                        {{ $event->headline }}
                    </p>
                    @endif
                    @if($dj)
                    <p class="text-xs sm:text-sm text-gray-600 mt-2">
                        Invitación de <strong class="text-gray-900">{{ $dj->name }}</strong>
                    </p>
                    @elseif($rp)
                    <p class="text-xs sm:text-sm text-gray-600 mt-2">
                        Invitación de <strong class="text-gray-900">{{ $rp->name }}</strong>
                    </p>
                    @endif
                </div>

                {{-- Detalles del evento --}}
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 mb-6 sm:mb-8">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Detalles del Evento</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-sm">
                        <div>
                            <p class="text-gray-600"><strong class="text-gray-900">Fecha:</strong> {{ $event->starts_at?->format('d/m/Y') ?? 'Por confirmar' }}</p>
                        </div>
                        <div>
                            @if($event->venue)
                            <p class="text-gray-600"><strong class="text-gray-900">Lugar:</strong> {{ $event->venue }}</p>
                            @endif
                            @if($event->city)
                            <p class="text-gray-600"><strong class="text-gray-900">Ciudad:</strong> {{ $event->city }}</p>
                            @endif
                        </div>
                    </div>
                    @if($event->description)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-700">{!! \Illuminate\Support\Str::limit(strip_tags($event->description), 200) !!}</p>
                    </div>
                    @endif
                    @if($event->djs()->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600 mb-2"><strong class="text-gray-900">Lineup:</strong></p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($event->djs as $eventDj)
                            <span class="inline-block bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700 border border-gray-200">
                                {{ $eventDj->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('guestlist.register.store', $inviteLink->token) }}" class="space-y-4 sm:space-y-6" id="guest-list-form" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-900 mb-2">
                            Nombre completo *
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-gray-900 placeholder-gray-400">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-900 mb-2">
                            Email *
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-gray-900 placeholder-gray-400">
                    </div>

                    <div>
                        <label for="whatsapp" class="block text-sm font-medium text-gray-900 mb-2">
                            WhatsApp
                        </label>
                        <input type="tel" 
                               id="whatsapp" 
                               name="whatsapp" 
                               value="{{ old('whatsapp') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-gray-900 placeholder-gray-400">
                    </div>

                    <div>
                        <label for="instagram_handle" class="block text-sm font-medium text-gray-900 mb-2">
                            Instagram
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-500">@</span>
                            <input type="text" 
                                   id="instagram_handle" 
                                   name="instagram_handle" 
                                   value="{{ old('instagram_handle') }}"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-gray-900 placeholder-gray-400">
                        </div>
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-900 mb-2">
                            Género
                        </label>
                        <select id="gender" 
                                name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-gray-900">
                            <option value="">Seleccionar...</option>
                            <option value="femenino" {{ old('gender') === 'femenino' ? 'selected' : '' }}>Femenino</option>
                            <option value="masculino" {{ old('gender') === 'masculino' ? 'selected' : '' }}>Masculino</option>
                            <option value="otro" {{ old('gender') === 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-start">
                            <input type="checkbox" 
                                   name="consent_marketing" 
                                   value="1"
                                   {{ old('consent_marketing') ? 'checked' : '' }}
                                   class="mt-1 mr-3 h-4 w-4 text-gray-900 border-gray-300 rounded focus:ring-gray-900">
                            <span class="text-sm text-gray-700">
                                Acepto ser contactado por WhatsApp o llamada telefónica para promociones de este evento o eventos futuros *
                            </span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                x-bind:disabled="submitting"
                                x-bind:class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                                class="w-full bg-gray-900 hover:bg-gray-800 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            <span x-show="!submitting">Confirmar Registro</span>
                            <span x-show="submitting" x-cloak class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
