# Модели и миграции для каталога строительной организации

## Обзор

Создана полная структура БД для сайта-каталога строительной организации с 8 основными моделями:

1. **Page** - Статические страницы сайта
2. **Service** - Услуги с древовидной структурой
3. **ProjectCategory** - Категории проектов (дома/бани)
4. **Project** - Каталог проектов домов и бань
5. **ProjectImage** - Галерея изображений проектов
6. **Block** - Универсальные блоки контента
7. **Setting** - Настройки сайта
8. **Lead** - Заявки с форм обратной связи

## Установка

### 1. Запуск миграций

```bash
php artisan migrate
```

Это создаст все необходимые таблицы в базе данных.

### 2. Заполнение тестовыми данными

```bash
php artisan db:seed --class=CatalogSeeder
```

Или отдельно по моделям:

```bash
php artisan tinker

# Страницы
Page::factory()->count(10)->create();

# Категории проектов
$houseCategory = ProjectCategory::factory()->create(['type' => 'house', 'name' => 'Дома']);
$saunaCategory = ProjectCategory::factory()->create(['type' => 'sauna', 'name' => 'Бани']);

# Проекты
$project = Project::factory()->create(['category_id' => $houseCategory->id]);

# Изображения проекта
ProjectImage::factory()->count(5)->create(['project_id' => $project->id]);

# Услуги (родительские)
$service = Service::factory()->create(['parent_id' => null]);

# Подуслуги
Service::factory()->count(3)->create(['parent_id' => $service->id]);

# Блоки
Block::factory()->count(5)->create();

# Настройки
Setting::create(['key' => 'phone', 'value' => '+7 (495) 123-45-67', 'group' => 'contacts']);

# Заявки
Lead::factory()->count(20)->create();
```

## Примеры использования

### Работа со страницами

```php
// Получить активные страницы
$pages = Page::where('is_active', true)->get();

// Найти страницу по slug
$page = Page::where('slug', 'about')->first();

// Создать страницу
$page = Page::create([
    'title' => 'О компании',
    'slug' => 'about',
    'content' => 'Текст о компании...',
    'meta_title' => 'О компании',
    'is_active' => true,
]);
```

### Работа с услугами

```php
// Получить все родительские услуги
$mainServices = Service::whereNull('parent_id')
    ->where('is_published', true)
    ->orderBy('sort_order')
    ->get();

// Получить услугу с подуслугами
$service = Service::with('children')->find(1);

// Создать услугу с подуслугами
$service = Service::create([
    'title' => 'Строительство домов',
    'slug' => 'stroitelstvo-domov',
    'description' => 'Краткое описание',
    'full_text' => 'Полный текст услуги...',
]);

Service::create([
    'title' => 'Каркасные дома',
    'slug' => 'karkasnie-doma',
    'parent_id' => $service->id,
    'description' => 'Описание...',
    'full_text' => 'Текст...',
]);
```

### Работа с проектами

```php
// Получить опубликованные проекты категории
$projects = Project::where('category_id', 1)
    ->where('is_published', true)
    ->orderBy('sort_order')
    ->get();

// Получить избранные проекты
$featuredProjects = Project::where('is_featured', true)
    ->where('is_published', true)
    ->with('category', 'images')
    ->get();

// Создать проект с изображениями
$project = Project::create([
    'title' => 'Дом "Комфорт"',
    'slug' => 'dom-komfort',
    'description' => 'Описание проекта',
    'category_id' => 1,
    'price_from' => 2500000,
    'price_to' => 3000000,
    'area' => 120.5,
    'floors' => 2,
    'bedrooms' => 3,
    'bathrooms' => 2,
    'has_garage' => true,
    'is_published' => true,
]);

// Добавить изображения
$project->images()->create([
    'image_path' => 'projects/image1.jpg',
    'sort_order' => 1,
]);
```

### Работа с категориями

```php
// Получить все категории домов
$houseCategories = ProjectCategory::where('type', 'house')->get();

// Получить категорию с проектами
$category = ProjectCategory::with('projects')->find(1);

// Создать категорию
$category = ProjectCategory::create([
    'name' => 'Одноэтажные дома',
    'slug' => 'odnoetazhnie-doma',
    'type' => 'house',
]);
```

### Работа с блоками

```php
// Получить активный блок по имени
$block = Block::where('name', 'promo-banner')
    ->where('is_active', true)
    ->first();

// Создать блок
$block = Block::create([
    'name' => 'summer-promo',
    'title' => 'Летняя акция',
    'content' => 'Скидка 15% на все проекты домов!',
    'link' => '/projects',
    'is_active' => true,
]);
```

### Работа с настройками

