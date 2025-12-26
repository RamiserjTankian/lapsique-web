<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- Primary Meta Tags --}}
    <title>@yield('title', __('messages.site.brand'))</title>
    <meta name="title" content="@yield('meta_title', __('messages.site.brand'))">
    <meta name="description" content="@yield('meta_description', __('messages.site.brand_tagline'))">
    <meta name="keywords" content="@yield('meta_keywords', 'música electrónica, DJ sets, techno, house, Riviera Maya, Playa del Carmen, Tulum, eventos electrónicos, sets en vivo')">
    
    {{-- Canonical URL --}}
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    
    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', __('messages.site.brand'))">
    <meta property="og:description" content="@yield('og_description', __('messages.site.brand_tagline'))">
    <meta property="og:image" content="@yield('og_image', url(asset('images/og-default.jpg')))">
    <meta property="og:image:secure_url" content="@yield('og_image', url(asset('images/og-default.jpg')))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:site_name" content="{{ __('messages.site.brand') }}">
    <meta property="og:locale" content="es_MX">
    
    {{-- Additional Open Graph Meta Tags (for events, articles, etc.) --}}
    @stack('og_meta')
    
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('twitter_url', url()->current())">
    <meta name="twitter:title" content="@yield('twitter_title', __('messages.site.brand'))">
    <meta name="twitter:description" content="@yield('twitter_description', __('messages.site.brand_tagline'))">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/og-default.jpg'))">
    
    {{-- Additional Meta Tags --}}
    <meta name="author" content="lapsique.media">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#000000">
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- JSON-LD Structured Data --}}
    @stack('structured_data')
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
                    <a href="{{ route('posts.index') }}" class="nav-link">Blog</a>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="https://www.youtube.com/@LAPSIQUEMEDIA" target="_blank" class="btn btn-primary hidden md:inline-flex" title="YouTube">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21.58 7.2c-.12-.86-.9-1.54-1.78-1.65C17.34 5.25 14 5.25 12 5.25s-5.34 0-7.8.3c-.88.11-1.66.79-1.78 1.65C2.25 8.77 2.25 10.13 2.25 12s0 3.23.17 4.8c.12.86.9 1.54 1.78 1.65 2.46.3 5.8.3 7.8.3s5.34 0 7.8-.3c.88-.11 1.66-.79 1.78-1.65.17-1.57.17-2.93.17-4.8s0-3.23-.17-4.8ZM10.5 14.7V9.3l4.1 2.7-4.1 2.7Z"/>
                        </svg>
                    </a>
                    @php
                        $locale = app()->getLocale();
                        $nextLocale = $locale === 'es' ? 'en' : 'es';
                        $langEmoji = $nextLocale === 'es' ? '🇲🇽' : '🇬🇧';
                    @endphp
                    <a href="{{ route('locale.switch', $nextLocale) }}" class="btn btn-ghost hidden md:inline-flex text-2xl leading-none" title="{{ $nextLocale === 'es' ? 'Cambiar a Español' : 'Switch to English' }}">{{ $langEmoji }}</a>
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
            <div class="mx-auto max-w-6xl px-6 py-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    {{-- Brand --}}
                    <div>
                        <p class="font-semibold uppercase tracking-[0.2em] text-gray-200">lapsique.media</p>
                        <p class="text-sm text-gray-400">Música electrónica, visuales y sets en vivo.</p>
                    </div>
                    
                    {{-- Navigation Links --}}
                    <div class="flex items-center gap-6 text-sm">
                        <a href="{{ route('djs.index') }}" class="nav-link">DJs</a>
                        <a href="{{ route('events.index') }}" class="nav-link">{{ __('messages.site.footer_links.events') }}</a>
                        <a href="{{ route('videos.index') }}" class="nav-link">{{ __('messages.site.footer_links.videos') }}</a>
                        <a href="{{ route('posts.index') }}" class="nav-link">Blog</a>
                    </div>
                    
                    {{-- Social Media Icons --}}
                    <div class="flex items-center gap-4">
                        <a href="https://www.youtube.com/{{ config('lapsique.youtube_handle') }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 transition hover:text-white" title="YouTube" aria-label="YouTube">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21.58 7.2c-.12-.86-.9-1.54-1.78-1.65C17.34 5.25 14 5.25 12 5.25s-5.34 0-7.8.3c-.88.11-1.66.79-1.78 1.65C2.25 8.77 2.25 10.13 2.25 12s0 3.23.17 4.8c.12.86.9 1.54 1.78 1.65 2.46.3 5.8.3 7.8.3s5.34 0 7.8-.3c.88-.11 1.66-.79 1.78-1.65.17-1.57.17-2.93.17-4.8s0-3.23-.17-4.8ZM10.5 14.7V9.3l4.1 2.7-4.1 2.7Z"/>
                            </svg>
                        </a>
                        <a href="{{ config('lapsique.instagram_url') }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 transition hover:text-white" title="Instagram" aria-label="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7.5 2h9A5.5 5.5 0 0122 7.5v9a5.5 5.5 0 01-5.5 5.5h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2zm0 1.5A4 4 0 003.5 7.5v9a4 4 0 004 4h9a4 4 0 004-4v-9a4 4 0 00-4-4h-9zM17.25 6a1.25 1.25 0 110 2.5 1.25 1.25 0 010-2.5zM12 7.5a4.5 4.5 0 110 9 4.5 4.5 0 010-9zm0 1.5a3 3 0 100 6 3 3 0 000-6z"/>
                            </svg>
                        </a>
                        <a href="https://wa.me/{{ config('lapsique.whatsapp_number') }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 transition hover:text-white" title="WhatsApp" aria-label="WhatsApp">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 01-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.85-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14-.01-.3-.01-.47-.01z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Lead Capture Popup Modal -->
    <div id="lead-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/70 backdrop-blur-sm px-4" style="display: none;">
        <div class="card relative w-full max-w-md animate-modal-in">
            <button onclick="closeLeadModal()" class="absolute right-4 top-4 text-gray-400 transition hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="px-8 py-8 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-2xl font-semibold text-white">¡Únete a la comunidad!</h3>
                    <p class="text-gray-300">Sé el primero en enterarte de próximos eventos, lanzamientos exclusivos y contenido especial.</p>
                </div>
                <form id="lead-form" class="space-y-4" onsubmit="submitLeadForm(event)">
                    <div>
                        <input type="text" name="name" id="lead-name" placeholder="Tu nombre" class="field" required>
                    </div>
                    <div>
                        <input type="email" name="email" id="lead-email" placeholder="Tu email" class="field" required>
                    </div>
                    <div>
                        <input type="tel" name="phone" id="lead-phone" placeholder="WhatsApp (opcional)" class="field">
                    </div>
                    <div>
                        <input type="text" name="instagram_handle" id="lead-instagram" placeholder="Instagram @usuario (opcional)" class="field">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="subscribed_newsletter" id="lead-newsletter" checked class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-white focus:ring-white">
                        <label for="lead-newsletter" class="text-sm text-gray-300">Quiero recibir el newsletter</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-full justify-center">
                        <span id="lead-btn-text">Suscribirme</span>
                        <span id="lead-btn-loading" class="hidden">Enviando...</span>
                    </button>
                    <p id="lead-error" class="text-red-400 text-sm hidden"></p>
                    <p id="lead-success" class="text-emerald-400 text-sm hidden"></p>
                </form>
            </div>
        </div>
    </div>

    <script>
        let leadModalShown = false;

        function showLeadModal() {
            if (leadModalShown || localStorage.getItem('leadModalShown')) {
                return;
            }
            const modal = document.getElementById('lead-modal');
            modal.style.display = 'flex';
            leadModalShown = true;
        }

        function closeLeadModal() {
            const modal = document.getElementById('lead-modal');
            modal.style.display = 'none';
            localStorage.setItem('leadModalShown', 'true');
        }

        async function submitLeadForm(e) {
            e.preventDefault();
            
            const form = e.target;
            const btnText = document.getElementById('lead-btn-text');
            const btnLoading = document.getElementById('lead-btn-loading');
            const errorEl = document.getElementById('lead-error');
            const successEl = document.getElementById('lead-success');
            
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                instagram_handle: formData.get('instagram_handle'),
                subscribed_newsletter: formData.get('subscribed_newsletter') === 'on',
                source: 'popup'
            };

            try {
                const response = await fetch('{{ route("customers.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    successEl.textContent = result.message;
                    successEl.classList.remove('hidden');
                    form.reset();
                    setTimeout(() => {
                        closeLeadModal();
                    }, 2000);
                } else {
                    errorEl.textContent = result.message || 'Error al enviar el formulario';
                    errorEl.classList.remove('hidden');
                }
            } catch (error) {
                errorEl.textContent = 'Error de conexión. Inténtalo de nuevo.';
                errorEl.classList.remove('hidden');
            } finally {
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            }
        }

        // Show modal after 10 seconds on homepage
        if (window.location.pathname === '/') {
            setTimeout(showLeadModal, 10000);
        }

        // Show modal on exit intent
        document.addEventListener('mouseout', function(e) {
            if (!e.toElement && !e.relatedTarget) {
                showLeadModal();
            }
        });
    </script>

    <style>
        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-modal-in {
            animation: modalIn 0.3s ease-out forwards;
        }
    </style>
</body>
</html>
