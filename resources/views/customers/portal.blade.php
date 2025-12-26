@extends('layouts.site')

@section('title', 'Mi Portal | ' . __('messages.site.brand'))

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-3">
            <p class="pill">Mi Cuenta</p>
            <h1 class="text-3xl font-semibold text-white">Portal de Cliente</h1>
            <p class="text-gray-300">Gestiona tus guest lists y mantente conectado con los eventos.</p>
        </div>

        @if (isset($customer))
            <!-- Customer Info Card -->
            <div class="card p-6 space-y-4">
                <div class="flex items-start justify-between">
                    <div class="space-y-2">
                        <h2 class="text-2xl font-semibold text-white">{{ $customer->name }}</h2>
                        <div class="space-y-1 text-sm text-gray-400">
                            <p>📧 {{ $customer->email }}</p>
                            @if ($customer->phone)
                                <p>📱 {{ $customer->phone }}</p>
                            @endif
                            @if ($customer->instagram_handle)
                                <p>📸 {{ '@' . $customer->instagram_handle }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($customer->subscribed_newsletter)
                        <span class="pill border-emerald-400 text-emerald-400">Newsletter Activo</span>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-3 pt-4">
                    <div class="card bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-gray-400">Guest Lists</p>
                        <p class="text-2xl font-bold text-white">{{ $customer->guestListEntries->count() }}</p>
                    </div>
                    <div class="card bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-gray-400">Eventos Asistidos</p>
                        <p class="text-2xl font-bold text-white">{{ $customer->guestListEntries->where('status', 'confirmed')->count() }}</p>
                    </div>
                    <div class="card bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-gray-400">Desde</p>
                        <p class="text-lg font-bold text-white">{{ $customer->created_at->format('M Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Guest List Entries -->
            <div class="space-y-4">
                <h2 class="text-2xl font-semibold text-white">Mis Guest Lists</h2>
                
                @if ($customer->guestListEntries->isEmpty())
                    <div class="card p-8 text-center space-y-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-400">No tienes guest lists todavía.</p>
                        <a href="{{ route('events.index') }}" class="btn btn-primary">Ver Eventos Disponibles</a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($customer->guestListEntries->sortByDesc('created_at') as $entry)
                            @php
                                $statusColors = [
                                    'pending' => 'border-yellow-400 text-yellow-400',
                                    'confirmed' => 'border-emerald-400 text-emerald-400',
                                    'rejected' => 'border-red-400 text-red-400',
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'confirmed' => 'Confirmado',
                                    'rejected' => 'Rechazado',
                                ];
                            @endphp
                            <div class="card p-6">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="space-y-2 flex-1">
                                        @if ($entry->event)
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xl font-semibold text-white">{{ $entry->event->title }}</h3>
                                                <span class="pill {{ $statusColors[$entry->status] ?? '' }}">
                                                    {{ $statusLabels[$entry->status] ?? $entry->status }}
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap gap-4 text-sm text-gray-400">
                                                <span>📅 {{ optional($entry->event->starts_at)->format('d M Y H:i') ?? 'Fecha por definir' }}</span>
                                                @if ($entry->event->venue)
                                                    <span>📍 {{ $entry->event->venue }}, {{ $entry->event->city }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xl font-semibold text-white">Guest List General</h3>
                                                <span class="pill {{ $statusColors[$entry->status] ?? '' }}">
                                                    {{ $statusLabels[$entry->status] ?? $entry->status }}
                                                </span>
                                            </div>
                                        @endif
                                        <p class="text-xs uppercase tracking-[0.18em] text-gray-500">
                                            Registrado el {{ $entry->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="flex gap-3">
                                        @if ($entry->status === 'confirmed' && $entry->event)
                                            <a href="{{ route('events.show', $entry->event) }}" class="btn btn-ghost">Ver Evento</a>
                                        @endif
                                        @if ($entry->event && $entry->event->ticket_url)
                                            <a href="{{ $entry->event->ticket_url }}" target="_blank" class="btn btn-primary">Tickets</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 sm:grid-cols-2">
                <a href="{{ route('events.index') }}" class="card card-animated p-6 group">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-white/10 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white group-hover:text-gray-200 transition">Ver Próximos Eventos</h3>
                            <p class="text-sm text-gray-400">Explora el calendario</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('posts.index') }}" class="card card-animated p-6 group">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-white/10 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white group-hover:text-gray-200 transition">Lee el Blog</h3>
                            <p class="text-sm text-gray-400">Noticias y contenido</p>
                        </div>
                    </div>
                </a>
            </div>
        @else
            <!-- Login/Access Form -->
            <div class="card p-8 max-w-md mx-auto">
                <div class="space-y-6">
                    <div class="text-center space-y-2">
                        <h2 class="text-2xl font-semibold text-white">Accede a tu Portal</h2>
                        <p class="text-gray-400">Ingresa tu email para ver tus guest lists</p>
                    </div>
                    <form method="GET" action="{{ route('customers.portal') }}" class="space-y-4">
                        <div>
                            <input type="email" name="email" placeholder="tu@email.com" class="field" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-full justify-center">Acceder</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

