<?php

return [
    'accepted' => 'Поле :attribute должно быть принято.',
    'array' => 'Поле :attribute должно быть массивом.',
    'before' => 'Поле :attribute должно быть датой до :date.',
    'boolean' => 'Поле :attribute должно быть истинным или ложным.',
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'current_password' => 'Неверный пароль.',
    'date' => 'Поле :attribute не является корректной датой.',
    'email' => 'Поле :attribute должно быть корректным адресом эл. почты.',
    'exists' => 'Выбранное значение :attribute не существует.',
    'in' => 'Выбранное значение :attribute некорректно.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'max' => [
        'array' => 'Поле :attribute не может содержать более :max элементов.',
        'numeric' => 'Поле :attribute не может быть больше :max.',
        'string' => 'Поле :attribute не может быть длиннее :max символов.',
    ],
    'min' => [
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
        'numeric' => 'Поле :attribute должно быть не меньше :min.',
        'string' => 'Поле :attribute должно быть не короче :min символов.',
    ],
    'numeric' => 'Поле :attribute должно быть числом.',
    'regex' => 'Поле :attribute имеет неверный формат.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'string' => 'Поле :attribute должно быть строкой.',
    'unique' => 'Такое значение поля :attribute уже используется.',
    'url' => 'Поле :attribute должно быть корректной ссылкой.',

    'password' => [
        'letters' => 'Поле :attribute должно содержать хотя бы одну букву.',
        'mixed' => 'Поле :attribute должно содержать хотя бы одну заглавную и одну строчную букву.',
        'numbers' => 'Поле :attribute должно содержать хотя бы одну цифру.',
        'symbols' => 'Поле :attribute должно содержать хотя бы один символ.',
        'uncompromised' => 'Это значение :attribute обнаружено в утечке данных. Выберите другое.',
    ],

    'attributes' => [
        'name' => 'имя',
        'surname' => 'фамилия',
        'phone' => 'номер телефона',
        'birth_date' => 'дата рождения',
        'email' => 'эл. почта',
        'password' => 'пароль',
        'current_password' => 'текущий пароль',
        'message' => 'сообщение',
        'subject' => 'тема',
        'code' => 'код',
        'quantity' => 'количество',
        'product_id' => 'товар',
    ],
];
