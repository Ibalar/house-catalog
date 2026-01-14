# Структура базы данных для сайта-каталога строительной организации

## Созданные модели и миграции

### 1. Page (Страницы)
- **Таблица**: `pages`
- **Модель**: `App\Models\Page`
- **Миграция**: `2024_01_15_000001_create_pages_table.php`
- **Назначение**: Статические страницы сайта (О компании, Контакты, Гарантии и т.д.)
- **Отношения**: Нет
- **Фабрика**: `PageFactory`

### 2. Service (Услуги)
- **Таблица**: `services`
- **Модель**: `App\Models\Service`
- **Миграция**: `2024_01_15_000002_create_services_table.php`
- **Назначение**: Услуги с древовидной структурой
- **Отношения**: 
  - `parent()` → BelongsTo Service (родительская услуга)
  - `children()` → HasMany Service (дочерние услуги)
- **Фабрика**: `ServiceFactory`

### 3. ProjectCategory (Категории проектов)
- **Таблица**: `project_categories`
- **Модель**: `App\Models\ProjectCategory`
- **Миграция**: `2024_01_15_000003_create_project_categories_table.php`
- **Назначение**: Группировка проектов (типы домов, бань)
- **Отношения**: 
  - `projects()` → HasMany Project
- **Фабрика**: `ProjectCategoryFactory`

### 4. Project (Проекты)
- **Таблица**: `projects`
- **Модель**: `App\Models\Project`
- **Миграция**: `2024_01_15_000004_create_projects_table.php`
- **Назначение**: Каталог проектов домов и бань
- **Отношения**: 
  - `category()` → BelongsTo ProjectCategory
  - `images()` → HasMany ProjectImage
- **Фабрика**: `ProjectFactory`

### 5. ProjectImage (Галерея проектов)
- **Таблица**: `project_images`
- **Модель**: `App\Models\ProjectImage`
- **Миграция**: `2024_01_15_000005_create_project_images_table.php`
- **Назначение**: Галерея изображений для каждого проекта
- **Отношения**: 
  - `project()` → BelongsTo Project

### 6. Block (Типовые блоки)
- **Таблица**: `blocks`
- **Модель**: `App\Models\Block`
- **Миграция**: `2024_01_15_000006_create_blocks_table.php`
- **Назначение**: Универсальные блоки для акций, баннеров, текстовых вставок
- **Отношения**: Нет
- **Фабрика**: `BlockFactory`

### 7. Setting (Настройки сайта)
- **Таблица**: `settings`
- **Модель**: `App\Models\Setting`
- **Миграция**: `2024_01_15_000007_create_settings_table.php`
- **Назначение**: Хранение общих настроек (телефоны, email, адрес, соц.сети)
- **Отношения**: Нет

### 8. Lead (Заявки)
- **Таблица**: `leads`
- **Модель**: `App\Models\Lead`
- **Миграция**: `2024_01_15_000008_create_leads_table.php`
- **Назначение**: Заявки с форм обратной связи
- **Отношения**: Нет

## Особенности реализации

### Типы данных и Casts

Все модели используют правильные типы данных:
- **String** для текстовых полей
- **Boolean** для флагов (is_active, is_published, has_garage и т.д.)
- **Integer** для числовых полей (sort_order, floors, bedrooms)
- **Decimal:2** для денежных и площадных полей (price_from, price_to, area)
- **JSON** для структурированных данных (meta_fields в Service)

### Индексы

Добавлены индексы на часто используемые колонки:
- `slug` - для всех моделей с URL
- `is_active`, `is_published` - для фильтрации
- `category_id`, `parent_id` - для JOIN операций
- `sort_order` - для сортировки
- `status` - для фильтрации заявок

### Foreign Keys

Установлены внешние ключи с каскадным удалением:
- `services.parent_id` → `services.id`
- `projects.category_id` → `project_categories.id`
- `project_images.project_id` → `projects.id`

### Fillable

Все модели содержат массив `$fillable` для массового присвоения значений.

## Использование

### Запуск миграций
```bash
php artisan migrate
```

### Создание тестовых данных
```php
// Страницы
Page::factory()->count(10)->create();

// Услуги (родительские)
Service::factory()->count(5)->create();

// Услуги (дочерние)
Service::factory()->count(10)->create([
    'parent_id' => Service::inRandomOrder()->first()->id
]);

// Категории проектов
ProjectCategory::factory()->count(5)->create();

// Проекты
Project::factory()->count(20)->create();

// Блоки
Block::factory()->count(5)->create();
```

## Диаграмма отношений

```
ProjectCategory
    ↓ (HasMany)
Project
    ↓ (HasMany)
ProjectImage

Service
    ↓ (self-reference)
Service (children)

Page (standalone)
Block (standalone)
Setting (standalone)
Lead (standalone)
```
