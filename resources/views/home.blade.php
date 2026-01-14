@extends('layouts.app')

@section('title', 'Главная')

@section('content')
<div class="hero">
    <div class="container">
        <h2 class="hero-title">
            {{ get_setting('site_name', 'Строительная компания') }}
        </h2>
        <p class="hero-description">
            {{ get_setting('site_description', 'Профессиональное строительство домов и бань') }}
        </p>
    </div>
</div>

@if($topServices->count() > 0)
<section class="section">
    <div class="container">
        <h2 class="section-title">Популярные услуги</h2>
        <div class="services-grid">
            @foreach($topServices as $service)
                <div class="service-card">
                    @if($service->image)
                        <div class="service-image">
                            <img src="{{ Storage::url($service->image) }}" alt="{{ $service->title }}">
                        </div>
                    @endif
                    <h3>{{ $service->title }}</h3>
                    <p>{{ Str::limit($service->description, 100) }}</p>
                    <a href="{{ route('services.show', $service->slug) }}" class="btn btn-primary">Подробнее</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($featuredProjects->count() > 0)
<section class="section projects-section">
    <div class="container">
        <h2 class="section-title">Популярные проекты</h2>
        <div class="projects-grid">
            @foreach($featuredProjects as $project)
                <div class="project-card">
                    @if($project->main_image)
                        <div class="project-image">
                            <img src="{{ Storage::url($project->main_image) }}" alt="{{ $project->title }}">
                        </div>
                    @endif
                    <div class="project-info">
                        <h3>{{ $project->title }}</h3>
                        <p>{{ Str::limit($project->description, 150) }}</p>
                        @if($project->price_from || $project->price_to)
                            <div class="project-price">
                                @if($project->price_from && $project->price_to)
                                    {{ number_format($project->price_from, 0, ',', ' ') }} - {{ number_format($project->price_to, 0, ',', ' ') }} руб.
                                @elseif($project->price_from)
                                    От {{ number_format($project->price_from, 0, ',', ' ') }} руб.
                                @else
                                    - руб.
                                @endif
                            </div>
                        @endif
                        <a href="{{ route('projects.show', $project->slug) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section cta-section">
    <div class="container">
        <div class="cta-box">
            <h2>Оставьте заявку</h2>
            <p>Мы поможем выбрать и построить ваш идеальный дом</p>
            
            <form class="lead-form" action="{{ route('leads.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Имя *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="message">Сообщение</label>
                    <textarea id="message" name="message" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Отправить заявку</button>
            </form>
            
            <div id="form-message" class="form-message"></div>
        </div>
    </div>
</section>

@if(get_block('promo_banner'))
<section class="section">
    <div class="container">
        {!! get_block('promo_banner') !!}
    </div>
</section>
@endif

@if(get_block('main_features'))
<section class="section features-section">
    <div class="container">
        {!! get_block('main_features') !!}
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const leadForms = document.querySelectorAll('.lead-form');
        
        leadForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const messageContainer = form.querySelector('#form-message');
                
                fetch('{{ route("leads.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageContainer.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                        form.reset();
                    } else {
                        let errorHtml = '<div class="alert alert-error">';
                        for (const [key, errors] of Object.entries(data.errors)) {
                            errorHtml += errors.join('<br>') + '<br>';
                        }
                        errorHtml += '</div>';
                        messageContainer.innerHTML = errorHtml;
                    }
                })
                .catch(error => {
                    messageContainer.innerHTML = '<div class="alert alert-error">Произошла ошибка. Попробуйте позже.</div>';
                });
            });
        });
    });
</script>
@endpush