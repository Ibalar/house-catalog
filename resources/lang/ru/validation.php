<?php

declare(strict_types=1);

return [
    'accepted' => 'Поле :attribute должно быть принято.',
    'accepted_if' => 'Поле :attribute должно быть принято, когда :other равно :value.',
    'active_url' => 'Поле :attribute не является действительным URL.',
    'after' => 'В поле :attribute должна быть дата после :date.',
    'after_or_equal' => 'В поле :attribute должна быть дата после или равная :date.',
    'alpha' => 'Поле :attribute может содержать только буквы.',
    'alpha_dash' => 'Поле :attribute может содержать только буквы, цифры, дефис и подчёркивание.',
    'alpha_num' => 'Поле :attribute может содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',

    'before' => 'В поле :attribute должна быть дата до :date.',
    'before_or_equal' => 'В поле :attribute должна быть дата до или равная :date.',
    'between' => [
        'numeric' => 'Значение :attribute должно быть от :min до :max.',
        'file' => 'Размер файла :attribute должен быть от :min до :max Килобайт.',
        'string' => 'Строка :attribute должна быть от :min до :max символов.',
        'array' => 'Количество элементов :attribute должно быть от :min до :max.',
    ],
    'boolean' => 'Поле :attribute должно иметь значение логического типа.',

    'confirmed' => 'Поле :attribute не совпадает с подтверждением.',

    'date' => 'Поле :attribute не является датой.',
    'date_equals' => 'Поле :attribute должно быть датой равной :date.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'decimal' => 'Поле :attribute должно содержать :decimal знаков после запятой.',
    'declined' => 'Поле :attribute должно быть отклонено.',
    'declined_if' => 'Поле :attribute должно быть отклонено, когда :other равно :value.',
    'different' => 'Поля :attribute и :other должны различаться.',
    'digits' => 'Длина цифрового поля :attribute должна быть :digits.',
    'digits_between' => 'Длина цифрового поля :attribute должна быть между :min и :max.',
    'dimensions' => 'Поле :attribute имеет недопустимые размеры изображения.',
    'distinct' => 'Поле :attribute содержит повторяющееся значение.',

    'email' => 'Поле :attribute должно содержать корректный email.',

    'exists' => 'Выбранное значение :attribute некорректно.',

    'file' => 'Поле :attribute должно быть файлом.',
    'filled' => 'Поле :attribute обязательно для заполнения.',

    'gt' => [
        'numeric' => 'Поле :attribute должно быть больше :value.',
        'file' => 'Размер файла :attribute должен быть больше :value Килобайт.',
        'string' => 'Длина строки :attribute должна быть больше :value символов.',
        'array' => 'Количество элементов :attribute должно быть больше :value.',
    ],
    'gte' => [
        'numeric' => 'Поле :attribute должно быть больше или равно :value.',
        'file' => 'Размер файла :attribute должен быть больше или равен :value Килобайт.',
        'string' => 'Длина строки :attribute должна быть больше или равна :value символов.',
        'array' => 'Количество элементов :attribute должно быть больше или равно :value.',
    ],

    'image' => 'Поле :attribute должно быть изображением.',
    'in' => 'Выбранное значение для :attribute некорректно.',
    'integer' => 'Поле :attribute должно быть целым числом.',

    'json' => 'Поле :attribute должно быть JSON строкой.',

    'lt' => [
        'numeric' => 'Поле :attribute должно быть меньше :value.',
        'file' => 'Размер файла :attribute должен быть меньше :value Килобайт.',
        'string' => 'Длина строки :attribute должна быть меньше :value символов.',
        'array' => 'Количество элементов :attribute должно быть меньше :value.',
    ],
    'lte' => [
        'numeric' => 'Поле :attribute должно быть меньше или равно :value.',
        'file' => 'Размер файла :attribute должен быть меньше или равен :value Килобайт.',
        'string' => 'Длина строки :attribute должна быть меньше или равна :value символов.',
        'array' => 'Количество элементов :attribute должно быть меньше или равно :value.',
    ],

    'max' => [
        'numeric' => 'Поле :attribute не может быть больше :max.',
        'file' => 'Размер файла :attribute не может быть больше :max Килобайт.',
        'string' => 'Поле :attribute не может быть больше :max символов.',
        'array' => 'Количество элементов :attribute не может быть больше :max.',
    ],
    'min' => [
        'numeric' => 'Поле :attribute должно быть минимум :min.',
        'file' => 'Размер файла :attribute должен быть не меньше :min Килобайт.',
        'string' => 'Поле :attribute должно содержать минимум :min символов.',
        'array' => 'Количество элементов :attribute должно быть не меньше :min.',
    ],

    'numeric' => 'Поле :attribute должно быть числом.',

    'present' => 'Поле :attribute должно присутствовать.',

    'regex' => 'Формат поля :attribute некорректен.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'required_if' => 'Поле :attribute обязательно для заполнения, когда :other равно :value.',
    'required_unless' => 'Поле :attribute обязательно для заполнения, если :other не равно :value.',
    'required_with' => 'Поле :attribute обязательно для заполнения, когда присутствует :values.',
    'required_with_all' => 'Поле :attribute обязательно для заполнения, когда присутствуют :values.',
    'required_without' => 'Поле :attribute обязательно для заполнения, когда отсутствует :values.',
    'required_without_all' => 'Поле :attribute обязательно для заполнения, когда отсутствуют :values.',

    'same' => 'Значения полей :attribute и :other должны совпадать.',
    'size' => [
        'numeric' => 'Поле :attribute должно быть равным :size.',
        'file' => 'Размер файла :attribute должен быть равен :size Килобайт.',
        'string' => 'Длина строки :attribute должна быть равной :size символов.',
        'array' => 'Количество элементов :attribute должно быть равным :size.',
    ],
    'string' => 'Поле :attribute должно быть строкой.',

    'unique' => ':attribute уже существует в системе.',
    'url' => 'Формат URL для :attribute некорректен.',

    // Custom rules
    'phone_format' => 'Формат телефона некорректен.',
    'csv_format' => 'Формат CSV некорректен.',

    'attributes' => require __DIR__ . '/attributes.php',
];
