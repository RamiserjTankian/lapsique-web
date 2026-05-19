@extends('emails.layout')

@section('title', 'Lapsique Newsletter')

@section('content')
    <h2 style="color: #ffffff; margin-top: 0;">Hola {{ $customer->name ?? 'querido amigo' }}! 👋</h2>
    
    <div style="margin: 20px 0; color: #e5e7eb;">
        {!! $emailContent !!}
    </div>
    
    @if($buttonUrl && $buttonText)
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $buttonUrl }}" class="button" style="display: inline-block; padding: 12px 30px; background-color: #ffffff; color: #050505; text-decoration: none; border-radius: 6px; font-weight: 600;">{{ $buttonText }}</a>
    </div>
    @endif
    
    <p style="color: #bdbdbd; font-size: 14px; margin-top: 30px;">
        El equipo de Lapsique
    </p>
    
    <!-- Tracking Pixel -->
    <img src="{{ $trackingPixelUrl }}" width="1" height="1" style="display: none;" alt="" />
@endsection
