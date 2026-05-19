<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Suscripción - Lapsique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $analyticsConfig = [
            'enabled' => config('analytics.enabled'),
            'endpoint' => route('analytics.collect'),
            'sampleRate' => config('analytics.sample_rate'),
            'sessionTimeout' => config('analytics.session_timeout'),
            'trackClicks' => config('analytics.track_clicks'),
            'trackForms' => config('analytics.track_forms'),
            'trackEngagement' => config('analytics.track_engagement'),
        ];
    @endphp
    <script>
        window.LapsiqueAnalytics = @json($analyticsConfig);
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            @if(request()->has('success'))
                <div class="text-center">
                    <div class="text-6xl mb-4">😢</div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Te extrañaremos
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Has sido desuscrito exitosamente de todas nuestras listas de correo.
                    </p>
                    <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition">
                        Volver al Inicio
                    </a>
                </div>
            @else
                <div>
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            Cancelar Suscripción
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300">
                            Lamentamos verte partir, {{ $customer->name }}.
                        </p>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            Si te desinscribes, ya no recibirás:
                        </p>
                        <ul class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                            <li>• Información sobre próximos eventos</li>
                            <li>• Noticias exclusivas sobre DJs y artistas</li>
                            <li>• Acceso anticipado a tickets</li>
                            <li>• Contenido especial de la comunidad</li>
                        </ul>
                    </div>

                    <form method="POST" class="space-y-4">
                        @csrf
                        
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                ¿Estás seguro que deseas cancelar tu suscripción?
                            </p>
                            
                            <div class="flex gap-4">
                                <a href="{{ route('home') }}" class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                    Mantener Suscripción
                                </a>
                                <button type="submit" class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    Sí, Cancelar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
