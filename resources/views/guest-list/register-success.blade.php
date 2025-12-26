@extends('layouts.site')

@section('title', 'Registro Exitoso | ' . __('messages.site.brand'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                ¡Registro Exitoso!
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                Tu registro para el evento <strong>{{ $event->title }}</strong> ha sido confirmado.
            </p>

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Información del Evento</h3>
                <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    <p><strong>Fecha:</strong> {{ $event->starts_at?->format('d/m/Y H:i') ?? 'Por confirmar' }}</p>
                    @if($event->venue)
                    <p><strong>Lugar:</strong> {{ $event->venue }}</p>
                    @endif
                    @if($event->city)
                    <p><strong>Ciudad:</strong> {{ $event->city }}</p>
                    @endif
                    @if($dj)
                    <p><strong>Invitado por:</strong> {{ $dj->name }}</p>
                    @elseif($rp)
                    <p><strong>Invitado por:</strong> {{ $rp->name }}</p>
                    @endif
                </div>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Te esperamos en el evento. Recibirás tu QR de check-in por email.
            </p>
        </div>
    </div>
</div>
@endsection
