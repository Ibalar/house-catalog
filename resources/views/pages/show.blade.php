@extends('layouts.app')

@section('content')
<div class="container">
    @if(isset($breadcrumbs))
    <nav class="breadcrumb">
        @foreach($breadcrumbs as $index => $crumb)
            @if($loop->last)
                <span>{{ $crumb['name'] }}</span>
            @else
                <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                <span>></span>
            @endif
        @endforeach
    </nav>
    @endif

    <article class="page-content">
        <h1>{{ $page->title }}</h1>

        <div class="page-body">
            {!! $page->content !!}
        </div>
    </article>

    @if(isset($breadcrumbs))
        {!! \App\Helpers\SeoHelper::breadcrumbList($breadcrumbs) !!}
    @endif
</div>
@endsection
