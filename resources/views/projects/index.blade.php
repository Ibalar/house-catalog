@extends('layouts.app')

@section('title', 'Проекты')

@push('styles')
<style>
.projects-page {
    display: flex;
    gap: 30px;
    margin-top: 30px;
}

.filters-sidebar {
    width: 25%;
    position: sticky;
    top: 20px;
    height: fit-content;
}

.filters-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.filter-group {
    margin-bottom: 20px;
}

.filter-group h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: #333;
}

.filter-group input[type="number"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.filter-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.range-inputs {
    display: flex;
    gap: 10px;
    align-items: center;
}

.range-inputs input {
    flex: 1;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.filter-checkbox input {
    margin-right: 8px;
}

.projects-main {
    width: 75%;
}

.sort-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.results-count {
    font-weight: bold;
    color: #666;
}

.sort-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.project-card {
    background: white;
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.project-card.featured {
    border-color: #ff6b6b;
}

.project-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.project-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.featured-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ff6b6b;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    z-index: 1;
}

.project-info {
    padding: 20px;
}

.project-info h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.project-description {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}

.project-specs {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 14px;
    color: #888;
}

.project-price {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
}

.no-projects {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.pagination {
    margin-top: 30px;
}

.pagination nav {
    display: flex;
    justify-content: center;
}

@media (max-width: 1023px) {
    .projects-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .projects-page {
        flex-direction: column;
    }
    
    .filters-sidebar {
        width: 100%;
        position: static;
        margin-bottom: 30px;
    }
    
    .projects-main {
        width: 100%;
    }
    
    .projects-grid {
        grid-template-columns: 1fr;
    }
    
    .sort-controls {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="projects-page">
        <aside class="filters-sidebar">
            <h2>Фильтры</h2>
            
            <form class="filters-form" method="GET" action="{{ route('projects.index') }}">
                <div class="filter-group">
                    <h3>Категория</h3>
                    <label class="filter-checkbox">
                        <input type="radio" name="type" value="" {{ !request('type') ? 'checked' : '' }}>
                        Все
                    </label>
                    <label class="filter-checkbox">
                        <input type="radio" name="type" value="house" {{ request('type') === 'house' ? 'checked' : '' }}>
                        Дома
                    </label>
                    <label class="filter-checkbox">
                        <input type="radio" name="type" value="sauna" {{ request('type') === 'sauna' ? 'checked' : '' }}>
                        Бани
                    </label>
                </div>

                <div class="filter-group">
                    <h3>Площадь (м²)</h3>
                    <div class="range-inputs">
                        <input type="number" name="area_min" placeholder="От" value="{{ request('area_min') }}" min="0" step="0.1">
                        <span>-</span>
                        <input type="number" name="area_max" placeholder="До" value="{{ request('area_max') }}" min="0" step="0.1">
                    </div>
                </div>

                <div class="filter-group">
                    <h3>Спальни</h3>
                    <select name="bedrooms">
                        <option value="">-- Все --</option>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="filter-group">
                    <h3>Санузлы</h3>
                    <select name="bathrooms">
                        <option value="">-- Все --</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ request('bathrooms') == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="filter-group">
                    <h3>Этажность</h3>
                    <select name="floors">
                        <option value="">-- Все --</option>
                        <option value="1" {{ request('floors') == '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ request('floors') == '2' ? 'selected' : '' }}>2</option>
                        <option value="3" {{ request('floors') == '3' ? 'selected' : '' }}>3</option>
                    </select>
                </div>

                <div class="filter-group">
                    <h3>Гараж</h3>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="has_garage" value="1" {{ request('has_garage') ? 'checked' : '' }}>
                        Наличие гаража
                    </label>
                </div>

                <div class="filter-group">
                    <h3>Тип крыши</h3>
                    @foreach($availableValues['roof_types'] as $roofType)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="roof_types[]" value="{{ $roofType }}" 
                                   {{ in_array($roofType, request('roof_types', [])) ? 'checked' : '' }}>
                            {{ $roofType }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h3>Стиль</h3>
                    @foreach($availableValues['styles'] as $style)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="styles[]" value="{{ $style }}" 
                                   {{ in_array($style, request('styles', [])) ? 'checked' : '' }}>
                            {{ $style }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h3>Цена (₽)</h3>
                    <div class="range-inputs">
                        <input type="number" name="price_min" placeholder="От" value="{{ request('price_min') }}" min="0" step="1000">
                        <span>-</span>
                        <input type="number" name="price_max" placeholder="До" value="{{ request('price_max') }}" min="0" step="1000">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Применить фильтры</button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Сбросить все фильтры</a>
            </form>
        </aside>

        <main class="projects-main">
            <h1>Проекты</h1>

            <div class="sort-controls">
                <div class="results-count">
                    Найдено {{ $projects->total() }} {{ trans_choice('проект|проекта|проектов', $projects->total()) }}
                </div>
                
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="default" {{ request('sort', 'default') == 'default' ? 'selected' : '' }}>По умолчанию</option>
                    <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>По популярности</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Новые первыми</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Цена: по возрастанию</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Цена: по убыванию</option>
                </select>
            </div>

            <div class="projects-grid">
                @forelse($projects as $project)
                    <article class="project-card {{ $project->is_featured ? 'featured' : '' }}">
                        @if($project->is_featured)
                            <div class="featured-badge">⭐ Популярный</div>
                        @endif

                        @if($project->main_image)
                            <div class="project-image">
                                <a href="{{ route('projects.show', $project->slug) }}">
                                    <img src="{{ Storage::url($project->main_image) }}" alt="{{ $project->title }}">
                                </a>
                            </div>
                        @endif

                        <div class="project-info">
                            <h3>
                                <a href="{{ route('projects.show', $project->slug) }}">{{ $project->title }}</a>
                            </h3>
                            
                            <p class="project-description">{{ Str::limit($project->description, 100) }}</p>

                            <div class="project-specs">
                                @if($project->area)
                                    <span class="spec">{{ $project->area }} м²</span>
                                @endif
                                @if($project->bedrooms)
                                    <span class="spec">{{ $project->bedrooms }} сп.</span>
                                @endif
                                @if($project->bathrooms)
                                    <span class="spec">{{ $project->bathrooms }} с/у</span>
                                @endif
                                @if($project->floors)
                                    <span class="spec">{{ $project->floors }} эт.</span>
                                @endif
                            </div>

                            @if($project->price_from || $project->price_to)
                                <div class="project-price">
                                    @if($project->price_from && $project->price_to)
                                        {{ number_format($project->price_from, 0, ',', ' ') }} - {{ number_format($project->price_to, 0, ',', ' ') }} ₽
                                    @elseif($project->price_from)
                                        от {{ number_format($project->price_from, 0, ',', ' ') }} ₽
                                    @endif
                                </div>
                            @endif

                            <a href="{{ route('projects.show', $project->slug) }}" class="btn btn-primary">Подробнее</a>
                        </div>
                    </article>
                @empty
                    <div class="no-projects">
                        <p>Проекты не найдены. Попробуйте изменить фильтры.</p>
                    </div>
                @endforelse
            </div>

            @if($projects->hasPages())
                <div class="pagination">
                    {{ $projects->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.filters-form');
    
    // Auto-submit on radio/checkbox change could be implemented, but we'll keep the explicit submit button for now
});
</script>
@endpush