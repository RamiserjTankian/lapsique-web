@extends('layouts.site')

@section('title', 'Link Expirado | ' . __('messages.site.brand'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Link No Disponible
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                Este link de registro ya no está disponible.
            </p>

            @if($inviteLink->expires_at && $inviteLink->expires_at->isPast())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                El link expiró el {{ $inviteLink->expires_at->format('d/m/Y H:i') }}.
            </p>
            @elseif($inviteLink->max_registrations && $inviteLink->current_registrations >= $inviteLink->max_registrations)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Se alcanzó el límite de registros permitidos ({{ $inviteLink->max_registrations }}).
            </p>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Este link ha sido desactivado.
            </p>
            @endif
        </div>
    </div>
</div>
@endsection

