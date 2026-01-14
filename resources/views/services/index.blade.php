@extends('layouts.app')

@section('content')
<div class="container">
    @if(isset($breadcrumbs))
    <nav class="breadcrumb">
        @foreach($breadcrumbs as $crumb)
            @if($loop->last)
                <span>{{ $crumb['name'] }}</span>
            @else
                <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                <span>></span>
            @endif
        @endforeach
    </nav>
    @endif

    <h1>{{ __('admin.services') }}</h1>

    <div class="services-list">
        @foreach($services as $service)
            <div class="service-item">
                <div class="service-card">
                    @if($service->image)
                        <div class="service-image">
                            <img src="{{ Storage::url($service->image) }}" alt="{{ $service->title }}" loading="lazy">
                        </div>
                    @endif

                    <div class="service-content">
                        <h2>{{ $service->title }}</h2>
                        <p>{{ $service->description }}</p>
                        <a href="{{ route('services.show', $service->slug) }}" class="btn btn-primary">{{ __('messages.more_details') }}</a>
                    </div>
                </div>

                @if($service->children->count() > 0)
                    <div class="service-children">
                        <h3>Включает услуги:</h3>
                        <ul class="children-list">
                            @foreach($service->children as $child)
                                <li>
                                    <a href="{{ route('services.show', $child->slug) }}">{{ $child->title }}</a>
                                    <p>{{ $child->description }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if(isset($breadcrumbs))
        {!! \App\Helpers\SeoHelper::breadcrumbList($breadcrumbs) !!}
    @endif
</div>
@endsection
