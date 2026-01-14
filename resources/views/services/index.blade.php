@extends('layouts.app')

@section('title', 'Услуги')

@section('content')
<div class="container">
    <h1>Услуги</h1>
    
    <div class="services-list">
        @foreach($services as $service)
            <div class="service-item">
                <div class="service-card">
                    @if($service->image)
                        <div class="service-image">
                            <img src="{{ Storage::url($service->image) }}" alt="{{ $service->title }}">
                        </div>
                    @endif
                    
                    <div class="service-content">
                        <h2>{{ $service->title }}</h2>
                        <p>{{ $service->description }}</p>
                        <a href="{{ route('services.show', $service->slug) }}" class="btn btn-primary">Подробнее</a>
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
</div>
@endsection