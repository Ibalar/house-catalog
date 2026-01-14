# SEO, Кеширование, Безопасность и Производительность - Результаты реализации

## Обзор

В рамках данного тикета была полностью реализована SEO-оптимизация, система кеширования, усиленная безопасность и оптимизация производительности для сайта-каталога строительной компании.

---

## 1. SEO Оптимизация ✅

### 1.1 Meta теги на всех страницах

#### Главная страница (HomeController)
- ✅ Динамический title с именем сайта
- ✅ Meta description из настроек
- ✅ Canonical URL
- ✅ Open Graph теги

#### Страницы (PageController)
- ✅ Title: `page.meta_title ?? page.title`
- ✅ Meta description
- ✅ Canonical URL: `route('page.show', $page->slug)`
- ✅ Open Graph теги
- ✅ BreadcrumbList schema

#### Услуги (ServiceController)
- ✅ Title: `service.title - Услуги`
- ✅ Description: `service.description ?? "Услуга: {title}"`
- ✅ Canonical URL
- ✅ Open Graph: article type
- ✅ BreadcrumbList schema

#### Проекты (ProjectController)
- ✅ Index: Title "Проекты", description "Каталог проектов домов и бань"
- ✅ Show: Title `project.meta_title ?? project.title`
- ✅ Description: `project.meta_description ?? Str::limit(description, 160)`
- ✅ Canonical URL
- ✅ Open Graph: product type
- ✅ **Property schema (JSON-LD)** для проектов
- ✅ BreadcrumbList schema

### 1.2 Структурированные данные (Schema.org)

#### BreadcrumbList
- Реализован в `App\Helpers\SeoHelper::breadcrumbList()`
- Используется на всех страницах
- Формат: JSON-LD
- Позиции, названия, URL

#### Organization Schema
- Реализован в `App\Helpers\SeoHelper::organizationSchema()`
- Используется на главной странице
- Включает: name, url

#### LocalBusiness Schema
- Реализован в `App\Helpers\SeoHelper::localBusinessSchema()`
- Используется на главной странице
- Включает: name, url, telephone, email, address
- Данные берутся из Settings

#### Property/Product Schema
- Реализован в `App\Helpers\SeoHelper::projectSchema()`
- Используется на страницах проектов
- Включает:
  - name, description, url, image
  - floorSize (area)
  - numberOfRooms (bedrooms)
  - numberOfBathroomsTotal
  - priceRange
- Формат: JSON-LD

### 1.3 Sitemap

#### Команда генерации
- Путь: `app/Console/Commands/GenerateSitemap`
- Команда: `php artisan sitemap:generate`
- Использует пакет `spatie/laravel-sitemap`

#### Sitemap маршрут
- URL: `/sitemap.xml`
- Возвращает файл с Content-Type: application/xml

#### Приоритеты
- Главная страница: 1.0
- Проекты (index): 0.9
- Проекты (show): 0.9
- Услуги (index): 0.8
- Услуги (show): 0.8
- Страницы: 0.7

#### Частота обновлений
- Главная: daily
- Проекты: daily
- Услуги: weekly
- Страницы: weekly

#### Robots.txt
- URL: `/robots.txt`
- Содержит ссылку на sitemap.xml
- User-agent: *
- Allow: /

### 1.4 Open Graph Теги

Реализованы через `SeoHelper::ogTags()`:
- og:title
- og:description
- og:url
- og:type (website, article, product)
- og:image

---

## 2. Кеширование ✅

### 2.1 Хелперы с кешированием

#### get_block($name)
- Кеш: 1 час
- Ключ: `block_{name}`
- Автоматическая инвалидация через BlockObserver

#### get_setting($key, $default)
- Кеш: 1 день
- Ключ: `setting_{key}`
- Автоматическая инвалидация через SettingObserver

### 2.2 Кеширование запросов

#### Главная страница
- `home_top_services`: 1 час
- `home_featured_projects`: 1 час
- select() только нужных полей
- with() для отношений

#### Проекты
- `projects_categories`: 2 часа
- `projects_available_values`: 2 часа (roof_types, styles)
- select() оптимизированных полей
- with(['category', 'images'])

