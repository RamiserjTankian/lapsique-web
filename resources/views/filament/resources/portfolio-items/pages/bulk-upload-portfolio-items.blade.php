<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Carga masiva de portafolio
        </x-slot>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Sube múltiples fotos y reels a la vez. El sistema detecta automáticamente el tipo y la orientación si eliges "Auto".
        </p>

        <div class="mt-6 space-y-4">
            {{ $this->form }}

            <x-filament::button color="primary" wire:click="submit">
                Cargar al portafolio
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
