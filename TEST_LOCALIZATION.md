# Тест локализации

## Проверка конфигурации

### 1. Проверить config/app.php
```bash
cat config/app.php | grep "locale"
```
Должно быть:
- `'locale' => env('APP_LOCALE', 'ru')`
- `'fallback_locale' => env('APP_FALLBACK_LOCALE', 'ru')`
- `'faker_locale' => env('APP_FAKER_LOCALE', 'ru_RU')`

### 2. Проверить config/moonshine.php
```bash
cat config/moonshine.php | grep -A 3 "Localizations"
```
Должно быть:
- `'locale' => 'ru'`
- `'locales' => ['ru']`

### 3. Проверить наличие файлов переводов
```bash
ls -la resources/lang/ru/
```
Должны быть файлы:
- admin.php
- attributes.php
- auth.php
- messages.php
- pagination.php
- validation.php

## Тестирование переводов

### Тест 1: Проверка ключей в Blade
```bash
grep -r "__('messages\." resources/views/ | head -5
grep -r "__('admin\." resources/views/ | head -5
grep -r "__('attributes\." resources/views/ | head -5
```

### Тест 2: Проверка контроллеров
```bash
grep -r "__('messages\." app/Http/Controllers/
grep -r "Главная\|Проекты\|Услуги" app/Http/Controllers/
```
Не должно быть хардкод строк, только __() вызовы.

### Тест 3: Проверка валидации
```bash
cat app/Http/Controllers/LeadController.php | grep "validate"
```
Валидация должна автоматически использовать переводы из validation.php.

## Ручное тестирование

### 1. Главная страница (/)
- [ ] Заголовок сайта на русском
- [ ] "Популярные услуги" вместо hardcode
- [ ] "Популярные проекты" вместо hardcode
- [ ] Кнопки "Подробнее"
- [ ] Форма: "Имя *", "Телефон *", "Email *", "Сообщение"
- [ ] Кнопка "Отправить заявку"

### 2. Страница услуг (/services)
- [ ] Заголовок "Услуги"
- [ ] Breadcrumbs: "Главная > Услуги"
- [ ] Кнопки "Подробнее"

### 3. Страница проектов (/projects)
- [ ] Заголовок "Проекты"
- [ ] Breadcrumbs: "Главная > Проекты"
- [ ] Фильтры на русском:
  - [ ] "Фильтр проектов"
  - [ ] "Категории"
  - [ ] "Площадь"
  - [ ] "Комнаты"
  - [ ] "Санузлы"
  - [ ] "Этажи"
  - [ ] "Гараж"
  - [ ] "Тип крыши"
  - [ ] "Стиль"
  - [ ] "Цена"
- [ ] Кнопки "Применить фильтры" и "Сбросить все фильтры"
- [ ] Сортировка:
  - [ ] "По умолчанию"
  - [ ] "По популярности"
  - [ ] "Новые первыми"
  - [ ] "Цена: по возрастанию"
  - [ ] "Цена: по убыванию"
- [ ] Значок "⭐ Популярный"
- [ ] Кнопка "Подробнее"
- [ ] "Проекты не найдены. Попробуйте изменить фильтры."

### 4. Навигация
- [ ] "Главная"
- [ ] "Услуги"
- [ ] "Проекты"

### 5. Футер
- [ ] "© 2024 [Название сайта]. Все права защищены."

### 6. Форма обратной связи
- [ ] Отправить форму с пустыми полями -> Валидация на русском
- [ ] Отправить форму с неверным email -> "Поле email должно содержать корректный email."
- [ ] Отправить корректную форму -> "Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время."

### 7. Admin панель (/admin)
- [ ] Войти в админку
- [ ] Проверить что все меню на русском (если MoonShine поддерживает)
- [ ] Создать/редактировать проект -> проверить названия полей

## Автоматические тесты

Можно создать PHPUnit тесты:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    /** @test */
    public function config_uses_russian_locale()
    {
        $this->assertEquals('ru', config('app.locale'));
        $this->assertEquals('ru', config('app.fallback_locale'));
        $this->assertEquals('ru_RU', config('app.faker_locale'));
        $this->assertEquals('ru', config('moonshine.locale'));
    }

    /** @test */
    public function translation_files_exist()
    {
        $this->assertFileExists(lang_path('ru/messages.php'));
        $this->assertFileExists(lang_path('ru/admin.php'));
        $this->assertFileExists(lang_path('ru/validation.php'));
        $this->assertFileExists(lang_path('ru/pagination.php'));
        $this->assertFileExists(lang_path('ru/auth.php'));
        $this->assertFileExists(lang_path('ru/attributes.php'));
    }

    /** @test */
    public function translations_work()
    {
        $this->assertEquals('Главная', __('messages.home'));
        $this->assertEquals('Проекты', __('admin.projects'));
        $this->assertEquals('Email', __('attributes.email'));
        $this->assertEquals('&laquo; Назад', __('pagination.previous'));
    }

    /** @test */
    public function validation_messages_are_in_russian()
    {
        $response = $this->post(route('leads.store'), []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'phone', 'email']);
        
        // Проверяем что сообщения на русском
        $errors = $response->json('errors');
        $this->assertStringContainsString('обязательно', $errors['name'][0]);
    }
}
```

## Результаты

- ✅ Конфигурация настроена на русский язык
- ✅ Все файлы переводов созданы
- ✅ Blade шаблоны используют __() helper
- ✅ Контроллеры возвращают переведенные сообщения
- ✅ Валидация на русском языке
- ✅ JavaScript получает переводы через window.translations
- ✅ Breadcrumbs на русском
- ✅ SEO мета-теги используют переводы

## Дополнительная информация

См. LOCALIZATION_GUIDE.md для полного руководства по использованию локализации.
