@extends('layouts.site')

@section('title', 'Acceso | ' . __('messages.site.brand'))

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-3">
            <p class="pill">Mi Portal</p>
            <h1 class="text-3xl font-semibold text-white md:text-4xl">Accede a tu portal de cliente</h1>
            <p class="max-w-2xl text-gray-300">Usa el email y la contraseña que recibiste para revisar tus sesiones, materiales entregados, pagos y tickets.</p>
        </div>

        <div class="card max-w-md border-white/15 bg-white/[0.04] p-8">
            @if ($errors->any())
                <div class="rounded-xl border border-red-400/40 bg-red-500/10 px-4 py-3 text-sm text-red-100 mb-4">
                    {{ $errors->first() }}
                </div>
            @endif
            <form method="POST" action="{{ route('customers.login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <input type="email" name="email" placeholder="tu@email.com" class="field" value="{{ old('email') }}" required>
                </div>
                <div>
                    <input type="password" name="password" placeholder="Contraseña" class="field" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="remember" value="1">
                    <span>Recordarme</span>
                </label>
                <button type="submit" class="btn btn-primary w-full justify-center">Ingresar</button>
            </form>
        </div>
    </div>
@endsection