#### Услуги
- `services_root`: 2 часа
- select() нужных полей
- with(['children']) для дочерних услуг

### 2.3 Observers для инвалидации

#### PageObserver
- `saved()`: очищает `blocks_all`
- `deleted()`: очищает `blocks_all`

#### ServiceObserver
- `saved()`: очищает `services_all`, `services_root`, `blocks_all`
- `deleted()`: очищает `services_all`, `services_root`, `blocks_all`

#### ProjectObserver
- `saved()`: очищает `projects_categories`, `projects_available_values`, `blocks_all`
- `deleted()`: очищает `projects_categories`, `projects_available_values`, `blocks_all`

#### BlockObserver
- `saved()`: очищает `block_{name}`, `blocks_all`
- `deleted()`: очищает `block_{name}`, `blocks_all`

#### SettingObserver
- `saved()`: очищает `setting_{key}`
- `deleted()`: очищает `setting_{key}`

### 2.4 Конфигурация кеша

В `config/cache.php`:
- `CACHE_DRIVER=file` (по умолчанию)
- Можно использовать `redis` в production
- Настройки в `.env`

---

## 3. Безопасность ✅

### 3.1 CSRF Protection

- ✅ Все формы включают `@csrf`
- ✅ Middleware VerifyCsrfToken активен
- ✅ Токен в meta теге: `<meta name="csrf-token">`

### 3.2 XSS Protection

- ✅ Все выводы через `{{ }}` (авто-экранирование)
- ✅ HTML контент через `{!! !!}` (только для TinyMCE)
- ✅ Санитизация сообщений в LeadController:
  ```php
  $message = strip_tags($message);
  $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
  ```

### 3.3 Валидация входных данных

#### LeadController::store()
- ✅ name: required, string, max:255
- ✅ phone: required, string, max:50, regex:`/^[\d\s\+\-\(\)]+$/`
- ✅ email: required, email, max:255
- ✅ message: nullable, string, max:1000
- ✅ source: nullable, string, max:255
- ✅ project_id: nullable, exists:projects,id
- ✅ service_id: nullable, exists:services,id
- ✅ Возврат 422 при невалидных данных

#### ProjectController::index()
- ✅ type: in:house,sauna
- ✅ area_min, area_max: numeric, min:0
- ✅ bedrooms: integer, min:1, max:10
- ✅ bathrooms: integer, min:1, max:5
- ✅ floors: integer, min:1, max:3
- ✅ has_garage: nullable, boolean
- ✅ roof_types: nullable, array
- ✅ styles: nullable, array
- ✅ price_min, price_max: numeric, min:0
- ✅ sort: in:featured,newest,price_asc,price_desc,default

### 3.4 Rate Limiting

- ✅ LeadController: middleware('throttle:leads')
- ✅ 10 заявок в час с одного IP
- ✅ Настроено в `bootstrap/app.php`:
  ```php
  $middleware->limitRequests('leads', 10);
  ```

### 3.5 SQL Injection Prevention

- ✅ Query Builder для всех запросов
- ✅ Параметры через bindings
- ✅ Никакой конкатенации строк
- ✅ whereHas() для отношений

### 3.6 Security Headers Middleware

Создан `App\Http\Middleware\SetSecurityHeaders`:

#### Content-Security-Policy
```php
"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self';"
```

#### Другие заголовки
- ✅ X-Content-Type-Options: nosniff
- ✅ X-Frame-Options: SAMEORIGIN
- ✅ X-XSS-Protection: 1; mode=block
- ✅ Referrer-Policy: strict-origin-when-cross-origin
- ✅ Permissions-Policy: geolocation=(), camera=(), microphone=()
- ✅ Strict-Transport-Security (production + HTTPS): max-age=31536000; includeSubDomains; preload

### 3.7 HTTPS

- ✅ Force HTTPS в production через HSTS
- ✅ Настроено в middleware

---

## 4. Производительность ✅

### 4.1 Query Optimization