```php
// Получить настройку
$phone = Setting::where('key', 'phone')->value('value');

// Получить настройки группы
$contacts = Setting::where('group', 'contacts')->get();

// Создать/обновить настройку
Setting::updateOrCreate(
    ['key' => 'phone'],
    ['value' => '+7 (495) 123-45-67', 'group' => 'contacts']
);
```

### Работа с заявками

```php
// Получить новые заявки
$newLeads = Lead::where('status', 'new')
    ->orderBy('created_at', 'desc')
    ->get();

// Создать заявку
$lead = Lead::create([
    'name' => 'Иван Иванов',
    'phone' => '+7 (495) 123-45-67',
    'email' => 'ivan@example.com',
    'source' => 'главная',
    'message' => 'Хочу заказать проект дома',
    'status' => 'new',
]);

// Изменить статус заявки
$lead->update(['status' => 'processed']);
```

## Отношения между моделями

### Service (древовидная структура)
```php
$service->parent;    // Родительская услуга
$service->children;  // Дочерние услуги
```

### Project
```php
$project->category;  // Категория проекта
$project->images;    // Изображения проекта
```

### ProjectCategory
```php
$category->projects; // Все проекты категории
```

### ProjectImage
```php
$image->project;     // Проект изображения
```

## Валидация данных

Рекомендуемая валидация при создании/обновлении:

### Page
```php
$rules = [
    'title' => 'required|string|max:255',
    'slug' => 'required|string|unique:pages,slug|max:255',
    'content' => 'required|string',
    'meta_title' => 'nullable|string|max:255',
    'meta_description' => 'nullable|string|max:500',
    'is_active' => 'boolean',
];
```

### Service
```php
$rules = [
    'title' => 'required|string|max:255',
    'slug' => 'required|string|unique:services,slug|max:255',
    'description' => 'required|string',
    'full_text' => 'required|string',
    'parent_id' => 'nullable|exists:services,id',
    'sort_order' => 'integer|min:0',
    'image' => 'nullable|string|max:255',
    'is_published' => 'boolean',
    'meta_fields' => 'nullable|json',
];
```

### Project
```php
$rules = [
    'title' => 'required|string|max:255',
    'slug' => 'required|string|unique:projects,slug|max:255',
    'description' => 'required|string',
    'category_id' => 'required|exists:project_categories,id',
    'price_from' => 'nullable|numeric|min:0',
    'price_to' => 'nullable|numeric|min:0|gte:price_from',
    'area' => 'nullable|numeric|min:0',
    'floors' => 'nullable|integer|min:1',
    'bedrooms' => 'nullable|integer|min:0',
    'bathrooms' => 'nullable|integer|min:0',
    'has_garage' => 'boolean',
    'is_featured' => 'boolean',
    'is_published' => 'boolean',
];
```

### Lead
```php
$rules = [
    'name' => 'required|string|max:255',
    'phone' => 'required|string|max:255',
    'email' => 'required|email|max:255',
    'source' => 'nullable|string|max:255',
    'message' => 'nullable|string',
    'status' => 'in:new,processed,completed',
];
```

## Полезные scopes (можно добавить в модели)

### Page
```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}
```

### Service
```php
public function scopePublished($query)
{
    return $query->where('is_published', true);
}

public function scopeRoot($query)
{
    return $query->whereNull('parent_id');
}

public function scopeOrdered($query)
{
    return $query->orderBy('sort_order');
}
```

### Project
```php
public function scopePublished($query)
{
    return $query->where('is_published', true);
}

public function scopeFeatured($query)
{
    return $query->where('is_featured', true);
}

public function scopeByCategory($query, $categoryId)
{
    return $query->where('category_id', $categoryId);
}
```

## Структура файлов

```
app/Models/
├── Block.php
├── Lead.php
├── Page.php
├── Project.php
├── ProjectCategory.php
├── ProjectImage.php
├── Service.php
├── Setting.php
└── User.php

database/migrations/
├── 2024_01_15_000001_create_pages_table.php
├── 2024_01_15_000002_create_services_table.php
├── 2024_01_15_000003_create_project_categories_table.php
├── 2024_01_15_000004_create_projects_table.php
├── 2024_01_15_000005_create_project_images_table.php
├── 2024_01_15_000006_create_blocks_table.php
├── 2024_01_15_000007_create_settings_table.php
└── 2024_01_15_000008_create_leads_table.php

database/factories/
├── BlockFactory.php
├── LeadFactory.php
├── PageFactory.php
├── ProjectCategoryFactory.php
├── ProjectFactory.php
├── ProjectImageFactory.php
├── ServiceFactory.php
└── SettingFactory.php

database/seeders/
└── CatalogSeeder.php
```
