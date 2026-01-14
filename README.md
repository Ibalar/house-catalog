# Laravel 12 + MoonShine 4 - Веб-каталог строительной организации

Этот проект представляет собой базовую установку Laravel 12 с MoonShine 4 админ-панелью и системой аутентификации для создания веб-каталога строительной организации.

## Технический стек

- **Laravel**: 12.x
- **MoonShine**: 4.x
- **PHP**: 8.3+
- **MySQL**: 5.7+ / 8.0+
- **Composer**: 2.x

## Возможности

- ✅ Laravel 12 (последняя версия)
- ✅ MoonShine 4 админ-панель
- ✅ Система аутентификации для админ-панели
- ✅ MySQL база данных
- ✅ Готовая структура для добавления моделей и контента

## Требования

Перед установкой убедитесь, что у вас установлены:

- PHP >= 8.3
- Composer
- MySQL >= 5.7 или MySQL 8.0+
- Node.js и NPM (для сборки фронтенд-ассетов)

## Установка

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd <project-directory>
```

### 2. Установка зависимостей

```bash
composer install
```

### 3. Настройка окружения

Скопируйте файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

Отредактируйте `.env` файл и настройте подключение к базе данных MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Генерация ключа приложения

Если ключ не был сгенерирован автоматически:

```bash
php artisan key:generate
```

### 5. Создание базы данных

Создайте базу данных MySQL с именем, указанным в `.env` (по умолчанию `laravel`):

```sql
CREATE DATABASE laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Запуск миграций

Выполните миграции для создания таблиц в базе данных:

```bash
php artisan migrate
```

Это создаст следующие таблицы:
- `users` - таблица пользователей Laravel
- `moonshine_users` - таблица администраторов MoonShine
- `moonshine_user_roles` - таблица ролей администраторов
- `cache`, `jobs`, `sessions` - служебные таблицы Laravel

### 7. Создание администратора MoonShine

Создайте первого администратора для доступа к админ-панели:

```bash
php artisan moonshine:user
```

Команда запросит:
- **Email**: admin@example.com (или ваш email)
- **Name**: Admin (или ваше имя)
- **Password**: введите надежный пароль

Или создайте администратора с параметрами в одной команде:

```bash
php artisan moonshine:user --username=admin@example.com --name=Admin --password=password
```

### 8. Запуск приложения

Запустите встроенный сервер разработки Laravel:

```bash
php artisan serve
```

Приложение будет доступно по адресу: [http://localhost:8000](http://localhost:8000)

### 9. Доступ к админ-панели

MoonShine админ-панель доступна по адресу:

**URL**: [http://localhost:8000/admin](http://localhost:8000/admin)

Войдите используя учетные данные администратора, созданные на шаге 7.

## Структура проекта

```
├── app/
│   ├── Models/           # Eloquent модели
│   ├── Providers/        # Service providers
│   │   └── MoonShineServiceProvider.php  # Конфигурация MoonShine
│   └── ...
├── config/
│   ├── moonshine.php     # Конфигурация MoonShine
│   └── ...
├── database/
│   ├── migrations/       # Миграции базы данных
│   └── ...
├── public/
│   └── vendor/
│       └── moonshine/    # Ассеты MoonShine
└── ...
```

## Конфигурация MoonShine

### Основные настройки

Конфигурация MoonShine находится в файле `config/moonshine.php`. Основные параметры:

- **prefix**: `admin` - префикс URL для админ-панели (можно изменить)
- **auth.enabled**: `true` - аутентификация включена
- **auth.guard**: `moonshine` - guard для аутентификации
- **auth.model**: `MoonshineUser::class` - модель пользователя

### Добавление ресурсов

Для создания нового ресурса (например, для управления товарами):

```bash
php artisan moonshine:resource ProductResource
```

Затем зарегистрируйте ресурс в `app/Providers/MoonShineServiceProvider.php`:

```php
public function boot(CoreContract $core, MoonShineConfigurator $config): void
{
    $core
        ->resources([
            new ProductResource(),
        ])
        ->pages([
        ]);
}
```

### Создание страниц

Для создания кастомной страницы:

```bash
php artisan moonshine:page CustomPage
```

## Дополнительные команды

### Очистка кеша

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Оптимизация для продакшена

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### MoonShine команды

```bash
# Создать ресурс
php artisan moonshine:resource ModelNameResource

# Создать страницу
php artisan moonshine:page PageName

# Создать пользователя
php artisan moonshine:user

# Создать policy
php artisan moonshine:policy ModelName
```

## Разработка

### Установка фронтенд-зависимостей

```bash
npm install
```

### Сборка ассетов

```bash
# Для разработки
npm run dev

# Для продакшена
npm run build
```

## Следующие шаги

После успешной установки вы можете:

1. Создать модели для каталога продукции
2. Создать ресурсы MoonShine для управления контентом
3. Настроить формы и поля в админ-панели
4. Добавить фронтенд для публичного каталога
5. Настроить загрузку изображений
6. Добавить категории и фильтры товаров

## Документация

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [MoonShine Documentation](https://moonshine-laravel.com/docs)

## Лицензия

Этот проект использует Laravel framework, который распространяется под лицензией [MIT license](https://opensource.org/licenses/MIT).
