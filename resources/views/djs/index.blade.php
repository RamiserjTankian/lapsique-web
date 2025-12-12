@extends('layouts.site')

@section('title', __('messages.djs_page.pill') . ' | ' . __('messages.site.brand'))

@section('content')
    <div class="flex flex-col gap-3">
        <p class="pill">{{ __('messages.djs_page.pill') }}</p>
        <h1 class="text-3xl font-semibold text-white">{{ __('messages.djs_page.title') }}</h1>
        <p class="text-gray-300">{{ __('messages.hero.description') }}</p>
    </div>

    @if ($djs->isEmpty())
        <div class="card px-6 py-4 text-gray-300">{{ __('messages.djs_page.empty') }}</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($djs as $dj)
                @php
                    $profile = $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                @endphp
                <a href="{{ route('djs.show', $dj) }}" class="card overflow-hidden group">
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
                        <p class="text-sm text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($dj->bio, 130) }}</p>
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-400">
                            {{ $dj->instagram_handle ? '@' . $dj->instagram_handle : __('messages.djs_page.instagram_pending') }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
