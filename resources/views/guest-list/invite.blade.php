@extends('layouts.site')

@section('title', 'Confirmar Invitación | ' . __('messages.site.brand'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    ¡Estás Invitado!
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $event->title }}
                </p>
                @if($dj)
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                    Invitación de <strong>{{ $dj->name }}</strong>
                </p>
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

            <form method="POST" action="{{ route('guestlist.invite.confirm', $entry->invite_token) }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre completo *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Teléfono
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="plus_ones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Acompañantes
                    </label>
                    <input type="number" 
                           id="plus_ones" 
                           name="plus_ones" 
                           value="{{ old('plus_ones', 0) }}" 
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Número de personas adicionales que vendrán contigo
                    </p>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                        Confirmar Asistencia
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p><strong>Fecha:</strong> {{ $event->starts_at?->format('d/m/Y H:i') ?? 'Por confirmar' }}</p>
                    @if($event->venue)
                    <p><strong>Lugar:</strong> {{ $event->venue }}</p>
                    @endif
                    @if($event->city)
                    <p><strong>Ciudad:</strong> {{ $event->city }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

