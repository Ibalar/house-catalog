@extends('layouts.app')

@section('title', 'Проекты')

@section('content')
<div class="container">
    <div class="projects-page">
        <aside class="filters-sidebar">
            <h2>Фильтры</h2>
            
            <form class="filters-form" method="GET" action="{{ route('projects.index') }}">
                <div class="filter-group">
                    <h3>Категория</h3>
                    @foreach($categories as $category)
                        <label class="filter-checkbox">
                            <input type="radio" name="category" value="{{ $category->id }}" 
                                   {{ request('category') == $category->id ? 'checked' : '' }}>
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h3>Площадь (м²)</h3>
                    <div class="range-inputs">
                        <input type="number" name="area_min" placeholder="От" value="{{ request('area_min') }}" min="0">
                        <span>-</span>
                        <input type="number" name="area_max" placeholder="До" value="{{ request('area_max') }}" min="0">
                    </div>
                </div>

                <div class="filter-group">
                    <h3>Спальни</h3>
                    <select name="bedrooms">
                        <option value="">Не важно</option>
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
                        <option value="">Не важно</option>
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
                        <option value="">Не важно</option>
                        @for($i = 1; $i <= 3; $i++)
                            <option value="{{ $i }}" {{ request('floors') == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="filter-group">
                    <h3>Опции</h3>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="has_garage" value="1" {{ request('has_garage') ? 'checked' : '' }}>
                        Гараж
                    </label>
                </div>

                <div class="filter-group">
                    <h3>Тип кровли</h3>
                    @php
                        $roofTypes = ['Односкатная', 'Двускатная', 'Соломенная', 'Плоская', 'Другое'];
                    @endphp
                    @foreach($roofTypes as $type)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="roof_type[]" value="{{ $type }}" 
                                   {{ in_array($type, request('roof_type', [])) ? 'checked' : '' }}>
                            {{ $type }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h3>Стиль</h3>
                    @php
                        $styles = ['Современный', 'Классический', 'Шале', 'Фахверк', 'Скандинавский', 'Другое'];
                    @endphp
                    @foreach($styles as $style)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="style[]" value="{{ $style }}" 
                                   {{ in_array($style, request('style', [])) ? 'checked' : '' }}>
                            {{ $style }}
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary">Применить фильтры</button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Сбросить</a>
            </form>
        </aside>

        <main class="projects-main">
            <h1>Проекты</h1>

            <div class="projects-grid">
                @forelse($projects as $project)
                    <article class="project-card {{ $project->is_featured ? 'featured' : '' }}">
                        @if($project->is_featured)
                            <span class="featured-badge">Популярный</span>
                        @endif

                        @if($project->main_image)
                            <div class="project-image">
                                <img src="{{ Storage::url($project->main_image) }}" alt="{{ $project->title }}">
                            </div>
                        @endif

                        <div class="project-info">
                            <h3>{{ $project->title }}</h3>
                            <p class="project-description">{{ Str::limit($project->description, 100) }}</p>

                            <div class="project-specs">
                                @if($project->area)
                                    <span class="spec">{{ $project->area }} м²</span>
                                @endif
                                @if($project->bedrooms)
                                    <span class="spec">{{ $project->bedrooms }} сп.</span>
                                @endif
                            </div>

                            @if($project->price_from || $project->price_to)
                                <div class="project-price">
                                    @if($project->price_from && $project->price_to)
                                        {{ number_format($project->price_from, 0, ',', ' ') }} - 
                                        {{ number_format($project->price_to, 0, ',', ' ') }} руб.
                                    @elseif($project->price_from)
                                        От {{ number_format($project->price_from, 0, ',', ' ') }} руб.
                                    @endif
                                </div>
                            @endif

                            <a href="{{ route('projects.show', $project->slug) }}" class="btn btn-primary">
                                Подробнее
                            </a>
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