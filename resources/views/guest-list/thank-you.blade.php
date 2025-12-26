@extends('layouts.site')

@section('title', 'Gracias por tu Registro | ' . __('messages.site.brand'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-20 w-20 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                ¡Gracias por tu Registro!
            </h1>

            <p class="text-xl text-gray-700 mb-8">
                Tu registro para <strong class="text-gray-900">{{ $event->title }}</strong> ha sido confirmado exitosamente.
            </p>

            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Evento</h3>
                <div class="space-y-2 text-sm text-gray-700">
                    <p><strong class="text-gray-900">Fecha:</strong> {{ $event->starts_at?->format('d/m/Y') ?? 'Por confirmar' }}</p>
                    @if($event->venue)
                    <p><strong class="text-gray-900">Lugar:</strong> {{ $event->venue }}</p>
                    @endif
                    @if($event->city)
                    <p><strong class="text-gray-900">Ciudad:</strong> {{ $event->city }}</p>
                    @endif
                    @if($dj)
                    <p><strong class="text-gray-900">Invitado por:</strong> {{ $dj->name }}</p>
                    @elseif($rp)
                    <p><strong class="text-gray-900">Invitado por:</strong> {{ $rp->name }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-6 mb-6">
                <p class="text-sm text-gray-700">
                    <strong class="text-gray-900">📧</strong> Te enviaremos tu QR de check-in por email.<br>
                    <strong class="text-gray-900">📱</strong> Te contactaremos por WhatsApp o teléfono si aceptaste recibir promociones.
                </p>
            </div>

            <div class="pt-4">
                <a href="{{ route('home') }}" 
                   class="inline-block bg-gray-900 hover:bg-gray-800 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
