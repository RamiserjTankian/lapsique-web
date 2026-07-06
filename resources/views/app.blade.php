<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" suppressHydrationWarning>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $pageMeta = \App\Support\PageMeta::forRequest(request());
    @endphp
    @include('partials.meta-tags', ['meta' => $pageMeta])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f5f0" id="theme-color-meta">
    <link rel="icon" type="image/png" sizes="64x64" href="/favicon-64.png">
    <link rel="icon" type="image/svg+xml" href="/favicon-light.svg" id="favicon-svg">
    <link rel="apple-touch-icon" href="/favicon-light.svg" id="apple-touch-icon">
    <script>
        (function () {
            var key = 'trascendental-theme';
            var stored = localStorage.getItem(key);
            var isAlternateSite = @json(config('trascendental.enabled_as_primary')) || window.location.pathname.indexOf('/trascendental') === 0;
            var theme = isAlternateSite ? 'light' : (stored === 'dark' || stored === 'light' ? stored : 'light');
            var root = document.documentElement;

            function syncBrowserTheme(nextTheme) {
                var meta = document.getElementById('theme-color-meta');
                var favicon = document.getElementById('favicon-svg');
                var appleTouchIcon = document.getElementById('apple-touch-icon');
                var iconHref = nextTheme === 'dark' ? '/favicon-dark.svg' : '/favicon-light.svg';

                if (meta) {
                    meta.setAttribute('content', nextTheme === 'dark' ? '#06060a' : '#f7f5f0');
                }

                if (favicon) {
                    favicon.setAttribute('href', iconHref);
                }

                if (appleTouchIcon) {
                    appleTouchIcon.setAttribute('href', iconHref);
                }
            }

            root.classList.remove('light', 'dark');
            root.classList.add(theme);
            syncBrowserTheme(theme);

            if (window.MutationObserver) {
                new MutationObserver(function () {
                    syncBrowserTheme(root.classList.contains('dark') ? 'dark' : 'light');
                }).observe(root, {
                    attributes: true,
                    attributeFilter: ['class'],
                });
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=IBM+Plex+Mono:wght@400;500;600&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">

    @routes
    @php
        $settings = \App\Models\SiteSetting::current();
        $analyticsConfig = [
            'enabled' => config('analytics.enabled'),
            'endpoint' => route('analytics.collect'),
            'sampleRate' => config('analytics.sample_rate'),
            'sessionTimeout' => config('analytics.session_timeout'),
            'trackClicks' => config('analytics.track_clicks'),
            'trackForms' => config('analytics.track_forms'),
            'trackEngagement' => config('analytics.track_engagement'),
            'presence' => [
                'heartbeatIntervalSeconds' => config('analytics.presence.heartbeat_interval_seconds'),
                'activeWindowSeconds' => config('analytics.presence.active_window_seconds'),
            ],
        ];
        $metaPixelConfig = \App\Support\Meta::pixelClientConfig();
        $metaPixelId = $metaPixelConfig['id'];
        $metaPixelEnabled = $metaPixelConfig['enabled'];
        $metaPixelTrackPageView = $metaPixelConfig['trackPageView'] ?? true;
        $routeName = request()->route()?->getName();
        $serviceType = match ($routeName) {
            'djset.show' => 'dj_set',
            'drone-sessions.show' => 'drone_session',
            'construction-progress.show' => 'construction_progress',
            'food-reels.show' => 'food_reels',
            'home', 'booking.show' => 'content_session',
            default => null,
        };
        $pageConfig = [
            'type' => $routeName ?: 'site',
            'name' => $routeName,
            'title' => config('app.name', 'Lapsique Media'),
            'path' => request()->getPathInfo(),
            'url' => url()->current(),
            'serviceType' => $serviceType,
        ];
    @endphp
    <script>
        window.SiteAnalytics = @json($analyticsConfig);
        window.SitePixel = @json($metaPixelConfig);
        window.SitePage = @json($pageConfig);
        window.__siteTrackerQueue = window.__siteTrackerQueue || [];
        window.__sitePixelQueue = window.__sitePixelQueue || [];

        window.SiteTracker = window.SiteTracker || {
            getContext: function () {
                return window.SiteTrackingContext || {};
            },
            track: function (name, options) {
                window.__siteTrackerQueue.push({
                    method: 'track',
                    name: name,
                    options: options || {},
                });
            },
            trackPageview: function (options) {
                window.__siteTrackerQueue.push({
                    method: 'trackPageview',
                    options: options || {},
                });
            },
            syncForms: function () {
                window.__siteTrackerQueue.push({
                    method: 'syncForms',
                    options: {},
                });
            },
        };

        window.trackMetaPixel = window.trackMetaPixel || function (eventName, payload, options) {
            window.__sitePixelQueue.push({
                method: 'track',
                eventName: eventName,
                payload: payload || {},
                options: options || null,
            });
        };

        window.trackMetaPixelCustom = window.trackMetaPixelCustom || function (eventName, payload, options) {
            window.__sitePixelQueue.push({
                method: 'trackCustom',
                eventName: eventName,
                payload: payload || {},
                options: options || null,
            });
        };
    </script>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @if ($metaPixelEnabled)
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $metaPixelId }}');
            @if ($metaPixelTrackPageView)
            fbq('track', 'PageView');
            @endif
        </script>
        @if ($metaPixelTrackPageView)
        <noscript>
            <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" />
        </noscript>
        @endif
    @endif
    @inertiaHead
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    @inertia
</body>
</html>
