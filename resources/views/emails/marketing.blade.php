@extends('emails.layout')

@section('title', 'Lapsique Newsletter')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0;">Hola {{ $customer->name }}! 👋</h2>
    
    <div style="margin: 20px 0; color: #e5e7eb;">
        {!! nl2br(e($emailContent)) !!}
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('events.index') }}" class="button">Ver Todos los Eventos</a>
    </div>
    
    <p style="color: #bdbdbd; font-size: 14px; margin-top: 30px;">
        El equipo de Lapsique
    </p>
@endsection
