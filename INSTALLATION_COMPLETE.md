# MoonShine 4 Admin Panel - Installation & Configuration

## 🚀 Установка зависимостей

```bash
# Установка TinyMCE и Laravel FileManager (нужно выполнить вручную)
composer require tinymce/tinymce unisharp/laravel-filemanager

# Опубликование конфигурации и ассетов
php artisan vendor:publish --tag=lfm_public
php artisan vendor:publish --tag=lfm_config
```

## 🔧 Настройка Laravel FileManager

### 1. Обновите `config/lfm.php`

```php
return [
    'use_package_routes' => true,
    'allow_private_folder' => false,
    'allow_shared_folder' => true,
    'shared_folder_name' => 'shares',
    'images_upload_path' => '/storage/photos',
    'files_upload_path' => '/storage/files',
    
    'disk' => 'public',
    'middlewares' => ['web', 'auth:moonshine'],
    
    'should_create_thumbnails' => true,
    'raster_mimetypes' => [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/webp',
    ],
];
```

### 2. Создайте маршруты в `routes/web.php`

```php
use UniSharp\LaravelFilemanager\Lfm;

Route::group(['prefix' => 'admin/filemanager', 'middleware' => ['web', 'auth:moonshine']], function () {
    Lfm::routes();
});
```

### 3. Настройте символическую ссылку

```bash
php artisan storage:link
```

## 📋 Интеграция TinyMCE

### 1. Создайте custom MoonShine field

Создайте `app/MoonShine/Field/TinyMCEField.php`:

```php
<?php

declare(strict_types=1);

namespace App\MoonShine\Field;

use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Textarea;

class TinyMCEField extends Textarea
{
    public function __construct(
        Closure|string|null $label = null,
        ?string $column = null,
        ?Closure $formatted = null,
        ?Closure $preview = null,
        ?string $hint = null,
        ?string $type = 'textarea',
        ?bool $isGroup = true,
    ) {
        parent::__construct($label, $column, $formatted, $preview, $hint, $type, $isGroup);
    }

    protected function prepareSound(): void
    {
        parent::prepareSound();

        $this->customAttributes([
            'data-tinymce' => 'true',
        ]);
    }
}
```

### 2. Добавьте TinyMCE и FileManager JS в админ-панель

Создайте `resources/views/vendor/moonshine/layouts/shared/head.blade.php`:

```blade
@extends('moonshine::layouts.shared.head')

@push('scripts')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editors = document.querySelectorAll('[data-tinymce="true"]');
    
    editors.forEach(editor => {
        tinymce.init({
            selector: `#${editor.id}`,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste imagetools wordcount',
                'filemanager'
            ],
            toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            height: 400,
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            
            external_filemanager_path: '/admin/filemanager/',
            filemanager_title: 'File Manager',
            external_plugins: {
                filemanager: '/vendor/laravel-filemanager/plugin.min.js'
            },
            
            file_picker_callback: function(callback, value, meta) {
                let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                let y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

                let type = 'file';
                if (meta.filetype === 'image') {
                    type = 'image';
                }

                tinymce.activeEditor.windowManager.openUrl({
                    url: `/admin/filemanager?type=${type}`,
                    title: 'File Manager',
                    width: x * 0.8,
                    height: y * 0.8,
                    onMessage: (api, message) => {
                        callback(message.content);
                    }
                });
            }
        });
    });
});
</script>
@endpush
```

## 📂 Настройка прав доступа

### 1. Создайте Permissions

```bash
php artisan moonshine:policy Page
php artisan moonshine:policy Service
# ... и так для всех ресурсов
```

### 2. Обновите Policies

В `app/Policies/PagePolicy.php`:

```php
public function viewAny($user): bool
{
    return true;
}

public function view($user, $item): bool
{
    return true;
}

// Добавьте остальные методы create, update, delete
```

### 3. Обновите Resources для использования Policies

Добавьте в каждый Resource:

```php
protected function getPolicy(): ?PolicyContract
{
    return new PagePolicy();
}
```

## 🎯 Требования к завершению

### ✅ Успешно создано:

1. **Все 8 MoonShine Resources**:
   - ✅ PageResource - управление статическими страницами
   - ✅ ServiceResource - иерархические услуги
   - ✅ ProjectCategoryResource - категории проектов
   - ✅ ProjectResource - проекты с вкладками
   - ✅ ProjectImageResource - изображения проектов
   - ✅ BlockResource - переиспользуемые блоки
   - ✅ SettingResource - настройки сайта
   - ✅ LeadResource - заявки с быстрыми действиями

2. **Файловая система**:
   - ✅ Все File поля настроены с правильными путями
   - ✅ Автоматическая очистка старых файлов
   - ✅ Валидация типов файлов

3. **Бизнес-логика**:
   - ✅ Slug поля с автогенерацией
   - ✅ Сортировка и фильтры
   - ✅ Вкладки в формах
   - ✅ Бейджи для статусов
   - ✅ Быстрые действия (actions)

### ⚠️ Необходимо выполнить вручную:

1. **Установить зависимости**:
   ```bash
   composer require tinymce/tinymce unisharp/laravel-filemanager
   ```

2. **Опубликовать FileManager**:
   ```bash
   php artisan vendor:publish --tag=lfm_config
   php artisan vendor:publish --tag=lfm_public
   ```

3. **Настроить TinyMCE интеграцию** (см. инструкции выше)

4. **Создать storage symlink**:
   ```bash
   php artisan storage:link
   ```

5. **Создать админ-пользователя** (если еще не создан):
   ```bash
   php artisan moonshine:user
   ```

6. **Настроить права доступа** с помощью Policies

## 🚀 Запуск приложения

После установки зависимостей:

```bash
# Запуск сервера
php artisan serve

# Перейдите в админ-панель
http://127.0.0.1:8000/admin

# Логин: ваш email
# Пароль: ваш пароль
```

## 🔍 Тестирование функциональности

### Проверьте каждый Resource:

1. **PageResource**: создайте страницу, проверьте TinyMCE редактор
2. **ServiceResource**: создайте иерархию услуг (родительские/дочерние)
3. **ProjectCategoryResource**: создайте категории (House, Sauna)
4. **ProjectResource**: создайте проект, загрузите изображения
5. **BlockResource**: создайте блок, проверьте макрос @block()
6. **SettingResource**: настройте параметры сайта
7. **LeadResource**: протестируйте статусы и быстрые действия

### Проверьте загрузку файлов:
- Публичный диск настроен в `config/moonshine.php`
- Пути: `public/storage/services`, `public/storage/projects`, и т.д.
- Проверьте через TinyMCE FileManager

## 📞 Поддержка

Если возникнут проблемы:

1. Проверьте логи: `storage/logs/laravel.log`
2. Проверьте права на директории: `chmod -R 775 storage public`
3. Убедитесь, что storage symlink создан: `ls -la public/storage`

---

**ВСЕ MOONSHINE RESOURCES УСПЕШНО СОЗДАНЫ! 🎉**

Вам осталось только установить зависимости и настроить TinyMCE + FileManager.