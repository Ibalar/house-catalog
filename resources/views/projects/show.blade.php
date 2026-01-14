@extends('layouts.app')

@section('title', $project->meta_title ?: $project->title)

@section('meta')
    @if($project->meta_description)
        <meta name="description" content="{{ $project->meta_description }}">
    @endif
@endsection

@section('content')
<div class="container">
    <nav class="breadcrumb">
        <a href="{{ url('/') }}">Главная</a>
        <span>></span>
        <a href="{{ route('projects.index') }}">Проекты</a>
        <span>></span>
        <span>{{ $project->title }}</span>
    </nav>

    <article class="project-detail">
        <h1>{{ $project->title }}</h1>

        <div class="project-images">
            @if($project->main_image)
                <div class="main-image">
                    <img id="main-image" src="{{ Storage::url($project->main_image) }}" 
                         alt="{{ $project->title }}">
                </div>
            @endif

            @if($project->images->count() > 0)
                <div class="image-gallery">
                    @foreach($project->images as $image)
                        <div class="gallery-thumb">
                            <img src="{{ Storage::url($image->image) }}" 
                                 alt="{{ $project->title }} - изображение {{ $loop->iteration }}"
                                 onclick="document.getElementById('main-image').src='{{ Storage::url($image->image) }}';">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="project-info">
            <div class="info-left">
                <section class="project-specs">
                    <h2>Характеристики</h2>
                    <dl class="specs-list">
                        @if($project->area)
                            <dt>Площадь:</dt>
                            <dd>{{ $project->area }} м²</dd>
                        @endif

                        @if($project->floors)
                            <dt>Этажность:</dt>
                            <dd>{{ $project->floors }}</dd>
                        @endif

                        @if($project->bedrooms)
                            <dt>Спальни:</dt>
                            <dd>{{ $project->bedrooms }}</dd>
                        @endif

                        @if($project->bathrooms)
                            <dt>Санузлы:</dt>
                            <dd>{{ $project->bathrooms }}</dd>
                        @endif

                        @if($project->has_garage)
                            <dt>Гараж:</dt>
                            <dd>Есть</dd>
                        @endif

                        @if($project->roof_type)
                            <dt>Кровля:</dt>
                            <dd>{{ $project->roof_type }}</dd>
                        @endif

                        @if($project->style)
                            <dt>Стиль:</dt>
                            <dd>{{ $project->style }}</dd>
                        @endif
                    </dl>
                </section>
            </div>

            <div class="info-right">
                <section class="project-price-section">
                    <h2>Цена</h2>
                    <div class="price-box">
                        @if($project->price_from && $project->price_to)
                            <p class="price-range">
                                {{ number_format($project->price_from, 0, ',', ' ') }} - 
                                {{ number_format($project->price_to, 0, ',', ' ') }}
                            </p>
                            <p class="currency">руб.</p>
                        @elseif($project->price_from)
                            <p class="price">От {{ number_format($project->price_from, 0, ',', ' ') }}</p>
                            <p class="currency">руб.</p>
                        @else
                            <p class="price">Цена по запросу</p>
                        @endif
                    </div>
                </section>

                @if($project->category)
                    <section class="project-category">
                        <h2>Категория</h2>
                        <p>
                            <a href="{{ route('projects.index', ['category' => $project->category->id]) }}">
                                {{ $project->category->name }}
                            </a>
                        </p>
                    </section>
                @endif
            </div>
        </div>

        @if($project->description)
            <section class="project-description">
                <h2>Описание проекта</h2>
                <div class="description-content">
                    {!! $project->description !!}
                </div>
            </section>
        @endif
    </article>

    <section class="cta-section">
        <div class="cta-box">
            <h2>Заинтересовались проектом?</h2>
            <p>Оставьте заявку и мы рассчитаем стоимость строительства для вашего участка</p>
            
            <form class="lead-form" action="{{ route('leads.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Имя *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Телефон *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="message">Сообщение</label>
                    <textarea id="message" name="message" rows="3" placeholder="Расскажите о ваших пожеланиях..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Отправить заявку</button>
            </form>
            
            <div id="form-message" class="form-message"></div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const leadForm = document.querySelector('.lead-form');
        
        if (leadForm) {
            leadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(leadForm);
                const messageContainer = document.getElementById('form-message');
                
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
                        leadForm.reset();
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
        }
    });
</script>
@endpush