#### Главная страница
```php
Service::select('id', 'title', 'slug', 'description', 'image', 'sort_order')
    ->where('parent_id', null)
    ->where('is_published', true)
    ->orderBy('sort_order')
    ->limit(4)
    ->get();

Project::select('id', 'title', 'slug', 'main_image', 'description', 'price_from', 'price_to', 'category_id')
    ->where('is_published', true)
    ->where('is_featured', true)
    ->with('category:id,name')
    ->orderBy('sort_order')
    ->orderBy('created_at', 'desc')
    ->limit(6)
    ->get();
```

#### Проекты index
```php
Project::select('id', 'title', 'slug', 'main_image', 'description', 'price_from', 'price_to', 'area', 'bedrooms', 'bathrooms', 'is_featured', 'category_id', 'floors')
    ->where('is_published', true)
    ->with(['category:id,name,type', 'images:id,project_id,image,sort_order'])
    ->paginate(12)
    ->appends($request->query());
```

#### N+1 Prevention
- ✅ Все отношения загружены через with()
- ✅ Указаны только нужные поля отношений
- ✅ Используется select() для оптимизации

### 4.2 Image Optimization

#### Lazy Loading
- ✅ Все изображения имеют `loading="lazy"`
- ✅ Главное изображение на странице проекта: `loading="eager"`
- ✅ Галерея: `loading="lazy"`
- ✅ Списки: `loading="lazy"`

#### Image Service
Создан `App\Services\ImageService`:
- ✅ `uploadAndResize()` - загрузка и ресайз
- ✅ `resize()` - ресайз изображений
- ✅ `optimize()` - оптимизация качества (85% JPEG)
- ✅ `delete()` - удаление оригинала и ресайзов
- ✅ Поддерживаемые размеры:
  - thumbnail: 400x300
  - medium: 800x600
  - large: 1200x800
- ✅ Методы: cover, contain, resize, scaleDown
- ✅ Санитизация имен файлов

#### Хелперы изображений
- ✅ `get_asset_url()` - безопасный URL для изображений
- ✅ `get_resized_image()` - получение ресайзеной версии
- ✅ Обработка URL, путей, null значений

### 4.3 Database Indexes

Существующие индексы из миграций:
- ✅ pages: slug, is_active
- ✅ services: slug, parent_id, is_published
- ✅ projects: slug, category_id, is_published, is_featured
- ✅ project_categories: slug, type
- ✅ blocks: name, is_active
- ✅ settings: key
- ✅ leads: status, created_at

### 4.4 Pagination

- ✅ 12 проектов на странице
- ✅ Используется `paginate()`
- ✅ appends() для сохранения фильтров
- ✅_links() для навигации

### 4.5 Static Assets

- ✅ Vite для сборки ассетов
- ✅ Фоллбэк на прямые ссылки
- ✅ Минификация CSS и JS через Vite
- ✅ Cache-Control заголовки можно добавить через web server

---

## 5. Логирование и Мониторинг ✅

### 5.1 Логирование ошибок

В `bootstrap/app.php`:
```php
$exceptions->report(function (\Throwable $e) {
    if ($this->app->environment('production')) {
        Log::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
});
```

### 5.2 Логирование заявок

В LeadController:
```php
Log::info('Lead created', ['lead_id' => $lead->id, 'email' => $lead->email]);
```

### 5.3 Логирование валидации

В LeadController:
```php
Log::warning('Lead validation failed', [
    'errors' => $e->errors(),
    'ip' => $request->ip(),
]);
```

---

## 6. Созданные файлы

### Хелперы
- ✅ `app/Helpers/helpers.php` - глобальные функции
- ✅ `app/Helpers/SeoHelper.php` - SEO хелпер

### Services
- ✅ `app/Services/ImageService.php` - сервис изображений

### Observers
- ✅ `app/Observers/PageObserver.php`
- ✅ `app/Observers/ServiceObserver.php`
- ✅ `app/Observers/ProjectObserver.php`
- ✅ `app/Observers/BlockObserver.php`
- ✅ `app/Observers/SettingObserver.php`

