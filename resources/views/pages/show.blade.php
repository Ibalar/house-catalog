@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)

@section('meta')
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
<div class="container">
    <nav class="breadcrumb">
        <a href="{{ url('/') }}">Главная</a>
        <span>></span>
        <span>{{ $page->title }}</span>
    </nav>

    <article class="page-content">
        <h1>{{ $page->title }}</h1>
        
        <div class="page-body">
            {!! $page->content !!}
        </div>
    </article>
</div>
@endsection