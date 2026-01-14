@extends('layouts.app')

@section('title', $service->title)

@section('content')
<div class="container">
    <nav class="breadcrumb">
        <a href="{{ url('/') }}">Главная</a>
        <span>></span>
        <a href="{{ route('services.index') }}">Услуги</a>
        <span>></span>
        <span>{{ $service->title }}</span>
    </nav>

    <article class="service-detail">
        <h1>{{ $service->title }}</h1>

        @if($service->image)
            <div class="service-main-image">
                <img src="{{ Storage::url($service->image) }}" alt="{{ $service->title }}">
            </div>
        @endif

        @if($service->full_text)
            <div class="service-content">
                {!! $service->full_text !!}
            </div>
        @endif

        @if($service->children->count() > 0)
            <section class="service-children-section">
                <h2>Включает услуги</h2>
                <div class="children-grid">
                    @foreach($service->children as $child)
                        <div class="child-service-card">
                            <h3>{{ $child->title }}</h3>
                            <p>{{ $child->description }}</p>
                            <a href="{{ route('services.show', $child->slug) }}" class="btn btn-secondary">Подробнее</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </article>

    <section class="cta-section">
        <div class="cta-box">
            <h2>Заинтересовались услугой?</h2>
            <p>Оставьте заявку и мы свяжемся с вами для уточнения деталей</p>
            
            <form class="lead-form" action="{{ route('leads.store') }}" method="POST">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">
                
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
                    <textarea id="message" name="message" rows="3"></textarea>
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