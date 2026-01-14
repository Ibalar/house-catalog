# Строительный каталог - Laravel 12 + MoonShine 4

Современный веб-каталог строительной компании с полным функционалом SEO, кеширования, безопасности и оптимизации производительности.

## Технический стек

- **Laravel**: 12.x
- **MoonShine**: 4.x
- **PHP**: 8.3+
- **MySQL**: 5.7+ / 8.0+
- **Composer**: 2.x
- **Vite**: 5.x

## Возможности

### Основной функционал
- ✅ Каталог проектов домов и бань с фильтрацией
- ✅ Система услуг с иерархической структурой
- ✅ Статические страницы с удобным редактированием
- ✅ Формы заявок с валидацией
- ✅ Админ-панель MoonShine 4

### SEO Оптимизация
- ✅ Meta теги (title, description, canonical)
- ✅ Open Graph теги для социальных сетей
- ✅ BreadcrumbList schema для навигации в SERP
- ✅ Organization schema для главной страницы
- ✅ LocalBusiness schema с контактной информацией
- ✅ Product/Property schema для страниц проектов
- ✅ Генерация sitemap.xml
- ✅ robots.txt с ссылкой на sitemap
- ✅ Чистые URL с читаемыми slug

### Кеширование
- ✅ Кеширование запросов к базе данных
- ✅ Кеширование блоков контента
- ✅ Кеширование настроек сайта
- ✅ Автоматическая инвалидация кеша при изменении данных
- ✅ Observers для всех моделей (Page, Service, Project, Block, Setting)

### Безопасность
- ✅ CSRF защита на всех формах
- ✅ XSS защита через экранирование Blade
- ✅ Валидация всех входных данных
- ✅ Rate limiting (10 заявок в час с IP)
- ✅ Санитизация пользовательского контента
- ✅ Безопасные заголовки (CSP, X-Frame-Options, XSS-Protection)
- ✅ Referrer-Policy и Permissions-Policy
- ✅ HSTS в production (при HTTPS)

### Производительность
- ✅ Оптимизированные SQL запросы с select()
- ✅ Eager loading отношений для избежания N+1
- ✅ Lazy loading изображений (loading="lazy")
- ✅ Пагинация результатов (12 на страницу)
- ✅ Логирование ошибок и событий
- ✅ ImageService для оптимизации изображений

## Требования

Перед установкой убедитесь, что у вас установлены:

- PHP >= 8.3
- Composer
- MySQL >= 5.7 или MySQL 8.0+
- Node.js и NPM (для сборки фронтенд-ассетов)
- Расширения PHP: mbstring, pdo, mysql, gd

## Установка

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd <project-directory>
```

### 2. Установка зависимостей

```bash
composer install
npm install
```

### 3. Настройка окружения

Скопируйте файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

Отредактируйте `.env` файл:

```env
APP_NAME="Строительная компания"
APP_URL=http://localhost:8000
APP_ENV=local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=your_password

# Кеширование (рекомендуется redis в production)
CACHE_DRIVER=file
# CACHE_DRIVER=redis

# Storage
FILESYSTEM_DISK=public
```

### 4. Генерация ключа приложения

```bash
php artisan key:generate
```

### 5. Настройка базы данных

Создайте базу данных и выполните миграции:

```bash
php artisan migrate
php artisan db:seed
```

### 6. Создание символической ссылки для storage

```bash
php artisan storage:link
```

### 7. Сборка фронтенд-ассетов

```bash
npm run build
```

или для разработки:

```bash
npm run dev
```

### 8. Генерация sitemap

```bash
php artisan sitemap:generate
```

### 9. Запуск сервера разработки

```bash
php artisan serve
```

Сайт будет доступен по адресу: http://localhost:8000

## Доступ к админ-панели

- URL: `/admin`
- Данные для входа создаются через seeder

## Оптимизация для Production

### Кеширование конфигурации

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

### Регенерация sitemap

```bash
php artisan sitemap:generate
```

Рекомендуется добавить команду в cron:

```bash
# Каждый день в 3 часа ночи
0 3 * * * cd /path/to/project && php artisan sitemap:generate
```

## Структура проекта

### Модели

- **Page** - статические страницы
- **Service** - услуги (с parent_id для иерархии)
- **Project** - проекты домов и бань
- **ProjectCategory** - категории проектов
- **ProjectImage** - изображения проектов
- **Block** - блоки контента (баннеры, футер и т.д.)
- **Setting** - настройки сайта
- **Lead** - заявки с форм

### Контроллеры

- **HomeController** - главная страница
- **PageController** - статические страницы
- **ServiceController** - услуги
- **ProjectController** - проекты с фильтрацией
- **LeadController** - обработка заявок

### Представления

```
resources/views/
├── layouts/
│   └── app.blade.php          # Главный layout
├── home.blade.php             # Главная страница
├── pages/
│   └── show.blade.php         # Статическая страница
├── services/
│   ├── index.blade.php         # Список услуг
│   └── show.blade.php         # Детальная услуга
└── projects/
    ├── index.blade.php         # Каталог проектов
    └── show.blade.php         # Детальный проект
