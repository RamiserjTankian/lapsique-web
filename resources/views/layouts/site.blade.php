<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('messages.site.brand'))</title>
    <meta name="description" content="{{ __('messages.site.brand_tagline') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink text-gray-100 min-h-screen antialiased">
    <div class="bg-grid min-h-screen">
        <header class="sticky top-0 z-50 border-b border-white/10 bg-black/60 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="{{ route('home') }}" class="text-sm font-semibold uppercase tracking-[0.3em] hover:text-white">
                    {{ __('messages.site.brand') }}
                </a>
                <nav class="hidden items-center gap-6 text-xs uppercase tracking-[0.18em] md:flex">
                    <a href="{{ route('home') }}" class="nav-link">{{ __('messages.site.nav.home') }}</a>
                    <a href="{{ route('djs.index') }}" class="nav-link">{{ __('messages.site.nav.djs') }}</a>
                    <a href="{{ route('events.index') }}" class="nav-link">{{ __('messages.site.nav.events') }}</a>
                    <a href="{{ route('videos.index') }}" class="nav-link">{{ __('messages.site.nav.videos') }}</a>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="{{ route('videos.index') }}" class="btn btn-primary hidden md:inline-flex">{{ __('messages.site.nav.cta') }}</a>
                    @php
                        $locale = app()->getLocale();
                        $nextLocale = $locale === 'es' ? 'en' : 'es';
                        $langLabel = $nextLocale === 'es' ? __('messages.site.nav.lang_es') : __('messages.site.nav.lang_en');
                    @endphp
                    <a href="{{ route('locale.switch', $nextLocale) }}" class="btn btn-ghost hidden md:inline-flex">{{ $langLabel }}</a>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-auto max-w-6xl px-6 pt-6">
                <div class="rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <main class="mx-auto max-w-6xl px-6 py-10 space-y-14">
            @yield('content')
        </main>

        <footer class="border-t border-white/10">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-6 py-8 text-sm text-gray-400 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-semibold uppercase tracking-[0.2em] text-gray-200">lapsique.media</p>
                    <p class="text-gray-400">Música electrónica, visuales y sets en vivo.</p>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ route('djs.index') }}" class="nav-link">{{ __('messages.site.footer_links.lineup') }}</a>
                    <a href="{{ route('events.index') }}" class="nav-link">{{ __('messages.site.footer_links.events') }}</a>
                    <a href="{{ route('videos.index') }}" class="nav-link">{{ __('messages.site.footer_links.videos') }}</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
