# Руководство по локализации проекта

## Обзор

Проект полностью локализован на русский язык. Все UI элементы, сообщения об ошибках, валидации и административная панель MoonShine используют русский язык по умолчанию.

## Конфигурация

### config/app.php
```php
'locale' => env('APP_LOCALE', 'ru'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'ru'),
'faker_locale' => env('APP_FAKER_LOCALE', 'ru_RU'),
```

### config/moonshine.php
```php
'locale' => 'ru',
'locales' => [
    'ru',
],
```

## Файлы переводов (resources/lang/ru/)

### 1. messages.php
Основные сообщения приложения:
- Навигация: `home`, `site_default_name`, `site_default_description`
- Статусы: `success`, `error`, `created`, `updated`, `deleted`
- Формы: `submit`, `send_request`, `name`, `phone`, `email`, `message`
- Проекты: `projects_not_found`, `apply_filters`, `reset_filters`, `filter_projects`
- UI элементы: `more_details`, `popular`, `price`, `area`, `rooms`, `bathrooms`, `floors`, `garage`
- И многое другое...

### 2. admin.php
Переводы для административной панели:
- Разделы: `dashboard`, `pages`, `services`, `projects`, `categories`, `images`, `blocks`, `settings`, `leads`
- Действия: `create_new`, `edit`, `delete`, `save`, `cancel`, `back`
- Поля: `title`, `description`, `slug`, `content`, `is_active`, `is_published`
- Импорт: `import`, `import_projects`, `csv_file`, `mode`

### 3. validation.php
Все Laravel правила валидации на русском:
- `required`: "Поле :attribute обязательно для заполнения."
- `email`: "Поле :attribute должно содержать корректный email."
- `max.string`: "Поле :attribute не может быть больше :max символов."
- `min.string`: "Поле :attribute должно содержать минимум :min символов."
- `numeric`: "Поле :attribute должно быть числом."
- `unique`: ":attribute уже существует в системе."
- И все остальные правила Laravel

### 4. pagination.php
Переводы пагинации:
- `previous`: "&laquo; Назад"
- `next`: "Далее &raquo;"

### 5. auth.php
Сообщения аутентификации:
- `failed`: "Учетные данные не совпадают с нашими записями."
- `password`: "Пароль неверный."
- `throttle`: "Слишком много попыток входа..."

### 6. attributes.php
Человекочитаемые названия полей для валидации:
- `title`: "Название"
- `email`: "Email"
- `phone`: "Телефон"
- `name`: "Имя"
- `message`: "Сообщение"
- `area`: "Площадь (м²)"
- И все другие поля форм

## Использование в коде

### В Blade шаблонах
```blade
<!-- Простой перевод -->
<h1>{{ __('admin.projects') }}</h1>

<!-- Перевод с параметрами -->
<p>{{ __('messages.created', ['model' => 'Проект']) }}</p>

<!-- В атрибутах -->
<input placeholder="{{ __('messages.from') }}">
<button>{{ __('messages.apply_filters') }}</button>
```

### В контроллерах
```php
// Возврат JSON с переводом
return response()->json([
    'success' => true,
    'message' => __('messages.thank_you_for_feedback'),
]);

// Валидация (автоматически использует attributes.php)
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|max:255',
]);
```

### В JavaScript
Переводы передаются в JavaScript через глобальный объект:
```javascript
// В layouts/app.blade.php
window.translations = {
    'success': '{{ __("messages.success") }}',
    'error': '{{ __("messages.error") }}'
};

// Использование в JS
alert(window.translations.success);
```

## Структура переводов

### Ключевые области перевода

#### Фронтенд
- ✅ Навигация (Главная, Услуги, Проекты)
- ✅ Фильтры проектов (все поля и кнопки)
- ✅ Формы обратной связи (все поля и сообщения)
- ✅ Карточки проектов (подробнее, популярный, характеристики)
- ✅ Пагинация (назад, далее)
- ✅ Сообщения об ошибках

#### Бэкенд
- ✅ Валидация форм (все правила Laravel)
- ✅ JSON ответы API (успех, ошибка, благодарность)
- ✅ Flash сообщения (создано, обновлено, удалено)

#### Админ-панель MoonShine
- ✅ Базовая конфигурация (locale='ru')
- ✅ Переводы разделов и действий
- ✅ Импорт CSV (все UI элементы)

## Добавление новых переводов

### Шаг 1: Добавить ключ в соответствующий файл
```php
// resources/lang/ru/messages.php
'new_key' => 'Новое значение',
```

### Шаг 2: Использовать в шаблонах
```blade
<p>{{ __('messages.new_key') }}</p>
```

### Шаг 3: Использовать в контроллерах
```php
$message = __('messages.new_key');
```

## Множественные формы

Для русского языка можно использовать `trans_choice()`:
```blade
{{ trans_choice('проект|проекта|проектов', $count) }}
```

Или определить в файле переводов:
```php
'projects_count' => '{0} проектов нет|{1} :count проект|[2,4] :count проекта|[5,*] :count проектов',
```

## Рекомендации

1. **Всегда используйте __() helper** - Никогда не хардкодите русские строки
2. **Группируйте переводы логически** - messages.php для фронтенда, admin.php для админки
3. **Используйте параметры** - :attribute, :model, :value для динамических значений
4. **Проверяйте контекст** - Одно слово может переводиться по-разному в разных контекстах
5. **Документируйте новые ключи** - Добавляйте комментарии для сложных переводов

## Проверка локализации

### Убедитесь что переведено:
- [x] config/app.php - locale='ru'
- [x] config/moonshine.php - locale='ru'
- [x] resources/lang/ru/*.php - все файлы созданы
- [x] resources/views/**/*.blade.php - используют __()
- [x] app/Http/Controllers/**/*.php - используют __() для сообщений
- [x] JSON ответы возвращают переведенные сообщения
- [x] Валидация использует русские сообщения
- [x] JavaScript получает переводы через window.translations

## Расширение локализации

### Добавление нового языка (например, английского)

1. Создать папку `resources/lang/en/`
2. Скопировать все файлы из `resources/lang/ru/`
3. Перевести значения на английский
4. Обновить config/app.php:
```php
'locales' => ['ru', 'en'],
```
5. Добавить middleware для переключения языка
6. Обновить config/moonshine.php:
```php
'locales' => ['ru', 'en'],
```

## Поддержка

При возникновении проблем с локализацией:
1. Проверьте правильность ключей переводов
2. Убедитесь что файлы находятся в `resources/lang/ru/`
3. Проверьте синтаксис PHP массивов в файлах переводов
4. Очистите кэш: `php artisan cache:clear`
5. Очистите кэш конфигурации: `php artisan config:clear`
6. Очистите кэш представлений: `php artisan view:clear`

## Итоги

✅ **Русский язык установлен как язык по умолчанию**
✅ **Все UI элементы админ-панели переведены**
✅ **Фронтенд полностью локализован**
✅ **Валидации выводят русские сообщения**
✅ **Готово к использованию русскоговорящей аудиторией**

Проект готов для развертывания с полной поддержкой русского языка!