```

### Observers

Все модели имеют наблюдатели для инвалидации кеша:
- **PageObserver**
- **ServiceObserver**
- **ProjectObserver**
- **BlockObserver**
- **SettingObserver**

### Хелперы

- **SeoHelper** - генерация meta тегов и schema.org
- **ImageService** - оптимизация изображений
- **get_block()** - получение блоков контента с кешированием
- **get_setting()** - получение настроек с кешированием

## Маршруты

| Путь | Контроллер | Описание |
|-------|-------------|-----------|
| `/` | HomeController::index | Главная страница |
| `/services` | ServiceController::index | Список услуг |
| `/services/{slug}` | ServiceController::show | Детальная услуга |
| `/projects` | ProjectController::index | Каталог проектов |
| `/projects/{slug}` | ProjectController::show | Детальный проект |
| `/leads` | LeadController::store | Создание заявки (POST) |
| `/{slug}` | PageController::show | Статическая страница |
| `/sitemap.xml` | - | Sitemap |
| `/robots.txt` | - | Robots.txt |

## Кеширование

Кеширование используется для:

### Запросы к базе данных
- `home_top_services` - топ услуг на главной (1 час)
- `home_featured_projects` - featured проекты на главной (1 час)
- `services_root` - корневые услуги (2 часа)
- `projects_categories` - категории проектов (2 часа)
- `projects_available_values` - доступные значения фильтров (2 часа)

### Контент
- `blocks_all` - все активные блоки (1 час)
- `block_{name}` - конкретный блок (1 час)
- `setting_{key}` - конкретная настройка (1 день)

### Инвалидация

Кеш автоматически очищается при:
- Создании/обновлении/удалении Page
- Создании/обновлении/удалении Service
- Создании/обновлении/удалении Project
- Создании/обновлении/удалении Block
- Создании/обновлении/удалении Setting

## SEO

### Meta теги
Каждая страница имеет:
- title
- meta description
- canonical URL
- Open Graph теги (title, description, image, url, type)

### Schema.org
- **BreadcrumbList** - на всех страницах
- **Organization** - на главной странице
- **LocalBusiness** - на главной странице
- **Property/Product** - на страницах проектов

### Sitemap
Генерируется автоматически командой `php artisan sitemap:generate`:
- Приоритеты: Projects 0.9, Services 0.8, Pages 0.7
- Включает только опубликованные/активные элементы
- lastmod берется из updated_at

## Безопасность

### CSRF
- Все формы включают `@csrf` токен
- Middleware VerifyCsrfToken активен

### XSS
- Все выводы через `{{ }}` (автоматическое экранирование)
- HTML контент (TinyMCE) через `{!! !!}`
- Санитизация сообщений в LeadController

### Валидация
- LeadController: name, phone (regex), email, message (max:1000)
- ProjectController: все фильтры валидированы

### Rate Limiting
- LeadController: 10 заявок в час с одного IP

### Security Headers
- Content-Security-Policy
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy
- Strict-Transport-Security (production + HTTPS)

## Производительность

### Query Optimization
- Используется `select()` для выборки только нужных полей
- Eager loading отношений через `with()`
- Индексы на часто используемых полях

### Image Optimization
- Lazy loading для всех изображений
- ImageService для ресайза изображений
- Поддержка размеров: thumbnail (400x300), medium (800x600), large (1200x800)

### Pagination
- 12 проектов на странице
- Используется `paginate()` для больших наборов данных

### Логирование
- Все ошибки логируются в production
- Предупреждения при неудачной валидации
- Логирование создания заявок

## Development vs Production

### Development
- `APP_ENV=local`
- Отображение ошибок включено
- Логи детальные
- Vite dev server

### Production
- `APP_ENV=production`
- Отключение отладки
- Кеширование конфигурации и маршрутов
- HSTS заголовки
- HTTPS

## Тестирование

Для запуска тестов:

```bash
php artisan test
```

## Лицензия

Этот проект является собственностью компании.

## Поддержка

Для вопросов и поддержки обращайтесь к команде разработки.
