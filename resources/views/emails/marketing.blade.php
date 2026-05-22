@extends('emails.layout')
@php use App\Support\EmailBrand; @endphp

@section('title', EmailBrand::WORDMARK.' — Newsletter')

@section('content')
    <h2 style="{{ EmailBrand::headingStyle() }}">Hola {{ $customer->name ?? 'querido amigo' }}</h2>

    <div style="margin:20px 0;color:{{ EmailBrand::FOREGROUND }};">
        {!! $emailContent !!}
    </div>

    @if($buttonUrl && $buttonText)
        @include('emails.partials._button', ['url' => $buttonUrl, 'label' => $buttonText])
    @endif

    <p style="margin-top:30px;{{ EmailBrand::mutedStyle() }}">
        El equipo de {{ EmailBrand::WORDMARK }}
    </p>
@endsection
