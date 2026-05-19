<x-filament-panels::page>
    {{ $this->schema }}

    @if (file_exists(public_path('images/logo-watermark.png')))
        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Vista Previa del Logo Actual</h3>
            <div class="flex items-center gap-4">
                <img 
                    src="{{ asset('images/logo-watermark.png') }}?v={{ time() }}" 
                    alt="Logo actual" 
                    class="max-h-32 max-w-32 object-contain"
                    onerror="this.style.display='none'"
                >
                <div class="flex-1">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Ubicación:</strong> <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-gray-900">public/images/logo-watermark.png</code>
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                        Este logo se usará automáticamente como marca de agua en todas las fotografías del portafolio.
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