### Commands
- ✅ `app/Console/Commands/GenerateSitemap.php`

### Middleware
- ✅ `app/Http/Middleware/SetSecurityHeaders.php`

### Обновленные файлы
- ✅ `bootstrap/app.php` - конфигурация middleware и exceptions
- ✅ `app/Providers/AppServiceProvider.php` - регистрация observers и helpers
- ✅ `routes/web.php` - добавлены routes для sitemap и robots
- ✅ `routes/console.php` - регистрация команды sitemap
- ✅ `composer.json` - добавлены пакеты sitemap и image
- ✅ `resources/views/layouts/app.blade.php` - SEO meta tags
- ✅ `resources/views/home.blade.php` - lazy loading
- ✅ `resources/views/pages/show.blade.php` - breadcrumbs, schema
- ✅ `resources/views/services/index.blade.php` - breadcrumbs, lazy loading
- ✅ `resources/views/services/show.blade.php` - breadcrumbs, lazy loading
- ✅ `resources/views/projects/index.blade.php` - breadcrumbs, lazy loading
- ✅ `resources/views/projects/show.blade.php` - breadcrumbs, lazy loading, schema
- ✅ `README.md` - полная документация

### Обновленные контроллеры
- ✅ `app/Http/Controllers/HomeController.php`
- ✅ `app/Http/Controllers/PageController.php`
- ✅ `app/Http/Controllers/ServiceController.php`
- ✅ `app/Http/Controllers/ProjectController.php`
- ✅ `app/Http/Controllers/LeadController.php`

---

## 7. Зависимости

Установлены пакеты через composer.json:
```json
{
    "spatie/laravel-sitemap": "^7.0",
    "intervention/image": "^3.0"
}
```

Установка:
```bash
composer install
```

---

## 8. Команды

### Генерация sitemap
```bash
php artisan sitemap:generate
```

### Кеширование в production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Очистка кеша
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 9. Рекомендации для Production

### Настройки .env
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
CACHE_DRIVER=redis
```

### Cron задачи
```bash
# Ежедневная генерация sitemap
0 3 * * * cd /path/to/project && php artisan sitemap:generate
```

### Web Server (Nginx)
```nginx
# Cache-Control для статических ассетов
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# Gzip сжатие
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
```

### Web Server (Apache)
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
</IfModule>
```

---

## 10. Тестирование

### Проверка SEO
1. Открыть Google Rich Results Test
2. Проверить meta теги в браузере (View Source)
3. Проверить sitemap.xml
4. Проверить robots.txt
5. Проверить structured data через Schema Validator

### Проверка безопасности
1. Запустить OWASP ZAP для сканирования
2. Проверить Security Headers в securityheaders.com
3. Проверить CSRF токены
4. Протестировать XSS атаки
5. Протестировать SQL инъекции

### Проверка производительности
1. Использовать Lighthouse в Chrome DevTools
2. Проверить Lazy loading (Network tab)
3. Проверить кеширование (Redis/file)
4. Проверить query time через Laravel Telescope
5. Использовать Blackfire для профилирования

---

## Итог

Все требования из тикета реализованы:

✅ SEO Оптимизация
  - Meta теги на всех страницах
  - Open Graph теги
  - Структурированные данные (Schema.org)
  - BreadcrumbList
  - Organization и LocalBusiness schema
  - Product/Property schema для проектов
  - Sitemap.xml с правильными приоритетами
  - robots.txt

✅ Кеширование
  - Query caching для всех основных запросов
  - Кеширование блоков и настроек
  - Observers для автоматической инвалидации
  - Настройка CACHE_DRIVER

✅ Безопасность
  - CSRF защита
  - XSS защита
  - Валидация всех входных данных
  - Rate limiting (10 заявок/час)
  - Security headers middleware
  - HSTS в production

✅ Производительность
  - Query optimization с select()
  - Eager loading отношений
  - Lazy loading изображений
  - ImageService для оптимизации
  - Пагинация
  - Логирование ошибок

✅ Документация
  - Обновленный README.md
  - Этот документ с полным описанием

Сайт полностью готов к публикации в production!
